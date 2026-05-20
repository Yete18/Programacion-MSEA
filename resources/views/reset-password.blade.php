<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSEA - Nueva contrasena</title>
  <link rel="stylesheet" href="{{ asset('css/password-reset.css') }}" />
</head>
<body>
  <main class="reset-page">
    <section class="reset-panel">
      <div class="reset-header">
        <img src="{{ asset('assets/img/logo.jpeg') }}" alt="MSEA" class="reset-logo" />
        <h1>Nueva contrasena</h1>
        <p>Crea una contrasena nueva para volver a ingresar a tu cuenta.</p>
      </div>

      @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
      @endif

      @if($errors->any())
        <div class="alert error">{{ $errors->first() }}</div>
      @endif

      <form action="{{ url('/reset-password') }}" method="POST" class="reset-form">
        @csrf
        <label for="contrasena">Nueva contrasena</label>
        <input
          type="password"
          id="contrasena"
          name="contrasena"
          placeholder="Minimo 6 caracteres"
          autocomplete="new-password"
          required
        />

        <label for="contrasena_confirmation">Confirmar contrasena</label>
        <input
          type="password"
          id="contrasena_confirmation"
          name="contrasena_confirmation"
          placeholder="Repite tu nueva contrasena"
          autocomplete="new-password"
          required
        />

        <button type="submit">Cambiar contrasena</button>
      </form>
    </section>
  </main>
</body>
</html>
