<?php
include __DIR__ . '/config.php';

/* ---- Data for price history chart (StockID 1, last 10) ---- */
$chartRes = $conn->query(
  "SELECT recordingTime, closingPrice
   FROM price_history
   WHERE StockID = 1
   ORDER BY recordingTime ASC
   LIMIT 10"
);

$chartLabels = [];
$chartValues = [];
while ($r = $chartRes->fetch_assoc()) {
    $chartLabels[] = $r['recordingTime'];
    $chartValues[] = (float)$r['closingPrice'];
}
/* ----------------------------------------------------------- */

$stockFilter = $_GET['stock'] ?? '';

if ($stockFilter !== '') {
    $stmt = $conn->prepare(
        "SELECT * FROM price_history WHERE StockID=? ORDER BY recordingTime DESC"
    );
    $stmt->bind_param("i", $stockFilter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        "SELECT * FROM price_history ORDER BY StockID, recordingTime DESC"
    );
}

$stocksRes = $conn->query("SELECT StockID FROM stock ORDER BY StockID");
?>

<h3 class="mt-3">Price History</h3>

<div class="card mb-3">
  <div class="card-body">
    <div class="d-flex justify-content-between">
      <h5 class="card-title mb-0">Stock 1 – Closing Price Trend</h5>
      <small class="text-muted">Last 10 records</small>
    </div>
    <canvas id="priceChart" height="80"></canvas>
  </div>
</div>

<script>
// expose data for dashboard_analyst.js
window.priceChartLabels = <?= json_encode($chartLabels); ?>;
window.priceChartValues = <?= json_encode($chartValues); ?>;
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
    <a href="price_form.php" class="btn btn-success">Add / Edit Price</a>
  </div>
</form>

<table class="table table-striped table-sm">
  <thead class="table-dark">
    <tr>
      <th>StockID</th>
      <th>Recording Time</th>
      <th>Open</th>
      <th>Close</th>
      <th>High</th>
      <th>Low</th>
      <th>Volume</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['StockID'] ?></td>
      <td><?= $row['recordingTime'] ?></td>
      <td><?= $row['openingPrice'] ?></td>
      <td><?= $row['closingPrice'] ?></td>
      <td><?= $row['high'] ?></td>
      <td><?= $row['low'] ?></td>
      <td><?= $row['volume'] ?></td>
      <td>
        <?php $t = urlencode($row['recordingTime']); ?>
        <a href="price_form.php?stock=<?= $row['StockID'] ?>&time=<?= $t ?>"
           class="btn btn-sm btn-warning">Edit</a>
        <a href="price_delete.php?stock=<?= $row['StockID'] ?>&time=<?= $t ?>"
           class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this record?');">Delete</a>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
