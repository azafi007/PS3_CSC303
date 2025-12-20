<?php
session_start();
require_once 'config.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn,"DELETE FROM transaction_participant WHERE ParticipantID=$id");
    header("Location: participants_list.php");
    exit;
}

$res = mysqli_query($conn,"SELECT * FROM transaction_participant ORDER BY ParticipantID");
?>

<!DOCTYPE html>
<html>
<head>
  <title>FIS Admin - Participants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #fff3e0, #e8eaf6);
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
      background: linear-gradient(90deg, #ff9800, #3f51b5);
      color: #fff;
    }
    .table thead {
      background: linear-gradient(90deg,#ff9800,#3f51b5);
      color: #fff;
    }
    .badge-soft { padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.75rem; }
    .badge-ind { background: rgba(33,150,243,0.12); color:#1976d2; }
    .badge-corp { background: rgba(76,175,80,0.12); color:#2e7d32; }
    .badge-inst { background: rgba(255,193,7,0.15); color:#ff8f00; }
  </style>
</head>
<body>
<div class="page-wrapper">
  <div class="card page-card">
    <div class="card-header d-flex justify-content-between align-items-center page-card-header">
      <div>
        <h4 class="mb-0">Transaction Participants</h4>
        <small class="text-light-50">Investors, institutions and companies involved in trades.</small>
      </div>
      <a href="dashboard_system.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <button onclick="window.location.href='participant_form.php';" class="btn btn-success btn-sm">
          + Add Participant
        </button>
        <span class="text-muted small">
          Total participants: <strong><?php echo mysqli_num_rows($res); ?></strong>
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Account Type</th>
              <th>Email</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while($p = mysqli_fetch_assoc($res)): ?>
            <?php
              $badgeClass = 'badge-ind';
              if (stripos($p['accountType'],'corporate') !== false) $badgeClass = 'badge-corp';
              if (stripos($p['accountType'],'institution') !== false) $badgeClass = 'badge-inst';
            ?>
            <tr>
              <td><?php echo $p['ParticipantID']; ?></td>
              <td><?php echo htmlspecialchars($p['name']); ?></td>
              <td>
                <span class="badge-soft <?php echo $badgeClass; ?>">
                  <?php echo htmlspecialchars($p['accountType']); ?>
                </span>
              </td>
              <td><?php echo htmlspecialchars($p['email']); ?></td>
              <td class="text-end">
                <a href="participant_form.php?id=<?php echo $p['ParticipantID']; ?>" class="btn btn-primary btn-sm">
                  Edit
                </a>
                <a href="participants_list.php?delete=<?php echo $p['ParticipantID']; ?>"
                   onclick="return confirm('Delete this participant?');"
                   class="btn btn-danger btn-sm">
                  Delete
                </a>
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
