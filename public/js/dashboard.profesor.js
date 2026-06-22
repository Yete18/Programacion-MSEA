document.addEventListener('DOMContentLoaded', () => {
  applyProfesorData();
  initGreeting();
  initNavigation();
  initSidebarMobile();
  initNotificaciones();
  initPerfil();
  initModalTarea();
  initFiltrosTareas();
  initBuscadorAlumnos();
  renderInicio();
  renderAlumnos();
  renderTareas();
  renderEjercicios();
  renderProgresoFull();
  renderCalendarioFull();
  animateStats();
});

const PROFESOR = Object.assign({
  nombre: 'Profesor',
  primerNombre: 'Profesor',
  usuario: 'profesor',
  email: 'Sin correo',
  especialidad: 'Sin especialidad',
  foto: '',
  totalAlumnos: 0,
  tareasRevision: 0,
  tareasCompletadas: 0,
  clasesHoy: 0,
  miembroDesde: 'Sin fecha',
}, window.MSEA_PROFESOR || {});

const ALUMNOS = Array.isArray(window.MSEA_ALUMNOS) ? window.MSEA_ALUMNOS : [];
const TAREAS_DATA = Array.isArray(window.MSEA_TAREAS) ? window.MSEA_TAREAS : [];

const NOTIFICACIONES = [
  { icon:'🎓', texto:'Dashboard del profesor conectado con Laravel', tiempo:'Ahora', leida:false },
];

const EJERCICIOS_DATA = [
  { icon:'🎵', nombre:'Escala de Do Mayor', desc:'Posición de primera mano, tempo 60', chips:['Violín','Básico','5 min'], xp:20 },
  { icon:'🎶', nombre:'Escala de Sol Mayor', desc:'Con cambio de posición, tempo 72', chips:['Violín','Medio','8 min'], xp:30 },
  { icon:'🎼', nombre:'Ejercicio de arco', desc:'Distribución del arco, notas largas', chips:['Todos','Medio','10 min'], xp:35 },
];

const CLASES_PROXIMAS = [
  { hora:'08:00', dia:'Hoy', nombre:'Clase grupal', alumnos:PROFESOR.totalAlumnos, sala:'Aula 1' },
];

function setEl(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = val;
}

function applyProfesorData() {
  const role = `👩‍🏫 Profesor · ${PROFESOR.especialidad || 'Sin especialidad'}`;
  setEl('s-name', `Prof. ${PROFESOR.nombre}`);
  setEl('s-role', role);
  setEl('pi-nombre', PROFESOR.nombre);
  setEl('pi-usuario', PROFESOR.usuario);
  setEl('pi-email', PROFESOR.email);
  setEl('pi-especialidad', PROFESOR.especialidad);
  setEl('pi-alumnos', `${PROFESOR.totalAlumnos} estudiantes`);
  setEl('pi-fecha', PROFESOR.miembroDesde);
  setEl('s-total-alumnos', PROFESOR.totalAlumnos);
  setEl('s-tareas-pendientes', PROFESOR.tareasRevision);
  setEl('s-clases-hoy', PROFESOR.clasesHoy);
  setEl('badge-tareas', PROFESOR.tareasRevision);
  applyAvatar(PROFESOR.foto);
  fillAlumnoSelect();
}

function applyAvatar(src) {
  ['sidebar-avatar','header-avatar','perfil-avatar'].forEach(id => {
    const img = document.getElementById(id);
    if (!img) return;
    img.src = src || '';
  });
}

function fillAlumnoSelect() {
  const select = document.getElementById('tarea-alumno');
  if (!select) return;
  select.innerHTML = '<option value="">Selecciona un alumno</option>';
  ALUMNOS.forEach(a => {
    const opt = document.createElement('option');
    opt.value = a.id || a.nombre;
    opt.textContent = `${a.nombre} · ${a.instrumento || 'Sin instrumento'}`;
    opt.dataset.instrumento = a.instrumento || '';
    select.appendChild(opt);
  });
}

