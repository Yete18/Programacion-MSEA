/* ============================================================
   MSEA — DASHBOARD ESTUDIANTE JS v2.0
   SPA: navegación entre vistas, notificaciones, avatar,
        perfil editable, cambio de foto con localStorage
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  initAvatar();
  applyStudentData();
  initGreeting();
  initSidebarMobile();
  initNavigation();
  initNotificaciones();
  initTareas();
  initRanking();
  initLogros();
  initEjercicios();
  initProgreso();
  initRankingFull();
  initLogrosFull();
  initPerfil();
  initImageFallbacks();
  animateStats();
});

/* ============================================================
   DATOS DEL ESTUDIANTE
   ============================================================ */
const ESTUDIANTE = {
  nombre:           'Carlos',
  apellido:         'Mamani',
  email:            'carlos.mamani@msea.edu.bo',
  instrumento:      'Violín',
  nivel:            3,
  xp:               320,
  xpMax:            500,
  puntos:           1240,
  racha:            5,
  ejerciciosHechos: 12,
  rankingPos:       4,
  sede:             'El Alto — Sede Central',
  profesor:         'Prof. Ana Quispe',
  miembroDesde:     'Enero 2024',
};

if (window.MSEA_ESTUDIANTE) {
  Object.assign(ESTUDIANTE, window.MSEA_ESTUDIANTE);
}

const TAREAS = [
  { nombre: 'Escala de Re Mayor',     emoji: '🎻', profesor: 'Prof. Ana Quispe', vence: 'Hoy',     urgencia: 'urgente', xp: 30, desc: 'Practica la escala de Re Mayor en dos octavas con arco separado.', tipo: 'escala' },
  { nombre: 'Ejercicio de arco',      emoji: '🎻', profesor: 'Prof. Ana Quispe', vence: 'Mañana',  urgencia: 'normal',  xp: 20, desc: 'Trabaja los golpes de arco: detaché y martelé en cuerdas al aire.', tipo: 'técnica' },
  { nombre: 'Lectura de notas — Sol', emoji: '🎵', profesor: 'Prof. Luis Rojas',  vence: 'Viernes', urgencia: 'proxima', xp: 25, desc: 'Lee y toca las notas en clave de Sol, compases 1 al 16.', tipo: 'lectura' },
  { nombre: 'Pieza: Minueto en Sol',  emoji: '🎼', profesor: 'Prof. Ana Quispe', vence: 'Lunes',   urgencia: 'proxima', xp: 50, desc: 'Memoriza y toca el Minueto en Sol de Bach, primera sección.', tipo: 'repertorio' },
];

const RANKING = [
  { nombre: 'Sofía Torres',  puntos: 1850, avatar: '👧', instrumento: '🎹 Piano',   esYo: false },
  { nombre: 'Mateo López',   puntos: 1620, avatar: '👦', instrumento: '🎸 Guitarra', esYo: false },
  { nombre: 'Valentina R.',  puntos: 1480, avatar: '👧', instrumento: '🎻 Viola',    esYo: false },
  { nombre: 'Carlos Mamani', puntos: 1240, avatar: '👦', instrumento: '🎻 Violín',   esYo: true  },
  { nombre: 'Lucía Flores',  puntos: 1100, avatar: '👧', instrumento: '🎹 Piano',    esYo: false },
  { nombre: 'Iker Colque',   puntos:  980, avatar: '👦', instrumento: '🥁 Percusión',esYo: false },
  { nombre: 'Valeria Ticona',puntos:  870, avatar: '👧', instrumento: '🎻 Chelo',    esYo: false },
];

if (Array.isArray(window.MSEA_RANKING) && window.MSEA_RANKING.length) {
  RANKING.splice(0, RANKING.length, ...window.MSEA_RANKING);
}

const LOGROS = [
  { emoji: '🔥', nombre: '¡Racha de 5!',     desc: '5 días seguidos',  bloqueado: false },
  { emoji: '🎯', nombre: 'Primer ejercicio', desc: 'Completaste 1',    bloqueado: false },
  { emoji: '⭐', nombre: '100 puntos',        desc: 'Puntos ganados',   bloqueado: false },
  { emoji: '🏆', nombre: 'Top 5',            desc: 'En el ranking',    bloqueado: false },
  { emoji: '🎵', nombre: '10 ejercicios',    desc: 'Sigue así!',       bloqueado: false },
  { emoji: '🌟', nombre: 'Semana perfecta',  desc: '7 días de racha',  bloqueado: true  },
  { emoji: '💎', nombre: 'Nivel 5',          desc: 'Sube de nivel',    bloqueado: true  },
  { emoji: '🚀', nombre: 'Cohete musical',   desc: '500 puntos',       bloqueado: true  },
];

