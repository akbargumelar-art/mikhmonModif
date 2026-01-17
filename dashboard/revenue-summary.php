<?php
/*
 * Dashboard Revenue Summary
 * Mini charts for dashboard
 */

// Process only if we have data
if (isset($API) && $API) {
    // Get last 7 days of data
    $getData = $API->comm("/system/script/print", array(
        "?comment" => "mikhmon",
    ));

    $TotalReg = count($getData);

    // Aggregate last 7 days revenue
    $last7Days = array();
    $topProducts = array();

    $today = new DateTime();
    for ($i = 6; $i >= 0; $i--) {
        $date = clone $today;
        $date->sub(new DateInterval("P{$i}D"));
        $dateStr = $date->format('m/d/Y');
        $last7Days[$dateStr] = 0;
    }

    for ($i = 0; $i < $TotalReg; $i++) {
        $getname = explode("-|-", $getData[$i]['name']);
        $date = $getname[0];
        $price = floatval($getname[3]);
        $profile = isset($getname[7]) ? $getname[7] : 'Unknown';

        // Check if in last 7 days
        if (isset($last7Days[$date])) {
            $last7Days[$date] += $price;
        }

        // Count products (all time top 5)
        if (!isset($topProducts[$profile])) {
            $topProducts[$profile] = 0;
        }
        $topProducts[$profile]++;
    }

    // Sort and get top 5
    arsort($topProducts);
    $topProducts = array_slice($topProducts, 0, 5, true);

    // Calculate totals
    $totalRevenue7Days = array_sum($last7Days);
    $totalProducts = array_sum($topProducts);
}
?>

<!-- Revenue Summary Card -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-money"></i> Revenue (Last 7 Days)</h3>
    </div>
    <div class="card-body">
        <div style="text-align: center; padding: 20px;">
            <h2 style="color: #5d9cec; margin: 0;">
                <?= $currency ?>
                <?php
                if (isset($totalRevenue7Days)) {
                    if (in_array($currency, $cekindo['indo'])) {
                        echo number_format($totalRevenue7Days, 0, '', '.');
                    } else {
                        echo number_format($totalRevenue7Days, 2, '.', ',');
                    }
                } else {
                    echo "0";
                }
                ?>
            </h2>
            <p style="color: #7f8c9a; margin: 5px 0 0 0;">Total Sales</p>
        </div>
        <div id="miniRevenueChart" style="height: 150px;"></div>
    </div>
</div>

<!-- Top Products Card -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-trophy"></i> Top 5 Products</h3>
    </div>
    <div class="card-body">
        <div class="overflow" style="max-height: 250px;">
            <table class="table table-sm">
                <tbody>
                    <?php
                    if (isset($topProducts) && !empty($topProducts)) {
                        $rank = 1;
                        foreach ($topProducts as $product => $count) {
                            $percentage = ($totalProducts > 0) ? round(($count / $totalProducts) * 100, 1) : 0;

                            // Medal icons
                            $medal = '';
                            if ($rank == 1)
                                $medal = '🥇';
                            elseif ($rank == 2)
                                $medal = '🥈';
                            elseif ($rank == 3)
                                $medal = '🥉';
                            else
                                $medal = $rank . '.';

                            echo '<tr>
                <td width="30">' . $medal . '</td>
                <td><b>' . $product . '</b></td>
                <td width="60" class="text-right">' . $count . ' sold</td>
                <td width="50" class="text-right"><span style="color: #5d9cec">' . $percentage . '%</span></td>
              </tr>';
                            $rank++;
                        }
                    } else {
                        echo '<tr><td colspan="4" class="text-center">No data available</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
  <?php if (isset($last7Days)) { ?>
      // Mini revenue chart
      var dates = <?= json_encode(array_keys($last7Days)) ?>;
                var amounts = <?= json_encode(array_values($last7Days)) ?>;

                var chartData = [];
                for (var i = 0; i < dates.length; i++) {
                    var dateParts = dates[i].split('/');
                    var dateObj = new Date(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
                    chartData.push([dateObj.getTime(), parseFloat(amounts[i])]);
                }

                Highcharts.chart('miniRevenueChart', {
                    chart: {
                        type: 'area',
                        backgroundColor: 'transparent',
                        height: 150
                    },
                    title: { text: null },
                    xAxis: {
                        type: 'datetime',
                        labels: { enabled: false },
                        gridLineWidth: 0
                    },
                    yAxis: {
                        title: { text: null },
                        labels: { enabled: false },
                        gridLineWidth: 0
                    },
                    tooltip: {
                        formatter: function () {
                            return '<b>' + Highcharts.dateFormat('%e %b', this.x) + '</b><br/>' +
                                '<?= $currency ?> ' + Highcharts.numberFormat(this.y, 0, '', '.');
                        }
                    },
                    legend: { enabled: false },
                    series: [{
                        data: chartData,
                        color: '#5d9cec',
                        fillColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, 'rgba(93, 156, 236, 0.3)'],
                                [1, 'rgba(93, 156, 236, 0.05)']
                            ]
                        },
                        lineWidth: 2,
                        marker: { enabled: false }
                    }],
                    credits: { enabled: false }
                });
  <?php } ?>
});
</script>