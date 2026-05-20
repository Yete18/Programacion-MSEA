<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSEA - Recuperar contrasena</title>
  <link rel="stylesheet" href="{{ asset('css/password-reset.css') }}" />
</head>
<body>
  <main class="reset-page">
    <section class="reset-panel">
      <a href="{{ url('/login') }}" class="back-link">Volver al login</a>

      <div class="reset-header">
        <img src="{{ asset('assets/img/logo.jpeg') }}" alt="MSEA" class="reset-logo" />
        <h1>Recuperar contrasena</h1>
        <p>Ingresa tu correo y te enviaremos un codigo de verificacion.</p>
      </div>

      @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
      @endif

      @if($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
      @endif

      <form action="{{ url('/forgot-password') }}" method="POST" class="reset-form">
        @csrf
        <label for="correo">Correo electronico</label>
        <input
          type="email"
          id="correo"
          name="correo"
          value="{{ old('correo') }}"
          placeholder="correo@ejemplo.com"
          autocomplete="email"
          required
        />

        <button type="submit">Enviar codigo</button>
      </form>
    </section>
  </main>
</body>
</html>
