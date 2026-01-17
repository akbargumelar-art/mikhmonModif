<?php
/*
 *  Add/Edit Access Point - MikhMon
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  
  $apId = $_GET['id'];
  $isEdit = !empty($apId);
  
  // Get existing data for edit
  if ($isEdit) {
    $getAP = $API->comm("/system/script/print", array(
      "?.id" => $apId,
    ));
    if (count($getAP) > 0) {
      $apData = explode("-|-", $getAP[0]['name']);
      $apName = $apData[0];
      $apLocation = $apData[1];
      $apIP = $apData[2];
      $apMAC = $apData[3];
      $apModel = $apData[4];
      $apUsername = isset($apData[5]) ? $apData[5] : '';
      $apPassword = isset($apData[6]) ? decrypt($apData[6]) : '';
      $apNotes = $getAP[0]['source'];
    } else {
      echo "<script>alert('Access Point tidak ditemukan!'); window.location='./?hotspot=access-points&session=".$session."';</script>";
      exit;
    }
  } else {
    $apName = $apLocation = $apIP = $apMAC = $apModel = $apUsername = $apPassword = $apNotes = "";
  }
  
  if (isset($_POST['save'])) {
    $apName = $_POST['ap_name'];
    $apLocation = $_POST['ap_location'];
    $apIP = $_POST['ap_ip'];
    $apMAC = $_POST['ap_mac'];
    $apModel = $_POST['ap_model'];
    $apUsername = $_POST['ap_username'];
    $apPassword = $_POST['ap_password'];
    $apNotes = $_POST['ap_notes'];
    
    // Encrypt password
    $apPasswordEnc = encrypt($apPassword);
    
    // Format: Name-|-Location-|-IP-|-MAC-|-Model-|-Username-|-Password
    $scriptName = $apName."-|-".$apLocation."-|-".$apIP."-|-".$apMAC."-|-".$apModel."-|-".$apUsername."-|-".$apPasswordEnc;
    
    if ($isEdit) {
      // Update existing
      $API->comm("/system/script/set", array(
        ".id" => $apId,
        "name" => $scriptName,
        "source" => $apNotes,
        "owner" => "accesspoint",
      ));
      echo "<script>alert('Access Point berhasil diupdate!'); window.location='./?hotspot=access-points&session=".$session."';</script>";
    } else {
      // Add new
      $API->comm("/system/script/add", array(
        "name" => $scriptName,
        "source" => $apNotes,
        "owner" => "accesspoint",
      ));
      echo "<script>alert('Access Point berhasil ditambahkan!'); window.location='./?hotspot=access-points&session=".$session."';</script>";
    }
  }
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3>
          <i class="fa fa-wifi"></i> <?= $isEdit ? 'Edit' : 'Tambah' ?> Access Point
        </h3>
      </div>
      <div class="card-body">
        <form method="POST" action="">
          <table class="table">
            <tr>
              <td width="200">Nama Access Point</td>
              <td>
                <input type="text" name="ap_name" class="form-control" 
                       value="<?= $apName; ?>" required placeholder="Contoh: AP-Office-1">
              </td>
            </tr>
            <tr>
              <td>Lokasi</td>
              <td>
                <input type="text" name="ap_location" class="form-control" 
                       value="<?= $apLocation; ?>" required placeholder="Contoh: Lantai 1 - Ruang Meeting">
              </td>
            </tr>
            <tr>
              <td>IP Address</td>
              <td>
                <input type="text" name="ap_ip" class="form-control" 
                       value="<?= $apIP; ?>" required placeholder="192.168.1.100">
              </td>
            </tr>
            <tr>
              <td>MAC Address</td>
              <td>
                <input type="text" name="ap_mac" class="form-control" 
                       value="<?= $apMAC; ?>" placeholder="AA:BB:CC:DD:EE:FF">
              </td>
            </tr>
            <tr>
              <td>Merk/Model</td>
              <td>
                <input type="text" name="ap_model" class="form-control" 
                       value="<?= $apModel; ?>" placeholder="Contoh: TP-Link EAP225">
              </td>
            </tr>
            <tr>
              <td>Username (Opsional)</td>
              <td>
                <input type="text" name="ap_username" class="form-control" 
                       value="<?= $apUsername; ?>" placeholder="Username untuk login ke AP">
              </td>
            </tr>
            <tr>
              <td>Password (Opsional)</td>
              <td>
                <input type="password" name="ap_password" class="form-control" 
                       value="<?= $apPassword; ?>" placeholder="Password untuk login ke AP">
              </td>
            </tr>
            <tr>
              <td>Catatan</td>
              <td>
                <textarea name="ap_notes" class="form-control" rows="3" 
                          placeholder="Catatan tambahan tentang AP ini"><?= $apNotes; ?></textarea>
              </td>
            </tr>
            <tr>
              <td></td>
              <td>
                <button type="submit" name="save" class="btn bg-success">
                  <i class="fa fa-save"></i> Simpan
                </button>
                <a href="./?hotspot=access-points&session=<?= $session; ?>" class="btn bg-secondary">
                  <i class="fa fa-times"></i> Batal
                </a>
              </td>
            </tr>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>

<?php } ?>
