<?php
session_start();
require_once 'config.php';

// auditors only
$auditors = mysqli_query($conn,"
   SELECT a.StaffID, s.name
   FROM auditor a
   JOIN staff s ON a.StaffID = s.StaffID
");

$a = ['ReportID'=>'','Date'=>'','FindingsSummary'=>'','StaffID'=>''];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn,"SELECT * FROM audit_report WHERE ReportID=$id");
    if ($res && mysqli_num_rows($res)==1) $a = mysqli_fetch_assoc($res);
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id   = $_POST['ReportID']!=='' ? (int)$_POST['ReportID'] : null;
    $date = $_POST['Date'] ?: date('Y-m-d');
    $sum  = mysqli_real_escape_string($conn,$_POST['FindingsSummary']);
    $sid  = (int)$_POST['StaffID'];

    if ($id) {
        $sql = "UPDATE audit_report
                SET Date='$date', FindingsSummary='$sum', StaffID=$sid
                WHERE ReportID=$id";
    } else {
        $sql = "INSERT INTO audit_report(StaffID,Date,FindingsSummary)
                VALUES($sid,'$date','$sum')";
    }
    mysqli_query($conn,$sql);
    header("Location: audit_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= $a['ReportID']?'Edit Report':'Add Report'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3><?= $a['ReportID']?'Edit Report':'Add Report'; ?></h3>
<form method="post">
  <input type="hidden" name="ReportID" value="<?= htmlspecialchars($a['ReportID']); ?>">
  <div class="mb-3">
    <label class="form-label">Date</label>
    <input type="date" name="Date" class="form-control"
           value="<?= $a['Date']; ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Auditor</label>
    <select name="StaffID" class="form-select" required>
      <option value="">Select…</option>
      <?php mysqli_data_seek($auditors,0); while($s=mysqli_fetch_assoc($auditors)): ?>
      <option value="<?= $s['StaffID']; ?>" <?= $s['StaffID']==$a['StaffID']?'selected':''; ?>>
        <?= $s['StaffID'].' - '.htmlspecialchars($s['name']); ?>
      </option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">Findings Summary</label>
    <textarea name="FindingsSummary" class="form-control" rows="4"><?= htmlspecialchars($a['FindingsSummary']); ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Save</button>
  <a href="audit_list.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>
