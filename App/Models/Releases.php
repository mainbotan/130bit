<?

namespace App\Models;

use App\Api\v1\Releases as ReleasesApi;

class Releases{
    private $releases__Api;
    public function __construct(
        ReleasesApi $releasesApi
    ){
        $this->releases__Api = $releasesApi;
    }

    public function getLatestReleasesByArtists($artists){
        if (!isset($artists)){return null;}
        $result = [];
        foreach ($artists as $artist){
            $artist_id = $artist['id'];
            $artist_avatar = $artist['picture_big'];
            $artist_name = $artist['name'];
            $release = $this->releases__Api->getLatestArtistReleases(['limit' => 1, 'offset' => 0, 'artist_id' => $artist_id])[0];
            $result[] = [
                'id' => $artist_id,
                'avatar' => $artist_avatar,
                'name' => $artist_name,
                'release' => $release
            ];
        }
        return $result;
    }

    public function getReleasesFromMyCollection($data){
        if ($data['user']){
            if ($data['user']['collection'] != null){
                $query_data = [
                    'list' => $data['user']['collection']['albums'],
                    'limit' => isset($data['limit']) ? (int) $data['limit'] : 10,
                    'offset' => isset($data['offset']) ? (int) $data['offset'] : 0,
                ];
                $releases = $this->releases__Api->getSeveralReleases($query_data);
                return $releases;
            }
            return false;
        }
        return false;
    }
    public function getNewReleases($data){
        $query_data = [
            'limit' => isset($data['limit']) ? (int) $data['limit'] : 10,
            'offset' => isset($data['offset']) ? (int) $data['offset'] : 0,
        ];
        $releases = $this->releases__Api->getReleasesByReleaseDate($query_data);
        return $releases;
    }
    public function getRecommendReleases($data){
        $query_data = [
            'limit' => isset($data['limit']) ? (int) $data['limit'] : 10,
            'offset' => isset($data['offset']) ? (int) $data['offset'] : 0,
        ];
        $releases = $this->releases__Api->getRecommendedReleases($query_data);
        if ($releases == null){
            return null;
        }
        return $releases;
    }
    public function getNewReleasesByArtists($data){
        if (empty($data['list']) || !is_array($data['list'])) {
            return false;
        }
        if (isset($data['list']) & count($data['list']) > 0){
            $query_data = [
                'limit' => isset($data['limit']) ? (int) $data['limit'] : 10,
                'offset' => isset($data['offset']) ? (int) $data['offset'] : 0,
                'list' => $data['list']
            ];
            $releases = $this->releases__Api->getSeveralReleasesByArtistsId($query_data);
            if ($releases != null){
                return $this->sortAlbumsByReleaseDate($releases);
            }
            return false;
        }
        return false;
    }
    private function sortAlbumsByReleaseDate($albums) {
        usort($albums, function($a, $b) {
            return strtotime($b['release_date']) - strtotime($a['release_date']);
        });
        return $albums;
    }

    // Рекомендация релиза
    public function recommendRelease($album_id, $spotifyAlbums__Model){
        if ($album_id == null){ return null; }
        $album = $this->releases__Api->getReleaseById(['id' => $album_id]);
        if (!$album){
            if (!$spotifyAlbums__Model->saveItem($album_id)){
                return false;
            }
        }
        // Добавляем флаг recommend
        if ($this->releases__Api->recommendReleaseById(['id' => $album_id])){
            return true;
        }
        return false;
    }
}