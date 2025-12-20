<?php
include 'config.php';

$stockId = $_GET['stock'] ?? null;
$time    = $_GET['time']  ?? null;
$editing = $stockId !== null && $time !== null;

$record = ['StockID'=>'','predictionDateTime'=>'','predictedPrice'=>'','targetDate'=>''];

if ($editing) {
    $stmt = $conn->prepare(
        "SELECT * FROM prediction WHERE StockID=? AND predictionDateTime=?"
    );
    $stmt->bind_param("is", $stockId, $time);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockId    = $_POST['StockID'];
    $predTime   = $_POST['predictionDateTime'];
    $price      = $_POST['predictedPrice'];
    $targetDate = $_POST['targetDate'];

    if ($editing) {
        $stmt = $conn->prepare(
          "UPDATE prediction
           SET predictedPrice=?, targetDate=?
           WHERE StockID=? AND predictionDateTime=?"
        );
        $stmt->bind_param("dsis", $price, $targetDate, $stockId, $time);
    } else {
        $stmt = $conn->prepare(
          "INSERT INTO prediction
           (StockID,predictionDateTime,predictedPrice,targetDate)
           VALUES (?,?,?,?)"
        );
        $stmt->bind_param("isds", $stockId, $predTime, $price, $targetDate);
    }
    $stmt->execute();
    header("Location: dashboard_analyst.php#pred");
    exit;
}
?>
<!doctype html>
<html>
<head>
  <title><?= $editing ? 'Edit' : 'Add' ?> Prediction</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<h2><?= $editing ? 'Edit' : 'Add' ?> Prediction</h2>

<form method="post" class="mt-3">
  <div class="mb-3">
    <label class="form-label">StockID</label>
    <input type="number" name="StockID" class="form-control"
           value="<?= htmlspecialchars($record['StockID']) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Prediction DateTime (YYYY-MM-DD HH:MM:SS)</label>
    <input type="text" name="predictionDateTime" class="form-control"
           value="<?= htmlspecialchars($record['predictionDateTime']) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Predicted Price</label>
    <input type="text" name="predictedPrice" class="form-control"
           value="<?= htmlspecialchars($record['predictedPrice']) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Target Date (YYYY-MM-DD)</label>
    <input type="text" name="targetDate" class="form-control"
           value="<?= htmlspecialchars($record['targetDate']) ?>" required>
  </div>

  <button class="btn btn-success"><?= $editing ? 'Update' : 'Create' ?></button>
  <a href="dashboard_analyst.php#pred" class="btn btn-secondary">Back</a>
</form>
</body>
</html>
