<?php
 
class MagicCrypt {

    private $iv = "0512051205120512";//密钥偏移量IV
 
    private $encryptKey = "1102110211021102";//AESkey，
 
    //加密
    public function encrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
        mcrypt_generic_init($module, $encryptKey, $localIV);
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($encryptStr) % $block);
        $encryptStr .= str_repeat(chr($pad), $pad);
        $encrypted = mcrypt_generic($module, $encryptStr);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        return base64_encode($encrypted);
 
    }
 
    //解密
    public function decrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
        mcrypt_generic_init($module, $encryptKey, $localIV);
        $encryptedData = base64_decode($encryptStr);
        $encryptedData = mdecrypt_generic($module, $encryptedData);
        return $encryptedData;
    }
}

$encryptObj = new MagicCrypt();
 
echo $encryptObj->encrypt(123)."\n"; //加密结果

echo $encryptObj->decrypt('1OXoGpblM1CLQaNpl5OzjA==')."\n"; // 解密结果
