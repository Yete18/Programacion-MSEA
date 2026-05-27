<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSEA - Panel Director</title>
  <link rel="stylesheet" href="{{ asset('css/dashboard-admin.css') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&family=Baloo+2:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="brand">
      <img src="{{ asset('assets/img/logo.jpeg') }}" alt="MSEA" />
      <span>MSEA</span>
    </div>
    <div class="admin-card">
      <p class="eyebrow">Director</p>
      <h2>{{ $adminData['nombre'] }}</h2>
      <p>{{ $adminData['email'] }}</p>
    </div>
    <nav class="admin-nav">
      <a href="#resumen" class="active">Centralizador</a>
      <a href="#elencos">Elencos y orquestas</a>
      <a href="#actividad">Actividad</a>
      <a href="#profesores">Profesores</a>
      <a href="#estudiantes">Estudiantes</a>
      <a href="#perfil">Perfil director</a>
      <a href="#nuevo-profesor">Registrar profesor</a>
    </nav>
    <form action="{{ url('/logout') }}" method="POST">
      @csrf
      <button class="logout" type="submit">Cerrar sesión</button>
    </form>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <div>
        <p class="eyebrow">Panel administrativo</p>
        <h1>Centralizador general</h1>
      </div>
      <div class="header-actions">
        <a href="#elencos" class="secondary-link">Ver elencos</a>
        <a href="#nuevo-profesor" class="primary-link">Nuevo profesor</a>
      </div>
    </header>

    @if(session('success'))
      <div class="alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
      <div class="alert error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
      <div class="alert error">{{ $errors->first() }}</div>
    @endif

    <section class="stats-grid" id="resumen">
      <div class="stat">
        <span>{{ $adminData['totalProfesores'] }}</span>
        <p>Profesores registrados</p>
      </div>
      <div class="stat">
        <span>{{ $adminData['totalEstudiantes'] }}</span>
        <p>Estudiantes registrados</p>
      </div>
      <div class="stat">
        <span>{{ count($profesoresData) }}</span>
        <p>Perfiles visibles</p>
      </div>
      <div class="stat">
        <span>{{ $adminData['totalElencos'] }}</span>
        <p>Elencos y orquestas</p>
      </div>
      <div class="stat">
        <span>{{ $adminData['totalTareas'] }}</span>
        <p>Tareas enviadas</p>
      </div>
      <div class="stat">
        <span>{{ $adminData['totalPracticas'] }}</span>
        <p>Practicas registradas</p>
      </div>
    </section>

    <section class="panel" id="elencos">
      <div class="section-head">
        <div>
          <h2>Elencos y orquestas</h2>
          <p>Centraliza las agrupaciones inicial, pre juvenil, juvenil y nuevos grupos.</p>
        </div>
      </div>

      <div class="split-grid">
        <div class="course-grid">
          @forelse($elencosData as $elenco)
            <article class="course-card">
              <div class="course-cover {{ strtolower($elenco['tipo']) === 'orquesta' ? 'orchestra' : 'ensemble' }}">
                <span>{{ $elenco['tipo'] }}</span>
              </div>
              <div class="course-body">
                <div>
                  <h3>{{ $elenco['nombre'] }}</h3>
                  <p>{{ $elenco['estudiantes'] }} estudiantes asignados</p>
                </div>
                <details class="inline-editor">
                  <summary>Editar</summary>
                  <form action="{{ url('/dashboard-admin/elencos/'.$elenco['id']) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <label>
                      <span>Nombre</span>
                      <input type="text" name="nombre" value="{{ $elenco['nombre'] }}" required />
                    </label>
                    <label>
                      <span>Tipo</span>
                      <select name="tipo" required>
                        <option value="Orquesta" @selected($elenco['tipo'] === 'Orquesta')>Orquesta</option>
                        <option value="Elenco" @selected($elenco['tipo'] === 'Elenco')>Elenco</option>
                      </select>
                    </label>
                    <button type="submit" class="secondary-btn">Guardar cambios</button>
                  </form>
                  <form action="{{ url('/dashboard-admin/elencos/'.$elenco['id']) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger-btn">Eliminar elenco</button>
                  </form>
                </details>
              </div>
            </article>
          @empty
            <div class="empty-state">
              <h3>Todavia no hay elencos</h3>
              <p>Crea una orquesta o elenco para empezar a organizar estudiantes.</p>
            </div>
          @endforelse
        </div>

        <div class="side-stack">
          <form action="{{ url('/dashboard-admin/elencos') }}" method="POST" class="compact-form">
            @csrf
            <h3>Crear agrupacion</h3>
            <label>
              <span>Nombre</span>
              <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Inicial, Pre Juvenil, Juvenil" required />
            </label>
            <label>
              <span>Tipo</span>
              <select name="tipo" required>
                <option value="Orquesta" @selected(old('tipo') === 'Orquesta')>Orquesta</option>
                <option value="Elenco" @selected(old('tipo') === 'Elenco')>Elenco</option>
              </select>
            </label>
            <button type="submit" class="primary-btn">Guardar elenco</button>
          </form>

          <form action="{{ url('/dashboard-admin/elencos/asignar-estudiante') }}" method="POST" class="compact-form">
            @csrf
            <h3>Asignar estudiante</h3>
            <label>
              <span>Estudiante</span>
              <select name="id_estudiante" required>
                <option value="">Selecciona estudiante</option>
                @foreach($estudiantesData as $estudiante)
                  <option value="{{ $estudiante['id'] }}">{{ $estudiante['nombre'] }} - {{ $estudiante['elenco'] }}</option>
                @endforeach
              </select>
            </label>
            <label>
              <span>Elenco u orquesta</span>
              <select name="id_elenco">
                <option value="">Sin elenco</option>
                @foreach($elencosData as $elenco)
                  <option value="{{ $elenco['id'] }}">{{ $elenco['tipo'] }} - {{ $elenco['nombre'] }}</option>
                @endforeach
              </select>
            </label>
            <button type="submit" class="primary-btn">Asignar</button>
          </form>

          <form action="{{ url('/dashboard-admin/profesores/asignar-estudiante') }}" method="POST" class="compact-form">
            @csrf
            <h3>Asignar profesor</h3>
            <label>
              <span>Estudiante</span>
              <select name="id_estudiante" required>
                <option value="">Selecciona estudiante</option>
                @foreach($estudiantesData as $estudiante)
                  <option value="{{ $estudiante['id'] }}">{{ $estudiante['nombre'] }} - {{ $estudiante['profesor'] }}</option>
                @endforeach
              </select>
            </label>
            <label>
              <span>Profesor particular</span>
              <select name="id_profesor">
                <option value="">Sin profesor</option>
                @foreach($profesoresData as $profesor)
                  <option value="{{ $profesor['id'] }}">{{ $profesor['nombre'] }} - {{ $profesor['especialidad'] }}</option>
                @endforeach
              </select>
            </label>
            <button type="submit" class="primary-btn">Asignar profesor</button>
          </form>
        </div>
      </div>
    </section>

    <section class="panel" id="actividad">
      <div class="section-head">
        <div>
          <h2>Actividad de la plataforma</h2>
          <p>Tareas enviadas por profesores, ejercicios completados y practicas registradas por estudiantes.</p>
        </div>
      </div>

      <div class="activity-grid">
        <div class="activity-list">
          @forelse($actividadData['items'] as $actividad)
            <article class="activity-item">
              <div>
                <span class="activity-type">{{ $actividad['tipo'] }}</span>
                <h3>{{ $actividad['titulo'] }}</h3>
                <p>{{ $actividad['detalle'] }}</p>
              </div>
              <time>{{ $actividad['fecha'] }}</time>
            </article>
          @empty
            <div class="empty-state">
              <h3>Sin actividad registrada</h3>
              <p>Cuando existan tareas, ejercicios o practicas guardadas en la base de datos, apareceran aqui.</p>
            </div>
          @endforelse
        </div>

        <aside class="backend-notes">
          <h3>Pendiente para backend</h3>
          @foreach($actividadData['pendientesBackend'] as $pendiente)
            <p>{{ $pendiente }}</p>
          @endforeach
        </aside>
      </div>
    </section>

    <section class="panel" id="profesores">
      <div class="section-head">
        <div>
          <h2>Profesores</h2>
          <p>Usuarios con acceso al dashboard de profesor.</p>
        </div>
      </div>

      <div class="teacher-card-grid">
        @forelse($profesoresData as $profesor)
          <details class="teacher-card">
            <summary>
              <div>
                <h3>{{ $profesor['nombre'] }}</h3>
                <p>{{ $profesor['especialidad'] }} - {{ $profesor['correo'] }}</p>
              </div>
              <span class="pill">{{ $profesor['estudiantes'] }} alumnos</span>
            </summary>
            <div class="teacher-detail">
              <p><strong>Celular:</strong> {{ $profesor['celular'] }}</p>
              <h4>Estudiantes asignados</h4>
              @forelse($profesor['estudiantesLista'] as $estudiante)
                <article class="student-row">
                  <div>
                    <strong>{{ $estudiante['nombre'] }}</strong>
                    <span>{{ $estudiante['correo'] }}</span>
                  </div>
                  <span class="pill">{{ $estudiante['elenco'] }}</span>
                </article>
              @empty
                <p class="muted-text">Todavia no tiene estudiantes asignados.</p>
              @endforelse
            </div>
          </details>
        @empty
          <div class="empty-state">
            <h3>Todavia no hay profesores registrados</h3>
            <p>Cuando el director registre profesores, apareceran como tarjetas desplegables.</p>
          </div>
        @endforelse
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Profesor</th>
              <th>Correo</th>
              <th>Especialidad</th>
              <th>Celular</th>
              <th>Alumnos</th>
            </tr>
          </thead>
          <tbody>
            @forelse($profesoresData as $profesor)
              <tr>
                <td>
                  <strong>{{ $profesor['nombre'] }}</strong>
                  <small>ID {{ $profesor['id'] }}</small>
                </td>
                <td>{{ $profesor['correo'] }}</td>
                <td>{{ $profesor['especialidad'] }}</td>
                <td>{{ $profesor['celular'] }}</td>
                <td><span class="pill">{{ $profesor['estudiantes'] }}</span></td>
              </tr>
            @empty
              <tr>
                <td colspan="5">Todavía no hay profesores registrados.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel" id="estudiantes">
      <div class="section-head">
        <div>
          <h2>Estudiantes</h2>
          <p>Vista rapida de profesor particular y elenco asignado.</p>
        </div>
      </div>

      <div class="student-admin-grid">
        @forelse($estudiantesData as $estudiante)
          <article class="student-admin-card">
            <div>
              <h3>{{ $estudiante['nombre'] }}</h3>
              <p>{{ $estudiante['correo'] }}</p>
            </div>
            <div class="student-meta">
              <span>{{ $estudiante['elenco'] }}</span>
              <span>{{ $estudiante['profesor'] }}</span>
            </div>
          </article>
        @empty
          <div class="empty-state">
            <h3>Todavia no hay estudiantes</h3>
            <p>Los estudiantes registrados desde el formulario publico apareceran aqui.</p>
          </div>
        @endforelse
      </div>
    </section>

    <section class="panel" id="perfil">
      <div class="section-head">
        <div>
          <h2>Perfil del director</h2>
          <p>Datos personales del super usuario y descripcion de trayectoria institucional.</p>
        </div>
      </div>

      <form action="{{ url('/dashboard-admin/perfil') }}" method="POST" class="teacher-form">
        @csrf
        <div class="field-grid">
          <label>
            <span>Nombres</span>
            <input type="text" name="nombres" value="{{ old('nombres', $directorProfile['nombres']) }}" required />
          </label>
          <label>
            <span>Apellido paterno</span>
            <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $directorProfile['apellido_paterno']) }}" />
          </label>
          <label>
            <span>Apellido materno</span>
            <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $directorProfile['apellido_materno']) }}" />
          </label>
          <label>
            <span>Correo</span>
            <input type="email" name="correo" value="{{ old('correo', $directorProfile['correo']) }}" required />
          </label>
          <label>
            <span>Carnet de identidad</span>
            <input type="text" name="ci" value="{{ old('ci', $directorProfile['ci']) }}" />
          </label>
          <label>
            <span>Celular</span>
            <input type="text" name="celular" value="{{ old('celular', $directorProfile['celular']) }}" />
          </label>
          <label>
            <span>Fecha de nacimiento</span>
            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $directorProfile['fecha_nacimiento']) }}" />
          </label>
          <label class="wide">
            <span>Direccion</span>
            <input type="text" name="direccion" value="{{ old('direccion', $directorProfile['direccion']) }}" />
          </label>
          <label class="wide">
            <span>Trayectoria</span>
            <textarea name="trayectoria" rows="5" @disabled(! $directorProfile['puedeGuardarTrayectoria'])>{{ old('trayectoria', $directorProfile['trayectoria']) }}</textarea>
          </label>
        </div>
        @if(! $directorProfile['puedeGuardarTrayectoria'])
          <p class="backend-hint">Para guardar trayectoria de forma persistente, backend debe agregar la columna <strong>usuarios.trayectoria</strong>.</p>
        @endif
        <div class="form-actions">
          <button type="submit" class="primary-btn">Guardar perfil</button>
        </div>
      </form>
    </section>

    <section class="panel" id="nuevo-profesor">
      <div class="section-head">
        <div>
          <h2>Registrar profesor</h2>
          <p>Crea la cuenta y el acceso al dashboard del maestro.</p>
        </div>
      </div>

      <form action="{{ url('/dashboard-admin/profesores') }}" method="POST" class="teacher-form">
        @csrf
        <div class="field-grid">
          <label>
            <span>Nombres</span>
            <input type="text" name="nombres" value="{{ old('nombres') }}" required />
          </label>
          <label>
            <span>Apellido paterno</span>
            <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required />
          </label>
          <label>
            <span>Apellido materno</span>
            <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}" />
          </label>
          <label>
            <span>Correo</span>
            <input type="email" name="correo" value="{{ old('correo') }}" required />
          </label>
          <label>
            <span>Carnet de identidad</span>
            <input type="text" name="ci" value="{{ old('ci') }}" />
          </label>
          <label>
            <span>Celular</span>
            <input type="text" name="celular" value="{{ old('celular') }}" />
          </label>
          <label>
            <span>Especialidad</span>
            <select name="especialidad">
              <option value="">Sin especialidad</option>
              @foreach(['violin' => 'Violin', 'viola' => 'Viola', 'chelo' => 'Chelo', 'bajo' => 'Bajo'] as $value => $label)
                <option value="{{ $value }}" @selected(old('especialidad') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </label>
          <label>
            <span>Fecha de nacimiento</span>
            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" />
          </label>
          <label class="wide">
            <span>Dirección</span>
            <input type="text" name="direccion" value="{{ old('direccion') }}" />
          </label>
          <label>
            <span>Contraseña temporal</span>
            <input type="password" name="contrasena" required />
          </label>
          <label>
            <span>Confirmar contraseña</span>
            <input type="password" name="contrasena_confirmation" required />
          </label>
        </div>

        <div class="form-actions">
          <button type="reset" class="secondary-btn">Limpiar</button>
          <button type="submit" class="primary-btn">Registrar profesor</button>
        </div>
      </form>
    </section>
  </main>
</div>
</body>
</html>
