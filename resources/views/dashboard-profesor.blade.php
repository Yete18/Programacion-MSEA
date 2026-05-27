<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>MSEA - Dashboard Profesor</title>
  <link rel="stylesheet" href="{{ asset('css/dashboard.profesor.css') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&family=Baloo+2:wght@700;800&display=swap" rel="stylesheet" />
</head>
<body>
<div class="app-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <img src="{{ asset('assets/img/logo.jpeg') }}" alt="MSEA" class="s-logo-img" />
      <span class="s-logo-text">MSEA</span>
    </div>

    <div class="sidebar-profile" id="btn-open-perfil" title="Ver mi perfil">
      <div class="s-avatar">
        <img src="" alt="Avatar" class="s-avatar-img" id="sidebar-avatar" />
        <div class="s-avatar-fallback">👩‍🏫</div>
        <div class="s-avatar-edit-hint">✏️</div>
      </div>
      <div class="s-profile-info">
        <p class="s-name" id="s-name">Profesor</p>
        <p class="s-role" id="s-role">👩‍🏫 Profesor</p>
      </div>
    </div>

    <div class="s-quick-stats">
      <div class="s-stat-item">
        <span class="s-stat-val" id="s-total-alumnos">0</span>
        <span class="s-stat-lbl">Alumnos</span>
      </div>
      <div class="s-stat-divider"></div>
      <div class="s-stat-item">
        <span class="s-stat-val" id="s-tareas-pendientes">0</span>
        <span class="s-stat-lbl">Por revisar</span>
      </div>
      <div class="s-stat-divider"></div>
      <div class="s-stat-item">
        <span class="s-stat-val" id="s-clases-hoy">0</span>
        <span class="s-stat-lbl">Clases hoy</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <a href="#" class="nav-item active" data-page="inicio"><span class="nav-icon">🏠</span><span class="nav-label">Inicio</span></a>
      <a href="#" class="nav-item" data-page="alumnos"><span class="nav-icon">🎓</span><span class="nav-label">Mis alumnos</span></a>
      <a href="#" class="nav-item" data-page="tareas"><span class="nav-icon">📋</span><span class="nav-label">Tareas</span><span class="nav-badge" id="badge-tareas">0</span></a>
      <a href="#" class="nav-item" data-page="ejercicios"><span class="nav-icon">🎯</span><span class="nav-label">Ejercicios</span></a>
      <a href="#" class="nav-item" data-page="progreso"><span class="nav-icon">📈</span><span class="nav-label">Progreso grupal</span></a>
      <a href="#" class="nav-item" data-page="calendario"><span class="nav-icon">📅</span><span class="nav-label">Calendario</span></a>
    </nav>

    <form action="{{ url('/logout') }}" method="POST">
      @csrf
      <button type="submit" class="sidebar-logout"><span>🚪</span> Cerrar sesión</button>
    </form>
  </aside>

  <div class="content-area">
    <header class="dash-header">
      <button class="hamburger" id="hamburger" aria-label="Abrir menu"><span></span><span></span><span></span></button>

      <div class="header-greeting">
        <h1 class="greeting-title" id="greeting-title">Bienvenido</h1>
        <p class="greeting-sub" id="greeting-sub">Tienes <strong>0 entregas</strong> pendientes de revisión</p>
      </div>

      <div class="header-actions">
        <button class="btn-nueva-tarea" id="btn-nueva-tarea">＋ Nueva tarea</button>

        <div class="notif-wrapper">
          <button class="notif-btn" id="notif-btn" aria-label="Notificaciones">🔔<span class="notif-dot" id="notif-dot"></span></button>
          <div class="notif-panel" id="notif-panel">
            <div class="notif-panel-header">
              <span class="notif-panel-title">Notificaciones</span>
              <button class="notif-mark-all" id="notif-mark-all">Marcar leídas ✓</button>
            </div>
            <ul class="notif-list" id="notif-list"></ul>
            <div class="notif-panel-footer">
              <span class="notif-empty" id="notif-empty" style="display:none">Sin notificaciones nuevas</span>
            </div>
          </div>
        </div>

        <button class="header-avatar-btn" id="header-avatar-btn" title="Mi perfil">
          <img src="" alt="Avatar" class="header-avatar-img" id="header-avatar" />
          <span class="header-avatar-fallback">👩‍🏫</span>
        </button>
      </div>
    </header>

    <main class="page-content active" id="page-inicio">
      <div class="dash-main">
        <div class="stats-row">
          <div class="card stat-card"><div class="stat-icon-wrap bg-verde">🎓</div><div class="stat-info"><p class="stat-value" id="st-alumnos">0</p><p class="stat-label">Alumnos activos</p></div><span class="stat-trend up">Activos</span></div>
          <div class="card stat-card"><div class="stat-icon-wrap bg-naranja">📋</div><div class="stat-info"><p class="stat-value" id="st-por-revisar">0</p><p class="stat-label">Por revisar</p></div><span class="stat-trend warn">Pendientes</span></div>
          <div class="card stat-card"><div class="stat-icon-wrap bg-azul">✅</div><div class="stat-info"><p class="stat-value" id="st-completadas">0</p><p class="stat-label">Ejercicios completados</p></div><span class="stat-trend up">Total</span></div>
          <div class="card stat-card"><div class="stat-icon-wrap bg-morado">📅</div><div class="stat-info"><p class="stat-value" id="st-clases">0</p><p class="stat-label">Clases esta semana</p></div><span class="stat-trend">Programadas</span></div>
        </div>

        <div class="row-mid">
          <div class="card entregas-card">
            <div class="card-header"><h3 class="card-title">📬 Entregas por revisar</h3><a href="#" class="card-link nav-trigger" data-page="tareas">Ver todas →</a></div>
            <ul class="entregas-list" id="entregas-list"></ul>
          </div>
          <div class="card alumnos-top-card">
            <div class="card-header"><h3 class="card-title">🔥 Alumnos más activos</h3><a href="#" class="card-link nav-trigger" data-page="alumnos">Ver todos →</a></div>
            <ul class="alumnos-top-list" id="alumnos-top-list"></ul>
          </div>
        </div>

        <div class="row-bottom">
          <div class="card progreso-grupal-card">
            <div class="card-header"><h3 class="card-title">📊 Progreso grupal</h3><a href="#" class="card-link nav-trigger" data-page="progreso">Ver detalle →</a></div>
            <div class="progreso-grupal-list" id="progreso-grupal-list"></div>
          </div>
          <div class="card calendario-card">
            <div class="card-header"><h3 class="card-title">📅 Próximas clases</h3><a href="#" class="card-link nav-trigger" data-page="calendario">Ver calendario →</a></div>
            <ul class="clases-list" id="clases-list"></ul>
          </div>
        </div>
      </div>
    </main>

    <main class="page-content" id="page-alumnos">
      <div class="dash-main">
        <div class="page-header"><h2 class="page-title">🎓 Mis Alumnos</h2><p class="page-desc">Gestiona y monitorea el avance de tus estudiantes.</p></div>
        <div class="search-bar-wrap"><input type="text" id="search-alumnos" class="search-bar" placeholder="🔍 Buscar alumno por nombre o instrumento..." /></div>
        <div class="alumnos-grid" id="alumnos-grid"></div>
      </div>
    </main>

    <main class="page-content" id="page-tareas">
      <div class="dash-main">
        <div class="page-header-row">
          <div><h2 class="page-title">📋 Gestión de Tareas</h2><p class="page-desc">Asigna, revisa y califica las tareas de tus alumnos.</p></div>
          <button class="btn-nueva-tarea-lg" id="btn-nueva-tarea-lg">＋ Nueva tarea</button>
        </div>
        <div class="filtros-tareas">
          <button class="filtro-btn active" data-filtro="todas">Todas</button>
          <button class="filtro-btn" data-filtro="pendiente">⏳ Pendientes</button>
          <button class="filtro-btn" data-filtro="entregada">📬 Entregadas</button>
          <button class="filtro-btn" data-filtro="calificada">✅ Calificadas</button>
        </div>
        <div class="tareas-tabla-wrap card">
          <table class="tareas-tabla" id="tareas-tabla">
            <thead><tr><th>Tarea</th><th>Alumno</th><th>Instrumento</th><th>Fecha límite</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody id="tareas-tbody"></tbody>
          </table>
        </div>
      </div>
    </main>

    <main class="page-content" id="page-ejercicios"><div class="dash-main"><div class="page-header-row"><div><h2 class="page-title">🎯 Biblioteca de Ejercicios</h2><p class="page-desc">Consulta y recomienda ejercicios a tus alumnos.</p></div></div><div class="ejercicios-grid" id="ejercicios-grid"></div></div></main>
    <main class="page-content" id="page-progreso"><div class="dash-main"><div class="page-header"><h2 class="page-title">📈 Progreso Grupal</h2><p class="page-desc">Visualiza el avance general de todos tus alumnos.</p></div><div class="progreso-full-grid" id="progreso-full-grid"></div></div></main>
    <main class="page-content" id="page-calendario"><div class="dash-main"><div class="page-header"><h2 class="page-title">📅 Calendario de Clases</h2><p class="page-desc">Tus clases programadas para esta semana.</p></div><div class="calendario-full" id="calendario-full"></div></div></main>
  </div>
