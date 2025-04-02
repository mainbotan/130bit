<?

namespace App\Api\v1\Spotify;

class Browse{
    function getCategoroies($data) {
        $accessToken = $data['token'];
        $offset = (int) $data['offset'];
        $limit = (int) $data['limit'];
        $url = 'https://api.spotify.com/v1/browse/categories'."?limit=$limit&offset=$offset";
    
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
}