const EJERCICIOS = [
  { emoji: '🎵', nombre: 'Escala de Do Mayor',    desc: 'Primera posición, tempo ♩=60', chips: ['🎻 Violín','⏱️ 5min','🌱 Básico'],  xp: 30, tipo: 'escala' },
  { emoji: '🥁', nombre: 'Ritmo básico 4/4',      desc: 'Golpea con precisión al metrónomo',  chips: ['🥁 Todos','⏱️ 3min','🌱 Básico'],  xp: 20, tipo: 'ritmo' },
  { emoji: '📖', nombre: 'Lectura: clave de Sol', desc: 'Identifica notas en el pentagrama',  chips: ['🎵 Teoría','⏱️ 8min','🌱 Básico'],  xp: 25, tipo: 'teoria' },
  { emoji: '🎵', nombre: 'Escala de Re Mayor',    desc: 'Dos octavas, arco separado ♩=72',    chips: ['🎻 Violín','⏱️ 7min','⚡ Medio'],   xp: 35, tipo: 'escala' },
  { emoji: '🥁', nombre: 'Ritmo sincopado',       desc: 'Practica patrones con síncopa',      chips: ['🥁 Todos','⏱️ 5min','⚡ Medio'],   xp: 30, tipo: 'ritmo' },
  { emoji: '📖', nombre: 'Intervalos musicales',  desc: 'Reconoce y canta intervalos',        chips: ['🎵 Teoría','⏱️ 10min','⚡ Medio'],  xp: 40, tipo: 'teoria' },
];

const NOTIFICACIONES = [
  { icono: '📋', texto: 'Nueva tarea asignada: Escala de Re Mayor', tiempo: 'Hace 10 min', leida: false },
  { icono: '🏆', texto: '¡Subiste al puesto #4 en el ranking!',    tiempo: 'Hace 1 hora',  leida: false },
  { icono: '⭐', texto: 'Lograste el logro "Top 5" 🎉',            tiempo: 'Ayer',         leida: true  },
  { icono: '🔥', texto: '¡Tu racha lleva 5 días! Sigue así',        tiempo: 'Ayer',         leida: true  },
  { icono: '💬', texto: 'Prof. Ana dejó un comentario en tu tarea', tiempo: 'Hace 2 días',  leida: true  },
];

const ACTIVIDAD = [
  { icono: '🎯', texto: 'Completaste: Escala de Do Mayor',   tiempo: 'Hoy, 09:15',       xp: '+30 XP' },
  { icono: '📋', texto: 'Entregaste: Ejercicio de arco',     tiempo: 'Ayer, 16:40',       xp: '+20 XP' },
  { icono: '⭐', texto: 'Desbloqueaste logro: Top 5',        tiempo: 'Ayer, 14:00',       xp: '+50 XP' },
  { icono: '🎯', texto: 'Completaste: Ritmo básico 4/4',     tiempo: 'Hace 2 días',       xp: '+20 XP' },
  { icono: '🔥', texto: 'Racha de 4 días alcanzada',         tiempo: 'Hace 3 días',       xp: '+10 XP' },
];

/* ============================================================
   CLAVE DE AVATAR EN localStorage
   ============================================================ */
const AVATAR_KEY = `msea_avatar_${ESTUDIANTE.idEstudiante || 'actual'}`;
let pendingAvatarDataUrl = null;

function studentFullName() {
  return ESTUDIANTE.nombreCompleto || `${ESTUDIANTE.nombre || ''} ${ESTUDIANTE.apellido || ''}`.trim() || 'Estudiante';
}

function studentRoleText() {
  const instrumento = ESTUDIANTE.instrumento || 'Sin instrumento';
  const nivel = ESTUDIANTE.nivelTexto || `Nivel ${ESTUDIANTE.nivel || 1}`;
  return `🎻 ${instrumento} · ${nivel}`;
}

function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function fallback(value) {
  return value || 'Sin registrar';
}

function inputValue(id, value) {
  const el = document.getElementById(id);
  if (el) el.value = value || '';
}

