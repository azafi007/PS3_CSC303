<?php
session_start();
require_once 'config.php';

// DELETE logic (same as before)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM manager WHERE StaffID=$id");
    mysqli_query($conn, "DELETE FROM auditor WHERE StaffID=$id");
    mysqli_query($conn, "DELETE FROM financial_analyst WHERE StaffID=$id");
    mysqli_query($conn, "DELETE FROM staff WHERE StaffID=$id");
    header("Location: staff_list.php");
    exit;
}

// READ staff data
$sql = "
SELECT s.StaffID, s.userName, s.name, s.designation, s.contact,
       CASE
         WHEN m.StaffID IS NOT NULL THEN 'Manager'
         WHEN au.StaffID IS NOT NULL THEN 'Auditor'
         WHEN fa.StaffID IS NOT NULL THEN 'Financial Analyst'
         ELSE 'Staff'
       END AS roleType
FROM staff s
LEFT JOIN manager m ON s.StaffID = m.StaffID
LEFT JOIN auditor au ON s.StaffID = au.StaffID
LEFT JOIN financial_analyst fa ON s.StaffID = fa.StaffID
";
$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>FIS Admin - Staff</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
      min-height: 100vh;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .page-wrapper {
      max-width: 1200px;
      margin: 30px auto;
    }
    .page-card {
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0,0,0,0.08);
      border: none;
    }
    .page-card-header {
      background: linear-gradient(90deg, #0d6efd, #6610f2);
      color: #fff;
    }
    .table thead {
      background: linear-gradient(90deg,#0d6efd,#6610f2);
      color: #fff;
    }
    .badge-soft {
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      font-size: 0.75rem;
    }
    .badge-manager   { background: rgba(13,110,253,0.1);  color:#0d6efd; }
    .badge-auditor   { background: rgba(220,53,69,0.1);   color:#dc3545; }
    .badge-fa        { background: rgba(25,135,84,0.1);   color:#198754; }
    .badge-staff     { background: rgba(108,117,125,0.1); color:#6c757d; }
  </style>
</head>
<body>
<div class="page-wrapper">
  <div class="card page-card">
    <!-- COLORFUL HEADER -->
    <div class="card-header d-flex justify-content-between align-items-center page-card-header">
      <div>
        <h4 class="mb-0">Staff Management</h4>
        <small class="text-light-50">Create, update and remove staff and roles.</small>
      </div>
      <a href="dashboard_system.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>

    <!-- PAGE BODY -->
    <div class="card-body">

      <!-- Top toolbar -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <button onclick="window.location.href='staff_form.php';" class="btn btn-success btn-sm">
          + Add Staff
        </button>
        <span class="text-muted small">
          Total staff: <strong><?php echo mysqli_num_rows($res); ?></strong>
        </span>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Name</th>
              <th>Designation</th>
              <th>Role Type</th>
              <th>Contact</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while($r = mysqli_fetch_assoc($res)): ?>
            <?php
              $badgeClass = 'badge-staff';
              if ($r['roleType'] === 'Manager')          $badgeClass = 'badge-manager';
              elseif ($r['roleType'] === 'Auditor')      $badgeClass = 'badge-auditor';
              elseif ($r['roleType'] === 'Financial Analyst') $badgeClass = 'badge-fa';
            ?>
            <tr>
              <td><?php echo $r['StaffID']; ?></td>
              <td><?php echo htmlspecialchars($r['userName']); ?></td>
              <td><?php echo htmlspecialchars($r['name']); ?></td>
              <td><?php echo htmlspecialchars($r['designation']); ?></td>
              <td>
                <span class="badge-soft <?php echo $badgeClass; ?>">
                  <?php echo $r['roleType']; ?>
                </span>
              </td>
              <td><?php echo htmlspecialchars($r['contact']); ?></td>
              <td class="text-end">
                <a href="staff_form.php?id=<?php echo $r['StaffID']; ?>" class="btn btn-primary btn-sm">
                  Edit
                </a>
                <a href="staff_list.php?delete=<?php echo $r['StaffID']; ?>"
                   onclick="return confirm('Delete this staff?');"
                   class="btn btn-danger btn-sm">
                  Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </div><!-- /card-body -->
  </div><!-- /card -->
</div><!-- /wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
