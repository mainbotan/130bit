<?

namespace App\Api\v1\Spotify;

class Tracks{
    function getTracks($data) {
        $accessToken = $data['token'];
        $trackIds = implode(',', $data['ids']);
        $url = 'https://api.spotify.com/v1/tracks?ids=' . urlencode($trackIds);
    
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
    function getTrack($data) {
        $accessToken = $data['token'];
        $trackId = $data['track_id'];
        $url = 'https://api.spotify.com/v1/tracks/' . urlencode($trackId);
    
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
