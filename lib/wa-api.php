<?php
/*
 *  WhatsApp API Library - MikhMon
 *  Universal WhatsApp API wrapper for various services
 */

class WhatsAppAPI
{
    private $apiUrl;
    private $apiKey;

    public function __construct($apiUrl, $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * Send WhatsApp message
     * @param string $phone Phone number with country code (628xxx)
     * @param string $message Message text
     * @return bool Success status
     */
    public function sendMessage($phone, $message)
    {
        // Common format for most Indonesian WA Gateway services
        $data = array(
            'target' => $phone,
            'message' => $message,
            'phone' => $phone, // Alternative parameter name
            'text' => $message, // Alternative parameter name
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->apiKey,
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Consider success if HTTP 200 or 201
        return ($httpCode == 200 || $httpCode == 201);
    }

    /**
     * Send voucher notification
     * @param string $phone Phone number
     * @param array $voucherData Voucher details
     * @return bool Success status
     */
    public function sendVoucherNotification($phone, $voucherData)
    {
        $template = "🎫 *Voucher WiFi Anda*\n\n";
        $template .= "Username: *{username}*\n";
        $template .= "Password: *{password}*\n";
        $template .= "Paket: *{profile}*\n";

        if (!empty($voucherData['timelimit'])) {
            $template .= "Durasi: {timelimit}\n";
        }

        if (!empty($voucherData['validity'])) {
            $template .= "Berlaku: {validity}\n";
        }

        if (!empty($voucherData['price'])) {
            $template .= "Harga: Rp {price}\n";
        }

        $template .= "\nTerima kasih! 🙏";

        $message = $this->replaceVariables($template, $voucherData);
        return $this->sendMessage($phone, $message);
    }

    /**
     * Send login notification
     * @param string $phone Phone number
     * @param string $username Username that logged in
     * @return bool Success status
     */
    public function sendLoginNotification($phone, $username)
    {
        $message = "✅ *Login Berhasil*\n\n";
        $message .= "User: *" . $username . "*\n";
        $message .= "Waktu: " . date('d/m/Y H:i:s') . "\n\n";
        $message .= "Selamat menikmati layanan internet!";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send logout notification
     * @param string $phone Phone number
     * @param string $username Username that logged out
     * @param string $duration Session duration
     * @return bool Success status
     */
    public function sendLogoutNotification($phone, $username, $duration = '')
    {
        $message = "🔴 *Logout*\n\n";
        $message .= "User: *" . $username . "*\n";

        if (!empty($duration)) {
            $message .= "Durasi Sesi: " . $duration . "\n";
        }

        $message .= "Waktu: " . date('d/m/Y H:i:s') . "\n\n";
        $message .= "Terima kasih telah menggunakan layanan kami!";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Send expiry warning
     * @param string $phone Phone number
     * @param string $username Username
     * @param string $expiryTime When the voucher expires
     * @return bool Success status
     */
    public function sendExpiryWarning($phone, $username, $expiryTime)
    {
        $message = "⚠️ *Pemberitahuan Voucher*\n\n";
        $message .= "User: *" . $username . "*\n";
        $message .= "Voucher Anda akan expired pada:\n";
        $message .= "*" . $expiryTime . "*\n\n";
        $message .= "Silakan hubungi kami untuk perpanjangan.";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Replace template variables with actual values
     * @param string $template Template string
     * @param array $data Data array
     * @return string Processed message
     */
    private function replaceVariables($template, $data)
    {
        $variables = array(
            '{username}' => isset($data['username']) ? $data['username'] : '',
            '{password}' => isset($data['password']) ? $data['password'] : '',
            '{profile}' => isset($data['profile']) ? $data['profile'] : '',
            '{timelimit}' => isset($data['timelimit']) ? $data['timelimit'] : '',
            '{validity}' => isset($data['validity']) ? $data['validity'] : '',
            '{price}' => isset($data['price']) ? number_format($data['price'], 0, ',', '.') : '',
            '{date}' => date('d/m/Y'),
            '{time}' => date('H:i:s'),
            '{datetime}' => date('d/m/Y H:i:s'),
        );

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Check if WhatsApp Gateway is enabled
     * @param object $API MikroTik API object
     * @return bool Enabled status
     */
    public static function isEnabled($API)
    {
        $getConfig = $API->comm("/system/script/print", array(
            "?name" => "wa-config",
        ));

        if (count($getConfig) > 0) {
            $config = json_decode($getConfig[0]['source'], true);
            return isset($config['enabled']) && $config['enabled'] == true;
        }

        return false;
    }

    /**
     * Get WhatsApp API instance from MikroTik config
     * @param object $API MikroTik API object
     * @return WhatsAppAPI|null API instance or null if not configured
     */
    public static function getInstance($API)
    {
        $getConfig = $API->comm("/system/script/print", array(
            "?name" => "wa-config",
        ));

        if (count($getConfig) > 0) {
            $config = json_decode($getConfig[0]['source'], true);

            if (isset($config['enabled']) && $config['enabled']) {
                $apiUrl = $config['api_url'];
                $apiKey = decrypt($config['api_key']);

                return new WhatsAppAPI($apiUrl, $apiKey);
            }
        }

        return null;
    }
}
?>