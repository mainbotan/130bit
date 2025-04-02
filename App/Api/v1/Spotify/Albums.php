<?

namespace App\Api\v1\Spotify;

class Albums{
    function getNewReleases($data) {
        $accessToken = $data['token'];
        $offset = (int) $data['offset'];
        $limit = (int) $data['limit'];
        $url = 'https://api.spotify.com/v1/browse/new-releases'."?limit=$limit&offset=$offset";
    
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
    function getAlbums($data) {
        $accessToken = $data['token'];
        $albumIds = implode(',', $data['ids']);
        $url = 'https://api.spotify.com/v1/albums?ids=' . urlencode($albumIds);
    
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
    function getAlbum($data) {
        $accessToken = $data['token'];
        $albumId = $data['album_id'];
        $url = 'https://api.spotify.com/v1/albums/' . urlencode($albumId);
    
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
