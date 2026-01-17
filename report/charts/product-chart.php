<?php
/*
 * Product Composition Chart
 * Shows best-selling voucher profiles
 */
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fa fa-pie-chart"></i>
            <?= isset($_product_composition) ? $_product_composition : "Best Selling Products" ?>
        </h3>
    </div>
    <div class="card-body">
        <div id="productChart" style="height: 300px;"></div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // Product data from PHP
        var productNames = <?= json_encode($productNames) ?>;
        var productCounts = <?= json_encode($productCounts) ?>;
        var totalProducts = <?= $totalProducts ?>;

        // Prepare data for pie chart
        var chartData = [];
        var colors = ['#5d9cec', '#48cfad', '#ffce54', '#ed5565', '#ac92ec', '#4fc1e9', '#a0d468', '#fc6e51', '#ec87c0', '#656d78'];

        for (var i = 0; i < productNames.length; i++) {
            var percentage = (productCounts[i] / totalProducts * 100).toFixed(1);
            chartData.push({
                name: productNames[i],
                y: productCounts[i],
                percentage: percentage,
                color: colors[i % colors.length]
            });
        }

        Highcharts.chart('productChart', {
            chart: {
                type: 'pie',
                backgroundColor: 'transparent'
            },
            title: {
                text: null
            },
            tooltip: {
                formatter: function () {
                    return '<b>' + this.point.name + '</b><br/>' +
                        'Sold: ' + this.y + ' vouchers<br/>' +
                        'Percentage: ' + this.point.percentage + '%';
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b><br>{point.percentage}%',
                        style: {
                            fontSize: '12px'
                        }
                    },
                    showInLegend: false
                }
            },
            series: [{
                name: 'Products',
                data: chartData
            }],
            credits: {
                enabled: false
            }
        });
    });
</script>