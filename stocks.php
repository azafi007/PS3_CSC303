<?php
session_start();
require_once 'config.php';

// DELETE
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM stock WHERE StockID=$id");
    header("Location: stocks.php");
    exit;
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
    .badge-soft { padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.75rem; }
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
                   onclick="return confirm('Delete this stock?');"
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
