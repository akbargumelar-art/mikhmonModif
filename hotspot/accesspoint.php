<?php
/*
 *  Access Point Management - MikhMon
 *  List and manage all access points
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
} else {

    // Get all access points from MikroTik scripts
    $getAP = $API->comm("/system/script/print", array(
        "?owner" => "accesspoint",
    ));
    $TotalAP = count($getAP);
    ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fa fa-wifi"></i> Data Access Point
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="input-group-4">
                                <input class="group-item group-item-l" style="padding-top:6px;"
                                    placeholder="<?= $_search ?>" type="text" id="filterTable">
                            </div>
                        </div>
                        <div class="col-6 text-right">
                            <a href="./?hotspot=add-access-point&session=<?= $session; ?>" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Tambah Access Point
                            </a>
                        </div>
                    </div>

                    <div class="overflow">
                        <table id="dataTable" class="table table-bordered table-hover text-nowrap" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama AP</th>
                                    <th>Lokasi</th>
                                    <th>IP Address</th>
                                    <th>MAC Address</th>
                                    <th>Merk/Model</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($TotalAP > 0) {
                                    $no = 1;
                                    for ($i = 0; $i < $TotalAP; $i++) {
                                        $apData = explode("-|-", $getAP[$i]['name']);
                                        $apId = $getAP[$i]['.id'];
                                        $apName = $apData[0];
                                        $apLocation = $apData[1];
                                        $apIP = $apData[2];
                                        $apMAC = $apData[3];
                                        $apModel = $apData[4];
                                        $apNotes = isset($apData[5]) ? $apData[5] : '';

                                        // Check AP status with ping
                                        $pingResult = $API->comm("/ping", array(
                                            "address" => $apIP,
                                            "count" => "2"
                                        ));

                                        $status = "Offline";
                                        $statusClass = "bg-danger";
                                        if (isset($pingResult[0]['received']) && $pingResult[0]['received'] > 0) {
                                            $status = "Online";
                                            $statusClass = "bg-success";
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $no++; ?></td>
                                            <td><strong><?= $apName; ?></strong></td>
                                            <td><?= $apLocation; ?></td>
                                            <td><code><?= $apIP; ?></code></td>
                                            <td><code><?= $apMAC; ?></code></td>
                                            <td><?= $apModel; ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $statusClass; ?>"
                                                    style="padding:5px 10px; border-radius:15px;">
                                                    <?= $status; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="./?hotspot=edit-access-point&id=<?= $apId; ?>&session=<?= $session; ?>"
                                                    class="btn btn-sm bg-info" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="./?remove-access-point=<?= $apId; ?>&session=<?= $session; ?>"
                                                    class="btn btn-sm bg-danger"
                                                    onclick="return confirm('Hapus Access Point <?= $apName; ?>?')" title="Hapus">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <i>Belum ada data access point. Silakan tambahkan access point baru.</i>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <p class="text-center">
                                Total Access Point: <strong><?= $TotalAP; ?></strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .badge {
            color: #fff;
            font-weight: 600;
            display: inline-block;
        }

        code {
            background: rgba(102, 126, 234, 0.1);
            padding: 3px 8px;
            border-radius: 4px;
            color: #667eea;
            font-family: 'Courier New', monospace;
        }
    </style>

<?php } ?>