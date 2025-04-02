<?

namespace App\Models;

use App\Api\v1\Tracks;
use App\Api\v1\Artists;
use App\Api\v1\Releases;
use App\Api\v1\Collections;
use App\Api\v1\Favourites;
use App\Api\v1\Subscriptions;
use App\Services\DecoderService;
use App\Api\v1\Users as UsersApi;

class Users{
    private $decoderService;
    private $usersApi;
    private $subscriptionsApi;
    private $collectionsApi;
    private $favouritesApi;
    private $releasesApi;
    private $artistsApi;
    private $tracksApi;
    public function __construct(
        DecoderService $decoderService, 
        UsersApi $usersApi,
        Subscriptions $subscriptionsApi,
        Collections $collectionsApi,
        Favourites $favouritesApi,
        Releases $releasesApi,
        Artists $artistsApi,
        Tracks $tracksApi
    ){
        $this->usersApi = $usersApi; 
        $this->decoderService = $decoderService;
        $this->subscriptionsApi = $subscriptionsApi;
        $this->collectionsApi = $collectionsApi;
        $this->favouritesApi = $favouritesApi;
        $this->releasesApi = $releasesApi;
        $this->artistsApi = $artistsApi;
        $this->tracksApi = $tracksApi;
    }  

    /**
     * Поиск пользователей по idx_name index
     */
    public function searchUsers(array $data){
        if (!isset($data['search'])){ return null; }
        if ($data['search'] == null){ return null; }
        if (mb_strlen($data['search']) > 100 or mb_strlen($data['search']) < 3){ return null; }
        return $this->usersApi->searchUsersByName([
            'search' => '%' . (string) $data['search'] . '%',
            'offset' => (int) $data['offset'],
            'limit' => (int) $data['limit']
        ]);
    }


    public function getUser(array $data){
        if (!isset($data['name']) & !isset($data['id'])){ return null; }
        if ($data['id'] != null){
            $user = $this->usersApi->getUserById(['id' => $data['id']]);
        }else{
            $user = $this->usersApi->getUserByName(['name' => $data['name']]);
        }
        if ($user == null){ return null; }
        $user_collection = $this->collectionsApi->getCollection(['owner_id' => $user['id']]);
        $user_subscriptions = $this->subscriptionsApi->getSubscriptions(['owner_id' => $user['id']]);
        $user_favourites = $this->favouritesApi->getFavourites(['owner_id' => $user['id']]);
        $user_collection['albums'] = $this->releasesApi->getSeveralReleases(['list' => $user_collection['albums'], 'limit' => 10000, 'offset' => 0]);
        $user_subscriptions['artists'] = $this->artistsApi->getSeveralArtistsByList(['list' => $user_subscriptions['artists'], 'limit' => 10000, 'offset' => 0]);
        $user_favourites['tracks'] = $this->tracksApi->getSeveralTracks(['list' => $user_favourites['tracks'], 'limit' => 10000, 'offset' => 0]);
        $user['collection'] = $user_collection;
        $user['subscriptions'] = $user_subscriptions;
        $user['favourites'] = $user_favourites;
        return $user;
    }
}