<?php
session_start();
require_once 'config.php';
require_once 'fraud_rules.php';

// stocks and participants as before
$stocks = mysqli_query($conn,"SELECT StockID FROM stock");
$parts  = mysqli_query($conn,"SELECT ParticipantID,name FROM transaction_participant");

// ONLY MANAGER STAFF
// adjust the WHERE according to your actual schema:
//   e.g. role='Manager' OR position='Manager' OR isManager=1
$staffs = mysqli_query($conn,"SELECT StaffID,name FROM staff WHERE designation = 'Manager'");


$t = [
    'TransactionID'  => '',
    'timeStamp'      => '',
    'amount'         => '',
    'StockID'        => '',
    'ParticipantID'  => '',
    'StaffID'        => ''
];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn,"SELECT * FROM stock_transaction WHERE TransactionID=$id");
    if ($res && mysqli_num_rows($res)==1) {
        $t = mysqli_fetch_assoc($res);
    }
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id    = $_POST['TransactionID']!=='' ? (int)$_POST['TransactionID'] : null;
    $time  = $_POST['timeStamp'] ?: date('Y-m-d H:i:s');
    $amt   = (float)$_POST['amount'];
    $sid   = (int)$_POST['StockID'];
    $pid   = (int)$_POST['ParticipantID'];
    $stfid = (int)$_POST['StaffID'];

    // SERVER-SIDE CHECK: must choose a manager
    if ($stfid === 0) {
        die("You must select a manager to perform a stock transaction.");
    }

    if ($id) {
        // UPDATE existing transaction
        $sql = "UPDATE stock_transaction
                SET timeStamp='$time',
                    amount=$amt,
                    StockID=$sid,
                    ParticipantID=$pid,
                    StaffID=$stfid
                WHERE TransactionID=$id";
        mysqli_query($conn,$sql);

        // optional: re-evaluate on edit
        // evaluate_fraud_rules($conn, $id, $sid, $pid, $amt);

    } else {
        // INSERT new transaction
        $sql = "INSERT INTO stock_transaction(timeStamp,amount,StockID,ParticipantID,StaffID)
                VALUES('$time',$amt,$sid,$pid,$stfid)";
        if (mysqli_query($conn,$sql)) {
            $newTxId = mysqli_insert_id($conn);

            // run fraud rules
            evaluate_fraud_rules($conn, $newTxId, $sid, $pid, $amt);
        }
    }

    header("Location: transactions_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= $t['TransactionID']?'Edit Transaction':'Add Transaction'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3><?= $t['TransactionID']?'Edit Transaction':'Add Transaction'; ?></h3>
<form method="post">
  <input type="hidden" name="TransactionID" value="<?= htmlspecialchars($t['TransactionID']); ?>">

  <div class="mb-3">
    <label class="form-label">Time</label>
    <input type="datetime-local" name="timeStamp" class="form-control"
           value="<?= $t['timeStamp'] ? date('Y-m-d\TH:i', strtotime($t['timeStamp'])) : '' ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Amount</label>
    <input type="number" step="0.01" name="amount" class="form-control" required
           value="<?= htmlspecialchars($t['amount']); ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Stock</label>
    <select name="StockID" class="form-select" required>
      <option value="">Select…</option>
      <?php mysqli_data_seek($stocks,0); while($s=mysqli_fetch_assoc($stocks)): ?>
      <option value="<?= $s['StockID']; ?>" <?= $s['StockID']==$t['StockID']?'selected':''; ?>>
        <?= $s['StockID']; ?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Participant</label>
    <select name="ParticipantID" class="form-select" required>
      <option value="">Select…</option>
      <?php mysqli_data_seek($parts,0); while($p=mysqli_fetch_assoc($parts)): ?>
      <option value="<?= $p['ParticipantID']; ?>" <?= $p['ParticipantID']==$t['ParticipantID']?'selected':''; ?>>
        <?= $p['ParticipantID'].' - '.htmlspecialchars($p['name']); ?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Staff (Manager only)</label>
    <select name="StaffID" class="form-select" required>
      <option value="">Select…</option>
      <?php mysqli_data_seek($staffs,0); while($s=mysqli_fetch_assoc($staffs)): ?>
      <option value="<?= $s['StaffID']; ?>" <?= $s['StaffID']==$t['StaffID']?'selected':''; ?>>
        <?= $s['StaffID'].' - '.htmlspecialchars($s['name']); ?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">Save</button>
  <a href="transactions_list.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>
