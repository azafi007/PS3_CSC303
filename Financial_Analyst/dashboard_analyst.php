<?php include 'config.php'; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>FIS Admin – Financial Analyst</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">FIS Admin</a>
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <li class="nav-item"><a class="nav-link active" href="#">Stock &amp; Market</a></li>
    </ul>
    <span class="navbar-text text-white">Logged in as analyst</span>
  </div>
</nav>

<div class="container-fluid mt-4">
  <h2 class="mb-3">Stock &amp; Market View</h2>

  <ul class="nav nav-tabs" id="analystTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab"
              data-bs-target="#stocks" type="button">
        Stocks
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab"
              data-bs-target="#history" type="button">
        Price History
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab"
              data-bs-target="#pred" type="button">
        Predictions
      </button>
    </li>
  </ul>

  <div class="tab-content mt-3">
    <div class="tab-pane fade show active" id="stocks">
      <?php include __DIR__ . '/stock_list.php'; ?>
    </div>
    <div class="tab-pane fade" id="history">
      <?php include __DIR__ . '/price_list.php'; ?>
    </div>
    <div class="tab-pane fade" id="pred">
      <?php include __DIR__ . '/prediction_list.php'; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// draw charts when DOM ready
document.addEventListener('DOMContentLoaded', function () {

  // ---- Price history chart ----
  if (typeof window.priceChartLabels !== 'undefined') {
    const ctx1 = document.getElementById('priceChart').getContext('2d');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: window.priceChartLabels,
        datasets: [{
          label: 'Closing Price',
          data: window.priceChartValues,
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true }
        },
        scales: {
          x: { title: { display: true, text: 'Time' } },
          y: { title: { display: true, text: 'Price' } }
        }
      }
    });
  }

  // ---- Prediction chart ----
  if (typeof window.predChartLabels !== 'undefined') {
    const ctx2 = document.getElementById('predChart').getContext('2d');
    new Chart(ctx2, {
      type: 'line',
      data: {
        labels: window.predChartLabels,
        datasets: [{
          label: 'Predicted Price',
          data: window.predChartValues,
          borderColor: 'rgba(255, 99, 132, 1)',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true }
        },
        scales: {
          x: { title: { display: true, text: 'Target Date' } },
          y: { title: { display: true, text: 'Price' } }
        }
      }
    });
  }
});
</script>
</body>
</html>