function applyStudentData() {
  const fullName = studentFullName();
  const roleText = studentRoleText();
  const xpRestante = Math.max((ESTUDIANTE.xpMax || 500) - (ESTUDIANTE.xp || 0), 0);

  setText('s-name', fullName);
  setText('s-role', roleText);
  setText('perfil-nombre-big', fullName);
  setText('perfil-rol-big', roleText);
  setText('info-nombre', fullName);
  setText('info-email', ESTUDIANTE.email || 'Sin correo');
  setText('info-ci', fallback(ESTUDIANTE.ci));
  setText('info-apellido-paterno', fallback(ESTUDIANTE.apellidoPaterno));
  setText('info-apellido-materno', fallback(ESTUDIANTE.apellidoMaterno));
  setText('info-celular', fallback(ESTUDIANTE.celular));
  setText('info-direccion', fallback(ESTUDIANTE.direccion));
  setText('info-fecha-nacimiento', fallback(ESTUDIANTE.fechaNacimiento));
  setText('info-instrumento', `🎻 ${ESTUDIANTE.instrumento || 'Sin instrumento'}`);
  setText('info-nivel', ESTUDIANTE.nivelTexto || `Nivel ${ESTUDIANTE.nivel || 1}`);
  setText('info-sede', ESTUDIANTE.sede || ESTUDIANTE.seccion || 'Sin seccion');
  setText('info-profesor', ESTUDIANTE.profesor || 'Sin profesor asignado');
  setText('info-miembro-desde', ESTUDIANTE.miembroDesde || 'Sin fecha');
  inputValue('edit-nombres', ESTUDIANTE.nombre);
  inputValue('edit-apellido-paterno', ESTUDIANTE.apellidoPaterno);
  inputValue('edit-apellido-materno', ESTUDIANTE.apellidoMaterno);
  inputValue('edit-email', ESTUDIANTE.email);
  inputValue('edit-ci', ESTUDIANTE.ci);
  inputValue('edit-celular', ESTUDIANTE.celular);
  inputValue('edit-direccion', ESTUDIANTE.direccion);
  inputValue('edit-fecha-nacimiento', ESTUDIANTE.fechaNacimiento);

  const xpHint = document.querySelector('.perfil-xp-hint');
  if (xpHint) xpHint.innerHTML = `Te faltan <strong>${xpRestante} XP</strong> para subir de nivel.`;

  const xpLabels = document.querySelectorAll('.perfil-xp-labels span');
  if (xpLabels[0]) xpLabels[0].textContent = `Nivel ${ESTUDIANTE.nivel || 1}`;
  if (xpLabels[2]) xpLabels[2].textContent = `Nivel ${(ESTUDIANTE.nivel || 1) + 1}`;

  const desbloqueados = document.querySelector('.logro-stat-mini .lsm-val');
  if (desbloqueados && Number.isFinite(Number(ESTUDIANTE.logrosDesbloqueados))) {
    desbloqueados.textContent = ESTUDIANTE.logrosDesbloqueados;
  }
}

/* ============================================================
   1. AVATAR — carga desde localStorage y aplica a todos los sitios
   ============================================================ */
function initAvatar() {
  applyAvatarEverywhere(ESTUDIANTE.foto);
}

function applyAvatarEverywhere(dataUrl) {
  const targets = [
    document.getElementById('s-avatar-img'),
    document.getElementById('header-avatar-img'),
    document.getElementById('perfil-avatar-img'),
  ];

  targets.forEach(img => {
    if (!img) return;
    if (dataUrl) {
      img.src = dataUrl;
      img.classList.add('loaded');
    } else {
      img.src = '';
      img.classList.remove('loaded');
    }
  });
}

function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function resizeImage(file, maxSize = 512, quality = 0.82) {
  const dataUrl = await new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (ev) => resolve(ev.target.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });

  const image = await new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = reject;
    img.src = dataUrl;
  });

  const scale = Math.min(maxSize / image.width, maxSize / image.height, 1);
  const canvas = document.createElement('canvas');
  canvas.width = Math.round(image.width * scale);
  canvas.height = Math.round(image.height * scale);
  const ctx = canvas.getContext('2d');
  ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

  return canvas.toDataURL('image/jpeg', quality);
}

async function saveProfile(foto = null) {
  const payload = {
    nombres: document.getElementById('edit-nombres')?.value.trim() || '',
    apellido_paterno: document.getElementById('edit-apellido-paterno')?.value.trim() || '',
    apellido_materno: document.getElementById('edit-apellido-materno')?.value.trim() || '',
    correo: document.getElementById('edit-email')?.value.trim() || '',
    ci: document.getElementById('edit-ci')?.value.trim() || '',
    celular: document.getElementById('edit-celular')?.value.trim() || '',
    direccion: document.getElementById('edit-direccion')?.value.trim() || '',
    fecha_nacimiento: document.getElementById('edit-fecha-nacimiento')?.value || '',
  };

  if (foto) payload.foto = foto;

  const response = await fetch('/dashboard-estudiante/perfil', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
    },
    body: JSON.stringify(payload),
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const errors = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
    throw new Error(errors || 'No se pudo guardar el perfil.');
  }

  const estudiante = data.estudiante || {};
  if (!foto) delete estudiante.foto;
  Object.assign(ESTUDIANTE, estudiante);
  applyStudentData();
  if (foto) applyAvatarEverywhere(estudiante.foto || foto);
  return data;
}

/* ============================================================
   2. SALUDO DINÁMICO
   ============================================================ */
