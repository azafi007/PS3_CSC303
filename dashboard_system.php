<?php
session_start();
require_once 'config.php';

// force login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// quick counts
$counts = [];
$tables = [
  'staff',
  'transaction_participant',
  'stock',
  'stock_transaction',
  'fraud_alert',
  'audit_report'
];
foreach ($tables as $t) {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM `$t`");
    $row = mysqli_fetch_assoc($res);
    $counts[$t] = $row ? $row['c'] : 0;
}

// staff and roles
$staff_sql = "
SELECT s.StaffID, s.userName, s.name, s.designation, s.contact,
       CASE
         WHEN m.StaffID IS NOT NULL THEN 'Manager'
         WHEN aud.StaffID IS NOT NULL THEN 'Auditor'
         WHEN fa.StaffID IS NOT NULL THEN 'Financial Analyst'
         ELSE 'Staff'
       END AS roleType
FROM staff s
LEFT JOIN manager m ON s.StaffID = m.StaffID
LEFT JOIN auditor aud ON s.StaffID = aud.StaffID
LEFT JOIN financial_analyst fa ON s.StaffID = fa.StaffID
";
$staff_res = mysqli_query($conn, $staff_sql);

// stock + history + prediction
$stock_sql = "
SELECT s.StockID,
       tp.name AS participant,
       s.totalShare,
       s.currentPrice,
       ph.recordingTime,
       ph.closingPrice,
       pr.predictedPrice,
       pr.targetDate
FROM stock s
JOIN transaction_participant tp ON s.ParticipantID = tp.ParticipantID
LEFT JOIN price_history ph ON s.StockID = ph.StockID
LEFT JOIN prediction pr ON s.StockID = pr.StockID
ORDER BY ph.recordingTime DESC, pr.targetDate DESC
LIMIT 25
";
$stock_res = mysqli_query($conn, $stock_sql);

// transactions & trades
$tx_sql = "
SELECT st.TransactionID,
       st.timeStamp,
       st.amount,
       st.StockID,
       tp.name AS participant,
       sf.name AS staffName
FROM stock_transaction st
LEFT JOIN transaction_participant tp ON st.ParticipantID = tp.ParticipantID
LEFT JOIN staff sf ON st.StaffID = sf.StaffID
ORDER BY st.timeStamp DESC
LIMIT 25
";
$tx_res = mysqli_query($conn, $tx_sql);

$trade_sql = "
SELECT t.TradeID,
       t.tradeAmount,
       t.tradeDate,
       b.name AS buyer,
       s.name AS seller
FROM trades t
LEFT JOIN transaction_participant b ON t.BuyerID = b.ParticipantID
LEFT JOIN transaction_participant s ON t.SellerID = s.ParticipantID
ORDER BY t.tradeDate DESC
LIMIT 25
";
$trade_res = mysqli_query($conn, $trade_sql);

// logs
$logs_sql = "
SELECT l.StaffID, sf.name AS staffName, l.activity, l.loginTime, l.tradeApproval
FROM logs l
LEFT JOIN staff sf ON l.StaffID = sf.StaffID
ORDER BY l.loginTime DESC
LIMIT 25
";
$logs_res = mysqli_query($conn, $logs_sql);

// fraud alerts
$fraud_sql = "
SELECT f.AlertID, f.riskScore, f.detectionDate,
       st.TransactionID, st.amount,
       tp.name AS participant
FROM fraud_alert f
LEFT JOIN stock_transaction st ON f.TransactionID = st.TransactionID
LEFT JOIN transaction_participant tp ON st.ParticipantID = tp.ParticipantID
ORDER BY f.detectionDate DESC
LIMIT 25
";
$fraud_res = mysqli_query($conn, $fraud_sql);

// audit reports
$audit_sql = "
SELECT ar.ReportID, ar.Date, ar.FindingsSummary,
       sf.name AS auditorName
FROM audit_report ar
LEFT JOIN auditor a ON ar.StaffID = a.StaffID
LEFT JOIN staff sf ON a.StaffID = sf.StaffID
ORDER BY ar.Date DESC
LIMIT 25
";
$audit_res = mysqli_query($conn, $audit_sql);

