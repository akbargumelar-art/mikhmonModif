<?php
/*
 * Revenue Chart Component
 * Displays daily/monthly revenue trends
 */
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-line-chart"></i>
            <?= isset($_revenue_trend) ? $_revenue_trend : "Revenue Trend" ?>
        </h3>
    </div>
    <div class="card-body">
        <div id="revenueChart" style="height: 300px;"></div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // Revenue data from PHP
        var revenueDates = <?= json_encode($revenueDates) ?>;
        var revenueAmounts = <?= json_encode($revenueAmounts) ?>;

        // Convert dates to timestamps
        var chartData = [];
        for (var i = 0; i < revenueDates.length; i++) {
            var dateParts = revenueDates[i].split('/');
            var dateObj = new Date(dateParts[2], parseInt(dateParts[0]) - 1, dateParts[1]);
            chartData.push([dateObj.getTime(), parseFloat(revenueAmounts[i])]);
        }

        Highcharts.chart('revenueChart', {
            chart: {
                type: 'areaspline',
                backgroundColor: 'transparent'
            },
            title: {
                text: null
            },
            xAxis: {
                type: 'datetime',
                dateTimeLabelFormats: {
                    day: '%e %b',
                    month: '%b \'%y'
                }
            },
            yAxis: {
                title: {
                    text: 'Revenue (<?= $currency ?>)'
                },
                labels: {
                    formatter: function () {
          <?php if (in_array($currency, $cekindo['indo'])) { ?>
                return '<?= $currency ?> ' + Highcharts.numberFormat(this.value, 0, '', '.');
          <?php } else { ?>
                return '<?= $currency ?> ' + Highcharts.numberFormat(this.value, 2, '.', ',');
          <?php } ?>
        }
                }
            },
            tooltip: {
                formatter: function () {
        <?php if (in_array($currency, $cekindo['indo'])) { ?>
              return '<b>' + Highcharts.dateFormat('%e %b %Y', this.x) + '</b><br/>' +
                                '<?= $currency ?> ' + Highcharts.numberFormat(this.y, 0, '', '.');
        <?php } else { ?>
              return '<b>' + Highcharts.dateFormat('%e %b %Y', this.x) + '</b><br/>' +
                                '<?= $currency ?> ' + Highcharts.numberFormat(this.y, 2, '.', ',');
        <?php } ?>
      }
            },
            series: [{
                name: 'Revenue',
                data: chartData,
                color: '#5d9cec',
                fillColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, 'rgba(93, 156, 236, 0.5)'],
                        [1, 'rgba(93, 156, 236, 0.1)']
                    ]
                },
                marker: {
                    radius: 4
                }
            }],
            credits: {
                enabled: false
            }
        });
    });
</script>