<?

namespace App\Models;

use App\Api\v1\Playlists as PlaylistsApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Playlists as SpotifyPlaylistsApi;
use DateTime;

class SpotifyPlaylists{
    private $playlistsApi;
    private $spotify__Autho;
    private $spotify__Playlists;
    public function __construct(
        PlaylistsApi $playlistsApi, 
        SpotifyPlaylistsApi $spotifyPlaylistsApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->playlistsApi = $playlistsApi;
        $this->spotify__Playlists = $spotifyPlaylistsApi;
        $this->spotify__Autho = $spotifyAuthoApi;
    }

    public function getPlaylistFromStorage($id){
        return $this->playlistsApi->getPlaylistById(['id' => $id]);
    }

    // @param (int) ID плейлиста
    // @return (bool) Результат сохранения

    public function saveItem($playlist_id){
        if ($this->getPlaylistFromStorage($playlist_id)){
            return true;
        }else{
            /*Получение токена*/
            $access_token = $this->spotify__Autho->getToken()['access_token'];

            /*___Получение плейлиста___*/
            $query_data = [
                'playlist_id' => $playlist_id,
                'token' => $access_token
            ];
            $playlist = $this->spotify__Playlists->getPlaylist($query_data);

            if ($playlist['id'] == null){ return false; }

            $tracks = [];
            foreach ($playlist['tracks']['items'] as $track){
                $track = $track['track'];
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
                    'cover' => $track['album']['images'][0]['url'],
                    'album' => [
                        'id' => $track['album']['id'],
                        'name' => $track['album']['name'],
                    ],
                    'artists' => $track_artists_upd
                ];
            }

            $owner = json_encode($playlist['owner']);

            $data = [
                'id' => $playlist['id'],
                'type' => 'playlist',
                'total_tracks' => count($tracks),
                'cover' => $playlist['images'][0]['url'],
                'name' => $playlist['name'],
                'description' => $playlist['description'],
                'uri' => $playlist['uri'],
                'owner' => $owner,
                'tracks' => json_encode($tracks, true)
            ];
            if ($this->playlistsApi->createPlaylist($data)){
                return true;
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