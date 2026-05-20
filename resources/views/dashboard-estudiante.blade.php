<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>MSEA — Mi Dashboard</title>
  <link rel="stylesheet" href="{{asset ('css/dashboard-estudiante.css') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&family=Baloo+2:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>

<div class="app-layout">

  <!-- ══════════════════════════════
       SIDEBAR
  ══════════════════════════════ -->
  <aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
      <img src="assets/img/logo.jpeg" alt="MSEA" class="s-logo-img" />
      <span class="s-logo-text">MSEA</span>
    </div>

    <!-- Perfil del usuario -->
    <div class="sidebar-profile" id="sidebar-profile-btn" title="Ver mi perfil">
      <div class="s-avatar">
        <img src="" alt="Avatar" class="s-avatar-img" id="s-avatar-img" />
        <div class="s-avatar-fallback" id="s-avatar-fallback">🎓</div>
        <div class="s-streak-badge" id="s-streak-badge">🔥5</div>
      </div>
      <div class="s-profile-info">
        <p class="s-name" id="s-name">Carlos Mamani</p>
        <p class="s-role" id="s-role">🎻 Violín · Nivel 3</p>
      </div>
      <span class="s-profile-arrow">›</span>
    </div>

    <!-- Barra de XP mini -->
    <div class="s-xp-bar">
      <div class="s-xp-labels">
        <span>XP</span>
        <span id="s-xp-text">320 / 500</span>
      </div>
      <div class="s-xp-track">
        <div class="s-xp-fill" id="s-xp-fill" style="width: 64%"></div>
      </div>
    </div>

    <!-- Navegación -->
    <nav class="sidebar-nav">
      <a href="#" class="nav-item active" data-page="inicio">
        <span class="nav-icon">🏠</span>
        <span class="nav-label">Inicio</span>
      </a>
      <a href="#" class="nav-item" data-page="tareas">
        <span class="nav-icon">📋</span>
        <span class="nav-label">Mis tareas</span>
        <span class="nav-badge" id="badge-tareas">3</span>
      </a>
      <a href="#" class="nav-item" data-page="ejercicios">
        <span class="nav-icon">🎯</span>
        <span class="nav-label">Ejercicios</span>
      </a>
      <a href="#" class="nav-item" data-page="progreso">
        <span class="nav-icon">📈</span>
        <span class="nav-label">Mi progreso</span>
      </a>
      <a href="#" class="nav-item" data-page="ranking">
        <span class="nav-icon">🏆</span>
        <span class="nav-label">Ranking</span>
      </a>
      <a href="#" class="nav-item" data-page="logros">
        <span class="nav-icon">⭐</span>
        <span class="nav-label">Logros</span>
      </a>
      <a href="#" class="nav-item" data-page="perfil">
        <span class="nav-icon">👤</span>
        <span class="nav-label">Mi perfil</span>
      </a>
    </nav>

    <!-- Botón cerrar sesión -->
    <form action="{{ url('/logout') }}" method="POST">
      @csrf
      <button type="submit" class="sidebar-logout">
        <span>🚪</span> Cerrar sesión
      </button>
    </form>

  </aside>

  <!-- ══════════════════════════════
       ÁREA DE CONTENIDO
  ══════════════════════════════ -->
  <div class="content-area">

    <!-- HEADER -->
    <header class="dash-header">
      <button class="hamburger" id="hamburger" aria-label="Abrir menú">
        <span></span><span></span><span></span>
      </button>

      <div class="header-greeting">
        <h1 class="greeting-title" id="greeting-title">¡Buenos días, Carlos! 👋</h1>
        <p class="greeting-sub" id="greeting-sub">Tienes <strong>3 tareas</strong> pendientes hoy</p>
      </div>

      <div class="header-actions">
        <div class="streak-pill">
          <span class="streak-fire">🔥</span>
          <span class="streak-count" id="streak-count">5</span>
          <span class="streak-label">días</span>
        </div>
        <div class="points-pill">
          <span>⭐</span>
          <span id="header-points">1,240</span>
        </div>

        <!-- Botón notificaciones -->
        <div class="notif-wrapper" id="notif-wrapper">
          <button class="notif-btn" id="notif-btn" aria-label="Notificaciones">
            🔔
            <span class="notif-dot" id="notif-dot"></span>
          </button>

          <!-- Panel de notificaciones -->
          <div class="notif-panel" id="notif-panel">
            <div class="notif-panel-header">
              <span class="notif-panel-title">🔔 Notificaciones</span>
              <button class="notif-clear-btn" id="notif-clear-btn">Marcar leídas</button>
            </div>
            <ul class="notif-list" id="notif-list">
              <!-- Generado por JS -->
            </ul>
            <div class="notif-panel-footer">
              <a href="#" class="notif-ver-todas">Ver todas las notificaciones →</a>
            </div>
          </div>
        </div>

        <!-- Avatar header (acceso rápido al perfil) -->
        <button class="header-avatar-btn" id="header-avatar-btn" title="Mi perfil">
          <img src="" alt="Yo" class="header-avatar-img" id="header-avatar-img" />
          <span class="header-avatar-fallback" id="header-avatar-fallback">🎓</span>
        </button>
      </div>
    </header>

    <!-- CONTENIDO PRINCIPAL — VISTAS SPA -->
    <main class="dash-main" id="dash-main">

      <!-- ══════════════ VISTA: INICIO ══════════════ -->
      <section class="page-view active" id="view-inicio">

        <div class="row-top">
          <!-- Banner -->
          <div class="card banner-card">
            <div class="banner-text">
              <p class="banner-tag">🎯 Objetivo del día</p>
              <h2 class="banner-title">¡Completa 2 ejercicios<br/>y gana <span class="accent">+50 XP</span>!</h2>
              <div class="banner-progress">
                <div class="bp-labels">
                  <span>Progreso de hoy</span>
                  <span id="banner-pct">1 / 2 ejercicios</span>
                </div>
                <div class="bp-track">
                  <div class="bp-fill" id="bp-fill" style="width: 50%"></div>
                </div>
              </div>
              <a href="#" class="btn-primary-sm" id="btn-ejercicio-rapido">
                Empezar ejercicio 🚀
              </a>
            </div>
            <div class="banner-illustration">
              <img src="assets/img/paganini.png" alt="Música" class="banner-img" />
              <div class="banner-img-fallback">🎻</div>
            </div>
          </div>

          <!-- Stats -->
          <div class="stats-column">
            <div class="card stat-card">
              <span class="stat-icon">⭐</span>
              <div class="stat-info">
                <p class="stat-value" id="stat-points">1,240</p>
                <p class="stat-label">Puntos totales</p>
              </div>
            </div>
            <div class="card stat-card">
              <span class="stat-icon">🔥</span>
              <div class="stat-info">
                <p class="stat-value" id="stat-streak">5</p>
                <p class="stat-label">Días de racha</p>
              </div>
            </div>
            <div class="card stat-card">
              <span class="stat-icon">🎯</span>
              <div class="stat-info">
                <p class="stat-value" id="stat-ejercicios">12</p>
                <p class="stat-label">Ejercicios hechos</p>
              </div>
            </div>
            <div class="card stat-card">
              <span class="stat-icon">🏅</span>
              <div class="stat-info">
                <p class="stat-value" id="stat-nivel">#4</p>
                <p class="stat-label">En el ranking</p>
              </div>
            </div>
          </div>
        </div>

        <div class="row-bottom">
          <!-- Tareas -->
          <div class="card tareas-card">
            <div class="card-header">
              <h3 class="card-title">📋 Tareas pendientes</h3>
              <a href="#" class="card-link nav-link-btn" data-page="tareas">Ver todas →</a>
            </div>
            <ul class="tareas-list" id="tareas-list"></ul>
          </div>

          <!-- Ejercicio del día -->
          <div class="card ejercicio-card">
            <div class="card-header">
              <h3 class="card-title">🎯 Ejercicio del día</h3>
              <span class="ejercicio-badge">+30 XP</span>
            </div>
            <div class="ejercicio-body">
              <div class="ejercicio-icon">🎵</div>
              <p class="ejercicio-nombre">Escala de Do Mayor</p>
              <p class="ejercicio-desc">Toca la escala de Do Mayor en posición de primera mano. Tempo: ♩= 60</p>
              <div class="ejercicio-meta">
                <span class="meta-chip">🎻 Violín</span>
                <span class="meta-chip">⏱️ 5 min</span>
                <span class="meta-chip">🌱 Básico</span>
              </div>
              <button class="btn-ejercicio nav-link-btn" data-page="ejercicios">
                ¡Empezar! 🚀
              </button>
            </div>
          </div>

          <!-- Mini ranking -->
          <div class="card ranking-card">
            <div class="card-header">
              <h3 class="card-title">🏆 Ranking semanal</h3>
              <a href="#" class="card-link nav-link-btn" data-page="ranking">Ver completo →</a>
            </div>
            <ul class="ranking-list" id="ranking-list"></ul>
          </div>
        </div>

        <div class="row-logros">
          <div class="card logros-card">
            <div class="card-header">
              <h3 class="card-title">⭐ Mis logros recientes</h3>
              <a href="#" class="card-link nav-link-btn" data-page="logros">Ver todos →</a>
            </div>
            <div class="logros-grid" id="logros-grid"></div>
          </div>
        </div>

      </section>

      <!-- ══════════════ VISTA: TAREAS ══════════════ -->
      <section class="page-view" id="view-tareas">
        <div class="page-header-inner">
          <h2 class="page-inner-title">📋 Mis tareas</h2>
          <p class="page-inner-sub">Aquí están todas tus tareas asignadas por tu profesor</p>
        </div>

        <div class="tareas-full-grid" id="tareas-full-grid">
          <!-- Generado por JS -->
        </div>
      </section>

      <!-- ══════════════ VISTA: EJERCICIOS ══════════════ -->
      <section class="page-view" id="view-ejercicios">
        <div class="page-header-inner">
          <h2 class="page-inner-title">🎯 Ejercicios</h2>
          <p class="page-inner-sub">Practica y gana XP con cada ejercicio completado</p>
        </div>

        <div class="ejercicios-filtros">
          <button class="filtro-btn active" data-filtro="todos">Todos</button>
          <button class="filtro-btn" data-filtro="ritmo">🥁 Ritmo</button>
          <button class="filtro-btn" data-filtro="escala">🎵 Escalas</button>
          <button class="filtro-btn" data-filtro="teoria">📖 Teoría</button>
        </div>

        <div class="ejercicios-grid" id="ejercicios-grid">
          <!-- Generado por JS -->
        </div>
      </section>

      <!-- ══════════════ VISTA: PROGRESO ══════════════ -->
      <section class="page-view" id="view-progreso">
        <div class="page-header-inner">
          <h2 class="page-inner-title">📈 Mi progreso</h2>
          <p class="page-inner-sub">Mira cuánto has avanzado esta semana</p>
        </div>

        <div class="progreso-grid">
          <!-- Semana actual -->
          <div class="card progreso-semana-card">
            <div class="card-header">
              <h3 class="card-title">📅 Esta semana</h3>
            </div>
            <div class="dias-semana" id="dias-semana">
              <!-- Generado por JS -->
            </div>
          </div>

          <!-- Stats detalladas -->
          <div class="card progreso-stats-card">
            <div class="card-header">
              <h3 class="card-title">📊 Mis estadísticas</h3>
            </div>
            <div class="progreso-stats-list">
              <div class="pstat-item">
                <span class="pstat-icon">🎯</span>
                <div class="pstat-info">
                  <p class="pstat-label">Ejercicios completados</p>
                  <div class="pstat-bar-wrap">
                    <div class="pstat-bar" style="width:75%"></div>
                  </div>
                </div>
                <span class="pstat-val">12 / 16</span>
              </div>
              <div class="pstat-item">
                <span class="pstat-icon">⭐</span>
                <div class="pstat-info">
                  <p class="pstat-label">XP ganada esta semana</p>
                  <div class="pstat-bar-wrap">
                    <div class="pstat-bar" style="width:64%"></div>
                  </div>
                </div>
                <span class="pstat-val">320 XP</span>
              </div>
              <div class="pstat-item">
                <span class="pstat-icon">📋</span>
                <div class="pstat-info">
                  <p class="pstat-label">Tareas entregadas</p>
                  <div class="pstat-bar-wrap">
                    <div class="pstat-bar" style="width:50%"></div>
                  </div>
                </div>
                <span class="pstat-val">2 / 4</span>
              </div>
              <div class="pstat-item">
                <span class="pstat-icon">🔥</span>
                <div class="pstat-info">
                  <p class="pstat-label">Racha actual</p>
                  <div class="pstat-bar-wrap">
                    <div class="pstat-bar" style="width:71%"></div>
                  </div>
                </div>
                <span class="pstat-val">5 días</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Historial de actividad -->
        <div class="card actividad-card">
          <div class="card-header">
            <h3 class="card-title">🕐 Actividad reciente</h3>
          </div>
          <ul class="actividad-list" id="actividad-list">
            <!-- Generado por JS -->
          </ul>
        </div>
      </section>

      <!-- ══════════════ VISTA: RANKING ══════════════ -->
      <section class="page-view" id="view-ranking">
        <div class="page-header-inner">
          <h2 class="page-inner-title">🏆 Ranking semanal</h2>
          <p class="page-inner-sub">¡Sube de posición completando ejercicios y tareas!</p>
        </div>

        <div class="ranking-full-wrap">
          <div class="podio" id="podio">
            <!-- Generado por JS -->
          </div>
          <div class="card ranking-full-card">
            <ul class="ranking-full-list" id="ranking-full-list">
              <!-- Generado por JS -->
            </ul>
          </div>
        </div>
      </section>

      <!-- ══════════════ VISTA: LOGROS ══════════════ -->
      <section class="page-view" id="view-logros">
        <div class="page-header-inner">
          <h2 class="page-inner-title">⭐ Mis logros</h2>
          <p class="page-inner-sub">¡Desbloquea todos los logros practicando cada día!</p>
        </div>

        <div class="logros-stats-row">
          <div class="card logro-stat-mini">
            <span class="lsm-emoji">🏅</span>
            <p class="lsm-val">5</p>
            <p class="lsm-label">Desbloqueados</p>
          </div>
          <div class="card logro-stat-mini">
            <span class="lsm-emoji">🔒</span>
            <p class="lsm-val">3</p>
            <p class="lsm-label">Por desbloquear</p>
          </div>
          <div class="card logro-stat-mini">
            <span class="lsm-emoji">📊</span>
            <p class="lsm-val">63%</p>
            <p class="lsm-label">Completado</p>
          </div>
        </div>

        <div class="card">
          <div class="logros-grid-full" id="logros-grid-full"></div>
        </div>
      </section>

      <!-- ══════════════ VISTA: PERFIL ══════════════ -->
      <section class="page-view" id="view-perfil">
        <div class="page-header-inner">
          <h2 class="page-inner-title">👤 Mi perfil</h2>
          <p class="page-inner-sub">Administra tu información y foto de perfil</p>
        </div>

        <div class="perfil-layout">

          <!-- Tarjeta de avatar -->
          <div class="card perfil-avatar-card">
            <div class="perfil-avatar-wrap">
              <div class="perfil-avatar-circle" id="perfil-avatar-circle">
                <img src="" alt="Avatar" class="perfil-avatar-img" id="perfil-avatar-img" />
                <div class="perfil-avatar-fallback" id="perfil-avatar-fallback">🎓</div>
                <button class="perfil-avatar-overlay" id="perfil-avatar-overlay" title="Cambiar foto">
                  <span class="pao-icon">📷</span>
                  <span class="pao-text">Cambiar foto</span>
                </button>
              </div>
              <!-- Input oculto para subir imagen -->
              <input type="file" id="avatar-file-input" accept="image/*" style="display:none" />
            </div>

            <h3 class="perfil-nombre-big" id="perfil-nombre-big">Carlos Mamani</h3>
            <p class="perfil-rol-big" id="perfil-rol-big">🎻 Violín · Nivel 3</p>

            <!-- Racha y puntos visuales -->
            <div class="perfil-badges-row">
              <div class="perfil-badge-item naranja">
                <span class="pbi-val" id="perfil-racha-val">5</span>
                <span class="pbi-label">🔥 Racha</span>
              </div>
              <div class="perfil-badge-item verde">
                <span class="pbi-val" id="perfil-pts-val">1,240</span>
                <span class="pbi-label">⭐ Puntos</span>
              </div>
              <div class="perfil-badge-item azul">
                <span class="pbi-val" id="perfil-rank-val">#4</span>
                <span class="pbi-label">🏆 Ranking</span>
              </div>
            </div>

            <!-- Barra XP grande -->
            <div class="perfil-xp-wrap">
              <div class="perfil-xp-labels">
                <span>Nivel 3</span>
                <span id="perfil-xp-text">320 / 500 XP</span>
                <span>Nivel 4</span>
              </div>
              <div class="perfil-xp-track">
                <div class="perfil-xp-fill" id="perfil-xp-fill" style="width: 64%"></div>
              </div>
              <p class="perfil-xp-hint">¡Te faltan <strong>180 XP</strong> para subir de nivel!</p>
            </div>
          </div>

          <!-- Información del estudiante -->
          <div class="perfil-info-col">

            <div class="card perfil-info-card">
              <div class="card-header">
                <h3 class="card-title">📋 Información personal</h3>
                <button class="btn-edit-info" id="btn-edit-info">✏️ Editar</button>
              </div>

              <!-- Modo vista -->
              <div class="info-view" id="info-view">
                <div class="info-row">
                  <span class="info-label">Nombre completo</span>
                  <span class="info-val" id="info-nombre">Carlos Mamani</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Correo electrónico</span>
                  <span class="info-val" id="info-email">carlos.mamani@msea.edu.bo</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Carnet de identidad</span>
                  <span class="info-val" id="info-ci">Sin registrar</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Apellido paterno</span>
                  <span class="info-val" id="info-apellido-paterno">Sin registrar</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Apellido materno</span>
                  <span class="info-val" id="info-apellido-materno">Sin registrar</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Celular</span>
                  <span class="info-val" id="info-celular">Sin registrar</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Direccion</span>
                  <span class="info-val" id="info-direccion">Sin registrar</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Fecha de nacimiento</span>
                  <span class="info-val" id="info-fecha-nacimiento">Sin registrar</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Instrumento</span>
                  <span class="info-val" id="info-instrumento">🎻 Violín</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Nivel</span>
                  <span class="info-val" id="info-nivel">Nivel 3 — Intermedio</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Sede</span>
                  <span class="info-val" id="info-sede">El Alto — Sede Central</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Profesor asignado</span>
                  <span class="info-val" id="info-profesor">Prof. Ana Quispe</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Miembro desde</span>
                  <span class="info-val" id="info-miembro-desde">Enero 2024</span>
                </div>
              </div>

              <!-- Modo edición -->
              <div class="info-edit hidden" id="info-edit">
                <div class="edit-field">
                  <label class="edit-label">Nombres</label>
                  <input type="text" class="edit-input" id="edit-nombres" value="Carlos" />
                </div>
                <div class="edit-field">
                  <label class="edit-label">Apellido paterno</label>
                  <input type="text" class="edit-input" id="edit-apellido-paterno" value="Mamani" />
                </div>
                <div class="edit-field">
                  <label class="edit-label">Apellido materno</label>
                  <input type="text" class="edit-input" id="edit-apellido-materno" />
                </div>
                <div class="edit-field">
                  <label class="edit-label">Correo electrónico</label>
                  <input type="email" class="edit-input" id="edit-email" value="carlos.mamani@msea.edu.bo" />
                </div>
                <div class="edit-field">
                  <label class="edit-label">Carnet de identidad</label>
                  <input type="text" class="edit-input" id="edit-ci" />
                </div>
                <div class="edit-field">
                  <label class="edit-label">Celular</label>
                  <input type="text" class="edit-input" id="edit-celular" />
                </div>
                <div class="edit-field">
                  <label class="edit-label">Direccion</label>
                  <input type="text" class="edit-input" id="edit-direccion" />
                </div>
                <div class="edit-field">
                  <label class="edit-label">Fecha de nacimiento</label>
                  <input type="date" class="edit-input" id="edit-fecha-nacimiento" />
                </div>
                <div class="edit-actions">
                  <button class="btn-save-info" id="btn-save-info">💾 Guardar cambios</button>
                  <button class="btn-cancel-info" id="btn-cancel-info">Cancelar</button>
                </div>
              </div>
            </div>

            <!-- Logros recientes en perfil -->
            <div class="card perfil-logros-mini">
              <div class="card-header">
                <h3 class="card-title">⭐ Logros recientes</h3>
                <a href="#" class="card-link nav-link-btn" data-page="logros">Ver todos →</a>
              </div>
              <div class="perfil-logros-row" id="perfil-logros-row">
                <!-- Generado por JS -->
              </div>
            </div>

          </div>
        </div>
      </section>

    </main>
  </div>
</div>

<!-- Overlay para móvil -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<!-- Modal de confirmación de foto -->
<div class="modal-overlay hidden" id="modal-foto">
  <div class="modal-box">
    <p class="modal-title">📷 ¿Usar esta foto?</p>
    <img src="" alt="Preview" class="modal-preview" id="modal-preview" />
    <div class="modal-actions">
      <button class="btn-modal-confirm" id="btn-foto-confirm">✅ Sí, usar esta foto</button>
      <button class="btn-modal-cancel" id="btn-foto-cancel">Cancelar</button>
    </div>
  </div>
</div>

<script>
  window.MSEA_ESTUDIANTE = @json($dashboardData ?? []);
  window.MSEA_RANKING = @json($rankingData ?? []);
</script>
<script src="{{ asset('js/dashboard-estudiante.js') }}"></script>
</body>
</html>