function initGreeting() {
  const hora = new Date().getHours();
  let saludo;
  if      (hora >= 6  && hora < 12) saludo = '¡Buenos días';
  else if (hora >= 12 && hora < 19) saludo = '¡Buenas tardes';
  else                               saludo = '¡Buenas noches';

  const el = document.getElementById('greeting-title');
  if (el) el.textContent = `${saludo}, ${ESTUDIANTE.nombre}! 👋`;

  // Barra XP sidebar
  const xpFill = document.getElementById('s-xp-fill');
  const xpText = document.getElementById('s-xp-text');
  if (xpFill) xpFill.style.width = `${(ESTUDIANTE.xp / ESTUDIANTE.xpMax) * 100}%`;
  if (xpText) xpText.textContent  = `${ESTUDIANTE.xp} / ${ESTUDIANTE.xpMax}`;

  // Racha
  const sc = document.getElementById('streak-count');
  const sb = document.getElementById('s-streak-badge');
  if (sc) sc.textContent = ESTUDIANTE.racha;
  if (sb) sb.textContent  = `🔥${ESTUDIANTE.racha}`;

  // Puntos
  const hp = document.getElementById('header-points');
  if (hp) hp.textContent = ESTUDIANTE.puntos.toLocaleString('es');
}

/* ============================================================
   3. SIDEBAR MÓVIL
   ============================================================ */
function initSidebarMobile() {
  const hamburger = document.getElementById('hamburger');
  const sidebar   = document.getElementById('sidebar');
  const overlay   = document.getElementById('sidebar-overlay');

  const open  = () => { sidebar?.classList.add('open'); overlay?.classList.add('active'); document.body.style.overflow = 'hidden'; };
  const close = () => { sidebar?.classList.remove('open'); overlay?.classList.remove('active'); document.body.style.overflow = ''; };

  hamburger?.addEventListener('click', open);
  overlay?.addEventListener('click', close);

  // Cerrar sidebar al navegar en móvil
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => {
      if (window.innerWidth <= 768) close();
    });
  });
}

/* ============================================================
   4. NAVEGACIÓN SPA
   ============================================================ */
function initNavigation() {
  // Redirigir sidebar-profile al perfil
  document.getElementById('sidebar-profile-btn')?.addEventListener('click', () => navigateTo('perfil'));
  document.getElementById('header-avatar-btn')?.addEventListener('click',   () => navigateTo('perfil'));

  // Nav items del sidebar
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      navigateTo(item.dataset.page);
    });
  });

  // Botones internos con data-page (ej: "Ver todas →")
  document.querySelectorAll('.nav-link-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      navigateTo(btn.dataset.page);
    });
  });

  // Botón "Empezar ejercicio" del banner
  document.getElementById('btn-ejercicio-rapido')?.addEventListener('click', (e) => {
    e.preventDefault();
    navigateTo('ejercicios');
  });

  // Filtros de ejercicios
  document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      renderEjerciciosFiltrados(btn.dataset.filtro);
    });
  });
}

function navigateTo(page) {
  if (!page) return;

  // Actualizar nav items
  document.querySelectorAll('.nav-item').forEach(item => {
    item.classList.toggle('active', item.dataset.page === page);
  });

  // Mostrar la vista correspondiente
  document.querySelectorAll('.page-view').forEach(view => {
    view.classList.remove('active');
  });

  const target = document.getElementById(`view-${page}`);
  if (target) {
    target.classList.add('active');
    // Scroll al inicio del contenido
    document.querySelector('.dash-main')?.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Actualizar greeting subtitle según página
  updateGreetingSub(page);
}

function updateGreetingSub(page) {
  const sub = document.getElementById('greeting-sub');
  if (!sub) return;
  const textos = {
    inicio:     `Tienes <strong>3 tareas</strong> pendientes hoy`,
    tareas:     `Tienes <strong>${TAREAS.length} tareas</strong> asignadas`,
    ejercicios: `Completa ejercicios y gana <strong>XP</strong>`,
    progreso:   `Tu progreso de esta <strong>semana</strong>`,
    ranking:    `Estás en el puesto <strong>#${ESTUDIANTE.rankingPos}</strong>`,
    logros:     `Tienes <strong>5 logros</strong> desbloqueados`,
    perfil:     `Bienvenido, <strong>${ESTUDIANTE.nombre}</strong>`,
  };
  sub.innerHTML = textos[page] || textos.inicio;
}

/* ============================================================
   5. NOTIFICACIONES
   ============================================================ */
function initNotificaciones() {
  const btn   = document.getElementById('notif-btn');
  const panel = document.getElementById('notif-panel');
  const dot   = document.getElementById('notif-dot');

  // Render notificaciones
  renderNotificaciones();

  // Actualizar punto rojo
  const noLeidas = NOTIFICACIONES.filter(n => !n.leida).length;
  if (dot && noLeidas === 0) dot.classList.add('hidden');

  // Toggle panel
  btn?.addEventListener('click', (e) => {
    e.stopPropagation();
    panel?.classList.toggle('open');
  });

  // Cerrar al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!document.getElementById('notif-wrapper')?.contains(e.target)) {
      panel?.classList.remove('open');
    }
  });

  // Marcar todas como leídas
  document.getElementById('notif-clear-btn')?.addEventListener('click', () => {
    NOTIFICACIONES.forEach(n => n.leida = true);
    renderNotificaciones();
    if (dot) dot.classList.add('hidden');
    showToast('✅ Notificaciones marcadas como leídas');
  });
}

