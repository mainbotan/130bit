<?

namespace App\Models;


use App\Api\v1\Releases as ReleasesApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Artists as SpotifyArtistsApi;
use App\Api\v1\Spotify\Search as SpotifySearchApi;
use App\Api\v1\Spotify\Albums as SpotifyAlbumsApi;

class Album{
    private $releasesApi;
    private $spotify__Albums;
    private $spotify__Artists;
    private $spotify__Autho;
    
    public function __construct(
        ReleasesApi $releasesApi, 
        SpotifyAlbumsApi $spotifyAlbumsApi, 
        SpotifyArtistsApi $spotifyArtistsApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->releasesApi = $releasesApi;
        $this->spotify__Albums = $spotifyAlbumsApi;
        $this->spotify__Artists = $spotifyArtistsApi;
        $this->spotify__Autho = $spotifyAuthoApi;
    }
    public function getAlbum($data){
        $album_id = $data['id'] ?? null;
        $album = $this->getFromStorage($album_id);
        if ($album){
            $album = [
                'type' => $album['type'],
                'total_tracks' => $album['total_tracks'],
                'id' => $album['id'],
                'album_image_big' => $album['cover'],
                'name' => $album['name'],
                'release_date' => $album['release_date'],
                'uri' => $album['uri'],
                'artists' => $album['artists'],
                'primary_artist' => $album['artists'][0]['name'],
                'label' => $album['label'],
                'popularity' => $album['popularity'],
                'tracks' => $album['tracks'],
                'isRecommend' => $album['recommend']
            ];
        }else{
            $token = $this->spotify__Autho->getToken()['access_token'];
            $album = $this->spotify__Albums->getAlbum(['album_id' => $album_id, 'token' => $token]);
            if ($album['id'] != null){ 
                $album = [
                    'type' => $album['type'],
                    'total_tracks' => $album['total_tracks'],
                    'id' => $album['id'],
                    'album_image_big' => $album['images'][0]['url'],
                    'name' => $album['name'],
                    'release_date' => $album['release_date'],
                    'uri' => $album['uri'],
                    'artists' => $album['artists'],
                    'primary_artist' => $album['artists'][0]['name'],
                    'label' => $album['label'],
                    'popularity' => $album['popularity'],
                    'tracks' => $album['tracks']['items'],
                    'isRecommend' => false
                ];
            }else{
                return false;
            }
        }
        if ($album){
            $album_name_count = mb_strlen($album['name']);
            if ($album_name_count >= 10){
                $album_name_size = 'Mini';
            }else{
                if ($album_name_count >= 9){
                    $album_name_size = 'Middle';
                }else{
                    $album_name_size = 'Max'; 
                }
            }
            $album['name_size'] = $album_name_size;
            
            return $album;
        }
    }
    public function getFromStorage($id){
        $release = $this->releasesApi->getReleaseById(['id' => $id]);
        if ($release == false){
            return false;
        }
        return $release;
    }
}