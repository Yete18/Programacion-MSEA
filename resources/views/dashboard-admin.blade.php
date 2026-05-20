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
      <a href="#profesores" class="active">Profesores</a>
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
        <h1>Gestión de profesores</h1>
      </div>
      <a href="#nuevo-profesor" class="primary-link">Nuevo profesor</a>
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

    <section class="stats-grid">
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
    </section>

    <section class="panel" id="profesores">
      <div class="section-head">
        <div>
          <h2>Profesores</h2>
          <p>Usuarios con acceso al dashboard de profesor.</p>
        </div>
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