function renderNotificaciones() {
  const lista = document.getElementById('notif-list');
  if (!lista) return;
  lista.innerHTML = '';

  NOTIFICACIONES.forEach((notif, i) => {
    const li = document.createElement('li');
    li.classList.add('notif-item');
    if (notif.leida) li.classList.add('leida');

    li.innerHTML = `
      <span class="notif-item-icon">${notif.icono}</span>
      <div class="notif-item-body">
        <p class="notif-item-texto">${notif.texto}</p>
        <p class="notif-item-tiempo">${notif.tiempo}</p>
      </div>
      ${!notif.leida ? '<span class="notif-unread-dot"></span>' : ''}
    `;

    li.addEventListener('click', () => {
      NOTIFICACIONES[i].leida = true;
      renderNotificaciones();
      const noLeidas = NOTIFICACIONES.filter(n => !n.leida).length;
      const dot = document.getElementById('notif-dot');
      if (dot && noLeidas === 0) dot.classList.add('hidden');
    });

    lista.appendChild(li);
  });
}

/* ============================================================
   6. TAREAS — Vista inicio (mini)
   ============================================================ */
function initTareas() {
  const lista = document.getElementById('tareas-list');
  if (!lista) return;

  TAREAS.slice(0, 3).forEach((tarea, i) => {
    const li = document.createElement('li');
    li.classList.add('tarea-item');
    li.style.animationDelay = `${i * 0.08}s`;

    const badgeClass = { urgente: 'badge-urgente', normal: 'badge-normal', proxima: 'badge-proxima' }[tarea.urgencia] || 'badge-normal';
    const badgeTexto = { urgente: '🔴 Hoy', normal: '🟢 Mañana', proxima: '🟡 Pronto' }[tarea.urgencia] || '';

    li.innerHTML = `
      <span class="tarea-emoji">${tarea.emoji}</span>
      <div class="tarea-info">
        <p class="tarea-nombre">${tarea.nombre}</p>
        <p class="tarea-meta">Vence: ${tarea.vence} · +${tarea.xp} XP</p>
      </div>
      <span class="tarea-badge ${badgeClass}">${badgeTexto}</span>
    `;

    li.addEventListener('click', () => { showToast(`📋 Abriendo: ${tarea.nombre}`); navigateTo('tareas'); });
    lista.appendChild(li);
  });

  // Vista completa de tareas
  renderTareasCompletas();
}

function renderTareasCompletas() {
  const grid = document.getElementById('tareas-full-grid');
  if (!grid) return;

  TAREAS.forEach((tarea, i) => {
    const div = document.createElement('div');
    div.classList.add('tarea-card-full', tarea.urgencia);
    div.style.animationDelay = `${i * 0.08}s`;

    const badgeClass = { urgente: 'badge-urgente', normal: 'badge-normal', proxima: 'badge-proxima' }[tarea.urgencia] || 'badge-normal';
    const badgeTexto = { urgente: '🔴 Urgente', normal: '🟢 Normal', proxima: '🟡 Próxima' }[tarea.urgencia] || '';

    div.innerHTML = `
      <div class="tcf-top">
        <span class="tcf-emoji">${tarea.emoji}</span>
        <div class="tcf-info">
          <p class="tcf-nombre">${tarea.nombre}</p>
          <p class="tcf-profe">${tarea.profesor}</p>
        </div>
        <span class="tarea-badge ${badgeClass} tcf-badge">${badgeTexto}</span>
      </div>
      <p class="tcf-desc">${tarea.desc}</p>
      <div class="tcf-meta">
        <span class="tcf-chip">📅 Vence: ${tarea.vence}</span>
        <span class="tcf-chip">+${tarea.xp} XP</span>
        <span class="tcf-chip">📂 ${tarea.tipo}</span>
      </div>
      <button class="tcf-btn">Ver tarea 🎯</button>
    `;

    div.querySelector('.tcf-btn').addEventListener('click', () => showToast(`📋 Abriendo: ${tarea.nombre}`));
    grid.appendChild(div);
  });
}

/* ============================================================
   7. RANKING — Mini inicio
   ============================================================ */
