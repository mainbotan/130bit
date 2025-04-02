<?

namespace App\Api\v1\Genius;

class Main{
    function searchAlbum($artist, $album) {
        // Формируем строку запроса
        $query_str = "$artist $album";
        $url = "https://api.genius.com/search?q=" . $query_str;
    
        // Инициализация cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . GENIUS_ACCESS_TOKEN,
        ]);
    
        // Выполнение запроса
        $response = curl_exec($curl);
        curl_close($curl);
    
        // Декодирование JSON-ответа
        $data = json_decode($response, true);
    
        if ($data['meta']['status'] == 200) {
            $results = $data['response']['hits'];
    
            // Фильтруем результаты, чтобы получить только альбомы
            $albums = array_filter($results, function($hit) {
                return $hit['type'] == 'album';
            });
    
            return $albums;
        }
    
        return null;
    }
    function getSong($data) {
        $song_id = (int) $data['song_id'];
        // URL для получения сведений о треке
        $url = 'https://api.genius.com/songs/' . $song_id;
    
        // Инициализация cURL
        $curl = curl_init();
    
        // Настройка параметров cURL
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . GENIUS_ACCESS_TOKEN
        ]);
    
        // Выполнение запроса
        $response = curl_exec($curl);
        curl_close($curl);
    
        // Декодирование JSON-ответа
        $data = json_decode($response, true);

        if ($data['meta']['status'] == 200){
            return $data['response']['song'];
        }
        return null;
    }
    function globalSearch($data) {
        $query_str = $data['query_str'];
        $url = "https://api.genius.com/search?q=" . $query_str;
    
        // Инициализация cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . GENIUS_ACCESS_TOKEN,
        ]);
    
        // Выполнение запроса
        $response = curl_exec($curl);
        curl_close($curl);
    
        // Декодирование JSON-ответа
        $data = json_decode($response, true);

        if ($data['meta']['status'] == 200){
            return $data['response'];
        }
        return null;
    }
    function getArtist($data) {
        $artist_id = $data['artist_id'];
        $url = "https://api.genius.com/artists/" . intval($artist_id);
    
        // Инициализация cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . GENIUS_ACCESS_TOKEN,
        ]);
    
        // Выполнение запроса
        $response = curl_exec($curl);
        curl_close($curl);
    
        // Декодирование JSON-ответа
        $data = json_decode($response, true);

        if ($data['meta']['status'] == 200){
            return $data['response']['artist'];
        }
        return null;
    }
    
    function getSongsByTitleAndArtist($data) {
        $artistName = $data['artist_name'];
        $songName = $data['song_title'];
        $url = 'https://api.genius.com/search?' . http_build_query(['q' => $songName . ' ' . $artistName]);
    
        // Инициализация cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . GENIUS_ACCESS_TOKEN,
        ]);
    
        // Выполнение запроса
        $response = curl_exec($curl);
        curl_close($curl);
    
        // Проверка ошибок cURL
        if ($response === false) {
            return null; // Возвращает null в случае ошибки
        }
    
        // Декодирование JSON-ответа
        $data = json_decode($response, true);

        // Проверка наличия результатов
        if (!isset($data['response']['hits']) || empty($data['response']['hits'])) {
            return null; // Альбом не найден
        }else{
            $res = [];
            // Перебор результатов поиска
            foreach ($data['response']['hits'] as $hit) {
                $id = $hit['result']['id'];
                $title = $hit['result']['title'];
                $title = $hit['result']['title'];
                $artists = [];
                $f_artists = $hit['result']['featured_artists'];
                foreach ($f_artists as $art){
                    $artists[] = $art['name'];
                }
                $p_artists = $hit['result']['primary_artists'];
                foreach ($p_artists as $art){
                    $artists[] = $art['name'];
                }

                $res[] = [
                    'id' => $id,
                    'title' => $title,
                    'artists' => $artists
                ];
            }
            return $res;
        }
    }
    function getArtistIdByNickname($data) {
        $nickname = $data['artist_name'];
        $url = "https://api.genius.com/search?limit=10&q=" . $nickname;
    
        // Инициализация cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . GENIUS_ACCESS_TOKEN,
        ]);
    
        // Выполнение запроса
        $response = curl_exec($curl);
        curl_close($curl);
    
        // Декодирование JSON-ответа
        $data = json_decode($response, true);

        return $data;
    }
}

