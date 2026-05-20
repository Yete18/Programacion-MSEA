<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MSEA — Instituto de Música</title>
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}" />
  <!-- Google Fonts: Nunito (redondeada y amigable para niños) -->
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&family=Baloo+2:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>

  <!-- ============================
       PARTÍCULAS DE FONDO
  ============================= -->
  <div class="particles" id="particles"></div>

  <!-- ============================
       PANTALLA DE BIENVENIDA
  ============================= -->
  <main class="welcome-screen">

    <!-- HEADER / LOGO -->
    <header class="welcome-header">
      <div class="logo-container">
        <img src="assets/img/logo.jpeg" alt="Logo MSEA" class="logo-img" />
        <span class="logo-text">MSEA <br /> Movimiento Sinfónico de El Alto</span>
      </div>
    </header>

    <!-- HERO CENTRAL -->
    <section class="hero">

      <!-- Slideshow de imágenes rotativas -->
      <div class="hero-illustration">

        <!-- Anillo decorativo de fondo -->
        <div class="illustration-ring"></div>

        <!-- Contenedor de imágenes (el JS las rota) -->
        <div class="slideshow-wrapper">
          <img src="assets/img/logo.jpeg" alt="Niño tocando violín"  class="slide-img active" />
          <img src="assets/img/portada.jpg" alt="Niña tocando viola"   class="slide-img" />
          <img src="assets/img/portada_2.jpg" alt="Niño tocando chelo"   class="slide-img" />
          <img src="assets/img/portada_3.jpg" alt="Niña tocando bajo"    class="slide-img" />
          <!-- Agrega más imágenes aquí con el mismo formato -->
        </div>

        <!-- Puntos indicadores -->
        <div class="slideshow-dots" id="slideshow-dots"></div>

        <!-- Íconos flotantes decorativos -->
        <div class="floating-icon fi-1">🎻</div>
        <div class="floating-icon fi-2">🎵</div>
        <div class="floating-icon fi-3">⭐</div>
        <div class="floating-icon fi-4">🎶</div>
        <div class="floating-icon fi-5">🏆</div>
      </div>

      <!-- Texto de bienvenida -->
      <div class="hero-text">
        <h1 class="hero-title">
          ¡Formando jóvenes<br />y niños<span class="highlight">con Valores!</span>!
        </h1>
        <p class="hero-subtitle">
          Toca, aprende y gana puntos con tu instrumento favorito. 🎼
        </p>

        <!-- BOTONES DE ACCIÓN -->
        <div class="hero-buttons">
          <a href="{{ url('/login') }}" class="btn btn-primary" id="btn-login">
            <span class="btn-icon">🚀</span>
            Iniciar sesión
          </a>
          <a href="{{ url('/registro') }}" class="btn btn-secondary" id="btn-register">
            <span class="btn-icon">✨</span>
            Soy nuevo aquí
          </a>
        </div>
      </div>

    </section>

    <!-- ÍCONOS DE INSTRUMENTOS (barra decorativa) -->
    <section class="instruments-bar">
      <div class="instrument-item">
        <span class="instrument-emoji">🎻</span>
        <span class="instrument-label">Violín</span>
      </div>
      <div class="instrument-item">
        <span class="instrument-emoji">🎻</span>
        <span class="instrument-label">Viola</span>
      </div>
      <div class="instrument-item">
        <span class="instrument-emoji">🎸</span>
        <span class="instrument-label">Violonchelo</span>
      </div>
      <div class="instrument-item">
        <span class="instrument-emoji">🎸</span>
        <span class="instrument-label">Contrabajo</span>
      </div>
      <div class="instrument-item">
        <span class="instrument-emoji">🎵</span>
        <span class="instrument-label">Teoría</span>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="welcome-footer">
      <p>© 2024 Instituto de Música MSEA · Todos los derechos reservados</p>
    </footer>

  </main>

  <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>