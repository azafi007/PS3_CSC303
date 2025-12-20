<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = 'warning';

// DELETE with FK check
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // check if this stock is used in stock_transaction
    $check = mysqli_query($conn, "SELECT COUNT(*) AS c FROM stock_transaction WHERE StockID=$id");
    $row   = mysqli_fetch_assoc($check);

    if ($row && $row['c'] > 0) {
        $count = (int)$row['c'];
        $message = "Cannot delete StockID $id: it is used in $count transaction(s) in stock_transaction. Delete those transactions first.";
        $message_type = 'danger';
    } else {
        mysqli_query($conn, "DELETE FROM stock WHERE StockID=$id");
        $message = "StockID $id deleted successfully.";
        $message_type = 'success';
    }
}

// READ stocks
$sql = "SELECT s.StockID, s.totalShare, s.currentPrice,
               tp.ParticipantID, tp.name AS participant
        FROM stock s
        LEFT JOIN transaction_participant tp
          ON s.ParticipantID = tp.ParticipantID";
$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>FIS Admin - Stocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #e0f7fa, #e8f5e9);
      min-height: 100vh;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .page-wrapper { max-width: 1200px; margin: 30px auto; }
    .page-card {
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0,0,0,0.08);
      border: none;
    }
    .page-card-header {
      background: linear-gradient(90deg, #00acc1, #43a047);
      color: #fff;
    }
    .table thead {
      background: linear-gradient(90deg,#00acc1,#43a047);
      color: #fff;
    }
  </style>
</head>
<body>
<div class="page-wrapper">
  <div class="card page-card">
    <div class="card-header d-flex justify-content-between align-items-center page-card-header">
      <div>
        <h4 class="mb-0">Stock Register</h4>
        <small class="text-light-50">All listed stocks with current price and total shares.</small>
      </div>
      <a href="dashboard_system.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
    <div class="card-body">

      <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> py-2">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="stock_form.php" class="btn btn-success btn-sm">+ Add Stock</a>
        <span class="text-muted small">
          Total stocks: <strong><?php echo mysqli_num_rows($res); ?></strong>
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Stock ID</th>
              <th>Participant</th>
              <th>Total Share</th>
              <th>Current Price</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while($r = mysqli_fetch_assoc($res)): ?>
            <tr>
              <td><?php echo $r['StockID']; ?></td>
              <td><?php echo htmlspecialchars($r['participant']); ?></td>
              <td><?php echo $r['totalShare']; ?></td>
              <td><?php echo $r['currentPrice']; ?></td>
              <td class="text-end">
                <a href="stock_form.php?id=<?php echo $r['StockID']; ?>" class="btn btn-primary btn-sm">Edit</a>
                <a href="stocks.php?delete=<?php echo $r['StockID']; ?>"
                   onclick="return confirm('Delete this stock? If it has transactions, deletion will be blocked.');"
                   class="btn btn-danger btn-sm">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
