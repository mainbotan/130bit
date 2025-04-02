<?php
namespace App\Services;

class AppService{
    public function isIphone() {
        return preg_match('/iPhone/i', $_SERVER['HTTP_USER_AGENT']);
    }
    public function isMobile() {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
    public function generateSecret($length = 11) {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialChars = '?&#$@';

        $allChars = $lowercase . $uppercase . $numbers . $specialChars;

        do {
            $password = '';
            for ($i = 0; $i < $length; $i++) {
                $password .= $allChars[random_int(0, strlen($allChars) - 1)];
            }
        } while (
            !preg_match('/[a-z]/', $password) || // Проверка на строчные
            !preg_match('/[A-Z]/', $password) || // Проверка на заглавные
            !preg_match('/[0-9]/', $password) || // Проверка на цифры
            !preg_match('/[\?&#\$@]/', $password) // Проверка на спецсимволы
        );

        return $password;
    }
    public function createSessionToken(){
        $token = openssl_encrypt($this->generateSecret(20), 'des-ede3-cbc', '300huev', 0, '130huev');
        if (!isset($_SESSION['token'])){
            $_SESSION['token'] = $token;
        }
    }
    public function checkSessionToken($token){
        if (trim($token) == $_SESSION['token'] and $_SESSION['token'] != null){
            return true;
        }
        return false;
    }
}