function initGreeting() {
  const hora = new Date().getHours();
  const saludo = hora < 12 ? 'Buenos días' : hora < 19 ? 'Buenas tardes' : 'Buenas noches';
  setEl('greeting-title', `¡${saludo}, Prof. ${PROFESOR.primerNombre || PROFESOR.nombre}! 👋`);
  const sub = document.getElementById('greeting-sub');
  if (sub) sub.innerHTML = `Tienes <strong>${PROFESOR.tareasRevision}</strong> entregas pendientes de revisión`;
}

function initNavigation() {
  const navItems = document.querySelectorAll('.nav-item');
  const pages = document.querySelectorAll('.page-content');
  const goTo = (page) => {
    navItems.forEach(n => n.classList.toggle('active', n.dataset.page === page));
    pages.forEach(p => p.classList.toggle('active', p.id === `page-${page}`));
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('sidebar-overlay')?.classList.remove('active');
    document.body.style.overflow = '';
  };
  navItems.forEach(item => item.addEventListener('click', e => {
    e.preventDefault();
    goTo(item.dataset.page);
  }));
  document.addEventListener('click', e => {
    const trigger = e.target.closest('.nav-trigger');
    if (!trigger) return;
    e.preventDefault();
    goTo(trigger.dataset.page);
  });
}

function initSidebarMobile() {
  const hamburger = document.getElementById('hamburger');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  hamburger?.addEventListener('click', () => {
    sidebar?.classList.add('open');
    overlay?.classList.add('active');
    document.body.style.overflow = 'hidden';
  });
  overlay?.addEventListener('click', () => {
    sidebar?.classList.remove('open');
    overlay?.classList.remove('active');
    document.body.style.overflow = '';
  });
}

function initNotificaciones() {
  const btn = document.getElementById('notif-btn');
  const panel = document.getElementById('notif-panel');
  const lista = document.getElementById('notif-list');
  const dot = document.getElementById('notif-dot');
  const markAll = document.getElementById('notif-mark-all');
  const render = () => {
    if (!lista) return;
    lista.innerHTML = '';
    const noLeidas = NOTIFICACIONES.filter(n => !n.leida).length;
    dot?.classList.toggle('hidden', noLeidas === 0);
    NOTIFICACIONES.forEach((notif, i) => {
      const li = document.createElement('li');
      li.className = `notif-item ${notif.leida ? 'leida' : ''}`;
      li.style.animationDelay = `${i * 0.05}s`;
      li.innerHTML = `<span class="notif-item-icon">${notif.icon}</span><div class="notif-item-body"><p class="notif-item-texto">${notif.texto}</p><p class="notif-item-tiempo">${notif.tiempo}</p></div><span class="notif-item-dot"></span>`;
      li.addEventListener('click', () => { notif.leida = true; render(); });
      lista.appendChild(li);
    });
  };
  render();
  btn?.addEventListener('click', e => { e.stopPropagation(); panel?.classList.toggle('open'); });
  document.addEventListener('click', e => {
    if (!panel?.contains(e.target) && !btn?.contains(e.target)) panel?.classList.remove('open');
  });
  markAll?.addEventListener('click', () => { NOTIFICACIONES.forEach(n => n.leida = true); render(); });
}

function initPerfil() {
  const overlay = document.getElementById('perfil-overlay');
  const drawer = document.getElementById('perfil-drawer');
  const previewBar = document.getElementById('avatar-preview-bar');
  const previewImg = document.getElementById('avatar-preview-img');
  const avatarInput = document.getElementById('avatar-input');
  const openDrawer = () => { drawer?.classList.add('open'); overlay?.classList.add('active'); };
  const closeDrawer = () => { drawer?.classList.remove('open'); overlay?.classList.remove('active'); if (previewBar) previewBar.style.display = 'none'; };
  document.getElementById('btn-open-perfil')?.addEventListener('click', openDrawer);
  document.getElementById('header-avatar-btn')?.addEventListener('click', openDrawer);
  document.getElementById('perfil-close')?.addEventListener('click', closeDrawer);
  overlay?.addEventListener('click', closeDrawer);
  avatarInput?.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    if (!['image/png','image/jpeg','image/gif','image/webp'].includes(file.type)) {
      showToast('Formato no permitido. Usa JPG, PNG, GIF o WEBP.');
      return;
    }
    const reader = new FileReader();
    reader.onload = ev => {
      if (previewImg) previewImg.src = ev.target.result;
      if (previewBar) previewBar.style.display = 'flex';
    };
    reader.readAsDataURL(file);
  });
  document.getElementById('btn-avatar-confirm')?.addEventListener('click', () => {
    if (previewImg?.src) applyAvatar(previewImg.src);
    if (previewBar) previewBar.style.display = 'none';
    showToast('Foto actualizada en pantalla. Falta conectar guardado del perfil.');
  });
  document.getElementById('btn-avatar-cancel')?.addEventListener('click', () => {
    if (previewBar) previewBar.style.display = 'none';
    if (avatarInput) avatarInput.value = '';
  });
}

