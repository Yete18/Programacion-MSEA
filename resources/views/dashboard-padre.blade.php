<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSEA - Seguimiento Familiar</title>
  <link rel="stylesheet" href="{{ asset('css/dashboard-padre.css') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&family=Baloo+2:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="{{ asset('assets/img/logo.jpeg') }}" alt="MSEA" class="s-logo-img" />
      <span class="s-logo-text">MSEA</span>
    </div>

    <div class="sidebar-profile">
      <div class="s-avatar">F</div>
      <div>
        <p class="s-name">{{ trim(($padre->nombres ?? 'Familia').' '.($padre->apellido_paterno ?? '')) }}</p>
        <p class="s-role">Seguimiento familiar</p>
      </div>
    </div>

    <div class="s-quick-stats">
      <div class="s-stat">
        <strong>{{ $estudiantes->count() }}</strong>
        <span>Estudiantes</span>
      </div>
      <div class="s-stat">
        <strong>{{ $estudiantes->sum('tareas_count') ?: $estudiantes->sum(fn ($hijo) => count($hijo['tareas'] ?? [])) }}</strong>
        <span>Tareas</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <a href="#resumen" class="nav-item active"><span>Inicio</span></a>
      <a href="#practica" class="nav-item"><span>Practica</span></a>
      <a href="#tareas" class="nav-item"><span>Tareas</span></a>
      <a href="#logros" class="nav-item"><span>Logros</span></a>
    </nav>

    <form action="{{ url('/logout') }}" method="POST">
      @csrf
      <button class="sidebar-logout" type="submit">Cerrar sesion</button>
    </form>
  </aside>

  <div class="content-area">
    <header class="dash-header">
      <div>
        <p class="eyebrow">Familia MSEA</p>
        <h1 class="greeting-title">Seguimiento de estudiantes</h1>
        <p class="greeting-sub">Acompanamiento academico, practica, tareas y logros.</p>
      </div>
      <span class="header-pill">{{ $estudiantes->count() }} estudiante(s)</span>
    </header>

    <main class="dash-main" id="resumen">
      @if($estudiantes->isNotEmpty())
        <div class="hijos-selector">
          @foreach($estudiantes as $index => $hijo)
            <button class="hijo-pill {{ $index === 0 ? 'active' : '' }}" data-hijo-id="{{ $hijo['id'] }}">
              {{ explode(' ', $hijo['nombre'])[0] }}
            </button>
          @endforeach
        </div>
      @endif

      @forelse($estudiantes as $index => $hijo)
        <section class="child-section {{ $index > 0 ? 'hidden-child' : '' }}" id="hijo-section-{{ $hijo['id'] }}">
          <div class="content-grid">
            <aside class="profile-card card-padre">
              <div class="child-avatar-wrap">
                @if($hijo['foto'])
                  <img src="{{ $hijo['foto'] }}" alt="Avatar" class="child-avatar-img" />
                @else
                  Est
                @endif
              </div>
              <h2 class="child-name">{{ $hijo['nombre'] }}</h2>
              <p class="child-instrument">{{ $hijo['instrumentos'] }}</p>

              <div class="pills-grid">
                <div class="pill-stat orange">
                  <span class="pill-stat-val">{{ $hijo['racha'] }}</span>
                  <span class="pill-stat-lbl">Racha dias</span>
                </div>
                <div class="pill-stat green">
                  <span class="pill-stat-val">#{{ $hijo['posicion'] ?: '-' }}</span>
                  <span class="pill-stat-lbl">Ranking</span>
                </div>
                <div class="pill-stat purple">
                  <span class="pill-stat-val">{{ $hijo['nivel'] }}</span>
                  <span class="pill-stat-lbl">Nivel</span>
                </div>
                <div class="pill-stat blue">
                  <span class="pill-stat-val">{{ $hijo['total_minutos'] }}m</span>
                  <span class="pill-stat-lbl">Practica</span>
                </div>
              </div>
            </aside>

            <div class="report-stack">
              <section class="card-padre" id="practica">
                <h3 class="card-title-padre">Minutos practicados en los ultimos 7 dias</h3>
                <div class="chart-container-padre">
                  @foreach($hijo['semana_practica'] as $dia)
                    <div class="chart-bar-wrap">
                      <div class="chart-bar" style="height: {{ min(100, max(4, $dia['minutos'] * 3.5)) }}%">
                        @if($dia['minutos'] > 0)
                          <span class="chart-bar-val">{{ $dia['minutos'] }}m</span>
                        @endif
                      </div>
                      <span class="chart-day">{{ $dia['dia'] }}</span>
                    </div>
                  @endforeach
                </div>
              </section>

              <section class="card-padre" id="tareas">
                <h3 class="card-title-padre">Estado de tareas y calificaciones</h3>
                <ul>
                  @forelse($hijo['tareas'] as $tarea)
                    <li class="task-item-padre">
                      <div class="task-header-padre">
                        <span class="task-title-padre">{{ $tarea['titulo'] }}</span>
                        @if($tarea['estado'] === 'calificada')
                          <span class="task-score">{{ $tarea['calificacion'] }}/100</span>
                        @else
                          <span class="task-badge-padre {{ $tarea['estado'] }}">
                            {{ $tarea['estado'] === 'pendiente' ? 'Pendiente' : 'Entregada' }}
                          </span>
                        @endif
                      </div>
                      <div class="task-meta-padre">Vence: {{ $tarea['limite'] }} - +{{ $tarea['xp'] }} XP</div>
                      @if($tarea['estado'] === 'calificada' && $tarea['comentario_profesor'])
                        <div class="teacher-feedback">
                          <span class="teacher-name-padre">Retroalimentacion del profesor</span>
                          "{{ $tarea['comentario_profesor'] }}"
                        </div>
                      @endif
                    </li>
                  @empty
                    <div class="empty-state-padre">No tiene tareas asignadas todavia.</div>
                  @endforelse
                </ul>
              </section>

              <section class="card-padre" id="logros">
                <h3 class="card-title-padre">Logros e insignias desbloqueadas</h3>
                <div class="logros-grid-padre">
                  @forelse($hijo['logros'] as $logro)
                    <article class="logro-card-padre">
                      <span class="logro-badge-icon">OK</span>
                      <p class="logro-name-padre">{{ $logro['nombre'] }}</p>
                      <span class="logro-date-padre">{{ $logro['desbloqueado_at'] }}</span>
                    </article>
                  @empty
                    <div class="empty-state-padre" style="grid-column:1/-1;">Aun no ha desbloqueado logros.</div>
                  @endforelse
                </div>
              </section>
            </div>
          </div>
        </section>
      @empty
        <section class="card-padre empty-state-padre">
          <h3 class="card-title-padre">Sin estudiantes vinculados</h3>
          <p>Solicita al administrador de MSEA que vincule a tu hijo con tu cuenta de correo.</p>
        </section>
      @endforelse
    </main>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const pills = document.querySelectorAll('.hijo-pill');
    const sections = document.querySelectorAll('.child-section');

    pills.forEach(pill => {
      pill.addEventListener('click', () => {
        pills.forEach(p => p.classList.remove('active'));
        pill.classList.add('active');

        sections.forEach(section => {
          section.classList.toggle('hidden-child', section.id !== `hijo-section-${pill.dataset.hijoId}`);
        });
      });
    });
  });
</script>
</body>
</html>
