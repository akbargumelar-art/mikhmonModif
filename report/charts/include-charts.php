<?php
/*
 * Include file for selling report charts
 * Add this after card-body opening in selling.php
 */

// Only show charts if there is data
if (isset($TotalReg) && $TotalReg > 0) {
    // Process the data
    include('./report/charts/process-data.php');

    echo '
  <!-- Charts Section -->
  <div class="row" style="margin-bottom: 20px;">
    <div class="col-6">
      ';
    include('./report/charts/revenue-chart.php');
    echo '
    </div>
    <div class="col-6">
      ';
    include('./report/charts/product-chart.php');
    echo '
    </div>
  </div>
  <!-- End Charts Section -->
  ';
}
?>