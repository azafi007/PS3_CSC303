<?php
session_start();
require_once 'config.php';

$p = ['ParticipantID'=>'','name'=>'','accountType'=>'','email'=>''];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn,"SELECT * FROM transaction_participant WHERE ParticipantID=$id");
    if ($res && mysqli_num_rows($res)==1) {
        $p = mysqli_fetch_assoc($res);
    }
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id   = $_POST['ParticipantID'] !== '' ? (int)$_POST['ParticipantID'] : null;
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $type = mysqli_real_escape_string($conn,$_POST['accountType']);
    $email= mysqli_real_escape_string($conn,$_POST['email']);

    if ($id) {
        $sql = "UPDATE transaction_participant
                SET name='$name', accountType='$type', email='$email'
                WHERE ParticipantID=$id";
    } else {
        $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT MAX(ParticipantID) AS mx FROM transaction_participant"));
        $id = ($row['mx'] ?? 500) + 1;
        $sql = "INSERT INTO transaction_participant(ParticipantID,name,accountType,email)
                VALUES($id,'$name','$type','$email')";
    }
    mysqli_query($conn,$sql);
    header("Location: participants_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= $p['ParticipantID']?'Edit Participant':'Add Participant'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<h3><?= $p['ParticipantID']?'Edit Participant':'Add Participant'; ?></h3>
<form method="post">
  <input type="hidden" name="ParticipantID" value="<?= htmlspecialchars($p['ParticipantID']); ?>">
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" required
           value="<?= htmlspecialchars($p['name']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Account Type</label>
    <input type="text" name="accountType" class="form-control"
           value="<?= htmlspecialchars($p['accountType']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control"
           value="<?= htmlspecialchars($p['email']); ?>">
  </div>
  <button type="submit" class="btn btn-primary">Save</button>
  <a href="participants_list.php" class="btn btn-secondary">Cancel</a>
</form>
</body>
</html>
