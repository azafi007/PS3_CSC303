<?php
session_start();
require_once 'config.php';

// get participants for dropdown
$parts = mysqli_query($conn, "SELECT ParticipantID, name FROM transaction_participant");

// if editing
$stock = [
  'StockID' => '',
  'ParticipantID' => '',
  'totalShare' => '',
  'currentPrice' => ''
];

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $res = mysqli_query($conn, "SELECT * FROM stock WHERE StockID=$id");
    if ($res && mysqli_num_rows($res) == 1) {
        $stock = mysqli_fetch_assoc($res);
    }
}

// handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = $_POST['StockID'] !== '' ? (int) $_POST['StockID'] : null;
    $part_id = (int) $_POST['ParticipantID'];
    $share   = (int) $_POST['totalShare'];
    $price   = (float) $_POST['currentPrice'];

    if ($id) {
        // UPDATE
        $sql = "UPDATE stock
                SET ParticipantID=$part_id, totalShare=$share, currentPrice=$price
                WHERE StockID=$id";
    } else {
        // INSERT (StockID AUTO or given manually, your table has PK but no AI; adjust if needed)
        $sql = "INSERT INTO stock (StockID, ParticipantID, totalShare, currentPrice)
                VALUES ($share + 1000, $part_id, $share, $price)";
        // For a proper design you would change StockID column to AUTO_INCREMENT in phpMyAdmin.
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: stocks.php");
        exit;
    } else {
        $error = "DB error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= $stock['StockID'] ? 'Edit Stock' : 'Add Stock'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3><?= $stock['StockID'] ? 'Edit Stock' : 'Add Stock'; ?></h3>
<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="post">
  <input type="hidden" name="StockID" value="<?= htmlspecialchars($stock['StockID']); ?>">

  <div class="mb-3">
    <label class="form-label">Participant</label>
    <select name="ParticipantID" class="form-select" required>
      <option value="">Select...</option>
      <?php mysqli_data_seek($parts, 0); ?>
      <?php while($p = mysqli_fetch_assoc($parts)): ?>
        <option value="<?= $p['ParticipantID']; ?>"
          <?= ($p['ParticipantID'] == $stock['ParticipantID']) ? 'selected' : ''; ?>>
          <?= $p['ParticipantID'] . ' - ' . htmlspecialchars($p['name']); ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Total Share</label>
    <input type="number" name="totalShare" class="form-control"
           value="<?= htmlspecialchars($stock['totalShare']); ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Current Price</label>
    <input type="number" step="0.01" name="currentPrice" class="form-control"
           value="<?= htmlspecialchars($stock['currentPrice']); ?>" required>
  </div>

  <button type="submit" class="btn btn-primary">Save</button>
  <a href="stocks.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>
