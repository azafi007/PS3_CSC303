<?php
session_start();
require_once 'config.php';

$staff = ['StaffID'=>'','userName'=>'','name'=>'','designation'=>'','contact'=>''];
$roleType = 'Staff';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "
      SELECT s.*, 
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
      WHERE s.StaffID=$id
    ";
    $res = mysqli_query($conn,$sql);
    if ($res && mysqli_num_rows($res)==1){
        $row = mysqli_fetch_assoc($res);
        $staff = $row;
        $roleType = $row['roleType'];
    }
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id   = $_POST['StaffID'] !== '' ? (int)$_POST['StaffID'] : null;
    $user = mysqli_real_escape_string($conn,$_POST['userName']);
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $des  = mysqli_real_escape_string($conn,$_POST['designation']);
    $cont = mysqli_real_escape_string($conn,$_POST['contact']);
    $roleType = $_POST['roleType'];

    if ($id) {
        $sql = "UPDATE staff SET userName='$user', name='$name', designation='$des', contact='$cont'
                WHERE StaffID=$id";
        mysqli_query($conn,$sql);
        // reset subtype tables
        mysqli_query($conn,"DELETE FROM manager WHERE StaffID=$id");
        mysqli_query($conn,"DELETE FROM auditor WHERE StaffID=$id");
        mysqli_query($conn,"DELETE FROM financial_analyst WHERE StaffID=$id");
    } else {
        // simple manual ID (for project), better: make StaffID AUTO_INCREMENT in DB
        $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT MAX(StaffID) AS mx FROM staff"));
        $id = ($row['mx'] ?? 100) + 1;
        $sql = "INSERT INTO staff(StaffID,userName,name,designation,contact)
                VALUES($id,'$user','$name','$des','$cont')";
        mysqli_query($conn,$sql);
    }

    if ($roleType=='Manager') {
        mysqli_query($conn,"INSERT INTO manager(StaffID,reportApproval,approvalStatus)
                            VALUES($id,'Standard','Approved')");
    } elseif ($roleType=='Auditor') {
        mysqli_query($conn,"INSERT INTO auditor(StaffID,`last review date`) VALUES($id,CURDATE())");
    } elseif ($roleType=='Financial Analyst') {
        mysqli_query($conn,"INSERT INTO financial_analyst(StaffID,`specialized area`)
                            VALUES($id,'General')");
    }

    header("Location: staff_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= $staff['StaffID'] ? 'Edit Staff':'Add Staff'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3><?= $staff['StaffID'] ? 'Edit Staff':'Add Staff'; ?></h3>
<form method="post">
  <input type="hidden" name="StaffID" value="<?= htmlspecialchars($staff['StaffID']); ?>">
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input type="text" name="userName" class="form-control" required
           value="<?= htmlspecialchars($staff['userName']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" required
           value="<?= htmlspecialchars($staff['name']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Designation</label>
    <input type="text" name="designation" class="form-control"
           value="<?= htmlspecialchars($staff['designation']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Contact</label>
    <input type="text" name="contact" class="form-control"
           value="<?= htmlspecialchars($staff['contact']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Role Type</label>
    <select name="roleType" class="form-select">
      <option <?= $roleType=='Staff'?'selected':''; ?>>Staff</option>
      <option <?= $roleType=='Manager'?'selected':''; ?>>Manager</option>
      <option <?= $roleType=='Auditor'?'selected':''; ?>>Auditor</option>
      <option <?= $roleType=='Financial Analyst'?'selected':''; ?>>Financial Analyst</option>
    </select>
  </div>
  <button type="submit" class="btn btn-primary">Save</button>
  <a href="staff_list.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>
