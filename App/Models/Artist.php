<?

namespace App\Models;

use App\Api\v1\Artists as ArtistsApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Artists as SpotifyArtistsApi;
use App\Api\v1\Spotify\Search as SpotifySearchApi;
use App\Api\v1\Spotify\Albums as SpotifyAlbumsApi;


class Artist{
    private $artistsApi;
    private $spotify__Albums;
    private $spotify__Artists;
    private $spotify__Autho;
    
    public function __construct(
        ArtistsApi $artistsApi, 
        SpotifyAlbumsApi $spotifyAlbumsApi, 
        SpotifyArtistsApi $spotifyArtistsApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->artistsApi = $artistsApi;
        $this->spotify__Albums = $spotifyAlbumsApi;
        $this->spotify__Artists = $spotifyArtistsApi;
        $this->spotify__Autho = $spotifyAuthoApi;
    }
    public function getArtist($data){
        $artist_id = $data['id'] ?? null;
        $artist = $this->getFromStorage($artist_id);
        if ($artist){
            $artist = [
                'id' => $artist['id'],
                'name' => $artist['name'],
                'popularity' => $artist['popularity'],
                'cover' => $artist['picture_big'],
                'followers' => $this->splitSubsNumber((int) $artist['followers']),
                'isRecommend' => true
            ];
        }else{
            $token = $this->spotify__Autho->getToken()['access_token'];
            $artist = $this->spotify__Artists->getArtistInfo(['artist_id' => $artist_id, 'token' => $token]);
            if ($artist['id'] != null){
                $artist = [
                    'id' => $artist['id'],
                    'name' => $artist['name'],
                    'popularity' => $artist['popularity'],
                    'cover' => $artist['images'][0]['url'],
                    'followers' => $this->splitSubsNumber((int) $artist['followers']['total']),
                    'isRecommend' => false
                ];
            }else{
                return false;
            }
        }
        $artist_name_count = mb_strlen($artist['name']);
        if ($artist_name_count >= 14){
            $artist_name_size = 'Mini';
        }else{
            if ($artist_name_count >= 8){
                $artist_name_size = 'Middle';
            }else{
                $artist_name_size = 'Max'; 
            }
        }
        $artist['name_size'] = $artist_name_size;
        $result = [
            'info' => $artist
        ];
        if ($data['isReleases']){
            if ($token == null){$token = $this->spotify__Autho->getToken()['access_token'];}
            /*___Получение альбомов и синглов артиста___*/
            $query_data = [
                'artist_id' => $artist['id'],
                'groups' => [
                    'album',
                    'single'
                ],
                'token' => $token,
                'limit' => 30,
                'offset' => 0
            ];
            $releases = $this->spotify__Artists->getArtistAlbums($query_data)['items'];
            $releases = $this->sortAlbumsByReleaseDate($releases);
            $result['all_releases'] = $releases; 
            $albums = $this->filterReleasesByType($releases, 'album');
            $result['albums'] = $albums;
        }
        if ($data['isTopTracks']){
            if ($token == null){$token = $this->spotify__Autho->getToken()['access_token'];}
            /*___Получение популярных треков артиста___*/
            $query_data = [
                'artist_id' => $artist['id'],
                'token' => $token
            ];
            $tracks = $this->spotify__Artists->getArtistTopTracks($query_data)['tracks'];
            $result['tracks'] = $tracks;
        }
        if ($data['isAppearsOn']){
            if ($token == null){$token = $this->spotify__Autho->getToken()['access_token'];}
            /*___Получение участий артиста___*/
            $query_data = [
                'artist_id' => $artist['id'],
                'groups' => [
                    'appears_on',
                ],
                'token' => $token,
                'limit' => 30,
                'offset' => 0
            ];
            $releases = $this->spotify__Artists->getArtistAlbums($query_data)['items'];
            $releases = $this->sortAlbumsByReleaseDate($releases);
            $result['appears_on'] = $releases; 
        }
        return $result;
    }
    public function getFromStorage($id){
        $artist = $this->artistsApi->getArtistById(['id' => $id]);
        if ($artist != false){
            return $artist;
        }
        return false;
    }
    private function splitSubsNumber($number) {
        return number_format($number, 0, '.', ' ');
    }
    public function sortAlbumsByReleaseDate($albums) {
        usort($albums, function($a, $b) {
            return strtotime($b['release_date']) - strtotime($a['release_date']);
        });
        return $albums;
    }
    public function filterReleasesByType(array $releases, string $type): array {
        return array_filter($releases, function($release) use ($type) {
            return isset($release['album_type']) && $release['album_type'] === $type;
        });
    }
}