function initRanking() {
  const lista = document.getElementById('ranking-list');
  if (!lista) return;

  RANKING.slice(0, 5).forEach((jugador, i) => {
    const li = document.createElement('li');
    li.classList.add('ranking-item');
    if (jugador.esYo) li.classList.add('es-yo');
    li.style.animationDelay = `${i * 0.07}s`;

    const posClase = i === 0 ? 'gold' : i === 1 ? 'silver' : i === 2 ? 'bronze' : '';
    const posText  = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `#${i + 1}`;

    li.innerHTML = `
      <span class="rank-pos ${posClase}">${posText}</span>
      <span class="rank-avatar">${jugador.avatar}</span>
      <div class="rank-info">
        <p class="rank-name">${jugador.nombre}</p>
        <p class="rank-pts">${jugador.puntos.toLocaleString('es')} pts</p>
      </div>
      ${jugador.esYo ? '<span class="rank-badge-yo">Tú</span>' : ''}
    `;
    lista.appendChild(li);
  });
}

/* ============================================================
   8. LOGROS — Mini inicio
   ============================================================ */
function initLogros() {
  const grid = document.getElementById('logros-grid');
  if (!grid) return;

  LOGROS.slice(0, 6).forEach((logro, i) => {
    const div = document.createElement('div');
    div.classList.add('logro-item');
    if (logro.bloqueado) div.classList.add('bloqueado');
    div.style.animationDelay = `${i * 0.06}s`;
    div.innerHTML = `
      <span class="logro-emoji">${logro.bloqueado ? '🔒' : logro.emoji}</span>
      <span class="logro-nombre">${logro.nombre}</span>
      <span class="logro-desc">${logro.desc}</span>
    `;
    if (!logro.bloqueado) div.addEventListener('click', () => showToast(`${logro.emoji} ${logro.nombre}`));
    grid.appendChild(div);
  });
}

/* ============================================================
   9. EJERCICIOS — Vista completa
   ============================================================ */
function initEjercicios() {
  renderEjerciciosFiltrados('todos');
}

function renderEjerciciosFiltrados(filtro) {
  const grid = document.getElementById('ejercicios-grid');
  if (!grid) return;
  grid.innerHTML = '';

  const filtrados = filtro === 'todos' ? EJERCICIOS : EJERCICIOS.filter(e => e.tipo === filtro);

  filtrados.forEach((ej, i) => {
    const div = document.createElement('div');
    div.classList.add('ejercicio-card-full');
    div.style.animationDelay = `${i * 0.06}s`;
    div.innerHTML = `
      <span class="ecf-icon">${ej.emoji}</span>
      <p class="ecf-nombre">${ej.nombre}</p>
      <p class="ecf-desc">${ej.desc}</p>
      <div class="ecf-chips">${ej.chips.map(c => `<span class="ecf-chip">${c}</span>`).join('')}</div>
      <span class="ecf-xp">+${ej.xp} XP</span>
      <button class="ecf-btn">¡Empezar! 🚀</button>
    `;
    div.querySelector('.ecf-btn').addEventListener('click', () => showToast(`🎯 Iniciando: ${ej.nombre}`));
    grid.appendChild(div);
  });

  if (filtrados.length === 0) {
    grid.innerHTML = `<p style="color:var(--gris-suave);font-weight:700;padding:20px;">No hay ejercicios en esta categoría aún.</p>`;
  }
}

/* ============================================================
   10. PROGRESO — Vista
   ============================================================ */
function initProgreso() {
  // Días de la semana
  const diasEl = document.getElementById('dias-semana');
  if (diasEl) {
    const dias = [
      { nombre: 'Lun', completado: true,  xp: '+30', hoy: false },
      { nombre: 'Mar', completado: true,  xp: '+50', hoy: false },
      { nombre: 'Mié', completado: true,  xp: '+20', hoy: false },
      { nombre: 'Jue', completado: true,  xp: '+40', hoy: false },
      { nombre: 'Vie', completado: true,  xp: '+30', hoy: true  },
      { nombre: 'Sáb', completado: false, xp: '',    hoy: false },
      { nombre: 'Dom', completado: false, xp: '',    hoy: false },
    ];
    dias.forEach(dia => {
      const div = document.createElement('div');
      div.classList.add('dia-item');
      if (dia.completado) div.classList.add('completado');
      if (dia.hoy) div.classList.add('hoy');
      div.innerHTML = `
        <span class="dia-nombre">${dia.nombre}</span>
        <span class="dia-emoji">${dia.completado ? '✅' : '⬜'}</span>
        <span class="dia-xp">${dia.xp}</span>
      `;
      diasEl.appendChild(div);
    });
  }

  // Actividad reciente
  const actEl = document.getElementById('actividad-list');
  if (actEl) {
    ACTIVIDAD.forEach(act => {
      const li = document.createElement('li');
      li.classList.add('actividad-item');
      li.innerHTML = `
        <span class="act-icon">${act.icono}</span>
        <div class="act-info">
          <p class="act-texto">${act.texto}</p>
          <p class="act-tiempo">${act.tiempo}</p>
        </div>
        <span class="act-xp">${act.xp}</span>
      `;
      actEl.appendChild(li);
    });
  }
}

