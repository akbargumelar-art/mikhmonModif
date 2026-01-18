<?php
/*
 * AP User Summary Component
 * Shows active users per Access Point
 */

// Only show if API is connected
if (!isset($API) || !$API) {
    return;
}

// Get all active users
try {
    $getActiveUsers = $API->comm("/ip/hotspot/active/print");
    if (!is_array($getActiveUsers)) {
        $getActiveUsers = array();
    }
} catch (Exception $e) {
    $getActiveUsers = array();
}

// Group users by interface (AP)
$apUserCount = array();
foreach ($getActiveUsers as $user) {
    if (isset($user['interface'])) {
        $interface = $user['interface'];
        if (!isset($apUserCount[$interface])) {
            $apUserCount[$interface] = 0;
        }
        $apUserCount[$interface]++;
    }
}

// Get interface details to get AP names from comments
try {
    $getInterfaces = $API->comm("/interface/print");
    if (!is_array($getInterfaces)) {
        $getInterfaces = array();
    }
} catch (Exception $e) {
    $getInterfaces = array();
}

$apNames = array();
foreach ($getInterfaces as $iface) {
    if (isset($iface['name'])) {
        $ifaceName = $iface['name'];
        $apNames[$ifaceName] = isset($iface['comment']) && $iface['comment'] != ""
            ? $iface['comment']
            : $ifaceName;
    }
}
?>

<!-- AP User Summary Card -->
<?php if (!empty($apUserCount)) { ?>
    <div class="card">
        <div class="card-header">
            <h3><i class="fa fa-wifi"></i> User Active per Access Point</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                // Display AP cards
                $colors = array('blue', 'green', 'yellow', 'red', 'purple', 'orange');
                $colorIndex = 0;

                foreach ($apUserCount as $interface => $count) {
                    $apName = isset($apNames[$interface]) ? $apNames[$interface] : $interface;
                    $color = $colors[$colorIndex % count($colors)];
                    $colorIndex++;

                    echo '
        <div class="col-3 col-box-6">
          <div class="box bg-' . $color . ' bmh-75">
            <div>
              <h1>' . $count . '
                <span style="font-size: 15px;">' . ($count == 1 ? "user" : "users") . '</span>
              </h1>
            </div>
            <div>
              <i class="fa fa-wifi"></i> ' . $apName . '
            </div>
          </div>
        </div>';
                }
                ?>
            </div>
        </div>
    </div>
<?php } ?>
<!-- End AP User Summary -->