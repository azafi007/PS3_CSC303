<?php
session_start();
require_once 'config.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn,"DELETE FROM audit_report WHERE ReportID=$id");
    header("Location: audit_list.php");
    exit;
}

$sql = "
SELECT ar.ReportID, ar.Date, ar.FindingsSummary,
       sf.name AS auditorName
FROM audit_report ar
LEFT JOIN auditor a ON ar.StaffID = a.StaffID
LEFT JOIN staff sf ON a.StaffID = sf.StaffID
ORDER BY ar.Date DESC
";
$res = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>FIS Admin - Audit Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #e8eaf6, #e0f2f1);
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
      background: linear-gradient(90deg, #3f51b5, #009688);
      color: #fff;
    }
    .table thead {
      background: linear-gradient(90deg,#3f51b5,#009688);
      color: #fff;
    }
  </style>
</head>
<body>
<div class="page-wrapper">
  <div class="card page-card">
    <div class="card-header d-flex justify-content-between align-items-center page-card-header">
      <div>
        <h4 class="mb-0">Audit Reports</h4>
        <small class="text-light-50">Independent reviews of transactions and alerts.</small>
      </div>
      <a href="dashboard_system.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </div>
    <div class="card-body">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="audit_form.php" class="btn btn-success btn-sm">+ Add Report</a>
        <span class="text-muted small">
          Total reports: <strong><?php echo mysqli_num_rows($res); ?></strong>
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Date</th>
              <th>Auditor</th>
              <th>Summary</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php while($r=mysqli_fetch_assoc($res)): ?>
            <tr>
              <td><?php echo $r['ReportID']; ?></td>
              <td><?php echo $r['Date']; ?></td>
              <td><?php echo htmlspecialchars($r['auditorName']); ?></td>
              <td><?php echo htmlspecialchars($r['FindingsSummary']); ?></td>
              <td class="text-end">
                <a href="audit_form.php?id=<?php echo $r['ReportID']; ?>" class="btn btn-primary btn-sm">Edit</a>
                <a href="audit_list.php?delete=<?php echo $r['ReportID']; ?>"
                   onclick="return confirm('Delete this report?');"
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
