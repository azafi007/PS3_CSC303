<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// simple create / delete for logs

// Delete
if (isset($_GET['delete'])) {
    $staffId = (int)$_GET['delete_staff'];
    $loginTime = $_GET['delete_time']; // datetime string

    $stmt = mysqli_prepare($conn,
        "DELETE FROM logs WHERE StaffID = ? AND loginTime = ?"
    );
    mysqli_stmt_bind_param($stmt, "is", $staffId, $loginTime);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: logs_list.php");
    exit;
}

// Create
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = (int)$_POST['StaffID'];
    $activity = trim($_POST['activity']);
    $loginTime = $_POST['loginTime'];     // yyyy-mm-ddThh:mm from input[type=datetime-local]
    $tradeApproval = trim($_POST['tradeApproval']);

    if ($staffId && $activity !== '' && $loginTime !== '') {
        // convert HTML datetime-local value to MySQL datetime
        $loginTime = str_replace('T', ' ', $loginTime);

        $stmt = mysqli_prepare($conn,
            "INSERT INTO logs (StaffID, activity, loginTime, tradeApproval)
             VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "isss", $staffId, $activity, $loginTime, $tradeApproval);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: logs_list.php");
    exit;
}

// load staff list for dropdown
$staff_res = mysqli_query($conn, "
    SELECT StaffID, name
    FROM staff
    ORDER BY StaffID
");

// load all logs
$logs_res = mysqli_query($conn, "
    SELECT l.StaffID, sf.name AS staffName, l.activity, l.loginTime, l.tradeApproval
    FROM logs l
    LEFT JOIN staff sf ON l.StaffID = sf.StaffID
    ORDER BY l.loginTime DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Staff Activity Logs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Staff Activity Logs</h3>
    <a href="dashboard_system.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
  </div>

  <!-- Add Log Form -->
  <div class="card mb-4">
    <div class="card-header">Add New Log Entry</div>
    <div class="card-body">
      <form method="post" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Staff</label>
          <select name="StaffID" class="form-select" required>
            <option value="">Select staff...</option>
            <?php while ($s = mysqli_fetch_assoc($staff_res)): ?>
              <option value="<?= $s['StaffID']; ?>">
                <?= $s['StaffID'].' - '.htmlspecialchars($s['name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Activity</label>
          <input type="text" name="activity" class="form-control" maxlength="30" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Login Time</label>
          <input type="datetime-local" name="loginTime" class="form-control" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Trade Approval</label>
          <input type="text" name="tradeApproval" class="form-control" maxlength="40">
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Save Log</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Logs Table -->
  <div class="card">
    <div class="card-header">All Logs</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Staff</th>
              <th>Activity</th>
              <th>Login Time</th>
              <th>Trade Approval</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($r = mysqli_fetch_assoc($logs_res)): ?>
            <tr>
              <td><?= htmlspecialchars($r['staffName']); ?></td>
              <td><?= htmlspecialchars($r['activity']); ?></td>
              <td><?= $r['loginTime']; ?></td>
              <td><?= htmlspecialchars($r['tradeApproval']); ?></td>
              <td>
                <a href="logs_list.php?delete=1&delete_staff=<?= $r['StaffID']; ?>&delete_time=<?= urlencode($r['loginTime']); ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this log entry?');">
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
</body>
</html>