</div>

<div class="modal-overlay" id="modal-overlay"></div>
<div class="modal" id="modal-tarea">
  <div class="modal-header"><h3 class="modal-title">📋 Nueva Tarea</h3><button class="modal-close" id="modal-close">✕</button></div>
  <div class="modal-body">
    <div class="field-group"><label class="field-label" for="tarea-titulo">📝 Título de la tarea</label><input type="text" id="tarea-titulo" class="field-input" placeholder="Ej: Escala de Sol Mayor" /><p class="field-error" id="err-titulo"></p></div>
    <div class="field-group"><label class="field-label" for="tarea-alumno">🎓 Asignar a</label><select id="tarea-alumno" class="field-input field-select"><option value="">Selecciona un alumno</option></select><p class="field-error" id="err-alumno"></p></div>
    <div class="field-row-2">
      <div class="field-group"><label class="field-label" for="tarea-instrumento">🎻 Instrumento</label><input type="text" id="tarea-instrumento" class="field-input" placeholder="Violín" /></div>
      <div class="field-group"><label class="field-label" for="tarea-xp">⭐ XP de recompensa</label><input type="number" id="tarea-xp" class="field-input" placeholder="30" min="5" max="100" value="30" /></div>
    </div>
    <div class="field-group"><label class="field-label" for="tarea-fecha">📅 Fecha límite</label><input type="date" id="tarea-fecha" class="field-input" /><p class="field-error" id="err-fecha"></p></div>
    <div class="field-group"><label class="field-label" for="tarea-desc">📄 Descripción / instrucciones</label><textarea id="tarea-desc" class="field-input field-textarea" placeholder="Describe qué debe hacer el alumno..."></textarea></div>
    <div class="field-group"><label class="field-label" for="tarea-archivo">🎼 Partitura, audio o material</label><input type="file" id="tarea-archivo" class="field-input" accept=".pdf,.mp3,.wav,.m4a,.jpg,.jpeg,.png" /><p class="field-error">El backend guardara este archivo en ejercicios.archivo o en la tabla de entregables.</p></div>
  </div>
  <div class="modal-footer"><button class="btn-modal-cancel" id="btn-modal-cancel">Cancelar</button><button class="btn-modal-submit" id="btn-modal-submit"><span class="btn-text">Asignar tarea</span><span class="btn-loader" id="modal-loader" style="display:none">Guardando...</span></button></div>
