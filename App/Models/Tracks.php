<?

namespace App\Models;

use App\Api\v1\Tracks as TracksApi;

class Tracks{
    private $tracks__Api;
    public function __construct(
        TracksApi $tracksApi
    ){
        $this->tracks__Api = $tracksApi;
    }
    public function getTracksFromMyFavourites($data){
        if ($data['user']){
            if ($data['user']['favourites'] != null){
                $query_data = [
                    'list' => $data['user']['favourites']['tracks'],
                    'limit' => isset($data['limit']) ? (int) $data['limit'] : 10,
                    'offset' => isset($data['offset']) ? (int) $data['offset'] : 0,
                ];
                $tracks = $this->tracks__Api->getSeveralTracks($query_data);
                return $tracks;
            }
            return false;
        }
        return false;
    }
}