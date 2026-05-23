<?php $pageTitle = 'Recuperar contrasena'; include __DIR__ . '/../components/header.php'; ?>
<main class="auth-shell">
  <section class="auth-visual"><h1>Recupera tu acceso con calma.</h1><p>Registraremos tu solicitud para que el equipo pueda ayudarte.</p></section>
  <section class="auth-card">
    <form class="auth-form soft-card" id="recoverForm">
      <span class="eyebrow">Seguridad</span>
      <h2 class="mb-3">Recuperar contrasena</h2>
      <input class="form-control mb-3" name="email" type="email" required placeholder="tu-correo@mirrorglam.do">
      <button class="btn btn-olive w-100">Enviar enlace</button>
      <a class="btn btn-light-soft w-100 mt-2" href="/auth/login.php">Volver al login</a>
      <!-- INSERTAR FETCH API para solicitar token de recuperacion en tabla usuarios/recordatorios -->
    </form>
  </section>
</main>
<?php include __DIR__ . '/../components/footer.php'; ?>
