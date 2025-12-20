<?php
include __DIR__ . '/../config.php';

$stockId = $_GET['stock'] ?? null;
$time    = $_GET['time']  ?? null;
$editing = $stockId !== null && $time !== null;

$record = [
  'StockID'       => '',
  'recordingTime' => '',
  'openingPrice'  => '',
  'closingPrice'  => '',
  'high'          => '',
  'low'           => '',
  'volume'        => ''
];

if ($editing) {
    $stmt = $conn->prepare(
        "SELECT * FROM price_history WHERE StockID=? AND recordingTime=?"
    );
    $stmt->bind_param("is", $stockId, $time);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockId       = $_POST['StockID'];
    $recordingTime = $_POST['recordingTime'];   // format: YYYY-MM-DD HH:MM:SS
    $open          = $_POST['openingPrice'];
    $close         = $_POST['closingPrice'];
    $high          = $_POST['high'];
    $low           = $_POST['low'];
    $volume        = $_POST['volume'];

    if ($editing) {
        $stmt = $conn->prepare(
          "UPDATE price_history
           SET openingPrice=?, closingPrice=?, high=?, low=?, volume=?
           WHERE StockID=? AND recordingTime=?"
        );
        $stmt->bind_param(
          "ddddiis",
          $open, $close, $high, $low, $volume, $stockId, $time
        );
    } else {
        $stmt = $conn->prepare(
          "INSERT INTO price_history
           (StockID, recordingTime, openingPrice, closingPrice, high, low, volume)
           VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->bind_param(
          "isddddi",
          $stockId, $recordingTime, $open, $close, $high, $low, $volume
        );
    }

    $stmt->execute();
    header("Location: ../dashboard_analyst.php#history");
    exit;
}
?>
<!doctype html>
<html>
<head>
  <title><?= $editing ? 'Edit' : 'Add' ?> Price History</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<h2><?= $editing ? 'Edit' : 'Add' ?> Price History</h2>

<form method="post" class="mt-3">
  <div class="mb-3">
    <label class="form-label">StockID</label>
    <input type="number" name="StockID" class="form-control"
           value="<?= htmlspecialchars($record['StockID']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Recording Time (YYYY-MM-DD HH:MM:SS)</label>
    <input type="text" name="recordingTime" class="form-control"
           value="<?= htmlspecialchars($record['recordingTime']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Opening Price</label>
    <input type="text" name="openingPrice" class="form-control"
           value="<?= htmlspecialchars($record['openingPrice']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Closing Price</label>
    <input type="text" name="closingPrice" class="form-control"
           value="<?= htmlspecialchars($record['closingPrice']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">High</label>
    <input type="text" name="high" class="form-control"
           value="<?= htmlspecialchars($record['high']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Low</label>
    <input type="text" name="low" class="form-control"
           value="<?= htmlspecialchars($record['low']) ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Volume</label>
    <input type="number" name="volume" class="form-control"
           value="<?= htmlspecialchars($record['volume']) ?>" required>
  </div>

  <button class="btn btn-success"><?= $editing ? 'Update' : 'Create' ?></button>
  <a href="../dashboard_analyst.php#history" class="btn btn-secondary">Back</a>
</form>
</body>
</html>
