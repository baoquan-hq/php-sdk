<?php
/**
 * Created by PhpStorm.
 * User: sbwdlihao
 * Date: 6/22/16
 * Time: 11:02 AM
 */

namespace com\baoquan\sdk\util;


use com\baoquan\sdk\exception\ClientException;
use phpseclib\Crypt\RSA;

class Utils
{
    /**
     * use rsa 256 to sign data
     * @param string $key_path private key file path
     * @param string $data data to sign
     * @return string signed data
     */
    public static function sign($key_path, $data) {
        if (empty($key_path)) {
            throw new \InvalidArgumentException('pem path can not be empty');
        }
        if (is_null($data)) {
            return null;
        }
        $key = file_get_contents($key_path);
        if ($key === false) {
            throw new ClientException('load private key failed, key file path may be wrong');
        }
        $rsa = new RSA();
        $load_key_success = $rsa->loadKey($key);
        if (!$load_key_success) {
            throw new ClientException('load private key failed, private key format may be invalid');
        }
        $rsa->setSignatureMode(RSA::ENCRYPTION_PKCS1);
        $rsa->setHash('sha256');
        $signature = $rsa->sign($data);
        if (empty($signature)) {
            throw new ClientException('sign failed, but should never happen');
        }
        return base64_encode($signature);
    }

    /**
     * use sha256 to create the checksum of input resource
     * @param resource $resource
     * @return string
     */
    public static function checksum($resource) {
        $context = hash_init('sha256');
        hash_update_stream($context, $resource);
        return hash_final($context);
    }
}