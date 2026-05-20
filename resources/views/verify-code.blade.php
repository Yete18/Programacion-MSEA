<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSEA - Verificar codigo</title>
  <link rel="stylesheet" href="{{ asset('css/password-reset.css') }}" />
</head>
<body>
  <main class="reset-page">
    <section class="reset-panel">
      <a href="{{ url('/forgot-password') }}" class="back-link">Cambiar correo</a>

      <div class="reset-header">
        <img src="{{ asset('assets/img/logo.jpeg') }}" alt="MSEA" class="reset-logo" />
        <h1>Codigo de verificacion</h1>
        <p>Escribe el codigo de 6 digitos enviado a {{ $correo }}.</p>
      </div>

      @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
      @endif

      @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
      @endif

      @if($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
      @endif

      <form action="{{ url('/verify-code') }}" method="POST" class="reset-form">
        @csrf
        <label for="codigo">Codigo</label>
        <input
          type="text"
          id="codigo"
          name="codigo"
          inputmode="numeric"
          pattern="[0-9]{6}"
          maxlength="6"
          placeholder="123456"
          autocomplete="one-time-code"
          required
        />

        <button type="submit">Verificar codigo</button>
      </form>
    </section>
  </main>
</body>
</html>