/* ============================================================
   11. RANKING — Vista completa
   ============================================================ */
function initRankingFull() {
  // Pódio (top 3)
  const podio = document.getElementById('podio');
  if (podio) {
    const top3 = RANKING.slice(0, 3);
    // Orden pódio: 2do, 1ro, 3ro
    const orden = top3.length >= 3 ? [top3[1], top3[0], top3[2]] : top3;
    const emojis = ['🥈', '🥇', '🥉'];
    orden.forEach((jugador, i) => {
      if (!jugador) return;
      const div = document.createElement('div');
      div.classList.add('podio-item');
      div.style.animationDelay = `${i * 0.1}s`;
      div.innerHTML = `
        <span class="podio-avatar">${jugador.avatar}</span>
        <span class="podio-nombre">${jugador.nombre.split(' ')[0]}</span>
        <span class="podio-pts">${jugador.puntos.toLocaleString('es')} pts</span>
        <div class="podio-bloque">${emojis[i]}</div>
      `;
      podio.appendChild(div);
    });
  }

  // Lista completa
  const lista = document.getElementById('ranking-full-list');
  if (!lista) return;
  RANKING.forEach((jugador, i) => {
    const li = document.createElement('li');
    li.classList.add('ranking-full-item');
    if (jugador.esYo) li.classList.add('es-yo');
    li.style.animationDelay = `${i * 0.06}s`;
    li.innerHTML = `
      <div class="rf-pos">${i + 1}</div>
      <span class="rf-avatar">${jugador.avatar}</span>
      <div class="rf-info">
        <p class="rf-nombre">${jugador.nombre} ${jugador.esYo ? '<span class="rank-badge-yo">Tú</span>' : ''}</p>
        <p class="rf-instrumento">${jugador.instrumento}</p>
      </div>
      <span class="rf-pts">${jugador.puntos.toLocaleString('es')} pts</span>
    `;
    lista.appendChild(li);
  });
}

/* ============================================================
   12. LOGROS — Vista completa
   ============================================================ */
function initLogrosFull() {
  const grid = document.getElementById('logros-grid-full');
  if (!grid) return;
  LOGROS.forEach((logro, i) => {
    const div = document.createElement('div');
    div.classList.add('logro-item');
    if (logro.bloqueado) div.classList.add('bloqueado');
    div.style.animationDelay = `${i * 0.06}s`;
    div.innerHTML = `
      <span class="logro-emoji">${logro.bloqueado ? '🔒' : logro.emoji}</span>
      <span class="logro-nombre">${logro.nombre}</span>
      <span class="logro-desc">${logro.desc}</span>
    `;
    if (!logro.bloqueado) div.addEventListener('click', () => showToast(`${logro.emoji} ¡${logro.nombre}!`));
    grid.appendChild(div);
  });
}

/* ============================================================
   13. PERFIL — Avatar, info, edición
   ============================================================ */
