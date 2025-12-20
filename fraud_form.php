<?php
session_start();
require_once 'config.php';

$txs = mysqli_query($conn,"SELECT TransactionID FROM stock_transaction ORDER BY TransactionID DESC");

$f = ['AlertID'=>'','riskScore'=>'','detectionDate'=>'','TransactionID'=>''];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn,"SELECT * FROM fraud_alert WHERE AlertID=$id");
    if ($res && mysqli_num_rows($res)==1) $f = mysqli_fetch_assoc($res);
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id   = $_POST['AlertID']!=='' ? (int)$_POST['AlertID'] : null;
    $risk = (int)$_POST['riskScore'];
    $date = $_POST['detectionDate'] ?: date('Y-m-d');
    $txid = (int)$_POST['TransactionID'];

    if ($id) {
        $sql = "UPDATE fraud_alert
                SET riskScore=$risk, detectionDate='$date', TransactionID=$txid
                WHERE AlertID=$id";
    } else {
        $sql = "INSERT INTO fraud_alert(riskScore,detectionDate,TransactionID)
                VALUES($risk,'$date',$txid)";
    }
    mysqli_query($conn,$sql);
    header("Location: fraud_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= $f['AlertID']?'Edit Alert':'Add Alert'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3><?= $f['AlertID']?'Edit Alert':'Add Alert'; ?></h3>
<form method="post">
  <input type="hidden" name="AlertID" value="<?= htmlspecialchars($f['AlertID']); ?>">
  <div class="mb-3">
    <label class="form-label">Risk Score</label>
    <input type="number" name="riskScore" class="form-control" required
           value="<?= htmlspecialchars($f['riskScore']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Detection Date</label>
    <input type="date" name="detectionDate" class="form-control"
           value="<?= $f['detectionDate']; ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Transaction</label>
    <select name="TransactionID" class="form-select" required>
      <option value="">Select…</option>
      <?php mysqli_data_seek($txs,0); while($t=mysqli_fetch_assoc($txs)): ?>
      <option value="<?= $t['TransactionID']; ?>"
        <?= $t['TransactionID']==$f['TransactionID']?'selected':''; ?>>
        <?= $t['TransactionID']; ?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>
  <button type="submit" class="btn btn-primary">Save</button>
  <a href="fraud_list.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>
