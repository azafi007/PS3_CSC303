<?php
include __DIR__ . '/config.php';

/* ---- Data for prediction chart (StockID 1, next 10) ---- */
$predRes = $conn->query(
  "SELECT targetDate, predictedPrice
   FROM prediction
   WHERE StockID = 1
   ORDER BY targetDate ASC
   LIMIT 10"
);

$predLabels = [];
$predValues = [];
while ($p = $predRes->fetch_assoc()) {
    $predLabels[] = $p['targetDate'];
    $predValues[] = (float)$p['predictedPrice'];
}
/* -------------------------------------------------------- */

$stockFilter = $_GET['stock'] ?? '';

if ($stockFilter !== '') {
    $stmt = $conn->prepare(
        "SELECT * FROM prediction WHERE StockID=? ORDER BY predictionDateTime DESC"
    );
    $stmt->bind_param("i", $stockFilter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        "SELECT * FROM prediction ORDER BY StockID, predictionDateTime DESC"
    );
}
$stocksRes = $conn->query("SELECT StockID FROM stock ORDER BY StockID");
?>

<h3 class="mt-3">Predictions</h3>

<div class="card mb-3">
  <div class="card-body">
    <div class="d-flex justify-content-between">
      <h5 class="card-title mb-0">Stock 1 – Predicted Price</h5>
      <small class="text-muted">Next 10 target dates</small>
    </div>
    <canvas id="predChart" height="80"></canvas>
  </div>
</div>

<script>
window.predChartLabels = <?= json_encode($predLabels); ?>;
window.predChartValues = <?= json_encode($predValues); ?>;
</script>

<form method="get" class="row g-2 mb-3">
  <div class="col-auto">
    <select name="stock" class="form-select">
      <option value="">All Stocks</option>
      <?php while ($s = $stocksRes->fetch_assoc()): ?>
        <option value="<?= $s['StockID'] ?>"
          <?= $stockFilter == $s['StockID'] ? 'selected' : '' ?>>
          Stock <?= $s['StockID'] ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="col-auto">
    <button class="btn btn-secondary">Filter</button>
  </div>
  <div class="col-auto">
    <a href="prediction_form.php" class="btn btn-success">Add / Edit Prediction</a>
  </div>
</form>

<table class="table table-striped table-sm">
  <thead class="table-dark">
    <tr>
      <th>StockID</th>
      <th>Prediction DateTime</th>
      <th>Predicted Price</th>
      <th>Target Date</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['StockID'] ?></td>
      <td><?= $row['predictionDateTime'] ?></td>
      <td><?= $row['predictedPrice'] ?></td>
      <td><?= $row['targetDate'] ?></td>
      <td>
        <?php $t = urlencode($row['predictionDateTime']); ?>
        <a href="prediction_form.php?stock=<?= $row['StockID'] ?>&time=<?= $t ?>"
           class="btn btn-sm btn-warning">Edit</a>
        <a href="prediction_delete.php?stock=<?= $row['StockID'] ?>&time=<?= $t ?>"
           class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this prediction?');">Delete</a>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
