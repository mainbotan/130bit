<?

namespace App\Api\v1\SoundCloud;

class Search{
    function searchSoundCloudTrack($query, $clientId, $authToken) {
        $url = 'https://api-v2.soundcloud.com/search/tracks';
        
        // Кодируем поисковый запрос
        $encodedQuery = $query;
        
        $headers = [
            'Accept: */*',
            'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Connection: keep-alive',
            'Content-Type: application/json',
            'Origin: https://soundcloud.com',
            'Referer: https://soundcloud.com/',
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'Authorization: OAuth ' . $authToken,
            'client_id: ' . $clientId
        ];
    
        $params = [
            'q' => $encodedQuery, // Используем закодированный запрос
            'client_id' => $clientId,
            'limit' => 25,
            'offset' => 0
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Включаем проверку SSL
        curl_setopt($ch, CURLOPT_FAILONERROR, true); // Возвращать ошибку при HTTP >=400
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Логирование
    
        // Для отладки: сохраняем лог cURL
        $verbose = fopen('curl.log', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = 'CURL Error: ' . curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }
        
        curl_close($ch);
        fclose($verbose);
    
        // Проверяем HTTP-код
        if ($httpCode !== 200) {
            return ['error' => "HTTP Error: $httpCode"];
        }
    
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'JSON Error: ' . json_last_error_msg()];
        }
        
        return $data;
    }
    
}