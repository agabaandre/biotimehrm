<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Encrypt / decrypt clk_log batches for peer Attend servers.
 * Shared one-time private key is stored in setting.remote_clk_private_key
 * on both the sender and the receiver.
 */
class Clk_log_remote
{
    const ALG = 'AES-256-CBC-HMAC-SHA256';

    /**
     * @param string $plaintext
     * @param string $privateKey
     * @return array{alg:string,iv:string,data:string,mac:string}
     */
    public function encrypt($plaintext, $privateKey)
    {
        $key = $this->deriveKey($privateKey);
        $iv = random_bytes(16);
        $cipher = openssl_encrypt((string) $plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            throw new Exception('Failed to encrypt clk_log payload');
        }
        $mac = hash_hmac('sha256', $iv . $cipher, $key);
        return [
            'alg' => self::ALG,
            'iv' => base64_encode($iv),
            'data' => base64_encode($cipher),
            'mac' => $mac,
        ];
    }

    /**
     * @param array $packet
     * @param string $privateKey
     * @return string
     */
    public function decrypt($packet, $privateKey)
    {
        if (!is_array($packet) || empty($packet['iv']) || empty($packet['data']) || empty($packet['mac'])) {
            throw new Exception('Invalid encrypted packet');
        }
        $key = $this->deriveKey($privateKey);
        $iv = base64_decode((string) $packet['iv'], true);
        $cipher = base64_decode((string) $packet['data'], true);
        if ($iv === false || $cipher === false) {
            throw new Exception('Invalid encrypted packet encoding');
        }
        $mac = hash_hmac('sha256', $iv . $cipher, $key);
        if (!hash_equals($mac, (string) $packet['mac'])) {
            throw new Exception('Packet MAC mismatch — wrong private key or tampered payload');
        }
        $plain = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) {
            throw new Exception('Failed to decrypt clk_log payload');
        }
        return $plain;
    }

    /**
     * @param string $privateKey
     * @return string
     */
    protected function deriveKey($privateKey)
    {
        $privateKey = trim((string) $privateKey);
        if ($privateKey === '') {
            throw new Exception('Remote clk_log private key is empty');
        }
        return hash('sha256', $privateKey, true);
    }
}
