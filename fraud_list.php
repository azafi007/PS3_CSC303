<?php
session_start();
require_once 'config.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn,"DELETE FROM fraud_alert WHERE AlertID=$id");
    header("Location: fraud_list.php");
    exit;
}

$sql = "
SELECT f.AlertID,f.riskScore,f.detectionDate,
       st.TransactionID,st.amount,
       tp.name AS participant
FROM fraud_alert f
LEFT JOIN stock_transaction st ON f.TransactionID = st.TransactionID
LEFT JOIN transaction_participant tp ON st.ParticipantID = tp.ParticipantID
ORDER BY f.detectionDate DESC
";
$res = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>FIS Admin - Fraud Alerts</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #ffebee, #fff3e0);
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
      background: linear-gradient(90deg, #f44336, #ff9800);
      color: #fff;
    }
    .table thead {
      background: linear-gradient(90deg,#f44336,#ff9800);
      color: #fff;
    }
  </style>
</head>
<body>
<div class="page-wrapper">
  <div class="card page-card">
    <div class="card-header d-flex justify-content-between align-items-center page-card-header">
      <div>
        <h4 class="mb-0">Fraud Alerts</h4>
        <small class="text-light-50">High‑risk or suspicious transactions flagged by the system.</small>
      </div>
      <a href="dashboard_system.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="fraud_form.php" class="btn btn-danger btn-sm">+ Add Alert</a>
        <span class="text-muted small">
          Total alerts: <strong><?php echo mysqli_num_rows($res); ?></strong>
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Risk</th>
              <th>Date</th>
              <th>Tx ID</th>
              <th>Amount</th>
              <th>Participant</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($res)): ?>
            <tr class="<?php echo ($r['riskScore'] >= 80) ? 'table-danger' : 'table-warning'; ?>">
              <td><?php echo $r['AlertID']; ?></td>
              <td><?php echo $r['riskScore']; ?></td>
              <td><?php echo $r['detectionDate']; ?></td>
              <td><?php echo $r['TransactionID']; ?></td>
              <td><?php echo $r['amount']; ?></td>
              <td><?php echo htmlspecialchars($r['participant']); ?></td>
              <td class="text-end">
                <a href="fraud_form.php?id=<?php echo $r['AlertID']; ?>" class="btn btn-primary btn-sm">Edit</a>
                <a href="fraud_list.php?delete=<?php echo $r['AlertID']; ?>"
                   onclick="return confirm('Delete this alert?');"
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