function initModalTarea() {
  const overlay = document.getElementById('modal-overlay');
  const modal = document.getElementById('modal-tarea');
  const btnSubmit = document.getElementById('btn-modal-submit');
  const openModal = () => { overlay?.classList.add('active'); modal?.classList.add('open'); };
  const closeModal = () => { overlay?.classList.remove('active'); modal?.classList.remove('open'); resetModal(); };
  document.getElementById('btn-nueva-tarea')?.addEventListener('click', openModal);
  document.getElementById('btn-nueva-tarea-lg')?.addEventListener('click', openModal);
  document.getElementById('modal-close')?.addEventListener('click', closeModal);
  document.getElementById('btn-modal-cancel')?.addEventListener('click', closeModal);
  overlay?.addEventListener('click', closeModal);
  const fechaInput = document.getElementById('tarea-fecha');
  if (fechaInput) {
    const hoy = new Date().toISOString().split('T')[0];
    fechaInput.min = hoy;
    fechaInput.value = hoy;
  }
  document.getElementById('tarea-alumno')?.addEventListener('change', e => {
    const selected = e.target.options[e.target.selectedIndex];
    const instrumento = document.getElementById('tarea-instrumento');
    if (instrumento) instrumento.value = selected?.dataset.instrumento || '';
  });
  btnSubmit?.addEventListener('click', async () => {
    if (!validateModal()) return;
    const titulo = document.getElementById('tarea-titulo')?.value.trim();
    const alumno = document.getElementById('tarea-alumno');
    const alumnoNombre = alumno?.options[alumno.selectedIndex]?.textContent || '';
    btnSubmit.disabled = true;
    try {
      const archivo = document.getElementById('tarea-archivo')?.files?.[0];
      const response = await fetch('/dashboard-profesor/tareas', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({
          titulo,
          id_estudiante: alumno?.value,
          descripcion: document.getElementById('tarea-desc')?.value.trim() || '',
          fecha_limite: document.getElementById('tarea-fecha')?.value || null,
          xp_recompensa: Number(document.getElementById('tarea-xp')?.value || 30),
          tipo: 'Repertorio',
          archivo: archivo?.name || null,
        }),
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(data.message || 'No se pudo guardar la tarea.');
      TAREAS_DATA.unshift({
        id: data.tarea?.id_tarea,
        titulo,
        alumno: alumnoNombre.split('·')[0].trim(),
        instrumento: document.getElementById('tarea-instrumento')?.value || 'Sin instrumento',
        limite: document.getElementById('tarea-fecha')?.value || null,
        estado: 'pendiente',
      });
      renderTareas();
      closeModal();
      showToast(`Tarea "${titulo}" guardada para ${alumnoNombre.split('·')[0].trim()}.`);
    } catch (error) {
      showToast(error.message);
    } finally {
      btnSubmit.disabled = false;
    }
  });
}

function validateModal() {
  let ok = true;
  const titulo = document.getElementById('tarea-titulo')?.value.trim();
  const alumno = document.getElementById('tarea-alumno')?.value;
  const fecha = document.getElementById('tarea-fecha')?.value;
  const err = (id, msg = '') => { const el = document.getElementById(id); if (el) el.textContent = msg; };
  err('err-titulo'); err('err-alumno'); err('err-fecha');
  if (!titulo || titulo.length < 3) { err('err-titulo', 'Ingresa un titulo valido.'); ok = false; }
  if (!alumno) { err('err-alumno', 'Selecciona un alumno.'); ok = false; }
  if (!fecha) { err('err-fecha', 'Selecciona una fecha limite.'); ok = false; }
  return ok;
}

function resetModal() {
  ['tarea-titulo','tarea-desc','tarea-instrumento','tarea-archivo'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  const alumno = document.getElementById('tarea-alumno');
  if (alumno) alumno.selectedIndex = 0;
  ['err-titulo','err-alumno','err-fecha'].forEach(id => setEl(id, ''));
}

function initFiltrosTareas() {
  const filtros = document.querySelectorAll('.filtro-btn');
  filtros.forEach(btn => btn.addEventListener('click', () => {
    filtros.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderTareas(btn.dataset.filtro);
  }));
}

function initBuscadorAlumnos() {
  const input = document.getElementById('search-alumnos');
  input?.addEventListener('input', () => renderAlumnos(input.value.toLowerCase().trim()));
}

function renderInicio() {
  renderEntregas();
  renderAlumnosTop();
  renderProgresoGrupal();
  renderClases();
}

function renderEntregas() {
  const lista = document.getElementById('entregas-list');
  if (!lista) return;
  lista.innerHTML = '';
  const entregas = TAREAS_DATA.filter(t => t.estado === 'entregada');
  if (!entregas.length) {
    lista.innerHTML = '<li class="entrega-item"><div class="entrega-info"><p class="entrega-alumno">Sin entregas pendientes</p><p class="entrega-tarea">Cuando los estudiantes entreguen tareas, aparecerán aquí.</p></div></li>';
    return;
  }
  entregas.slice(0, 4).forEach((e, i) => {
    const li = document.createElement('li');
    li.className = 'entrega-item';
    li.style.animationDelay = `${i * 0.07}s`;
    li.innerHTML = `<span class="entrega-avatar">🎓</span><div class="entrega-info"><p class="entrega-alumno">${e.alumno}</p><p class="entrega-tarea">📋 ${e.titulo}</p></div><span class="entrega-tiempo">${formatFecha(e.limite)}</span><button class="btn-revisar">Revisar</button>`;
    li.querySelector('.btn-revisar')?.addEventListener('click', ev => { ev.stopPropagation(); openRevisarModal(e); });
    lista.appendChild(li);
  });
}

function renderAlumnosTop() {
  const topLista = document.getElementById('alumnos-top-list');
  if (!topLista) return;
  topLista.innerHTML = '';
  const top = [...ALUMNOS].sort((a, b) => (b.puntos || 0) - (a.puntos || 0)).slice(0, 4);
  if (!top.length) {
    topLista.innerHTML = '<li class="alumno-top-item"><div class="at-info"><p class="at-nombre">Sin alumnos asignados</p><p class="at-racha">Asigna estudiantes al profesor para ver actividad.</p></div></li>';
    return;
  }
  top.forEach((a, i) => {
    const li = document.createElement('li');
    li.className = 'alumno-top-item';
    const medal = i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `#${i + 1}`;
    li.innerHTML = `<span class="at-pos">${medal}</span><span class="at-avatar">${a.avatar || '🎓'}</span><div class="at-info"><p class="at-nombre">${a.nombre}</p><p class="at-racha">🔥 ${a.racha || 0} días de racha</p></div><span class="at-pts">${Number(a.puntos || 0).toLocaleString('es')} pts</span>`;
    topLista.appendChild(li);
  });
}

function renderProgresoGrupal() {
  const progLista = document.getElementById('progreso-grupal-list');
  if (!progLista) return;
  progLista.innerHTML = '';
  const grupos = {};
  ALUMNOS.forEach(a => {
    const inst = a.instrumento || 'Sin instrumento';
    if (!grupos[inst]) grupos[inst] = { alumnos: 0, puntos: 0 };
    grupos[inst].alumnos += 1;
    grupos[inst].puntos += Number(a.puntos || 0);
  });
  const entries = Object.entries(grupos);
  if (!entries.length) {
    progLista.innerHTML = '<p class="page-desc">Aún no hay progreso grupal para mostrar.</p>';
    return;
  }
  entries.forEach(([instrumento, data], i) => {
    const pct = Math.min(100, Math.round((data.puntos / Math.max(data.alumnos, 1)) / 20));
    const div = document.createElement('div');
    div.className = 'progreso-inst-row';
    div.innerHTML = `<div class="pi-row-header"><span class="pi-inst-name">${instrumento}</span><span class="pi-pct">${pct}%</span></div><div class="pi-track"><div class="pi-fill" style="width:0%;background:${['#8CC63F','#42A5F5','#AB47BC','#FF9800'][i % 4]}" data-w="${pct}%"></div></div><span class="pi-alumnos">${data.alumnos} alumnos</span>`;
    progLista.appendChild(div);
  });
  setTimeout(() => document.querySelectorAll('.pi-fill').forEach(el => { el.style.width = el.dataset.w; }), 200);
}

function renderClases() {
  const clasesLista = document.getElementById('clases-list');
  if (!clasesLista) return;
  clasesLista.innerHTML = '';
  CLASES_PROXIMAS.forEach((c, i) => {
    const li = document.createElement('li');
    li.className = 'clase-item';
    li.style.animationDelay = `${i * 0.07}s`;
    li.innerHTML = `<div class="clase-hora"><span class="clase-hh">${c.hora}</span><span class="clase-dia">${c.dia}</span></div><div class="clase-info"><p class="clase-nombre">${c.nombre}</p><p class="clase-meta">👥 ${c.alumnos} alumnos · 📍 ${c.sala}</p></div><span class="clase-chip">${c.dia}</span>`;
    clasesLista.appendChild(li);
  });
}

function renderAlumnos(filtro = '') {
  const grid = document.getElementById('alumnos-grid');
  if (!grid) return;
  grid.innerHTML = '';
  const filtrados = filtro ? ALUMNOS.filter(a => a.nombre.toLowerCase().includes(filtro) || String(a.instrumento || '').toLowerCase().includes(filtro)) : ALUMNOS;
  if (!filtrados.length) {
    grid.innerHTML = '<p style="color:var(--gris-suave);font-weight:700;padding:20px">No se encontraron alumnos.</p>';
    return;
  }
  filtrados.forEach((a, i) => {
    const card = document.createElement('div');
    card.className = 'alumno-card';
    card.style.animationDelay = `${i * 0.06}s`;
    const pct = Math.round((Number(a.xp || 0) / Math.max(Number(a.xpMax || 500), 1)) * 100);
    card.innerHTML = `<div class="ac-avatar">${a.avatar || '🎓'}</div><p class="ac-nombre">${a.nombre}</p><p class="ac-instrumento">🎻 ${a.instrumento || 'Sin instrumento'}</p><span class="ac-nivel">${a.nivel || 'Sin nivel'}</span><div class="ac-stats"><div class="ac-stat"><span class="ac-stat-val">⭐${Number(a.puntos || 0).toLocaleString('es')}</span><span class="ac-stat-lbl">Puntos</span></div><div class="ac-stat"><span class="ac-stat-val">🔥${a.racha || 0}</span><span class="ac-stat-lbl">Racha</span></div><div class="ac-stat"><span class="ac-stat-val">${pct}%</span><span class="ac-stat-lbl">XP Nivel</span></div></div><button class="btn-ver-alumno">Ver progreso</button>`;
    card.querySelector('.btn-ver-alumno')?.addEventListener('click', () => openProgresoModal(a.id));
    grid.appendChild(card);
  });
}

function renderTareas(filtro = 'todas') {
  const tbody = document.getElementById('tareas-tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const data = filtro === 'todas' ? TAREAS_DATA : TAREAS_DATA.filter(t => t.estado === filtro);
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="6">Todavía no hay tareas guardadas.</td></tr>';
    return;
  }
  const estadoMap = {
    pendiente: { cls:'estado-pendiente', txt:'⏳ Pendiente' },
    entregada: { cls:'estado-entregada', txt:'📬 Entregada' },
    calificada: { cls:'estado-calificada', txt:'✅ Calificada' },
  };
  data.forEach((t, i) => {
    const tr = document.createElement('tr');
    tr.style.animationDelay = `${i * 0.05}s`;
    const est = estadoMap[t.estado] || estadoMap.pendiente;
    tr.innerHTML = `<td><strong>${t.titulo}</strong></td><td>${t.alumno}</td><td>${t.instrumento || 'Sin instrumento'}</td><td>${formatFecha(t.limite)}</td><td><span class="estado-chip ${est.cls}">${est.txt}</span></td><td><button class="btn-accion revisar">Ver</button></td>`;
    tr.querySelector('.revisar').addEventListener('click', () => openRevisarModal(t));
    tbody.appendChild(tr);
  });
}

function renderEjercicios() {
  const grid = document.getElementById('ejercicios-grid');
  if (!grid) return;
  grid.innerHTML = '';
  EJERCICIOS_DATA.forEach((e, i) => {
    const card = document.createElement('div');
    card.className = 'ejercicio-card';
    card.style.animationDelay = `${i * 0.07}s`;
    card.innerHTML = `<div class="ej-icon">${e.icon}</div><p class="ej-nombre">${e.nombre}</p><p class="ej-desc">${e.desc}</p><div class="ej-chips">${e.chips.map(c => `<span class="ej-chip">${c}</span>`).join('')}</div><span class="ej-xp">+${e.xp} XP</span>`;
    card.addEventListener('click', () => showToast(`Ejercicio: ${e.nombre}`));
    grid.appendChild(card);
  });
}

function renderProgresoFull() {
  const grid = document.getElementById('progreso-full-grid');
  if (!grid) return;
  grid.innerHTML = '';
  if (!ALUMNOS.length) {
    grid.innerHTML = '<p class="page-desc">No hay alumnos asignados todavía.</p>';
    return;
  }
  ALUMNOS.forEach((a, i) => {
    const pct = Math.round((Number(a.xp || 0) / Math.max(Number(a.xpMax || 500), 1)) * 100);
    const card = document.createElement('div');
    card.className = 'progreso-alumno-card';
    card.style.animationDelay = `${i * 0.06}s`;
    card.innerHTML = `<div class="pa-avatar">${a.avatar || '🎓'}</div><div class="pa-info"><p class="pa-nombre">${a.nombre} · ${a.instrumento || 'Sin instrumento'}</p><div class="pa-xp-track"><div class="pa-xp-fill" style="width:0%" data-w="${pct}%"></div></div><div class="pa-meta"><span>${a.nivel || 'Nivel 1'}</span><span>${a.xp || 0} / ${a.xpMax || 500} XP (${pct}%)</span></div></div>`;
    grid.appendChild(card);
  });
  setTimeout(() => document.querySelectorAll('.pa-xp-fill').forEach(el => { el.style.width = el.dataset.w; }), 300);
}

function renderCalendarioFull() {
  const wrap = document.getElementById('calendario-full');
  if (!wrap) return;
  wrap.innerHTML = '';
  const grupos = {};
  CLASES_PROXIMAS.forEach(c => { if (!grupos[c.dia]) grupos[c.dia] = []; grupos[c.dia].push(c); });
  Object.entries(grupos).forEach(([dia, clases]) => {
    const div = document.createElement('div');
    div.className = 'cal-dia-grupo';
    div.innerHTML = `<div class="cal-dia-header">📅 ${dia}</div>`;
    const ul = document.createElement('ul');
    ul.className = 'cal-clases-list';
    clases.forEach(c => {
      const li = document.createElement('li');
      li.className = 'cal-clase-item';
      li.innerHTML = `<span class="cal-hora">${c.hora}</span><div class="cal-clase-info"><p class="cal-clase-nombre">${c.nombre}</p><p class="cal-clase-meta">👥 ${c.alumnos} alumnos · 📍 ${c.sala}</p></div><span class="cal-clase-chip">${c.sala}</span>`;
      ul.appendChild(li);
    });
    div.appendChild(ul);
    wrap.appendChild(div);
  });
}

function animateStats() {
  [
    { id:'st-alumnos', val: PROFESOR.totalAlumnos },
    { id:'st-por-revisar', val: PROFESOR.tareasRevision },
    { id:'st-completadas', val: PROFESOR.tareasCompletadas },
    { id:'st-clases', val: PROFESOR.clasesHoy },
  ].forEach(({ id, val }) => {
    const el = document.getElementById(id);
    if (!el) return;
    let cur = 0;
    const step = Math.max(1, Math.ceil(Number(val || 0) / 25));
    const iv = setInterval(() => {
      cur = Math.min(cur + step, Number(val || 0));
      el.textContent = cur;
      if (cur >= Number(val || 0)) clearInterval(iv);
    }, 40);
  });
}

function formatFecha(str) {
  if (!str) return '-';
  const [y, m, d] = String(str).split('-');
  const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
  return `${d || ''} ${meses[(parseInt(m, 10) || 1) - 1]} ${y || ''}`.trim();
}

function wait(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function showToast(msg, dur = 2500) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._t);
  t._t = setTimeout(() => t.classList.remove('show'), dur);
}

async function openProgresoModal(alumnoId) {
  const modal = document.getElementById('modal-progreso-alumno');
  const overlay = document.getElementById('modal-overlay');
  const nombreEl = document.getElementById('progreso-alumno-nombre');
  const minutosEl = document.getElementById('progreso-total-minutos');
  const completadosEl = document.getElementById('progreso-ejercicios-completados');
  const afinacionesEl = document.getElementById('progreso-afinaciones-hechas');
  const precisionEl = document.getElementById('progreso-precision-ritmo');
  const historialLista = document.getElementById('progreso-historial-lista');

  if (!modal || !overlay) return;

  if (nombreEl) nombreEl.textContent = 'Cargando...';
  if (historialLista) historialLista.innerHTML = '<li class="notif-item">Cargando historial...</li>';

  modal.style.display = 'block';
  modal.classList.add('open');
  overlay.classList.add('active');

  const close = () => {
    modal.style.display = 'none';
    modal.classList.remove('open');
    overlay.classList.remove('active');
  };

  document.getElementById('btn-progreso-cerrar').onclick = close;
  document.getElementById('modal-progreso-close').onclick = close;

  try {
    const response = await fetch(`/dashboard-profesor/alumnos/${alumnoId}/progreso`);
    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'Error al obtener progreso.');

    const est = data.estudiante;
    if (nombreEl) nombreEl.textContent = est.nombre;
    if (minutosEl) minutosEl.textContent = est.totalMinutos;
    if (completadosEl) completadosEl.textContent = est.ejerciciosHechos;
    if (afinacionesEl) afinacionesEl.textContent = est.afinacionesHechas;
    if (precisionEl) precisionEl.textContent = est.precisionRitmo + '%';

    if (historialLista) {
      historialLista.innerHTML = '';
      if (!data.historial || data.historial.length === 0) {
        historialLista.innerHTML = '<li class="notif-item" style="padding: 10px 0;"><div class="notif-item-body"><p class="notif-item-texto" style="margin:0;font-size:0.9rem;color:#64748b;">Sin prácticas registradas aún.</p></div></li>';
      } else {
        data.historial.forEach(h => {
          const li = document.createElement('li');
          li.className = 'notif-item';
          li.style.borderBottom = '1px solid #f1f5f9';
          li.style.padding = '8px 0';
          li.innerHTML = `
            <div class="notif-item-body" style="margin-left: 10px;">
              <p class="notif-item-texto" style="font-weight:700;margin:0;font-size:0.9rem;color:#1e293b;">${h.tipo}</p>
              <p class="notif-item-texto" style="margin:2px 0 0 0;font-size:0.8rem;color:#475569;">${h.detalle}</p>
              <p class="notif-item-tiempo" style="margin:2px 0 0 0;font-size:0.75rem;color:#94a3b8;">${h.fecha}</p>
            </div>
          `;
          historialLista.appendChild(li);
        });
      }
    }
  } catch (error) {
    showToast(error.message);
    close();
  }
}

