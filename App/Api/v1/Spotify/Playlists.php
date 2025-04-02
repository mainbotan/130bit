<?

namespace App\Api\v1\Spotify;

class Playlists{
    function getPlaylistTracks($data) {
        $accessToken = $data['token'];
        $playlistId = $data['playlist_id'];
        $url = 'https://api.spotify.com/v1/playlists/' . urlencode($playlistId) . '/tracks';
    
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
    function getPlaylistImage($data) {
        $accessToken = $data['token'];
        $playlistId = $data['playlist_id'];
        $url = 'https://api.spotify.com/v1/playlists/' . urlencode($playlistId) . '/images';
    
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
    function getPlaylist($data) {
        $accessToken = $data['token'];
        $playlistId = $data['playlist_id'];
        $url = 'https://api.spotify.com/v1/playlists/' . urlencode($playlistId);
    
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