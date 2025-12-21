<?php
session_start();
require_once 'config.php';

// optional auth check
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Load participants for dropdowns
$participants = mysqli_query(
    $conn,
    "SELECT ParticipantID, name FROM transaction_participant ORDER BY ParticipantID"
);

// Default empty trade
$t = [
    'TradeID'     => '',
    'tradeAmount' => '',
    'tradeDate'   => '',
    'BuyerID'     => '',
    'SellerID'    => ''
];

// Handle delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM trades WHERE TradeID = $delId");
    header("Location: trades_manage.php");
    exit;
}

// Load single trade for edit
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn, "SELECT * FROM trades WHERE TradeID = $id");
    if ($res && mysqli_num_rows($res) === 1) {
        $t = mysqli_fetch_assoc($res);
    }
}

// Handle create / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = $_POST['TradeID'] !== '' ? (int)$_POST['TradeID'] : null;
    $amount = (float)$_POST['tradeAmount'];
    $date   = $_POST['tradeDate']; // yyyy-mm-dd from input[type=date]
    $buyer  = (int)$_POST['BuyerID'];
    $seller = (int)$_POST['SellerID'];

    if ($buyer === 0 || $seller === 0) {
        die("Buyer and Seller must be selected.");
    }

    if ($id) {
        $sql = "UPDATE trades
                SET tradeAmount = $amount,
                    tradeDate   = '$date',
                    BuyerID     = $buyer,
                    SellerID    = $seller
                WHERE TradeID  = $id";
        mysqli_query($conn, $sql);
    } else {
        $sql = "INSERT INTO trades (tradeAmount, tradeDate, BuyerID, SellerID)
                VALUES ($amount, '$date', $buyer, $seller)";
        mysqli_query($conn, $sql);
    }

    header("Location: trades_manage.php");
    exit;
}

// List all trades
$listSql = "
  SELECT t.TradeID,
         t.tradeAmount,
         t.tradeDate,
         buyer.ParticipantID  AS BuyerID,
         buyer.name           AS BuyerName,
         seller.ParticipantID AS SellerID,
         seller.name          AS SellerName
  FROM trades t
  LEFT JOIN transaction_participant buyer
         ON t.BuyerID = buyer.ParticipantID
  LEFT JOIN transaction_participant seller
         ON t.SellerID = seller.ParticipantID
  ORDER BY t.tradeDate DESC, t.TradeID DESC
";
$listRes = mysqli_query($conn, $listSql);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Trades</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3><?= $t['TradeID'] ? 'Edit Trade' : 'Add Trade'; ?></h3>

<form method="post" class="mb-4">
  <input type="hidden" name="TradeID" value="<?= htmlspecialchars($t['TradeID']); ?>">

  <div class="mb-3">
    <label class="form-label">Trade Date</label>
    <input type="date" name="tradeDate" class="form-control" required
           value="<?= $t['tradeDate'] ?: date('Y-m-d'); ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Trade Amount</label>
    <input type="number" step="0.01" name="tradeAmount" class="form-control" required
           value="<?= htmlspecialchars($t['tradeAmount']); ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Buyer</label>
    <select name="BuyerID" class="form-select" required>
      <option value="">Select…</option>
      <?php mysqli_data_seek($participants, 0); while ($p = mysqli_fetch_assoc($participants)): ?>
      <option value="<?= $p['ParticipantID']; ?>"
        <?= $p['ParticipantID'] == $t['BuyerID'] ? 'selected' : ''; ?>>
        <?= $p['ParticipantID'].' - '.htmlspecialchars($p['name']); ?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Seller</label>
    <select name="SellerID" class="form-select" required>
      <option value="">Select…</option>
      <?php mysqli_data_seek($participants, 0); while ($p = mysqli_fetch_assoc($participants)): ?>
      <option value="<?= $p['ParticipantID']; ?>"
        <?= $p['ParticipantID'] == $t['SellerID'] ? 'selected' : ''; ?>>
        <?= $p['ParticipantID'].' - '.htmlspecialchars($p['name']); ?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-primary">Save</button>
  <a href="trades_manage.php" class="btn btn-secondary">Cancel</a>
<a href="dashboard_system.php" class="btn btn-outline-secondary">Back to Dashboard</a>
</form>

<h3>All Trades</h3>
<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Date</th>
      <th>Amount</th>
      <th>Buyer</th>
      <th>Seller</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = mysqli_fetch_assoc($listRes)): ?>
    <tr>
      <td><?= htmlspecialchars($row['TradeID']); ?></td>
      <td><?= htmlspecialchars($row['tradeDate']); ?></td>
      <td><?= htmlspecialchars($row['tradeAmount']); ?></td>
      <td><?= htmlspecialchars($row['BuyerID'].' - '.$row['BuyerName']); ?></td>
      <td><?= htmlspecialchars($row['SellerID'].' - '.$row['SellerName']); ?></td>
      <td>
        <a href="trades_manage.php?id=<?= $row['TradeID']; ?>" class="btn btn-sm btn-warning">Edit</a>
        <a href="trades_manage.php?delete=<?= $row['TradeID']; ?>"
           class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this trade?');">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

</body>
</html>