</div>

<div class="perfil-overlay" id="perfil-overlay"></div>
<aside class="perfil-drawer" id="perfil-drawer">
  <div class="perfil-drawer-header"><h2 class="perfil-drawer-title">👤 Mi Perfil</h2><button class="perfil-close" id="perfil-close">✕</button></div>
  <div class="perfil-avatar-section">
    <div class="perfil-avatar-wrap"><img src="" alt="Avatar" class="perfil-avatar-img" id="perfil-avatar" /><div class="perfil-avatar-fallback" id="perfil-avatar-fallback">👩‍🏫</div><label class="perfil-avatar-change" for="avatar-input" title="Cambiar foto">📷</label><input type="file" id="avatar-input" accept="image/png,image/jpeg,image/gif,image/webp" /></div>
    <p class="perfil-avatar-hint">Toca la cámara para cambiar tu foto</p>
    <div class="avatar-preview-bar" id="avatar-preview-bar" style="display:none"><img src="" alt="Preview" class="avatar-preview-img" id="avatar-preview-img" /><div class="avatar-preview-btns"><button class="btn-avatar-confirm" id="btn-avatar-confirm">Guardar foto</button><button class="btn-avatar-cancel" id="btn-avatar-cancel">Cancelar</button></div></div>
  </div>
  <div class="perfil-info-section">
    <div class="perfil-info-row"><span class="pi-icon">👤</span><div class="pi-content"><span class="pi-label">Nombre</span><span class="pi-value" id="pi-nombre">Profesor</span></div></div>
    <div class="perfil-info-row"><span class="pi-icon">🏷️</span><div class="pi-content"><span class="pi-label">Usuario</span><span class="pi-value" id="pi-usuario">profesor</span></div></div>
    <div class="perfil-info-row"><span class="pi-icon">📧</span><div class="pi-content"><span class="pi-label">Correo</span><span class="pi-value" id="pi-email">Sin correo</span></div></div>
    <div class="perfil-info-row"><span class="pi-icon">🎻</span><div class="pi-content"><span class="pi-label">Especialidad</span><span class="pi-value" id="pi-especialidad">Sin especialidad</span></div></div>
    <div class="perfil-info-row"><span class="pi-icon">🎓</span><div class="pi-content"><span class="pi-label">Alumnos a cargo</span><span class="pi-value" id="pi-alumnos">0 estudiantes</span></div></div>
    <div class="perfil-info-row"><span class="pi-icon">📅</span><div class="pi-content"><span class="pi-label">Miembro desde</span><span class="pi-value" id="pi-fecha">Sin fecha</span></div></div>
  </div>
  <form action="{{ url('/logout') }}" method="POST" class="perfil-logout-form">@csrf<button type="submit" class="perfil-logout-btn">🚪 Cerrar sesión</button></form>
</aside>

<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="toast" id="toast"></div>

<script>
  window.MSEA_PROFESOR = @json($profesorData ?? []);
  window.MSEA_ALUMNOS = @json($alumnosData ?? []);
  window.MSEA_TAREAS = @json($tareasData ?? []);
</script>
<script src="{{ asset('js/dashboard.profesor.js') }}"></script>
</body>
</html>
