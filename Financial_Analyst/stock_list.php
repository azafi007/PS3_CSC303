<?php
include 'config.php';

$q = $_GET['q'] ?? '';

if ($q !== '') {
    $sql = "SELECT * FROM stock WHERE StockID = ? OR ParticipantID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM stock");
}
?>
<h3 class="mt-3">Stocks</h3>

<form method="get" class="row g-2 mb-3">
  <div class="col-auto">
    <input type="text" name="q" class="form-control"
           placeholder="Search by StockID or ParticipantID"
           value="<?= htmlspecialchars($q) ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-secondary">Search</button>
  </div>
  <div class="col-auto">
    <a href="stock_form.php" class="btn btn-primary">Add Stock</a>
  </div>
</form>

<table class="table table-striped table-sm">
  <thead class="table-dark">
    <tr>
      <th>StockID</th>
      <th>ParticipantID</th>
      <th>Total Share</th>
      <th>Current Price</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['StockID'] ?></td>
      <td><?= $row['ParticipantID'] ?></td>
      <td><?= $row['totalShare'] ?></td>
      <td><?= $row['currentPrice'] ?></td>
      <td>
        <a href="stock_form.php?id=<?= $row['StockID'] ?>" class="btn btn-sm btn-warning">Edit</a>
        <a href="stock_delete.php?id=<?= $row['StockID'] ?>"
           class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this stock?');">Delete</a>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
