<?php

class AuthCrypt
{
    public function __construct()
    {
        
    }

    function __destruct()
    {
        
    }

    public function crypt_encode($encKey, $encVal)
    {
        try {
            $key = md5($encKey);
            $iv_size = 8;
            $key = substr($key, 0, $iv_size);

            return base64_encode(openssl_encrypt($encVal, 'DES-ECB', $key, OPENSSL_RAW_DATA));

/*
            $key = md5($encKey);
            
            $td  = mcrypt_module_open('des', '', 'ecb', '');
            $key = substr($key, 0, mcrypt_enc_get_key_size($td));
            $iv  = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            
            if (mcrypt_generic_init($td, $key, $iv) < 0) {
              exit('error.');
            }
            
            $enc_text = base64_encode(mcrypt_generic($td, $encVal));
            
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
            
            return $enc_text;
*/
        } catch (Exception $e) {
              return $e;
        }
    }

    public function crypt_decode($encKey, $encVal)
    {
        try {
            $key = md5($encKey);
            $iv_size = 8;
            $key = substr($key, 0, $iv_size);
            $encVal = base64_decode($encVal);

            return openssl_decrypt($encVal, 'DES-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);

/*
            $key = md5($encKey);

            $td  = mcrypt_module_open('des', '', 'ecb', '');
            $key = substr($key, 0, mcrypt_enc_get_key_size($td));
            $iv  = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

            if (mcrypt_generic_init($td, $key, $iv) < 0) {
              exit('error.');
            }

            $dec_text = mdecrypt_generic($td, base64_decode($encVal));

            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);

            return $dec_text;
*/
        } catch (Exception $e) {
              return "";
        }
    }
}
?>
