<?php $pageTitle = 'Login'; $bodyClass = 'auth-page'; include __DIR__ . '/../components/header.php'; ?>
<main class="auth-shell">
  <section class="auth-visual">
    <div class="brand-lockup mb-4"><img class="brand-logo-img" src="/assets/img/mirror-glam-logo.svg" alt="Mirror Glam"><span>Mirror Glam</span></div>
    <h1>Gestion elegante para un salon impecable.</h1>
    <p>Acceso seguro para administradores y empleados.</p>
  </section>
  <section class="auth-card">
    <form class="auth-form soft-card" id="loginForm">
      <span class="eyebrow">Acceso</span>
      <h2 class="mb-3">Iniciar sesion</h2>
      <label class="form-label">Correo</label>
      <input class="form-control mb-3" name="email" type="email" required placeholder="admin@mirrorglam.do">
      <label class="form-label">Contrasena</label>
      <input class="form-control mb-3" name="password" type="password" required placeholder="********">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <label class="form-check-label"><input class="form-check-input me-2" type="checkbox" name="remember"> Recordarme</label>
        <a href="/auth/recover.php">Recuperar</a>
      </div>
      <button class="btn btn-olive w-100" type="submit">Entrar</button>
      <a class="btn btn-light-soft w-100 mt-2" href="/index.php">Volver al inicio</a>
      <!--
        AUTENTICACION:
        - Conectar aqui con controllers/AuthController.php
        - Validar usuarios desde tabla usuarios
        - Verificar password_hash
        - Verificar roles desde tabla roles
        - Redirigir administrador a /admin/dashboard.php y empleado a /empleados/dashboard.php
      -->
    </form>
  </section>
</main>
<?php include __DIR__ . '/../components/footer.php'; ?>
