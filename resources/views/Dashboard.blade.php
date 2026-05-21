@extends('layout.template')

@section('content')

<style type="text/css">
  .content-wrapper { background-color: #f4f6f9; }

  .dash-title { font-size: 1.6rem; font-weight: 700; color: #1f2d3d; margin: 0; }
  .dash-breadcrumb { font-size: .8rem; color: #8a95a5; }

  .btn-soft {
    background: #fff;
    border: 1px solid #e3e8ef;
    color: #4b5563;
    font-size: .82rem;
    font-weight: 600;
    border-radius: 8px;
  }
  .btn-soft:hover { background: #f3f4f6; color: #1f2d3d; }

  .stat-card {
    background: #fff;
    border: 1px solid #edf0f5;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 1px 2px rgba(16,24,40,.04);
    height: 100%;
  }
  .stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
  }
  .stat-label {
    font-size: .68rem; font-weight: 700; letter-spacing: .06em;
    color: #98a2b3; text-transform: uppercase;
  }
  .stat-value { font-size: 1.5rem; font-weight: 700; color: #1f2d3d; line-height: 1.2; }
  .stat-sub { font-size: .72rem; color: #98a2b3; }
  .trend-up { color: #16a34a; font-weight: 600; }
  .trend-down { color: #dc2626; font-weight: 600; }

  .bg-soft-teal { background: #e6f7f4; color: #14b8a6; }
  .bg-soft-green { background: #e7f6ec; color: #22c55e; }
  .bg-soft-amber { background: #fdf3e3; color: #f59e0b; }
  .bg-soft-rose { background: #fdecef; color: #ef4444; }

  .panel-card {
    background: #fff;
    border: 1px solid #edf0f5;
    border-radius: 14px;
    box-shadow: 0 1px 2px rgba(16,24,40,.04);
  }
  .panel-head {
    padding: 16px 20px;
    border-bottom: 1px solid #f0f2f6;
    display: flex; align-items: center; justify-content: space-between;
  }
  .panel-title { font-size: .98rem; font-weight: 700; color: #1f2d3d; margin: 0; }
  .legend-chip { font-size: .72rem; color: #6b7280; font-weight: 600; margin-left: 14px; }
  .legend-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: 5px; }

  .tbl-dash { width: 100%; }
  .tbl-dash thead th {
    font-size: .68rem; font-weight: 700; letter-spacing: .05em;
    color: #98a2b3; text-transform: uppercase;
    border: none; padding: 12px 20px;
  }
  .tbl-dash tbody td {
    padding: 14px 20px; font-size: .86rem; color: #344054;
    border-top: 1px solid #f3f4f6; vertical-align: middle;
  }
  .tbl-dash tbody tr:hover { background: #fafbfc; }
  .txn-id { font-weight: 700; color: #1f2d3d; }
  .status-pill {
    font-size: .66rem; font-weight: 700; letter-spacing: .04em;
    padding: 4px 10px; border-radius: 20px; text-transform: uppercase;
  }
  .status-completed { background: #e7f6ec; color: #16a34a; }
  .status-pending { background: #fdf3e3; color: #d97706; }
  .panel-foot { padding: 12px 20px; font-size: .78rem; color: #98a2b3; border-top: 1px solid #f0f2f6; }
  .link-blue { color: #2563eb; font-weight: 600; font-size: .8rem; text-decoration: none; }
  .link-blue:hover { text-decoration: underline; }
</style>

<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row align-items-center mb-2">
        <div class="col-sm-6">
          <div class="dash-breadcrumb">Home / Dashboard</div>
          <h1 class="dash-title mt-1">Dashboard Overview</h1>
        </div>
        <!-- <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
          <button type="button" class="btn btn-soft btn-sm px-3 py-2 mr-2">
            <i class="far fa-calendar-alt mr-1"></i> Last 30 Days
          </button>
          <button type="button" class="btn btn-primary btn-sm px-3 py-2" style="border-radius:8px;font-weight:600;">
            <i class="fas fa-download mr-1"></i> Export Report
          </button>
        </div> -->
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">

      <!-- Stat cards -->
      <div class="row">
        <div class="col-lg-3 col-6 mb-3">
          <div class="stat-card d-flex align-items-center">
            <div class="stat-icon bg-soft-teal mr-3"><i class="fas fa-wallet"></i></div>
            <div>
              <div class="stat-label">Sales Today</div>
              <div class="stat-value" id="stat-sales-value">-</div>
              <div class="stat-sub" id="stat-sales-sub">&nbsp;</div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
          <div class="stat-card d-flex align-items-center">
            <div class="stat-icon bg-soft-green mr-3"><i class="fas fa-money-bill-wave"></i></div>
            <div>
              <div class="stat-label">MTD Revenue</div>
              <div class="stat-value" id="stat-mtd-value">-</div>
              <div class="stat-sub" id="stat-mtd-sub">&nbsp;</div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
          <div class="stat-card d-flex align-items-center">
            <div class="stat-icon bg-soft-amber mr-3"><i class="fas fa-shopping-cart"></i></div>
            <div>
              <div class="stat-label">Orders</div>
              <div class="stat-value" id="stat-orders-value">-</div>
              <div class="stat-sub" id="stat-orders-sub">&nbsp;</div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
          <div class="stat-card d-flex align-items-center">
            <div class="stat-icon bg-soft-rose mr-3"><i class="fas fa-chart-line"></i></div>
            <div>
              <div class="stat-label">Profit</div>
              <div class="stat-value" id="stat-profit-value">-</div>
              <!-- <div class="stat-sub" id="stat-profit-sub">&nbsp;</div> -->
            </div>
          </div>
        </div>
      </div>

      <!-- Income & Cost Trend chart -->
      <div class="row">
        <div class="col-12 mb-3">
          <div class="panel-card">
            <div class="panel-head">
              <h3 class="panel-title">7-Day Income &amp; Cost Trend</h3>
              <div>
                <span class="legend-chip"><span class="legend-dot" style="background:#22c55e;"></span>Income</span>
                <span class="legend-chip"><span class="legend-dot" style="background:#ef4444;"></span>Cost</span>
                <span class="legend-chip"><span class="legend-dot" style="background:#2563eb;"></span>Profit</span>
              </div>
            </div>
            <div class="card-body" style="position:relative;height:300px;">
              <canvas id="trendChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Sales Transactions -->
      <div class="row">
        <div class="col-12">
          <div class="panel-card">
            <div class="panel-head">
              <h3 class="panel-title">Recent Sales Transactions</h3>
              <a href="{{ url('penjualan') }}" class="link-blue">View All Sales</a>
            </div>
            <div class="table-responsive">
              <table class="tbl-dash">
                <thead>
                  <tr>
                    <th>Transaction ID</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($recentSales as $sale)
                    @php
                      $st = strtolower(trim($sale->status));
                      $stClass = in_array($st, ['tunai','lunas','completed','selesai']) ? 'status-completed' : 'status-pending';
                    @endphp
                    <tr>
                      <td class="txn-id">#{{ $sale->no_transaksi }}</td>
                      <td>{{ \Carbon\Carbon::parse($sale->tanggal)->format('d M Y H:i') }}</td>
                      <td>{{ $sale->customer }}</td>
                      <td>{{ (int) $sale->items }} {{ (int) $sale->items > 1 ? 'items' : 'item' }}</td>
                      <td>IDR {{ number_format((float) $sale->amount, 0, ',', '.') }}</td>
                      <td><span class="status-pill {{ $stClass }}">{{ $sale->status }}</span></td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-muted" style="padding:24px;">Belum ada transaksi penjualan</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <div class="panel-foot">Menampilkan {{ count($recentSales) }} transaksi terbaru</div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

@endsection


@section('scripts')
<script id="trend-data" type="application/json">{!! json_encode(['labels' => $trendLabels, 'income' => $trendIncome, 'cost' => $trendCost, 'profit' => $trendProfit]) !!}</script>
<script type="text/javascript">
  function formatIDR(n) {
    n = Number(n) || 0;
    return 'IDR ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
  }
  function formatPct(n, digits) {
    if (n === null || n === undefined || isNaN(n)) return '0%';
    return Number(n).toFixed(digits == null ? 2 : digits) + '%';
  }
  function trendSpan(value, suffix) {
    var n = Number(value) || 0;
    var cls = n >= 0 ? 'trend-up' : 'trend-down';
    var icon = n >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
    var sign = n > 0 ? '+' : '';
    return '<span class="' + cls + '"><i class="fas ' + icon + '"></i> '
         + sign + formatPct(n) + '</span> ' + (suffix || '');
  }

  $(function () {
    $.getJSON("{{ url('/api/dashboard-stats') }}", function (res) {
      // 1. Sales Today
      if (res.sales_today) {
        $('#stat-sales-value').text(formatIDR(res.sales_today.nominal));
        $('#stat-sales-sub').html(trendSpan(res.sales_today.changingPercentage, 'from yesterday'));
      }
      // 2. MTD Revenue
      if (res.mtd_revenue) {
        $('#stat-mtd-value').text(formatIDR(res.mtd_revenue.ActualSalesMTD));
        var ach = Number(res.mtd_revenue.Achievement) || 0;
        var plan = formatIDR(res.mtd_revenue.PlanSalesMTD);
        var cls = ach >= 100 ? 'trend-up' : (ach >= 80 ? 'trend-up' : 'trend-down');
        var icon = ach >= 80 ? 'fa-arrow-up' : 'fa-arrow-down';
        $('#stat-mtd-sub').html(
          '<span class="' + cls + '"><i class="fas ' + icon + '"></i> ' + formatPct(ach) + '</span> of ' + plan
        );
      }
      // 3. Orders
      if (res.orders) {
        $('#stat-orders-value').text(Number(res.orders.jumlahTransaksi || 0).toLocaleString('id-ID'));
        $('#stat-orders-sub').text('Avg ' + formatIDR(res.orders.AvgPerTransaction) + ' / order');
      }
      // 4. Profit
      if (res.profit) {
        var selisih = Number(res.profit.selisih) || 0;
        $('#stat-profit-value').text(formatIDR(selisih));
        var income = formatIDR(res.profit.income);
        var cost   = formatIDR(res.profit.cost);
        var cls2  = selisih >= 0 ? 'trend-up' : 'trend-down';
        var icon2 = selisih >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
        $('#stat-profit-sub').html(
          '<span class="' + cls2 + '"><i class="fas ' + icon2 + '"></i> ' + income + '</span> - ' + cost
        );
      }
    });
  });

  $(function () {
    var ctx = document.getElementById('trendChart').getContext('2d');

    var trendData = JSON.parse(document.getElementById('trend-data').textContent);
    var labels  = trendData.labels;
    var income  = trendData.income;
    var cost    = trendData.cost;
    var profit  = trendData.profit;

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Income',
            type: 'bar',
            data: income,
            backgroundColor: '#22c55e',
            barPercentage: 0.6,
            categoryPercentage: 0.6,
            order: 2
          },
          {
            label: 'Cost',
            type: 'bar',
            data: cost,
            backgroundColor: '#ef4444',
            barPercentage: 0.6,
            categoryPercentage: 0.6,
            order: 2
          },
          {
            label: 'Profit',
            type: 'line',
            data: profit,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.08)',
            borderWidth: 3,
            pointBackgroundColor: '#2563eb',
            pointRadius: 4,
            fill: false,
            lineTension: 0.3,
            order: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false },
        tooltips: {
          mode: 'index',
          intersect: false,
          callbacks: {
            label: function (item, data) {
              var name = data.datasets[item.datasetIndex].label;
              return name + ': IDR ' + Number(item.yLabel).toLocaleString('id-ID');
            }
          }
        },
        scales: {
          xAxes: [{
            gridLines: { display: false },
            ticks: { fontColor: '#98a2b3', fontSize: 11 }
          }],
          yAxes: [{
            gridLines: { color: '#f0f2f6', drawBorder: false },
            ticks: {
              fontColor: '#98a2b3',
              fontSize: 11,
              beginAtZero: true,
              callback: function (value) {
                return 'IDR ' + (value / 1000000) + 'm';
              }
            }
          }]
        }
      }
    });
  });
</script>
@endsection
