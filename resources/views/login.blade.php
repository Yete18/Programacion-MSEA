<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSEA — Iniciar sesión</title>
  <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&family=Baloo+2:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>

  <div class="login-page">

    <!-- ============================
         PANEL IZQUIERDO (decorativo)
    ============================= -->
    <aside class="login-panel">

      <!-- Partículas del panel -->
      <div class="panel-particles" id="panel-particles"></div>

      <div class="panel-content">

        <!-- Logo -->
        <a href="{{ url('/index') }}" class="panel-logo">
          <img src="assets/img/logo.jpeg" alt="Logo MSEA" class="panel-logo-img" />
          <span class="panel-logo-text">MSEA </br>Movimiento Sinfónico </br>de El Alto</span>
        </a>

        <!-- Ilustración central del panel -->
        <div class="panel-illustration">
          <img src="assets/img/img_login_1.jpg" alt="Músico" class="panel-img" />
          <!-- Fallback emoji -->
          <div class="panel-img-fallback">🎻</div>
        </div>

        <!-- Mensaje motivador -->
        <div class="panel-message">
          <h2 class="panel-title">¡Bienvenido de vuelta!</h2>
          <p class="panel-subtitle">Tu música te está esperando. 🎵</p>
        </div>

        <!-- Íconos flotantes -->
        <span class="p-float pf-1">⭐</span>
        <span class="p-float pf-2">🎶</span>
        <span class="p-float pf-3">🏆</span>
        <span class="p-float pf-4">✨</span>

      </div>
    </aside>

    <!-- ============================
         PANEL DERECHO (formulario)
    ============================= -->
    <main class="login-form-section">

      <!-- Botón volver (móvil) -->
      <a href="{{url ('/') }}" class="back-link">
        <span>←</span> Volver
      </a>

      <div class="form-container">

        <!-- Encabezado del formulario -->
        <div class="form-header">
          <h1 class="form-title">Iniciar sesión</h1>
          <p class="form-subtitle">Ingresa tus datos para continuar</p>
        </div>

        <!-- FORMULARIO -->
        <form class="login-form" id="login-form" novalidate action="/login" method="POST">
          @csrf
          <!-- Selector de rol -->
          <div class="field-group">
            <label class="field-label" for="rol">¿Quién eres?</label>
            <div class="role-selector" id="role-selector">
              <button type="button" class="role-btn active" data-role="estudiante">
                <span class="role-icon">🎓</span>
                <span class="role-name">Estudiante</span>
              </button>
              <button type="button" class="role-btn" data-role="profesor">
                <span class="role-icon">👩‍🏫</span>
                <span class="role-name">Profesor</span>
              </button>
              <button type="button" class="role-btn" data-role="admin">
                <span class="role-icon">🏫</span>
                <span class="role-name">Director</span>
              </button>
              <button type="button" class="role-btn" data-role="padre">
                <span class="role-icon">👨‍👩‍👧</span>
                <span class="role-name">Padre</span>
              </button>
            </div>
            <!-- Input oculto que guarda el rol seleccionado -->
            <input type="hidden" id="rol" name="rol" value="estudiante" />
          </div>

          <!-- Campo usuario -->
          <div class="field-group">
            <label class="field-label" for="usuario">
              <span class="label-icon">👤</span> Usuario
            </label>
            <div class="input-wrapper">
              <input
                type="email"
                id="correo"
                name="correo"
                class="field-input"
                placeholder="Tu correo"
                autocomplete="email"
                required
              />
              <span class="input-status" id="status-usuario"></span>
            </div>
            <p class="field-error" id="error-usuario"></p>
          </div>

          <!-- Campo contraseña -->
          <div class="field-group">
            <label class="field-label" for="password">
              <span class="label-icon">🔒</span> Contraseña
            </label>
            <div class="input-wrapper">
              <input
                type="password"
                id="contrasena"
                name="contrasena"
                class="field-input"
                placeholder="Tu contraseña"
                autocomplete="current-password"
                required
              />
              <!-- Toggle mostrar/ocultar contraseña -->
              <button type="button" class="toggle-password" id="toggle-password" aria-label="Mostrar contraseña">
                👁️
              </button>
            </div>
            <p class="field-error" id="error-password"></p>
          </div>

          <!-- Olvidé mi contraseña -->
          <div class="form-options">
            <a href="{{ url('/forgot-password') }}" class="forgot-link" id="forgot-link">¿Olvidaste tu contraseña?</a>
          </div>
          
          @if(session('error'))
            <div style="color:red; margin-bottom:10px;">
              {{ session('error') }}
            </div>
          @endif

          @if(session('success'))
            <div style="color:green; margin-bottom:10px;">
              {{ session('success') }}
            </div>
          @endif

          @if($errors->any())
            <div style="color:red; margin-bottom:10px;">
              {{ $errors->first() }}
            </div>
          @endif

          <!-- Botón ingresar -->
          <button type="submit" class="btn-login" id="btn-login">
            <span class="btn-text">Ingresar 🚀</span>
            <span class="btn-loader" id="btn-loader" style="display:none;">⏳ Cargando...</span>
          </button>

          <!-- Mensaje de error general -->
          <div class="form-alert" id="form-alert" style="display:none;"></div>

        </form>

        <!-- Pie del formulario -->
        <div class="form-footer">
          <p>¿Eres nuevo? <a href="{{ url('/registro') }}" class="register-link">Regístrate aquí ✨</a></p>
        </div>

      </div>
    </main>

  </div><!-- fin .login-page -->

  <script src="{{asset('js/login.js') }}"></script>
</body>
</html>
