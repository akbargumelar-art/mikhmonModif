<?php
/*
 *  Remove Access Point - MikhMon
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
} else {

    $apId = $_GET['remove-access-point'];

    if (!empty($apId)) {
        // Get AP name for display
        $getAP = $API->comm("/system/script/print", array(
            "?.id" => $apId,
        ));

        if (count($getAP) > 0) {
            $apData = explode("-|-", $getAP[0]['name']);
            $apName = $apData[0];

            // Remove the access point
            $API->comm("/system/script/remove", array(
                ".id" => $apId,
            ));

            echo "<script>alert('Access Point " . $apName . " berhasil dihapus!'); window.location='./?hotspot=access-points&session=" . $session . "';</script>";
        } else {
            echo "<script>alert('Access Point tidak ditemukan!'); window.location='./?hotspot=access-points&session=" . $session . "';</script>";
        }
    } else {
        echo "<script>window.location='./?hotspot=access-points&session=" . $session . "';</script>";
    }
}
?>