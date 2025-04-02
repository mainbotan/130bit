<?

namespace App\Models;

use App\Api\v1\Tracks as TracksApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Tracks as SpotifyTracksApi;
use DateTime;

class SpotifyTracks{
    private $tracks__Api;
    private $spotify__Autho;
    private $spotify__Tracks;
    public function __construct(
        TracksApi $tracksApi, 
        SpotifyTracksApi $spotifyTracksApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->tracks__Api = $tracksApi;
        $this->spotify__Tracks = $spotifyTracksApi;
        $this->spotify__Autho = $spotifyAuthoApi;
    }

    public function getTrackFromStorage($id){
        return $this->tracks__Api->getTrackById(['id' => $id]);
    }

    // @param (int) ID трека
    // @return (bool) Результат сохранения

    public function saveItem($track_id){
        if ($this->getTrackFromStorage($track_id)){
            return true;
        }else{
            /*Получение токена*/
            $access_token = $this->spotify__Autho->getToken()['access_token'];

            /*_______Получение трека_______*/
            $query_data = [
                'track_id' => $track_id,
                'token' => $access_token
            ];
            $track = $this->spotify__Tracks->getTrack($query_data);
            if ($track['id'] != null){
                $data = [];
                $artists = [];
                foreach ($track['artists'] as $artist){
                    $id = $artist['id'];
                    $name = $artist['name'];
                    $artists[] = [
                        'id' => $id,
                        'name' => $name
                    ];
                }
                $data = [
                    'id' => $track['id'],
                    'uri' => $track['uri'],
                    'isrc' => $track['external_ids']['isrc'],
                    'type' => $track['type'],
                    'popularity' => $track['popularity'],
                    'cover' => $track['album']['images'][0]['url'],
                    'name' => $track['name'],
                    'artists' => json_encode($artists, true),
                    'primary_artist_name' => $track['artists'][0]['name'],
                    'primary_artist_id' => $track['artists'][0]['id'],
                    'album_id' => $track['album']['id'],
                    'album_name' => $track['album']['name'],
                    'release_date' => $this->formatDate($track['album']['release_date']),
                    'explicit' => $track['explicit']
                ];
                if($this->tracks__Api->createTrack($data)){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }

    // Вспомогательные функции 
    // Перенести в сервисы
    function escapeHtml($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    function formatDate($date) {
        $datetime = new DateTime($date);
        return $datetime->format('Y-m-d');
    }
}