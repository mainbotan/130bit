<?

namespace App\Api\v1\Spotify;

class Search{
    function search($data) {
        // Извлекаем access_token из массива данных
        $accessToken = $data['token'];
        $query = $data['query'];
        $type = $data['type'];
        $offset = (int) $data['offset'];
        $limit = (int) $data['limit'];
  
        // Формируем URL для запроса
        $url = 'https://api.spotify.com/v1/search?q=' . urlencode($query) . '&type=' . urlencode($type) . "&offset=$offset&limit=$limit";
  
        // Инициализация cURL
        $ch = curl_init();
  
        // Настройка параметров cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
  
        // Выполнение запроса
        $response = curl_exec($ch);
  
        // Проверка на наличие ошибок
        if (curl_errno($ch)) {
            // Если есть ошибка, возвращаем её
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }
  
        // Закрываем cURL
        curl_close($ch);
  
        // Декодируем ответ JSON
        $decodedResponse = json_decode($response, true);
  
        // Возвращаем декодированный ответ
        return $decodedResponse;
      }
}