<?php
require_once __DIR__ . '/../../backend/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    // “nombre_conector” es el campo en la tabla registros
    $conectores = $conn
        ->query("
            SELECT DISTINCT nombre_conector
              FROM registros
             ORDER BY nombre_conector
        ")
        ->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $conectores = [];
}
?>
<link rel="stylesheet" href="../css/styles_base_personal.css">

<div class="personal-db-container">
  <h2>Base de Datos Personal por Conector</h2>

  <form class="form-inline" onsubmit="return false;">
    <label for="conector">Conector:</label>
    <select id="conector-personal">
      <option value="">-- Selecciona --</option>
      <?php foreach ($conectores as $c): ?>
        <option value="<?= htmlspecialchars($c, ENT_QUOTES) ?>">
          <?= htmlspecialchars($c, ENT_QUOTES) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <div class="table-responsive">
    <table id="tabla-registros-conector" border="1" cellpadding="6" cellspacing="0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Teléfono</th>
          <th>Estado</th>
          <th>Observaciones</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan="6" style="text-align:center;">Selecciona un conector</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script>
const tbody = document.querySelector('#tabla-registros-conector tbody');
const select = document.querySelector('#conector-personal');

if (!select || !tbody) {
  console.log('No se encontraron elementos necesarios para Base de Datos Personal');
  return;
}
</script>

