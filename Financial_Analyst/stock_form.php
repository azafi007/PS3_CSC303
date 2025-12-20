<?php
include 'config.php';

$id = $_GET['id'] ?? null;
$editing = $id !== null;

$stock = ['ParticipantID'=>'','totalShare'=>'','currentPrice'=>''];

if ($editing) {
    $stmt = $conn->prepare("SELECT * FROM stock WHERE StockID=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stock = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $participant  = $_POST['ParticipantID'];
    $totalShare   = $_POST['totalShare'];
    $currentPrice = $_POST['currentPrice'];

    if ($editing) {
        $stmt = $conn->prepare(
          "UPDATE stock
           SET ParticipantID=?, totalShare=?, currentPrice=?
           WHERE StockID=?"
        );
        $stmt->bind_param("iidi", $participant, $totalShare, $currentPrice, $id);
    } else {
        $stmt = $conn->prepare(
          "INSERT INTO stock (ParticipantID,totalShare,currentPrice)
           VALUES (?,?,?)"
        );
        $stmt->bind_param("iid", $participant, $totalShare, $currentPrice);
    }
    $stmt->execute();
    header("Location: dashboard_analyst.php#stocks");
    exit;
}
?>
<!doctype html>
<html>
<head>
  <title><?= $editing ? 'Edit' : 'Add' ?> Stock</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<h2><?= $editing ? 'Edit' : 'Add' ?> Stock</h2>

<form method="post" class="mt-3">
  <div class="mb-3">
    <label class="form-label">ParticipantID</label>
    <input type="number" name="ParticipantID" class="form-control"
           value="<?= htmlspecialchars($stock['ParticipantID']) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Total Share</label>
    <input type="number" name="totalShare" class="form-control"
           value="<?= htmlspecialchars($stock['totalShare']) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Current Price</label>
    <input type="text" name="currentPrice" class="form-control"
           value="<?= htmlspecialchars($stock['currentPrice']) ?>" required>
  </div>

  <button class="btn btn-success"><?= $editing ? 'Update' : 'Create' ?></button>
  <a href="dashboard_analyst.php#stocks" class="btn btn-secondary">Back</a>
</form>
</body>
</html>
