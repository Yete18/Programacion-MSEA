<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSEA — Crear cuenta</title>
  <link rel="stylesheet" href="{{ asset('css/registro.css') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&family=Baloo+2:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>

<div class="registro-page">

  <!-- ============================
       PANEL IZQUIERDO (decorativo)
  ============================= -->
  <aside class="reg-panel">
    <div class="panel-particles" id="panel-particles"></div>

    <div class="panel-content">

      <a href="{{ url ('/index') }}" class="panel-logo">
        <img src="assets/img/logo.jpeg" alt="Logo MSEA" class="panel-logo-img" />
        <span class="panel-logo-text">MSEA </br>Movimiento Sinfónico </br>de El Alto</span>
      </a>

      <!-- Ilustración -->
      <div class="panel-illustration">
        <img src="assets/img/img_registro_1.jpg" alt="Nuevo músico" class="panel-img" />
        <div class="panel-img-fallback">🎼</div>
      </div>

      <!-- Pasos visuales en el panel -->
      <div class="panel-steps">
        <div class="panel-step active" id="pstep-1">
          <span class="ps-number">1</span>
          <span class="ps-label">Tus datos</span>
        </div>
        <div class="panel-step-line"></div>
        <div class="panel-step" id="pstep-2">
          <span class="ps-number">2</span>
          <span class="ps-label">Tu instrumento</span>
        </div>
      </div>

      <p class="panel-subtitle">¡Únete a la familia MSEA! 🎵</p>

      <!-- Íconos flotantes -->
      <span class="p-float pf-1">🎻</span>
      <span class="p-float pf-2">⭐</span>
      <span class="p-float pf-3">🎶</span>
      <span class="p-float pf-4">🎸</span>

    </div>
  </aside>

  <!-- ============================
       PANEL DERECHO — Formulario
  ============================= -->
  <main class="reg-form-section">

    <!-- Botón volver (móvil) -->
    <a href="{{ url('/login') }}" class="back-link">← Volver</a>

    <div class="form-container">

      <!-- Encabezado + barra de progreso -->
      <div class="form-header">
        <h1 class="form-title" id="form-title">Crear cuenta</h1>
        <p class="form-subtitle" id="form-subtitle">Paso 1 de 2 — Cuéntanos quién eres</p>

        <!-- Barra de progreso -->
        <div class="progress-bar">
          <div class="progress-fill" id="progress-fill"></div>
        </div>
      </div>

      <!-- ══════════════════════════
           PASO 1: Datos personales
      ══════════════════════════ -->
      @if(session('error'))
        <div class="form-alert error" style="display:block;">{{ session('error') }}</div>
      @endif

      @if($errors->any())
        <div class="form-alert error" style="display:block;">{{ $errors->first() }}</div>
      @endif

      <form action="{{ url('/registro') }}" method="POST" id="registro-form" novalidate>
        @csrf
        <input type="hidden" name="rol" value="estudiante" />

      <div class="form-step active" id="step-1">

        <!-- Nombre y apellido en fila -->
        <div class="field-row">
          <div class="field-group">
            <label class="field-label" for="nombre">
              <span>👤</span> Nombre
            </label>
            <div class="input-wrapper">
              <input type="text" id="nombre" name="nombres"
                class="field-input" placeholder="Tu nombre"
                autocomplete="given-name" value="{{ old('nombres') }}" required />
              <span class="input-status" id="status-nombre"></span>
            </div>
            <p class="field-error" id="error-nombre"></p>
          </div>

          <div class="field-group">
            <label class="field-label" for="apellido">
              <span>👤</span> Apellido
            </label>
            <div class="input-wrapper">
              <input type="text" id="apellido" name="apellido_paterno"
                class="field-input" placeholder="Tu apellido"
                autocomplete="family-name" value="{{ old('apellido_paterno') }}" required />
              <span class="input-status" id="status-apellido"></span>
            </div>
            <p class="field-error" id="error-apellido"></p>
          </div>
        </div>

        <!-- Usuario -->
        <div class="field-group">
          <label class="field-label" for="usuario">
            <span>🏷️</span> Nombre de usuario
          </label>
          <div class="input-wrapper">
            <input type="text" id="usuario" name="usuario"
              class="field-input" placeholder="Ej: carlos_violin"
              autocomplete="username" value="{{ old('usuario') }}" required />
            <span class="input-status" id="status-usuario"></span>
          </div>
          <p class="field-hint">Solo letras, números y guión bajo. Sin espacios.</p>
          <p class="field-error" id="error-usuario"></p>
        </div>

        <!-- Email -->
        <div class="field-group">
          <label class="field-label" for="email">
            <span>📧</span> Correo electrónico
          </label>
          <div class="input-wrapper">
            <input type="email" id="email" name="correo"
              class="field-input" placeholder="correo@ejemplo.com"
              autocomplete="email" value="{{ old('correo') }}" required />
            <span class="input-status" id="status-email"></span>
          </div>
          <p class="field-error" id="error-email"></p>
        </div>

        <!-- Botón siguiente -->
        <button type="button" class="btn-next" id="btn-next-1">
          Siguiente ➜
        </button>

      </div>
      <!-- FIN PASO 1 -->

      <!-- ══════════════════════════
           PASO 2: Instrumento y contraseña
      ══════════════════════════ -->
      <div class="form-step" id="step-2">

        <!-- Selector de instrumento -->
        <div class="field-group">
          <label class="field-label">
            <span>🎻</span> ¿Qué instrumento tocas?
          </label>
          <div class="instrument-selector" id="instrument-selector">
            <button type="button" class="instrument-btn" data-value="violin">
              <span class="inst-emoji">🎻</span>
              <span class="inst-name">Violín</span>
            </button>
            <button type="button" class="instrument-btn" data-value="viola">
              <span class="inst-emoji">🎻</span>
              <span class="inst-name">Viola</span>
            </button>
            <button type="button" class="instrument-btn" data-value="chelo">
              <span class="inst-emoji">🎸</span>
              <span class="inst-name">Chelo</span>
            </button>
            <button type="button" class="instrument-btn" data-value="bajo">
              <span class="inst-emoji">🎸</span>
              <span class="inst-name">Bajo</span>
            </button>
          </div>
          <input type="hidden" id="instrumento" name="instrumento" value="" />
          <p class="field-error" id="error-instrumento"></p>
        </div>

        <!-- Nivel -->
        <div class="field-group">
          <label class="field-label">
            <span>📊</span> ¿Cuál es tu nivel?
          </label>
          <div class="level-selector" id="level-selector">
            <button type="button" class="level-btn" data-value="principiante">
              <span class="level-icon">🌱</span>
              <span class="level-name">Principiante</span>
            </button>
            <button type="button" class="level-btn" data-value="intermedio">
              <span class="level-icon">🌿</span>
              <span class="level-name">Intermedio</span>
            </button>
            <button type="button" class="level-btn" data-value="avanzado">
              <span class="level-icon">🌳</span>
              <span class="level-name">Avanzado</span>
            </button>
          </div>
          <input type="hidden" id="nivel" name="nivel" value="" />
          <p class="field-error" id="error-nivel"></p>
        </div>

        <!-- Contraseña -->
        <div class="field-group">
          <label class="field-label" for="password">
            <span>🔒</span> Contraseña
          </label>
          <div class="input-wrapper">
            <input type="password" id="password" name="contrasena"
              class="field-input" placeholder="Mínimo 6 caracteres"
              autocomplete="new-password" required />
            <button type="button" class="toggle-password" id="toggle-pass1">👁️</button>
          </div>
          <!-- Barra de fortaleza -->
          <div class="strength-bar">
            <div class="strength-fill" id="strength-fill"></div>
          </div>
          <p class="strength-label" id="strength-label"></p>
          <p class="field-error" id="error-password"></p>
        </div>

        <!-- Confirmar contraseña -->
        <div class="field-group">
          <label class="field-label" for="confirm-password">
            <span>🔒</span> Confirmar contraseña
          </label>
          <div class="input-wrapper">
            <input type="password" id="confirm-password" name="contrasena_confirmation"
              class="field-input" placeholder="Repite tu contraseña"
              autocomplete="new-password" required />
            <button type="button" class="toggle-password" id="toggle-pass2">👁️</button>
          </div>
          <p class="field-error" id="error-confirm"></p>
        </div>

        <!-- Botones: atrás + registrar -->
        <div class="btn-row">
          <button type="button" class="btn-back" id="btn-back-2">
            ← Atrás
          </button>
          <button type="submit" class="btn-submit" id="btn-submit">
            <span class="btn-text">¡Registrarme! 🎉</span>
            <span class="btn-loader" id="btn-loader" style="display:none;">⏳ Creando...</span>
          </button>
        </div>

        <!-- Alerta general -->
        <div class="form-alert" id="form-alert" style="display:none;"></div>

      </div>
      <!-- FIN PASO 2 -->

      </form>

      <!-- ══════════════════════════
           PASO 3: Éxito
      ══════════════════════════ -->
      <div class="form-step" id="step-success">
        <div class="success-screen">
          <div class="success-emoji">🎉</div>
          <h2 class="success-title">¡Cuenta creada!</h2>
          <p class="success-msg">
            Bienvenido a MSEA, <strong id="success-name"></strong>.<br/>
            Ya puedes empezar a tocar y ganar puntos. 🎵
          </p>
          <a href="{{ url('/login') }}" class="btn-goto-login">Ir al login 🚀</a>
        </div>
      </div>
      <!-- FIN PASO ÉXITO -->

      <!-- Pie del formulario -->
      <div class="form-footer" id="form-footer">
        <p>¿Ya tienes cuenta? <a href="{{ url('/login') }}" class="login-link">Inicia sesión aquí 🚀</a></p>
      </div>

    </div>
  </main>

</div>

<script src="{{ asset('js/registro.js') }}"></script>
</body>
</html>
