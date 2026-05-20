/* ============================================================
   MSEA — JAVASCRIPT LOGIN
   Funciones: selector de rol, validación, toggle contraseña,
              partículas del panel, simulación de login
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  initPanelParticles();
  initRoleSelector();
  initPasswordToggle();
  initFormValidation();
  initPanelImgFallback();
});

/* ============================================================
   1. PARTÍCULAS DEL PANEL VERDE
   ============================================================ */
function initPanelParticles() {
  const container = document.getElementById('panel-particles');
  if (!container) return;

  for (let i = 0; i < 12; i++) {
    const p = document.createElement('div');
    p.classList.add('panel-particle');
    const size     = Math.random() * 50 + 15;
    const left     = Math.random() * 100;
    const duration = Math.random() * 8 + 6;
    const delay    = Math.random() * 10;
    p.style.cssText = `
      width: ${size}px; height: ${size}px;
      left: ${left}%;
      bottom: -${size}px;
      animation-duration: ${duration}s;
      animation-delay: -${delay}s;
    `;
    container.appendChild(p);
  }
}

/* ============================================================
   2. SELECTOR DE ROL
   Al hacer clic en un rol, lo marca como activo y
   actualiza el input hidden con el valor
   ============================================================ */
function initRoleSelector() {
  const roleBtns  = document.querySelectorAll('.role-btn');
  const rolInput  = document.getElementById('rol');
  if (!roleBtns.length || !rolInput) return;

  roleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      // Quitar active a todos
      roleBtns.forEach(b => b.classList.remove('active'));
      // Poner active al clickeado
      btn.classList.add('active');
      // Actualizar valor del input hidden
      rolInput.value = btn.dataset.role;

      // Pequeña animación de feedback
      btn.style.transform = 'scale(0.93)';
      setTimeout(() => btn.style.transform = '', 150);
    });
  });
}

/* ============================================================
   3. TOGGLE MOSTRAR / OCULTAR CONTRASEÑA
   ============================================================ */
function initPasswordToggle() {
  const toggle   = document.getElementById('toggle-password');
  const passInput = document.getElementById('contrasena');
  if (!toggle || !passInput) return;

  let visible = false;

  toggle.addEventListener('click', () => {
    visible = !visible;
    passInput.type = visible ? 'text' : 'password';
    toggle.textContent = visible ? '🙈' : '👁️';
    toggle.title = visible ? 'Ocultar contraseña' : 'Mostrar contraseña';
  });
}

/* ============================================================
   4. VALIDACIÓN DEL FORMULARIO
   Valida en tiempo real y al enviar.
   Por ahora simula el login (sin backend).
   ============================================================ */
function initFormValidation() {
  const form        = document.getElementById('login-form');
  const usuarioInput = document.getElementById('correo');
  const passInput    = document.getElementById('contrasena');
  const btnLogin     = document.getElementById('btn-login');
  const btnText      = btnLogin?.querySelector('.btn-text');
  const btnLoader    = document.getElementById('btn-loader');
  const formAlert    = document.getElementById('form-alert');

  if (!form) return;

  // ── Validación en tiempo real al salir del campo ──
  usuarioInput?.addEventListener('blur', () => validateField(usuarioInput, 'usuario'));
  passInput?.addEventListener('blur',    () => validateField(passInput, 'password'));

  // ── Limpiar error al escribir ──
  usuarioInput?.addEventListener('input', () => clearFieldError(usuarioInput, 'usuario'));
  passInput?.addEventListener('input',    () => clearFieldError(passInput, 'password'));

  // ── Envío del formulario ──
  form.addEventListener('submit', async (e) => {
    //e.preventDefault();

    // Validar todos los campos
    const okUsuario = validateField(usuarioInput, 'usuario');
    const okPass    = validateField(passInput, 'password');

    if (!okUsuario || !okPass) {
      e.preventDefault();
      return;
    }

    

    // Simular carga (aquí irá la llamada al backend)
    //setLoading(true);
    //hideAlert();

    //await simulateLogin(usuarioInput.value, passInput.value);

    //setLoading(false);
  });

  // ── Helpers ──

  function validateField(input, name) {
    const val = input.value.trim();
    let error = '';

    if (name === 'usuario') {
      if (!val)          error = '⚠️ Ingresa tu nombre de usuario';
      else if (val.length < 3) error = '⚠️ El usuario debe tener al menos 3 caracteres';
    }

    if (name === 'password') {
      if (!val)          error = '⚠️ Ingresa tu contraseña';
      else if (val.length < 4) error = '⚠️ La contraseña es demasiado corta';
    }

    showFieldError(input, name, error);
    return !error;
  }

  function showFieldError(input, name, message) {
    const errorEl   = document.getElementById(`error-${name}`);
    const statusEl  = document.getElementById(`status-${name}`);

    if (message) {
      input.classList.add('invalid');
      input.classList.remove('valid');
      if (errorEl)  errorEl.textContent = message;
      if (statusEl) statusEl.textContent = '❌';
    } else {
      input.classList.remove('invalid');
      input.classList.add('valid');
      if (errorEl)  errorEl.textContent = '';
      if (statusEl) statusEl.textContent = '✅';
    }
  }

  function clearFieldError(input, name) {
    if (input.classList.contains('invalid')) {
      input.classList.remove('invalid');
      const errorEl = document.getElementById(`error-${name}`);
      if (errorEl) errorEl.textContent = '';
    }
  }

  function setLoading(state) {
    if (!btnLogin || !btnText || !btnLoader) return;
    btnLogin.disabled = state;
    btnText.style.display  = state ? 'none' : 'inline';
    btnLoader.style.display = state ? 'inline' : 'none';
  }

  function showAlert(message, type = 'error') {
    if (!formAlert) return;
    formAlert.textContent = message;
    formAlert.className   = `form-alert ${type}`;
    formAlert.style.display = 'block';
  }

  function hideAlert() {
    if (formAlert) formAlert.style.display = 'none';
  }

  /* ── SIMULACIÓN DE LOGIN ──
     Reemplaza este bloque con tu llamada real al backend.
     Usuarios de prueba:
       estudiante / 1234  → dashboard del estudiante
       profesor   / 1234  → dashboard del profesor
       director   / 1234  → dashboard del director
  ── */
  async function simulateLogin(usuario, password) {
    // Simular delay de red
    await wait(1400);

    const rol = document.getElementById('rol')?.value;

    const credenciales = {
      estudiante: { user: 'estudiante', pass: '1234', ruta: '/dashboard-estudiante' },
      profesor:   { user: 'profesor',   pass: '1234', ruta: 'dashboard-profesor.html'   },
      admin:      { user: 'director',   pass: '1234', ruta: 'dashboard-admin.html'       },
    };

    const cred = credenciales[rol];

    if (cred && usuario === cred.user && password === cred.pass) {
      showAlert('✅ ¡Bienvenido! Ingresando...', 'success');
      await wait(800);
      // Redirigir al dashboard correspondiente
      window.location.href = cred.ruta;
    } else {
      showAlert('❌ Usuario o contraseña incorrectos. Inténtalo de nuevo.', 'error');
    }
  }

  function wait(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

/* ============================================================
   5. FALLBACK IMAGEN DEL PANEL
   ============================================================ */
function initPanelImgFallback() {
  const img = document.querySelector('.panel-img');
  if (!img) return;
  img.addEventListener('error', () => {
    img.classList.add('error');
  });
}
