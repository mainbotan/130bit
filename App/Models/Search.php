<?

namespace App\Models;

use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Search as SpotifySearchApi;

class Search{
    private $spotify__Search;
    private $spotify__Autho;
    
    public function __construct(
        SpotifySearchApi $spotifySearchApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->spotify__Search = $spotifySearchApi;
        $this->spotify__Autho = $spotifyAuthoApi;
    }

    /**
     * Это ёбаная грязь, так не надо делать
     * 
     * !!--Redis--!!
     */
    public function universalSearch($goal, $params){
        $token = $this->spotify__Autho->getToken()['access_token'];
        if (!isset($goal)){return false;}
        if (!isset($params)){return false;}
        $result = [];
        
        foreach ($params as $param=>$count){
            /*___Поиск по артистам___*/
            $query_data = [
                'query' => $goal,
                'type' => $param,
                'offset' => 0,
                'limit' => $count,
                'token' => $token
            ];
            $result[$param.'s'] = $this->spotify__Search->search($query_data)[$param.'s']['items'];
        }
        return $result;
    }
}