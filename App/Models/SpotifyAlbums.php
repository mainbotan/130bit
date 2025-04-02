<?

namespace App\Models;

use App\Api\v1\Releases as ReleasesApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Albums as SpotifyAlbumsApi;
use DateTime;

class SpotifyAlbums{
    private $releases__Api;
    private $spotify__Autho;
    private $spotify__Albums;
    public function __construct(
        ReleasesApi $releasesApi, 
        SpotifyAlbumsApi $spotifyAlbumsApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->releases__Api = $releasesApi;
        $this->spotify__Albums = $spotifyAlbumsApi;
        $this->spotify__Autho = $spotifyAuthoApi;
    }

    public function getReleaseFromStorage($id){
        return $this->releases__Api->getReleaseById(['id' => $id]);
    }

    // @param (int) ID альбом
    // @return (bool) Результат сохранения

    public function saveItem($album_id){
        if ($this->getReleaseFromStorage($album_id)){
            return true;
        }else{
            /*Получение токена*/
            $access_token = $this->spotify__Autho->getToken()['access_token'];

            /*___Получение альбома___*/
            $query_data = [
                'album_id' => $album_id,
                'token' => $access_token
            ];
            $album = $this->spotify__Albums->getAlbum($query_data);

            if ($album['name'] != null){
                $artists = [];
                foreach ($album['artists'] as $artist){
                    $id = $artist['id'];
                    $name = $artist['name'];
                    $artists[] = [
                        'id' => $id,
                        'name' => $name
                    ];
                }
                $artists = json_encode($artists, true);

                $tracks = [];
                foreach ($album['tracks']['items'] as $track){
                    $track_artists_upd = [];
                    foreach ($track['artists'] as $artist){
                        $track_artists_upd[] = [
                            'id' => $artist['id'],
                            'name' => $artist['name']
                        ];
                    }
                    $tracks[] = [
                        'uri' => $track['uri'],
                        'id' => $track['id'],
                        'name' => $track['name'],
                        'number' => (int) $track['track_number'],
                        'disc_number' => (int) $track['disc_number'],
                        'artists' => $track_artists_upd
                    ];
                }
                $tracks = json_encode($tracks, true);

                $data = [
                    'id' => $album['id'],
                    'type' => $album['type'],
                    'total_tracks' => $album['total_tracks'],
                    'cover' => $album['images'][0]['url'],
                    'name' => $album['name'],
                    'release_date' => $this->formatDate($album['release_date']),
                    'uri' => $album['uri'],
                    'artists' => $artists,
                    'primary_artist_name' => $album['artists'][0]['name'],
                    'primary_artist_id' => $album['artists'][0]['id'],
                    'tracks' => $tracks,
                    'label' => $album['label'],
                    'popularity' => $album['popularity']
                ];
                if ($this->releases__Api->createRelease($data)){
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