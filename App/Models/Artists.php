<?

namespace App\Models;

use App\Api\v1\Artists as ArtistsApi;

class Artists{
    private $artists__Api;
    public function __construct(
        ArtistsApi $artistsApi
    ){
        $this->artists__Api = $artistsApi;
    }
    public function getArtistsFromMySubs($data){
        if ($data['user']){
            if ($data['user']['subscriptions'] != null){
                $query_data = [
                    'list' => $data['user']['subscriptions']['artists'],
                    'limit' => isset($data['limit']) ? (int) $data['limit'] : 10,
                    'offset' => isset($data['offset']) ? (int) $data['offset'] : 0,
                ];
                $artists = $this->artists__Api->getSeveralArtistsByList($query_data);
                return $artists;
            }
            return false;
        }
        return false;
    }
    public function getArtistsByPopularity($limit){
        $artists = $this->artists__Api->getArtistsByPopularity(['limit' => $limit ?? 10, 'offset' => 0]);
        return $artists;
    }

    // Рекомендация артиста
    public function recommendArtist($artist_id, $spotifyArtists__Model){
        if ($artist_id == null){
            return false;
        }
        if ($this->artists__Api->getArtistById(['id' => $artist_id])){
            return true;
        }
        if ($spotifyArtists__Model->saveItem($artist_id)){
            return true;
        }
        return false;
    }
}