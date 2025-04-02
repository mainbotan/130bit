<?



namespace App\Api\v1\Spotify;

class Episodes{
    function getEpisodes($data) {
        $accessToken = $data['token'];
        $episodeIds = $data['ids']; // Массив идентификаторов эпизодов
        $idsString = implode(',', $episodeIds); // Преобразуем массив в строку, разделенную запятыми
        $url = 'https://api.spotify.com/v1/episodes?ids=' . urlencode($idsString);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
    
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }
    
        curl_close($ch);
        return json_decode($response, true);
    }
    function getEpisode($data) {
        $accessToken = $data['token'];
        $episodeId = $data['episode_id'];
        $url = 'https://api.spotify.com/v1/episodes/' . urlencode($episodeId);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
    
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }
    
        curl_close($ch);
        return json_decode($response, true);
    }
}