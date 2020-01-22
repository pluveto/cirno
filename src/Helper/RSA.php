<?php
/**
 * 使用openssl实现非对称加密
 * 
 * @since 2015-11-10
 */
class Rsa
{
    /**
     * 私钥
     * 
     */
    private $_privKey;

    /**
     * 公钥
     * 
     */
    private $_pubKey;

    /**
     * 保存文件地址
     */
    private $_keyPath;

    /**
     * 指定密钥文件地址
     * 
     */
    public function __construct($path)
    {
        $this->_keyPath = $path;
        $this->setupPrivKey();
        $this->setupPubKey();
    }


    /**
     * 设置私钥
     * 
     */
    public function setupPrivKey()
    {
        if (is_resource($this->_privKey)) {
            return true;
        }
        $file = $this->_keyPath . DIRECTORY_SEPARATOR . 'priv.key';
        $privKey = file_get_contents($file);
        $this->_privKey = openssl_pkey_get_private($privKey);
        return true;
    }

    /**
     * 设置公钥
     * 
     */
    public function setupPubKey()
    {
        if (is_resource($this->_pubKey)) {
            return true;
        }
        $file = $this->_keyPath . DIRECTORY_SEPARATOR . 'pub.key';
        $pubKey = file_get_contents($file);
        $this->_pubKey = openssl_pkey_get_public($pubKey);
        return true;
    }

    /**
     * 用私钥加密
     * 
     */
    public function privEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $this->setupPrivKey();
        $result = openssl_private_encrypt($data, $encrypted, $this->_privKey);
        if ($result) {
            return base64_encode($encrypted);
        }
        return null;
    }

    /**
     * 私钥解密
     * 
     */
    public function privDecrypt($encrypted)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $this->setupPrivKey();
        $encrypted = base64_decode($encrypted);
        $result = openssl_private_decrypt($encrypted, $decrypted, $this->_privKey);
        if ($result) {
            return $decrypted;
        }
        return null;
    }

    /**
     * 公钥加密
     * 
     */
    public function pubEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $this->setupPubKey();
        $result = openssl_public_encrypt($data, $encrypted, $this->_pubKey);
        if ($result) {
            return base64_encode($encrypted);
        }
        return null;
    }

    /**
     * 公钥解密
     * 
     */
    public function pubDecrypt($crypted)
    {
        if (!is_string($crypted)) {
            return null;
        }
        $this->setupPubKey();
        $crypted = base64_decode($crypted);
        $result = openssl_public_decrypt($crypted, $decrypted, $this->_pubKey);
        if ($result) {
            return $decrypted;
        }
        return null;
    }

    /**
     * __destruct
     * 
     */
    public function __destruct() {
        @fclose($this->_privKey);
        @fclose($this->_pubKey);
    }
}

?> 