// participants
$participant_sql = "
SELECT tp.ParticipantID,
       tp.name,
       tp.accountType,
       tp.email,
       CASE
         WHEN inv.ParticipantID IS NOT NULL THEN 'Investor'
         WHEN inst.ParticipantID IS NOT NULL THEN 'Institution'
         WHEN c.ParticipantID IS NOT NULL THEN 'Company'
         ELSE 'Other'
       END AS roleType,
       COALESCE(inv.totalShare, inst.totalShare, c.totalShare) AS totalShare,
       COALESCE(inv.currentPrice, inst.currentPrice, c.currentPrice) AS currentPrice
FROM transaction_participant tp
LEFT JOIN investor inv ON tp.ParticipantID = inv.ParticipantID
LEFT JOIN institution inst ON tp.ParticipantID = inst.ParticipantID
LEFT JOIN company c ON tp.ParticipantID = c.ParticipantID
ORDER BY tp.ParticipantID
";
$participant_res = mysqli_query($conn, $participant_sql);

// ---------- OVERVIEW CHART DATA --------------------

// chart 1: transactions per participant (top 5)
$chart_tx = mysqli_query($conn,"
    SELECT tp.name AS pname, COUNT(*) AS cnt
    FROM stock_transaction st
    JOIN transaction_participant tp ON st.ParticipantID = tp.ParticipantID
    GROUP BY st.ParticipantID
    ORDER BY cnt DESC
    LIMIT 5
");
$tx_labels = $tx_values = [];
while($row = mysqli_fetch_assoc($chart_tx)){
    $tx_labels[] = $row['pname'];
    $tx_values[] = (int)$row['cnt'];
}

// chart 2: price history for StockID = 1
$chart_price = mysqli_query($conn,"
    SELECT recordingTime, closingPrice
    FROM price_history
    WHERE StockID = 1
    ORDER BY recordingTime
");
$ph_labels = $ph_values = [];
while($row = mysqli_fetch_assoc($chart_price)){
    $ph_labels[] = $row['recordingTime'];
    $ph_values[] = (float)$row['closingPrice'];
}

// chart 3: participant type breakdown
$chart_role = mysqli_query($conn,"
    SELECT
      SUM(CASE WHEN inv.ParticipantID IS NOT NULL THEN 1 ELSE 0 END) AS Investor,
      SUM(CASE WHEN inst.ParticipantID IS NOT NULL THEN 1 ELSE 0 END) AS Institution,
      SUM(CASE WHEN c.ParticipantID IS NOT NULL THEN 1 ELSE 0 END) AS Company,
      SUM(CASE WHEN inv.ParticipantID IS NULL
                AND inst.ParticipantID IS NULL
                AND c.ParticipantID IS NULL THEN 1 ELSE 0 END) AS Other
    FROM transaction_participant tp
    LEFT JOIN investor inv ON tp.ParticipantID = inv.ParticipantID
    LEFT JOIN institution inst ON tp.ParticipantID = inst.ParticipantID
    LEFT JOIN company c ON tp.ParticipantID = c.ParticipantID
");
$role_row = mysqli_fetch_assoc($chart_role);
$role_labels = ['Investor','Institution','Company','Other'];
$role_values = [
    (int)$role_row['Investor'],
    (int)$role_row['Institution'],
    (int)$role_row['Company'],
    (int)$role_row['Other']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Financial Institution – System Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- [web:82][web:88] -->
  <style>
    body { background-color: #f5f6fa; }
    .content-area { max-height: 100vh; overflow-y: auto; }
    .section-title { margin-top: 40px; margin-bottom: 10px; }

    /* Colorful top navbar */
    .navbar-gradient {
        background: linear-gradient(90deg, #0d6efd, #6610f2, #ff6f61);
    }
    .navbar-gradient .navbar-brand {
        color: #fff !important;
        letter-spacing: 0.05em;
    }
    .navbar-gradient .nav-link {
        color: rgba(255,255,255,0.85) !important;
        font-weight: 500;
    }
    .navbar-gradient .nav-link:hover,
    .navbar-gradient .nav-link:focus {
        color: #fff !important;
        text-decoration: underline;
    }
    .navbar-gradient .nav-link.active {
        color: #fff !important;
        border-bottom: 2px solid #ffd54f;
    }
    .navbar-gradient .navbar-toggler {
        border-color: rgba(255,255,255,0.7);
    }
    .navbar-gradient .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255,255,255,0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
  </style>
</head>
<body>
<div class="d-flex flex-column" style="height:100vh;">

  <!-- TOP BAR NAVIGATION WITH LOGOUT -->
  <nav class="navbar navbar-expand-lg navbar-gradient px-3">
    <a class="navbar-brand fw-bold me-4" href="#">FIS Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="topNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="#overview">Overview</a></li>
        <li class="nav-item"><a class="nav-link" href="#staff">Staff &amp; Roles</a></li>
        <li class="nav-item"><a class="nav-link" href="#stock">Stock &amp; Market</a></li>
        <li class="nav-item"><a class="nav-link" href="#tx">Transactions &amp; Trades</a></li>
        <li class="nav-item"><a class="nav-link" href="#risk">Risk &amp; Compliance</a></li>
        <li class="nav-item"><a class="nav-link" href="#party">Participants</a></li>
      </ul>
      <span class="me-3 small text-light">
        Logged in as <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
      </span>
      <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="flex-grow-1 p-4 content-area">

    <!-- OVERVIEW WITH KPIs + CHARTS -->
    <div id="overview">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h3>System Overview</h3>
          <p class="text-muted mb-0">Overall activity and participant distribution for the system.</p>
        </div>
        <!-- All-time / Export removed -->
      </div>

      <!-- KPI CARDS WITH MANAGE LINKS -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
          <div class="card text-center shadow-sm">
            <div class="card-body">
              <div class="text-muted small">Staff</div>
              <div class="fs-4"><?php echo $counts['staff']; ?></div>
              <a href="staff_list.php" class="btn btn-outline-primary btn-sm mt-2">Manage</a>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="card text-center shadow-sm">
            <div class="card-body">
              <div class="text-muted small">Participants</div>
              <div class="fs-4"><?php echo $counts['transaction_participant']; ?></div>
              <a href="participants_list.php" class="btn btn-outline-primary btn-sm mt-2">Manage</a>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="card text-center shadow-sm">
            <div class="card-body">
              <div class="text-muted small">Stocks</div>
              <div class="fs-4"><?php echo $counts['stock']; ?></div>
              <a href="stocks.php" class="btn btn-outline-primary btn-sm mt-2">Manage</a>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="card text-center shadow-sm">
            <div class="card-body">
              <div class="text-muted small">Transactions</div>
              <div class="fs-4"><?php echo $counts['stock_transaction']; ?></div>
              <a href="transactions_list.php" class="btn btn-outline-primary btn-sm mt-2">Manage</a>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="card text-center shadow-sm">
            <div class="card-body">
              <div class="text-muted small">Fraud Alerts</div>
              <div class="fs-4 text-danger"><?php echo $counts['fraud_alert']; ?></div>
              <a href="fraud_list.php" class="btn btn-outline-danger btn-sm mt-2">Manage</a>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-2">
          <div class="card text-center shadow-sm">
            <div class="card-body">
              <div class="text-muted small">Audit Reports</div>
              <div class="fs-4"><?php echo $counts['audit_report']; ?></div>
              <a href="audit_list.php" class="btn btn-outline-primary btn-sm mt-2">Manage</a>
            </div>
          </div>
        </div>
      </div>

      <!-- OVERVIEW CHARTS -->
      <div class="row g-4 mb-5">
        <div class="col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-header small text-muted">Top Participants by Transactions</div>
            <div class="card-body">
              <canvas id="txPerParticipant"></canvas>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-header small text-muted">Stock 1 – Closing Price Over Time</div>
            <div class="card-body">
              <canvas id="priceHistory"></canvas>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card shadow-sm h-100">
            <div class="card-header small text-muted">Participant Type Breakdown</div>
            <div class="card-body">
              <canvas id="participantRoles"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- STAFF & ROLES -->
    <h4 id="staff" class="section-title">Staff & Roles</h4>
    <div class="mb-2">
      <a href="staff_list.php" class="btn btn-success btn-sm">Add / Edit Staff</a>
    </div>
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th><th>Username</th><th>Name</th><th>Designation</th><th>Role Type</th><th>Contact</th>
              </tr>
            </thead>
            <tbody>
            <?php while($r = mysqli_fetch_assoc($staff_res)): ?>
              <tr>
                <td><?php echo $r['StaffID']; ?></td>
                <td><?php echo htmlspecialchars($r['userName']); ?></td>
                <td><?php echo htmlspecialchars($r['name']); ?></td>
                <td><?php echo htmlspecialchars($r['designation']); ?></td>
                <td><span class="badge bg-secondary"><?php echo $r['roleType']; ?></span></td>
                <td><?php echo htmlspecialchars($r['contact']); ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- STOCK & MARKET -->
    <h4 id="stock" class="section-title">Stock & Market View</h4>
    <div class="mb-2">
      <a href="stocks.php" class="btn btn-success btn-sm">Add / Edit Stocks</a>
    </div>
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Stock</th><th>Participant</th><th>Total Share</th><th>Current Price</th>
                <th>Last Close</th><th>Recorded At</th><th>Predicted Price</th><th>Target Date</th>
              </tr>
            </thead>
            <tbody>
            <?php while($r = mysqli_fetch_assoc($stock_res)): ?>
              <tr>
                <td><?php echo $r['StockID']; ?></td>
                <td><?php echo htmlspecialchars($r['participant']); ?></td>
                <td><?php echo $r['totalShare']; ?></td>
                <td><?php echo $r['currentPrice']; ?></td>
                <td><?php echo $r['closingPrice']; ?></td>
                <td><?php echo $r['recordingTime']; ?></td>
                <td><?php echo $r['predictedPrice']; ?></td>
                <td><?php echo $r['targetDate']; ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TRANSACTIONS & TRADES -->
    <h4 id="tx" class="section-title">Transactions & Trades</h4>
    <div class="mb-2">
      <a href="transactions_list.php" class="btn btn-success btn-sm">Add / Edit Transactions</a>
    </div>
    <div class="row">
      <div class="col-lg-7">
        <div class="card mb-4 shadow-sm">
          <div class="card-header small text-muted">Recent Stock Transactions</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID</th><th>Time</th><th>Amount</th><th>Stock</th><th>Participant</th><th>Staff</th>
                  </tr>
                </thead>
                <tbody>
                <?php while($r = mysqli_fetch_assoc($tx_res)): ?>
                  <tr>
                    <td><?php echo $r['TransactionID']; ?></td>
                    <td><?php echo $r['timeStamp']; ?></td>
                    <td><?php echo $r['amount']; ?></td>
                    <td><?php echo $r['StockID']; ?></td>
                    <td><?php echo htmlspecialchars($r['participant']); ?></td>
                    <td><?php echo htmlspecialchars($r['staffName']); ?></td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card mb-4 shadow-sm">
          <div class="card-header small text-muted">Recent Trades</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID</th><th>Date</th><th>Amount</th><th>Buyer</th><th>Seller</th>
                  </tr>
                </thead>
                <tbody>
                <?php while($r = mysqli_fetch_assoc($trade_res)): ?>
                  <tr>
                    <td><?php echo $r['TradeID']; ?></td>
                    <td><?php echo $r['tradeDate']; ?></td>
                    <td><?php echo $r['tradeAmount']; ?></td>
                    <td><?php echo htmlspecialchars($r['buyer']); ?></td>
                    <td><?php echo htmlspecialchars($r['seller']); ?></td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="card mb-4 shadow-sm">
          <div class="card-header small text-muted">Recent Staff Activity (Logs)</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Staff</th><th>Activity</th><th>Login Time</th><th>Trade Approval</th>
                  </tr>
                </thead>
                <tbody>
                <?php while($r = mysqli_fetch_assoc($logs_res)): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($r['staffName']); ?></td>
                    <td><?php echo htmlspecialchars($r['activity']); ?></td>
                    <td><?php echo $r['loginTime']; ?></td>
                    <td><?php echo htmlspecialchars($r['tradeApproval']); ?></td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- RISK & COMPLIANCE -->
    <h4 id="risk" class="section-title">Risk & Compliance</h4>
    <div class="mb-2">
      <a href="fraud_list.php" class="btn btn-danger btn-sm">Add / Edit Fraud Alerts</a>
      <a href="audit_list.php" class="btn btn-success btn-sm ms-2">Add / Edit Audit Reports</a>
    </div>
    <div class="row">
      <div class="col-lg-7">
        <div class="card mb-4 shadow-sm">
          <div class="card-header small text-muted">Fraud Alerts</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Alert</th><th>Risk</th><th>Detection Date</th><th>Tx ID</th><th>Amount</th><th>Participant</th>
                  </tr>
                </thead>
                <tbody>
                <?php while($r = mysqli_fetch_assoc($fraud_res)): ?>
                  <tr>
                    <td><?php echo $r['AlertID']; ?></td>
                    <td>
                      <span class="badge <?php echo ($r['riskScore'] >= 80) ? 'bg-danger' : 'bg-warning'; ?>">
                        <?php echo $r['riskScore']; ?>
                      </span>
                    </td>
                    <td><?php echo $r['detectionDate']; ?></td>
                    <td><?php echo $r['TransactionID']; ?></td>
                    <td><?php echo $r['amount']; ?></td>
                    <td><?php echo htmlspecialchars($r['participant']); ?></td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card mb-4 shadow-sm">
          <div class="card-header small text-muted">Audit Reports</div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Report</th><th>Date</th><th>Auditor</th><th>Summary</th>
                  </tr>
                </thead>
                <tbody>
                <?php while($r = mysqli_fetch_assoc($audit_res)): ?>
                  <tr>
                    <td><?php echo $r['ReportID']; ?></td>
                    <td><?php echo $r['Date']; ?></td>
                    <td><?php echo htmlspecialchars($r['auditorName']); ?></td>
                    <td><?php echo htmlspecialchars($r['FindingsSummary']); ?></td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- PARTICIPANTS -->
    <h4 id="party" class="section-title">Participants</h4>
    <div class="mb-2">
      <a href="participants_list.php" class="btn btn-success btn-sm">Add / Edit Participants</a>
    </div>
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th><th>Name</th><th>Account Type</th><th>Email</th>
                <th>Role</th><th>Total Share</th><th>Current Price</th>
              </tr>
            </thead>
            <tbody>
            <?php while($r = mysqli_fetch_assoc($participant_res)): ?>
              <tr>
                <td><?php echo $r['ParticipantID']; ?></td>
                <td><?php echo htmlspecialchars($r['name']); ?></td>
                <td><?php echo htmlspecialchars($r['accountType']); ?></td>
                <td><?php echo htmlspecialchars($r['email']); ?></td>
                <td><?php echo $r['roleType']; ?></td>
                <td><?php echo $r['totalShare']; ?></td>
                <td><?php echo $r['currentPrice']; ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <footer class="text-muted small mt-4 mb-2">
      Financial Institution System – DBMS Project Dashboard
    </footer>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// data for Chart.js [web:86][web:89]
const txLabels   = <?php echo json_encode($tx_labels); ?>;
const txValues   = <?php echo json_encode($tx_values); ?>;
const phLabels   = <?php echo json_encode($ph_labels); ?>;
const phValues   = <?php echo json_encode($ph_values); ?>;
const roleLabels = <?php echo json_encode($role_labels); ?>;
const roleValues = <?php echo json_encode($role_values); ?>;

// bar: transactions per participant
new Chart(document.getElementById('txPerParticipant'), {
  type: 'bar',
  data: {
    labels: txLabels,
    datasets: [{
      label: 'Transactions',
      data: txValues,
      backgroundColor: 'rgba(54, 162, 235, 0.7)'
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { autoSkip: false } },
      y: { beginAtZero: true }
    }
  }
});

// line: price history
new Chart(document.getElementById('priceHistory'), {
  type: 'line',
  data: {
    labels: phLabels,
    datasets: [{
      label: 'Closing Price',
      data: phValues,
      borderColor: 'rgba(75, 192, 192, 1)',
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      tension: 0.2,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { maxTicksLimit: 6 } },
      y: { beginAtZero: false }
    }
  }
});

// pie: participant roles
new Chart(document.getElementById('participantRoles'), {
  type: 'pie',
  data: {
    labels: roleLabels,
    datasets: [{
      data: roleValues,
      backgroundColor: [
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(201, 203, 207, 0.7)'
      ]
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'bottom' } }
  }
});
</script>
</body>
</html>
