/* ============================================================
   MSEA — JAVASCRIPT PANTALLA DE BIENVENIDA
   Funciones: partículas de fondo, botones interactivos,
              manejo de imágenes fallback
   ============================================================ */

// ── EJECUTAR CUANDO EL DOM ESTÉ LISTO ──
document.addEventListener('DOMContentLoaded', () => {
  initParticles();
  initButtonEffects();
  initSlideshow();
  initInstrumentHover();
});

/* ============================================================
   1. PARTÍCULAS FLOTANTES DE FONDO
   Genera círculos verdes/amarillos animados como fondo decorativo
   ============================================================ */
function initParticles() {
  const container = document.getElementById('particles');
  if (!container) return;

  const colores = [
    'var(--verde-principal)',
    'var(--verde-claro)',
    'var(--amarillo)',
    'var(--verde-suave)',
  ];

  const cantidad = 18;

  for (let i = 0; i < cantidad; i++) {
    const particula = document.createElement('div');
    particula.classList.add('particle');

    // Tamaño aleatorio entre 20px y 80px
    const size = Math.random() * 60 + 20;
    // Posición horizontal aleatoria
    const leftPct = Math.random() * 100;
    // Duración de animación entre 8s y 18s
    const duration = Math.random() * 10 + 8;
    // Retraso para que no empiecen todas al mismo tiempo
    const delay = Math.random() * 12;
    // Color aleatorio de la paleta
    const color = colores[Math.floor(Math.random() * colores.length)];

    particula.style.cssText = `
      width: ${size}px;
      height: ${size}px;
      left: ${leftPct}%;
      bottom: -${size}px;
      background-color: ${color};
      animation-duration: ${duration}s;
      animation-delay: -${delay}s;
    `;

    container.appendChild(particula);
  }
}

/* ============================================================
   2. EFECTOS EN BOTONES
   Añade efecto de "rebote" al hacer clic en los botones
   ============================================================ */
function initButtonEffects() {
  const botones = document.querySelectorAll('.btn');

  botones.forEach(btn => {
    btn.addEventListener('click', function (e) {
      // Crear efecto de onda (ripple) en el punto de clic
      const ripple = document.createElement('span');
      ripple.classList.add('ripple-effect');

      const rect = btn.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;

      ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.4);
        width: 10px;
        height: 10px;
        left: ${x - 5}px;
        top: ${y - 5}px;
        animation: rippleExpand 0.5s ease-out forwards;
        pointer-events: none;
      `;

      // Asegurarse de que el botón tenga overflow hidden
      btn.style.overflow = 'hidden';
      btn.appendChild(ripple);

      // Eliminar el elemento después de la animación
      setTimeout(() => ripple.remove(), 500);
    });
  });

  // Añadir keyframe para ripple dinámicamente
  const style = document.createElement('style');
  style.textContent = `
    @keyframes rippleExpand {
      to {
        transform: scale(30);
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(style);
}

/* ============================================================
   3. SLIDESHOW DE IMÁGENES ROTATIVAS
   Rota automáticamente entre todas las .slide-img en bucle.
   También genera los dots indicadores dinámicamente.
   ============================================================ */
function initSlideshow() {
  const slides    = Array.from(document.querySelectorAll('.slide-img'));
  const dotsWrap  = document.getElementById('slideshow-dots');
  if (!slides.length || !dotsWrap) return;

  let current   = 0;
  const INTERVAL = 3500; // ms entre cada imagen
  let timer;

  // ── Generar dots dinámicamente según cuántas imágenes haya ──
  slides.forEach((_, i) => {
    const dot = document.createElement('button');
    dot.classList.add('dot');
    dot.setAttribute('aria-label', `Imagen ${i + 1}`);
    if (i === 0) dot.classList.add('active');
    dot.addEventListener('click', () => {
      goTo(i);
      resetTimer();
    });
    dotsWrap.appendChild(dot);
  });

  const dots = Array.from(dotsWrap.querySelectorAll('.dot'));

  // ── Fallback: si la imagen no carga, poner emoji de violín ──
  slides.forEach(img => {
    img.addEventListener('error', () => {
      img.style.display = 'none';
      const fallback = document.createElement('div');
      fallback.style.cssText = `
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 5rem;
        background: linear-gradient(135deg, #E8F5D0, #A8D858);
        border-radius: 50%;
      `;
      fallback.textContent = ['🎻','🎸','🎵','🎼'][slides.indexOf(img) % 4];
      img.parentElement.appendChild(fallback);
    });
  });

  // ── Ir a un índice específico ──
  function goTo(index) {
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');

    current = (index + slides.length) % slides.length;

    slides[current].classList.add('active');
    dots[current].classList.add('active');
  }

  // ── Avanzar al siguiente ──
  function next() {
    goTo(current + 1);
  }

  // ── Arrancar el timer automático ──
  function startTimer() {
    timer = setInterval(next, INTERVAL);
  }

  function resetTimer() {
    clearInterval(timer);
    startTimer();
  }

  // Iniciar
  startTimer();
}

/* ============================================================
   4. EFECTO HOVER EN INSTRUMENTOS
   Pequeña animación de sonido al hacer hover en cada instrumento
   ============================================================ */
function initInstrumentHover() {
  const items = document.querySelectorAll('.instrument-item');

  // Notas musicales que aparecen al hacer hover
  const notas = ['🎵', '🎶', '♪', '♫'];

  items.forEach(item => {
    item.addEventListener('mouseenter', () => {
      spawnNotaMusical(item);
    });
  });

  function spawnNotaMusical(parentEl) {
    const nota = document.createElement('span');
    nota.textContent = notas[Math.floor(Math.random() * notas.length)];
    nota.style.cssText = `
      position: absolute;
      font-size: 1.2rem;
      pointer-events: none;
      animation: notaFlota 1s ease-out forwards;
      z-index: 100;
    `;

    // Posicionar relativo al elemento instrumento
    parentEl.style.position = 'relative';
    parentEl.appendChild(nota);

    // Añadir keyframe de nota flotante
    if (!document.getElementById('nota-style')) {
      const s = document.createElement('style');
      s.id = 'nota-style';
      s.textContent = `
        @keyframes notaFlota {
          0%   { opacity: 1; transform: translateY(0) scale(1); }
          100% { opacity: 0; transform: translateY(-40px) scale(1.3); }
        }
      `;
      document.head.appendChild(s);
    }

    setTimeout(() => nota.remove(), 1000);
  }
}

/* ============================================================
   5. NAVEGACIÓN (preparado para futuras páginas)
   Por ahora los botones usan href directos en el HTML.
   Este bloque está listo para lógica adicional si se necesita.
   ============================================================ */
function navegarA(pagina) {
  window.location.href = pagina;
}