function openRevisarModal(tarea) {
  const modal = document.getElementById('modal-revisar-tarea');
  const overlay = document.getElementById('modal-overlay');
  
  if (!modal || !overlay) return;

  const tTitulo = document.getElementById('revisar-tarea-titulo');
  const tAlumno = document.getElementById('revisar-tarea-alumno');
  const tComentario = document.getElementById('revisar-tarea-comentario-estudiante');
  const tArchivo = document.getElementById('revisar-tarea-archivo-name');
  const btnSubmit = document.getElementById('btn-revisar-submit');
  const btnCancel = document.getElementById('btn-revisar-cancel');
  const btnClose = document.getElementById('modal-revisar-close');
  
  const califInput = document.getElementById('revisar-calificacion');
  const feedbackText = document.getElementById('revisar-comentario-profesor');

  if (tTitulo) tTitulo.textContent = tarea.titulo;
  if (tAlumno) tAlumno.textContent = tarea.alumno;
  
  if (tarea.estado === 'pendiente') {
    if (tComentario) tComentario.textContent = 'El estudiante aún no ha enviado comentarios.';
    if (tArchivo) tArchivo.textContent = 'Sin archivo entregado.';
    if (califInput) califInput.disabled = true;
    if (feedbackText) feedbackText.disabled = true;
    if (btnSubmit) btnSubmit.style.display = 'none';
  } else {
    if (tComentario) tComentario.textContent = tarea.comentario_estudiante || 'Sin comentarios del estudiante.';
    if (tArchivo) tArchivo.textContent = tarea.archivo_entrega || 'Sin archivo adjunto.';
    if (califInput) {
      califInput.disabled = false;
      califInput.value = 100;
    }
    if (feedbackText) {
      feedbackText.disabled = false;
      feedbackText.value = '';
    }
    if (btnSubmit) btnSubmit.style.display = 'inline-block';
  }

  modal.style.display = 'block';
  modal.classList.add('open');
  overlay.classList.add('active');

  const close = () => {
    modal.style.display = 'none';
    modal.classList.remove('open');
    overlay.classList.remove('active');
    
    // Clean event handlers
    const newSubmitBtn = btnSubmit.cloneNode(true);
    btnSubmit.replaceWith(newSubmitBtn);
    const newCancelBtn = btnCancel.cloneNode(true);
    btnCancel.replaceWith(newCancelBtn);
    const newCloseBtn = btnClose.cloneNode(true);
    btnClose.replaceWith(newCloseBtn);
  };

  document.getElementById('btn-revisar-cancel').onclick = close;
  document.getElementById('modal-revisar-close').onclick = close;

  if (tarea.estado !== 'pendiente' && btnSubmit) {
    document.getElementById('btn-revisar-submit').onclick = async () => {
      const calif = Number(document.getElementById('revisar-calificacion').value);
      const comments = document.getElementById('revisar-comentario-profesor').value.trim();
      const errEl = document.getElementById('err-revisar-calificacion');

      if (isNaN(calif) || calif < 0 || calif > 100) {
        if (errEl) errEl.textContent = 'Ingresa una calificación válida entre 0 y 100.';
        return;
      }

      if (errEl) errEl.textContent = '';
      
      const submitBtnCurrent = document.getElementById('btn-revisar-submit');
      if (submitBtnCurrent) submitBtnCurrent.disabled = true;

      try {
        const response = await fetch(`/dashboard-profesor/entregas/${tarea.id_entrega}/calificar`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          },
          body: JSON.stringify({
            calificacion: calif,
            comentario_profesor: comments,
          }),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'Error al calificar tarea.');

        showToast('✅ ¡Tarea calificada correctamente!');
        tarea.estado = 'calificada';
        close();
        
        // Refresh UI lists
        renderEntregas();
        renderTareas();
      } catch (error) {
        showToast(error.message);
      } finally {
        const submitBtnCurrent = document.getElementById('btn-revisar-submit');
        if (submitBtnCurrent) submitBtnCurrent.disabled = false;
      }
    };
  }
}

window.showToast = showToast;
