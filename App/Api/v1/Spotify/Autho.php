<?

namespace App\Api\v1\Spotify;

class Autho{
    function getToken(){
        // URL для запроса
        $url = "https://accounts.spotify.com/api/token";

        // Данные для отправки
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => SPOTIFY_CLIENT_ID,
            'client_secret' => SPOTIFY_CLIENT_SECRET
        ];

        // Инициализация cURL
        $ch = curl_init($url);

        // Установка параметров cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Выполнение запроса
        $response = curl_exec($ch);

        // Проверка на наличие ошибок
        if (curl_errno($ch)) {
            return curl_error($ch);
        } else {
            // Обработка ответа
            $responseData = json_decode($response, true);
            return $responseData;
        }
    }
}