function initPerfil() {
  // XP en perfil
  const fill = document.getElementById('perfil-xp-fill');
  const text = document.getElementById('perfil-xp-text');
  const racha = document.getElementById('perfil-racha-val');
  const pts   = document.getElementById('perfil-pts-val');
  const rank  = document.getElementById('perfil-rank-val');

  if (fill) fill.style.width = `${(ESTUDIANTE.xp / ESTUDIANTE.xpMax) * 100}%`;
  if (text) text.textContent  = `${ESTUDIANTE.xp} / ${ESTUDIANTE.xpMax} XP`;
  if (racha) racha.textContent = ESTUDIANTE.racha;
  if (pts)   pts.textContent   = ESTUDIANTE.puntos.toLocaleString('es');
  if (rank)  rank.textContent  = `#${ESTUDIANTE.rankingPos}`;

  // Logros mini en perfil
  const logrosRow = document.getElementById('perfil-logros-row');
  if (logrosRow) {
    LOGROS.filter(l => !l.bloqueado).forEach(logro => {
      const div = document.createElement('div');
      div.classList.add('perfil-logro-mini');
      div.innerHTML = `
        <span class="plm-emoji">${logro.emoji}</span>
        <span class="plm-nombre">${logro.nombre}</span>
      `;
      logrosRow.appendChild(div);
    });
  }

  // Cambio de foto: click en overlay o en círculo del avatar
  const overlay     = document.getElementById('perfil-avatar-overlay');
  const fileInput   = document.getElementById('avatar-file-input');
  const modalOverlay= document.getElementById('modal-foto');
  const modalPrev   = document.getElementById('modal-preview');

  overlay?.addEventListener('click', (e) => {
    e.stopPropagation();
    fileInput?.click();
  });

  fileInput?.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    try {
      pendingAvatarDataUrl = await resizeImage(file);
      if (modalPrev) modalPrev.src = pendingAvatarDataUrl;
      modalOverlay?.classList.remove('hidden');
    } catch (error) {
      showToast('No se pudo leer la imagen seleccionada.');
    }
  });

  // Confirmar foto
  document.getElementById('btn-foto-confirm')?.addEventListener('click', () => {
    const src = modalPrev?.src;
    if (src) {
      saveProfile(src).catch((error) => showToast(error.message));
      applyAvatarEverywhere(src);
      showToast('📷 ¡Foto de perfil actualizada!');
    }
    modalOverlay?.classList.add('hidden');
    if (fileInput) fileInput.value = '';
  });

  // Cancelar foto
  document.getElementById('btn-foto-cancel')?.addEventListener('click', () => {
    modalOverlay?.classList.add('hidden');
    if (fileInput) fileInput.value = '';
  });

  // Editar información
  const btnEdit   = document.getElementById('btn-edit-info');
  const infoView  = document.getElementById('info-view');
  const infoEdit  = document.getElementById('info-edit');

  btnEdit?.addEventListener('click', () => {
    infoView?.classList.add('hidden');
    infoEdit?.classList.remove('hidden');
    btnEdit.textContent = '';
  });

  document.getElementById('btn-cancel-info')?.addEventListener('click', () => {
    infoEdit?.classList.add('hidden');
    infoView?.classList.remove('hidden');
    btnEdit.textContent = '✏️ Editar';
  });

  document.getElementById('btn-save-info')?.addEventListener('click', async () => {
    try {
      await saveProfile();
      initGreeting();
      infoEdit?.classList.add('hidden');
      infoView?.classList.remove('hidden');
      btnEdit.textContent = 'Editar';
      showToast('Informacion guardada correctamente.');
    } catch (error) {
      showToast(error.message);
    }
    return;

    const nuevoNombre = document.getElementById('edit-nombre')?.value.trim();
    const nuevoEmail  = document.getElementById('edit-email')?.value.trim();

    if (nuevoNombre) {
      document.getElementById('info-nombre').textContent  = nuevoNombre;
      document.getElementById('s-name').textContent       = nuevoNombre;
      document.getElementById('perfil-nombre-big').textContent = nuevoNombre;
      ESTUDIANTE.nombre = nuevoNombre.split(' ')[0];
      document.getElementById('greeting-title').textContent = `¡Hola, ${ESTUDIANTE.nombre}! 👋`;
    }
    if (nuevoEmail) {
      document.getElementById('info-email').textContent = nuevoEmail;
    }

    infoEdit?.classList.add('hidden');
    infoView?.classList.remove('hidden');
    btnEdit.textContent = '✏️ Editar';
    showToast('💾 ¡Información guardada correctamente!');
  });
}

/* ============================================================
   14. ANIMACIÓN DE STATS
   ============================================================ */
function animateStats() {
  const targets = [
    { id: 'stat-points',     value: ESTUDIANTE.puntos,           prefix: '',  suffix: '' },
    { id: 'stat-streak',     value: ESTUDIANTE.racha,            prefix: '',  suffix: '' },
    { id: 'stat-ejercicios', value: ESTUDIANTE.ejerciciosHechos, prefix: '',  suffix: '' },
    { id: 'stat-nivel',      value: ESTUDIANTE.rankingPos,       prefix: '#', suffix: '' },
  ];
  targets.forEach(({ id, value, prefix = '', suffix = '' }) => {
    const el = document.getElementById(id);
    if (!el) return;
    let current = 0;
    const step = Math.max(1, Math.ceil(value / 30));
    const interval = setInterval(() => {
      current = Math.min(current + step, value);
      el.textContent = prefix + current.toLocaleString('es') + suffix;
      if (current >= value) clearInterval(interval);
    }, 40);
  });
}

/* ============================================================
   15. FALLBACKS DE IMÁGENES
   ============================================================ */
function initImageFallbacks() {
  document.querySelector('.s-logo-img')?.addEventListener('error', function () { this.style.display = 'none'; });
  document.querySelector('.banner-img')?.addEventListener('error', function () {
    this.style.display = 'none';
    const fallback = this.nextElementSibling;
    if (fallback) fallback.style.display = 'flex';
  });
}

/* ============================================================
   16. TOAST
   ============================================================ */
function showToast(mensaje, duracion = 2500) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = mensaje;
  toast.classList.add('show');
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), duracion);
}
