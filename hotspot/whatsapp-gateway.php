<?php
/*
 *  WhatsApp Gateway Configuration - MikhMon
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
} else {

    // Get WhatsApp Gateway settings from MikroTik script
    $getWAConfig = $API->comm("/system/script/print", array(
        "?name" => "wa-config",
    ));

    if (count($getWAConfig) > 0) {
        $waSettings = json_decode($getWAConfig[0]['source'], true);
        $waApiUrl = isset($waSettings['api_url']) ? $waSettings['api_url'] : '';
        $waApiKey = isset($waSettings['api_key']) ? $waSettings['api_key'] : '';
        $waEnabled = isset($waSettings['enabled']) ? $waSettings['enabled'] : false;
    } else {
        $waApiUrl = $waApiKey = '';
        $waEnabled = false;
    }

    // Save configuration
    if (isset($_POST['save_config'])) {
        $waApiUrl = $_POST['wa_api_url'];
        $waApiKey = $_POST['wa_api_key'];
        $waEnabled = isset($_POST['wa_enabled']) ? true : false;

        $config = array(
            'api_url' => $waApiUrl,
            'api_key' => encrypt($waApiKey),
            'enabled' => $waEnabled
        );

        $configJson = json_encode($config);

        if (count($getWAConfig) > 0) {
            // Update existing
            $API->comm("/system/script/set", array(
                ".id" => $getWAConfig[0]['.id'],
                "source" => $configJson,
            ));
        } else {
            // Create new
            $API->comm("/system/script/add", array(
                "name" => "wa-config",
                "source" => $configJson,
                "owner" => "whatsapp",
            ));
        }

        echo "<script>alert('Konfigurasi WhatsApp berhasil disimpan!'); window.location='./?hotspot=whatsapp-gateway&session=" . $session . "';</script>";
    }

    // Test connection
    if (isset($_POST['test_connection'])) {
        $testUrl = $_POST['wa_api_url'];
        $testKey = $_POST['wa_api_key'];

        // Simple test by sending a request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $testKey
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 || $httpCode == 201) {
            $testResult = "<div class='alert bg-success'>✓ Koneksi berhasil! API WhatsApp dapat dijangkau.</div>";
        } else {
            $testResult = "<div class='alert bg-danger'>✗ Koneksi gagal! Periksa URL dan API Key Anda. (HTTP Code: " . $httpCode . ")</div>";
        }
    }

    // Send test message
    if (isset($_POST['send_test'])) {
        $testPhone = $_POST['test_phone'];
        $testMessage = $_POST['test_message'];

        include_once('../lib/wa-api.php');
        $waApi = new WhatsAppAPI($waApiUrl, decrypt($waApiKey));
        $sent = $waApi->sendMessage($testPhone, $testMessage);

        if ($sent) {
            $sendResult = "<div class='alert bg-success'>✓ Pesan berhasil dikirim ke " . $testPhone . "</div>";
        } else {
            $sendResult = "<div class='alert bg-danger'>✗ Gagal mengirim pesan. Periksa konfigurasi Anda.</div>";
        }
    }
    ?>

    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa fa-whatsapp"></i> Konfigurasi WhatsApp Gateway</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($testResult))
                        echo $testResult; ?>
                    <?php if (isset($sendResult))
                        echo $sendResult; ?>

                    <form method="POST" action="">
                        <table class="table">
                            <tr>
                                <td width="200"><strong>Status</strong></td>
                                <td>
                                    <label>
                                        <input type="checkbox" name="wa_enabled" <?= $waEnabled ? 'checked' : ''; ?>>
                                        Aktifkan WhatsApp Gateway
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>API URL</strong></td>
                                <td>
                                    <input type="text" name="wa_api_url" class="form-control" value="<?= $waApiUrl; ?>"
                                        required placeholder="https://api.fonnte.com/send atau endpoint API Anda">
                                    <small style="opacity:0.7;">Contoh: Fonnte, Wablas, atau WhatsApp Business API</small>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>API Key / Token</strong></td>
                                <td>
                                    <input type="password" name="wa_api_key" class="form-control"
                                        value="<?= !empty($waApiKey) ? decrypt($waApiKey) : ''; ?>"
                                        placeholder="Token API Anda">
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button type="submit" name="save_config" class="btn bg-success">
                                        <i class="fa fa-save"></i> Simpan Konfigurasi
                                    </button>
                                    <button type="submit" name="test_connection" class="btn bg-info">
                                        <i class="fa fa-plug"></i> Test Koneksi
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fa fa-paper-plane"></i> Kirim Pesan Test</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <table class="table">
                            <tr>
                                <td width="200"><strong>Nomor Tujuan</strong></td>
                                <td>
                                    <input type="text" name="test_phone" class="form-control" placeholder="628123456789"
                                        required>
                                    <small style="opacity:0.7;">Format: 628XXXXXXXXX (gunakan kode negara tanpa +)</small>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Pesan</strong></td>
                                <td>
                                    <textarea name="test_message" class="form-control" rows="4"
                                        required>Halo! Ini adalah pesan test dari MikhMon WhatsApp Gateway.</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button type="submit" name="send_test" class="btn bg-primary">
                                        <i class="fa fa-send"></i> Kirim Pesan Test
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa fa-info-circle"></i> Template Variabel</h3>
                </div>
                <div class="card-body">
                    <p>Gunakan variabel berikut dalam template pesan:</p>
                    <ul style="font-size:13px; line-height:1.8;">
                        <li><code>{username}</code> - Username voucher</li>
                        <li><code>{password}</code> - Password voucher</li>
                        <li><code>{profile}</code> - Nama profile</li>
                        <li><code>{timelimit}</code> - Batas waktu</li>
                        <li><code>{validity}</code> - Masa berlaku</li>
                        <li><code>{price}</code> - Harga voucher</li>
                        <li><code>{date}</code> - Tanggal sekarang</li>
                    </ul>

                    <hr>

                    <p><strong>Contoh Template:</strong></p>
                    <div style="background:rgba(0,0,0,0.2); padding:10px; border-radius:5px; font-size:12px;">
                        Voucher WiFi Anda:<br>
                        Username: {username}<br>
                        Password: {password}<br>
                        Paket: {profile}<br>
                        Berlaku: {validity}<br><br>
                        Terima kasih!
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fa fa-gear"></i> Fitur Otomatis</h3>
                </div>
                <div class="card-body" style="font-size:13px;">
                    <p>Notifikasi otomatis tersedia untuk:</p>
                    <ul>
                        <li>✓ Voucher baru dibuat</li>
                        <li>✓ User login</li>
                        <li>✓ User logout</li>
                        <li>✓ Voucher akan expired (24 jam sebelumnya)</li>
                    </ul>
                    <p><small>*Fitur ini akan aktif jika WhatsApp Gateway diaktifkan</small></p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: 500;
        }

        code {
            background: rgba(102, 126, 234, 0.15);
            padding: 2px 6px;
            border-radius: 3px;
            color: #667eea;
            font-size: 12px;
        }
    </style>

<?php } ?>