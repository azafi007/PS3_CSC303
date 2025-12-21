<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Load stocks for dropdown (only IDs you have in stock table)
$stocks_res = mysqli_query($conn, "
    SELECT s.StockID, tp.name AS participant
    FROM stock s
    LEFT JOIN transaction_participant tp ON s.ParticipantID = tp.ParticipantID
    ORDER BY s.StockID
");

// default empty prediction
$p = [
    'StockID'          => '',
    'predictionDateTime' => '',
    'predictedPrice'   => '',
    'targetDate'       => ''
];

// Handle delete (one prediction row identified by StockID + predictionDateTime)
if (isset($_GET['delete_stock']) && isset($_GET['delete_time'])) {
    $stockId = (int)$_GET['delete_stock'];
    $predTime = $_GET['delete_time']; // datetime string

    $stmt = mysqli_prepare($conn,
        "DELETE FROM prediction WHERE StockID = ? AND predictionDateTime = ?"
    );
    mysqli_stmt_bind_param($stmt, "is", $stockId, $predTime);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: prediction_manage.php");
    exit;
}

// Load single prediction for edit
if (isset($_GET['stock']) && isset($_GET['time'])) {
    $stockId = (int)$_GET['stock'];
    $predTime = $_GET['time'];

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM prediction WHERE StockID = ? AND predictionDateTime = ?"
    );
    mysqli_stmt_bind_param($stmt, "is", $stockId, $predTime);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res && mysqli_num_rows($res) === 1) {
        $p = mysqli_fetch_assoc($res);
    }
    mysqli_stmt_close($stmt);
}

// Handle create / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockId   = (int)$_POST['StockID'];
    $predDT    = $_POST['predictionDateTime']; // from datetime-local
    $price     = (float)$_POST['predictedPrice'];
    $target    = $_POST['targetDate'];

    $oldStock  = isset($_POST['oldStockID']) ? (int)$_POST['oldStockID'] : null;
    $oldDT     = $_POST['oldPredictionDateTime'] ?? '';

    // convert HTML datetime-local to MySQL datetime
    $predDT_mysql = str_replace('T', ' ', $predDT);

    if ($oldStock && $oldDT !== '') {
        // update existing row (PK: StockID + predictionDateTime)
        $stmt = mysqli_prepare($conn,"
            UPDATE prediction
               SET StockID = ?, predictionDateTime = ?, predictedPrice = ?, targetDate = ?
             WHERE StockID = ? AND predictionDateTime = ?
        ");
        mysqli_stmt_bind_param($stmt, "isdiss",
            $stockId, $predDT_mysql, $price, $target,
            $oldStock, $oldDT
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // insert new row
        $stmt = mysqli_prepare($conn,"
            INSERT INTO prediction (StockID, predictionDateTime, predictedPrice, targetDate)
            VALUES (?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "isds", $stockId, $predDT_mysql, $price, $target);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: prediction_manage.php");
    exit;
}

// list all predictions (join with stock + participant name)
$list_res = mysqli_query($conn,"
    SELECT p.StockID, p.predictionDateTime, p.predictedPrice, p.targetDate,
           tp.name AS participant
    FROM prediction p
    LEFT JOIN stock s ON p.StockID = s.StockID
    LEFT JOIN transaction_participant tp ON s.ParticipantID = tp.ParticipantID
    ORDER BY p.predictionDateTime DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Predictions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?= ($p['StockID'] && $p['predictionDateTime']) ? 'Edit Prediction' : 'Add Prediction'; ?></h3>
    <a href="dashboard_system.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <form method="post" class="row g-3">
        <!-- hidden original PK for update -->
        <input type="hidden" name="oldStockID" value="<?= htmlspecialchars($p['StockID']); ?>">
        <input type="hidden" name="oldPredictionDateTime" value="<?= htmlspecialchars($p['predictionDateTime']); ?>">

        <div class="col-md-3">
          <label class="form-label">Stock</label>
          <select name="StockID" class="form-select" required>
            <option value="">Select stock...</option>
            <?php mysqli_data_seek($stocks_res, 0); while($s = mysqli_fetch_assoc($stocks_res)): ?>
              <option value="<?= $s['StockID']; ?>"
                <?= ($s['StockID'] == $p['StockID']) ? 'selected' : ''; ?>>
                <?= $s['StockID'].' - '.htmlspecialchars($s['participant']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Prediction Date &amp; Time</label>
          <input type="datetime-local" name="predictionDateTime" class="form-control" required
                 value="<?php
                   if (!empty($p['predictionDateTime'])) {
                     echo str_replace(' ', 'T', $p['predictionDateTime']);
                   }
                 ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Predicted Price</label>
          <input type="number" step="0.01" name="predictedPrice" class="form-control" required
                 value="<?= htmlspecialchars($p['predictedPrice']); ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label">Target Date</label>
          <input type="date" name="targetDate" class="form-control" required
                 value="<?= htmlspecialchars($p['targetDate']); ?>">
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Save Prediction</button>
          <a href="prediction_manage.php" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">All Predictions</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Stock</th>
              <th>Participant</th>
              <th>Prediction DateTime</th>
              <th>Predicted Price</th>
              <th>Target Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while($r = mysqli_fetch_assoc($list_res)): ?>
            <tr>
              <td><?= $r['StockID']; ?></td>
              <td><?= htmlspecialchars($r['participant']); ?></td>
              <td><?= $r['predictionDateTime']; ?></td>
              <td><?= $r['predictedPrice']; ?></td>
              <td><?= $r['targetDate']; ?></td>
              <td>
                <a href="prediction_manage.php?stock=<?= $r['StockID']; ?>&time=<?= urlencode($r['predictionDateTime']); ?>"
                   class="btn btn-sm btn-warning">Edit</a>
                <a href="prediction_manage.php?delete_stock=<?= $r['StockID']; ?>&delete_time=<?= urlencode($r['predictionDateTime']); ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this prediction?');">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
