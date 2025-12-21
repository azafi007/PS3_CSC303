<?php
session_start();
require_once 'config.php';

// optionally enforce login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// join trades with participants for names
$sql = "
  SELECT t.TradeID,
         t.tradeAmount,
         t.tradeDate,
         buyer.ParticipantID AS BuyerID,
         buyer.name          AS BuyerName,
         seller.ParticipantID AS SellerID,
         seller.name          AS SellerName
  FROM trades t
  LEFT JOIN transaction_participant buyer
         ON t.BuyerID = buyer.ParticipantID
  LEFT JOIN transaction_participant seller
         ON t.SellerID = seller.ParticipantID
  ORDER BY t.tradeDate DESC, t.TradeID DESC
";
$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Trades</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3>Trades</h3>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Trade ID</th>
      <th>Date</th>
      <th>Amount</th>
      <th>Buyer</th>
      <th>Seller</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = mysqli_fetch_assoc($res)): ?>
    <tr>
      <td><?= htmlspecialchars($row['TradeID']); ?></td>
      <td><?= htmlspecialchars($row['tradeDate']); ?></td>
      <td><?= htmlspecialchars($row['tradeAmount']); ?></td>
      <td><?= htmlspecialchars($row['BuyerID'].' - '.$row['BuyerName']); ?></td>
      <td><?= htmlspecialchars($row['SellerID'].' - '.$row['SellerName']); ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</body>
</html>
