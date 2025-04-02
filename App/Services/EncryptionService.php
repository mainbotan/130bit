<?

namespace App\Services;

class EncryptionService{
    function mc_encrypt($encrypt, $key) {
        $encrypt = serialize($encrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $key = pack('H*', $key);
        $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
        $encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
        return $encoded;
    }  
    // Decrypt Function
    function mc_decrypt($decrypt, $key) {
        $decrypt = str_replace(' ', '+' , $decrypt);
        $decrypt = explode('|', $decrypt.'|');
        $decoded = base64_decode($decrypt[0]);
        $iv = base64_decode($decrypt[1]);
        if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
        $key = pack('H*', $key);
        $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
        $mac = substr($decrypted, -64);
        $decrypted = substr($decrypted, 0, -64);
        $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
        if($calcmac!==$mac){ return false; }
        $decrypted = unserialize($decrypted);
        return $decrypted;
    }
    public function encryptNumberWithSalt($number, $salt, $key){
        // Совмещаем число с солью
        $data = $salt . $number;

        // Обрезаем соль, если ее длина превышает 8 байт
        $salt = substr($salt, 0, 8);

        // Шифруем с использованием 3DES
        $encrypted = openssl_encrypt($data, 'des-ede3-cbc', $key, 0, $salt);

        // Кодируем в base64
        $encoded = base64_encode($encrypted);

        return $encoded;
    }
    public function decryptStringWithSalt($encoded, $salt, $key){
        // Декодируем из base64
        $decoded = base64_decode($encoded);

        // Обрезаем соль, если ее длина превышает 8 байт
        $salt = substr($salt, 0, 8);

        // Дешифруем с использованием 3DES
        $decrypted = openssl_decrypt($decoded, 'des-ede3-cbc', $key, 0, $salt);

        // Извлекаем число
        $number = substr($decrypted, strlen($salt));

        return $number;
    }

}