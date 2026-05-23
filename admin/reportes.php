<?php
$pageTitle = 'Reportes';
$active = 'reportes';
$role = 'admin';
$moduleKey = 'reportes';
$moduleTitle = 'Historial de reportes';
$moduleIcon = 'fa-chart-pie';
$moduleHint = 'Analitica y exportacion';
include __DIR__ . '/../components/header.php';
$reportFromDefault = date('Y-m-01');
$reportToDefault = date('Y-m-d');
?>
<div class="layout">
  <?php include __DIR__ . '/../components/sidebar.php'; ?>
  <main class="main">
    <?php include __DIR__ . '/../components/topbar.php'; ?>

    <section class="content-card no-print">
      <div class="module-heading">
        <div>
          <span class="eyebrow">Generador de reportes</span>
          <h2>Crear reporte administrativo</h2>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-light-soft" data-report-export="csv"><i class="fa-solid fa-file-csv"></i> CSV</button>
          <button class="btn btn-light-soft" data-report-export="excel"><i class="fa-solid fa-file-excel"></i> Excel</button>
          <button class="btn btn-olive" data-report-export="pdf"><i class="fa-solid fa-file-pdf"></i> PDF</button>
        </div>
      </div>

      <div class="report-filters">
        <select class="form-select" id="reportType">
          <option value="ingresos">Ingresos</option>
          <option value="servicios">Servicios</option>
          <option value="empleados">Empleados</option>
          <option value="clientes">Clientes frecuentes</option>
          <option value="inventario">Inventario</option>
          <option value="citas">Citas</option>
        </select>
        <input class="form-control" id="reportFrom" type="date" value="<?= $reportFromDefault ?>">
        <input class="form-control" id="reportTo" type="date" value="<?= $reportToDefault ?>">
        <button class="btn btn-olive" id="generateReportBtn"><i class="fa-solid fa-chart-simple"></i> Generar</button>
      </div>

      <!--
        REPORTES REALES:
        - INSERTAR FETCH API a controllers/ReportesController.php
        - LLAMAR vistas SQL, procedimientos almacenados o consultas agregadas
        - Exportar PDF/Excel desde backend cuando se requiera
      -->
    </section>

    <section class="print-area" id="reportPrintable">
      <div class="content-card report-cover">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
          <div class="brand-lockup">
            <img class="brand-logo-img" src="/assets/img/mirror-glam-logo.svg" alt="Mirror Glam">
            <span>Mirror Glam</span>
          </div>
          <div class="text-end">
            <span class="eyebrow">Reporte generado</span>
            <h2 id="reportTitle">Ingresos</h2>
            <p id="reportRange"><?= $reportFromDefault ?> al <?= $reportToDefault ?></p>
          </div>
        </div>
      </div>

      <section class="metric-grid mb-3" id="reportMetrics"></section>

      <section class="dashboard-grid">
        <div class="content-card">
          <div class="module-heading">
            <div><span class="eyebrow">Tendencia</span><h2>Resumen visual</h2></div>
          </div>
          <canvas id="reportChart" height="120"></canvas>
        </div>
        <div class="content-card">
          <div class="module-heading">
            <div><span class="eyebrow">Lectura rapida</span><h2>Conclusiones</h2></div>
          </div>
          <ul class="report-insights" id="reportInsights"></ul>
        </div>
      </section>

      <section class="content-card">
        <div class="module-heading">
          <div><span class="eyebrow">Datos del reporte</span><h2>Detalle exportable</h2></div>
        </div>
        <div class="table-responsive">
          <table class="table app-table align-middle" id="generatedReportTable">
            <thead></thead>
            <tbody></tbody>
          </table>
        </div>
      </section>
    </section>

    <?php include __DIR__ . '/../components/module-table.php'; ?>
  </main>
</div>
<?php include __DIR__ . '/../components/footer.php'; ?>
