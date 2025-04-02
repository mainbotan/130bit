<?

namespace App\Models;


use App\Api\v1\Playlists as PlaylistsApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Artists as SpotifyArtistsApi;
use App\Api\v1\Spotify\Search as SpotifySearchApi;
use App\Api\v1\Spotify\Playlists as SpotifyPlaylistsApi;

class Playlist{
    private $playlistsApi;
    private $spotify__Playlists;
    private $spotify__Artists;
    private $spotify__Autho;
    
    public function __construct(
        PlaylistsApi $playlistsApi, 
        SpotifyPlaylistsApi $spotifyPlaylistsApi, 
        SpotifyArtistsApi $spotifyArtistsApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->playlistsApi = $playlistsApi;
        $this->spotify__Playlists = $spotifyPlaylistsApi;
        $this->spotify__Artists = $spotifyArtistsApi;
        $this->spotify__Autho = $spotifyAuthoApi;
    }

    public function getPlaylist($data){
        $playlist_id = $data['id'] ?? null;
        $playlist = $this->getFromStorage($playlist_id);
        if ($playlist){
            $playlist = [
                'id' => $playlist['id'],
                'name' => $playlist['name'],
                'description' => $playlist['description'],
                'cover' => $playlist['cover'],
                'total_tracks' => $playlist['total_tracks'],
                'tracks' => $playlist['tracks'],
                'owner' => $playlist['owner'],
                'followers' => $playlist['followers']['total'],
                'isRecommend' => $playlist['recommend']
            ];
        }else{
            $token = $this->spotify__Autho->getToken()['access_token'];
            $playlist = $this->spotify__Playlists->getPlaylist(['playlist_id' => $playlist_id, 'token' => $token]);
            if ($playlist['id'] == null){ return null; } 
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
                    'cover' => $track['album']['images'][0]['url'],
                    'number' => (int) $track['track_number'],
                    'disc_number' => (int) $track['disc_number'],
                    'album' => [
                        'id' => $track['album']['id'],
                        'name' => $track['album']['name'],
                    ],
                    'artists' => $track_artists_upd
                ];
            }

            $playlist = [
                'id' => $playlist['id'],
                'name' => $playlist['name'],
                'description' => $playlist['description'],
                'cover' => $playlist['images'][0]['url'],
                'total_tracks' => count($playlist['tracks']['items']),
                'tracks' => $tracks,
                'owner' => $playlist['owner'],
                'followers' => $playlist['followers']['total'],
                'isRecommend' => false
            ];
        }

        $name_count = mb_strlen($playlist['name']);
        if ($name_count >= 10){
            $name_size = 'Mini';
        }else{
            if ($name_count >= 9){
                $name_size = 'Middle';
            }else{
                $name_size = 'Max'; 
            }
        }
        $playlist['name_size'] = $name_size;
        return $playlist;
    }
    public function getFromStorage($id){
        $playlist = $this->playlistsApi->getPlaylistById(['id' => $id]);
        if ($playlist == false){
            return false;
        }
        return $playlist;
    }
}