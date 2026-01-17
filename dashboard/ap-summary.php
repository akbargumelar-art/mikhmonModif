\u003c?php
/*
* AP User Summary Component
* Shows active users per Access Point
*/

// Get all active users
$getActiveUsers = $API-\u003ecomm("/ip/hotspot/active/print");

// Group users by interface (AP)
$apUserCount = array();
foreach ($getActiveUsers as $user) {
$interface = $user['interface'];
if (!isset($apUserCount[$interface])) {
$apUserCount[$interface] = 0;
}
$apUserCount[$interface]++;
}

// Get interface details to get AP names from comments
$getInterfaces = $API-\u003ecomm("/interface/print");
$apNames = array();
foreach ($getInterfaces as $iface) {
$ifaceName = $iface['name'];
$apNames[$ifaceName] = isset($iface['comment']) && $iface['comment'] != ""
? $iface['comment']
: $ifaceName;
}
?\u003e

\u003c!-- AP User Summary Card --\u003e
\u003cdiv class="card"\u003e
\u003cdiv class="card-header"\u003e\u003ch3\u003e\u003ci class="fa fa-wifi"\u003e\u003c/i\u003e \u003c?=
isset($_ap_users_summary) ? $_ap_users_summary : "User Active per Access Point" ?\u003e\u003c/h3\u003e\u003c/div\u003e
\u003cdiv class="card-body"\u003e
\u003cdiv class="row"\u003e
\u003c?php
// Display AP cards
$colors = array('blue', 'green', 'yellow', 'red', 'purple', 'orange');
$colorIndex = 0;

if (empty($apUserCount)) {
echo '\u003cdiv class="col-12 text-center"\u003e\u003cp\u003eNo active users on any Access
Point\u003c/p\u003e\u003c/div\u003e';
} else {
foreach ($apUserCount as $interface =\u003e $count) {
$apName = isset($apNames[$interface]) ? $apNames[$interface] : $interface;
$color = $colors[$colorIndex % count($colors)];
$colorIndex++;

echo '
\u003cdiv class="col-3 col-box-6"\u003e
\u003cdiv class="box bg-'.$color.' bmh-75"\u003e
\u003cdiv\u003e
\u003ch1\u003e'.$count.'
\u003cspan style="font-size: 15px;"\u003e'.($count == 1 ? "user" : "users").'\u003c/span\u003e
\u003c/h1\u003e
\u003c/div\u003e
\u003cdiv\u003e
\u003ci class="fa fa-wifi"\u003e\u003c/i\u003e '.$apName.'
\u003c/div\u003e
\u003c/div\u003e
\u003c/div\u003e';
}
}
?\u003e
\u003c/div\u003e
\u003c/div\u003e
\u003c/div\u003e
\u003c!-- End AP User Summary --\u003e