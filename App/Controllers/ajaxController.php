<?php
session_start();

require_once '../../vendor/autoload.php';
require_once '../AppConfiguration.php';

use App\Services\AppService;
use App\Services\EncryptionService;
use App\Services\DecoderService;
use App\Models\Comments as CommentsModel;
use App\Models\Playlists as PlaylistsModel;
use App\Models\Users as UsersModel;
use App\Models\Album as AlbumModel;
use App\Models\Releases as ReleasesModel;
use App\Models\Artist as ArtistModel;
use App\Models\Artists as ArtistsModel;
use App\Models\Account as AccountModel;
use App\Models\Track as TrackModel;
use App\Models\Tracks as TracksModel;
use App\Models\Search as SearchModel;
use App\Models\SpotifyPlaylists as SpotifyPlaylistsModel;
use App\Models\SpotifyAlbums as SpotifyAlbumsModel;
use App\Models\SpotifyArtists as SpotifyArtistsModel;
use App\Models\SpotifyTracks as SpotifyTracksModel;
use App\Models\SoundCloud as SoundCloudModel;
use App\Models\GeniusArtist as GeniusArtistModel;
use App\Models\LastFMArtists as LastFMArtistsModel;
use App\Models\LastFMAlbums as LastFMAlbumsModel;
use App\Api\v1\Comments as CommentsApi;
use App\Api\v1\Sessions as SessionsApi;
use App\Api\v1\Users as UsersApi;
use App\Api\v1\Tracks as TracksApi;
use App\Api\v1\Trackers as TrackersApi;
use App\Api\v1\Artists as ArtistsApi;
use App\Api\v1\Releases as ReleasesApi;
use App\Api\v1\Account as AccountApi;
use App\Api\v1\Collections as CollectionsApi;
use App\Api\v1\Favourites as FavouritesApi;
use App\Api\v1\Subscriptions as SubscriptionsApi;
use App\Api\v1\Friends as FriendsApi;
use App\Api\v1\Playlists as PlaylistsApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Artists as SpotifyArtistsApi;
use App\Api\v1\Spotify\Search as SpotifySearchApi;
use App\Api\v1\Spotify\Albums as SpotifyAlbumsApi;
use App\Api\v1\Spotify\Tracks as SpotifyTracksApi;
use App\Api\v1\Spotify\Episodes as SpotifyEpisodesApi;
use App\Api\v1\Spotify\Playlists as SpotifyPlaylistsApi;
use App\Api\v1\SoundCloud\Search as SoundCloudSearchApi;
use App\Api\v1\Genius\Main as GeniusApi;
use App\Api\v1\LastFM\Artists as LastFmArtistsApi;
use App\Api\v1\LastFM\Albums as LastFmAlbumsApi;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class AjaxController {
    private $appService;
    private $encryptionService;
    private $decoderService;
    private $twig;
    private $releasesApi;
    private $artistsApi;
    private $tracksApi;
    private $trackersApi;
    private $accountApi;
    private $collectionsApi;
    private $favouritesApi;
    private $subscriptionsApi;
    private $spotifyAuthoApi;
    private $spotifyArtistsApi;
    private $spotifySearchApi;
    private $spotifyAlbumsApi;
    private $spotifyTracksApi;
    private $spotifyEpisodesApi;
    private $spotifyPlaylistsApi;
    private $soundCloudSearchApi;
    private $geniusApi;
    private $lastFmArtistsApi;
    private $lastFmAlbumsApi;
    private $friendsApi;
    private $usersApi;
    private $sessionsApi;
    private $playlistsApi;
    private $commentsApi;

    public function __construct(
        AppService $appService,
        EncryptionService $encryptionService,
        DecoderService $decoderService,
        Environment $twig,
        ReleasesApi $releasesApi,
        ArtistsApi $artistsApi,
        TracksApi $tracksApi,
        TrackersApi $trackersApi,
        AccountApi $accountApi,
        CollectionsApi $collectionsApi,
        FavouritesApi $favouritesApi,
        SubscriptionsApi $subscriptionsApi,
        SpotifyAuthoApi $spotifyAuthoApi,
        SpotifyArtistsApi $spotifyArtistsApi,
        SpotifySearchApi $spotifySearchApi,
        SpotifyAlbumsApi $spotifyAlbumsApi,
        SpotifyTracksApi $spotifyTracksApi,
        SpotifyEpisodesApi $spotifyEpisodesApi,
        SpotifyPlaylistsApi $spotifyPlaylistsApi,
        SoundCloudSearchApi $soundCloudSearchApi,
        GeniusApi $geniusApi,
        LastFmArtistsApi $lastFmArtistsApi,
        LastFmAlbumsApi $lastFmAlbumsApi,
        FriendsApi $friendsApi,
        UsersApi $usersApi,
        SessionsApi $sessionsApi,
        PlaylistsApi $playlistsApi,
        CommentsApi $commentsApi
    ) {
        $this->appService = $appService;
        $this->encryptionService = $encryptionService;
        $this->decoderService = $decoderService;
        $this->twig = $twig;
        $this->releasesApi = $releasesApi;
        $this->artistsApi = $artistsApi;
        $this->tracksApi = $tracksApi;
        $this->trackersApi = $trackersApi;
        $this->accountApi = $accountApi;
        $this->collectionsApi = $collectionsApi;
        $this->favouritesApi = $favouritesApi;
        $this->subscriptionsApi = $subscriptionsApi;
        $this->spotifyAuthoApi = $spotifyAuthoApi;
        $this->spotifyArtistsApi = $spotifyArtistsApi;
        $this->spotifySearchApi = $spotifySearchApi;
        $this->spotifyAlbumsApi = $spotifyAlbumsApi;
        $this->spotifyTracksApi = $spotifyTracksApi;
        $this->spotifyEpisodesApi = $spotifyEpisodesApi;
        $this->spotifyPlaylistsApi = $spotifyPlaylistsApi;
        $this->soundCloudSearchApi = $soundCloudSearchApi;
        $this->geniusApi = $geniusApi;
        $this->lastFmArtistsApi = $lastFmArtistsApi;
        $this->lastFmAlbumsApi = $lastFmAlbumsApi;
        $this->friendsApi = $friendsApi;
        $this->usersApi = $usersApi;
        $this->sessionsApi = $sessionsApi;
        $this->playlistsApi = $playlistsApi;
        $this->commentsApi = $commentsApi;
    }

    public function handleRequest() {
        // $token = $_POST['token'] ?? '';
        // if (empty($token) || !$this->appService->checkSessionToken($token)) {
        //     return $this->jsonResponse('invalid_token', [], 401);
        // }

        $action = $_POST['action'] ?? '';
        $data = $_POST['data'] ?? [];

        switch ($action) {
            case 'getNotifications':
                return $this->getNotifications($data);
            case 'getLightSearch':
                return $this->getLightSearch($data);
            case 'getHardSearch':
                return $this->getHardSearch($data);
            case 'likeAlbum':
                return $this->likeAlbum($data);
            case 'likeArtist':
                return $this->likeArtist($data);
            case 'likeTrack':
                return $this->likeTrack($data);
            case 'likeUser':
                return $this->likeUser($data);
            case 'recommendArtist':
                return $this->recommendArtist($data);
            case 'recommendAlbum':
                return $this->recommendAlbum($data);
            case 'recommendPlaylist':
                return $this->recommendPlaylist($data);
            case 'lazyLoading_recommendedReleases':
                return $this->lazyLoadingRecommendedReleases($data);
            case 'lazyLoading_newReleases':
                return $this->lazyLoadingNewReleases($data);
            case 'lazyLoading_mySubscriptions':
                return $this->lazyLoadingMySubscriptions($data);
            case 'lazyLoading_myFriends':
                return $this->lazyLoadingMyFriends($data);
            case 'lazyLoading_myCollection':
                return $this->lazyLoadingMyCollection($data);
            case 'lazyLoading_myFavourites':
                return $this->lazyLoadingMyFavourites($data);
            case 'getSoundCloudId':
                return $this->getTrackSoundCloudId($data);
            case 'saveCurrentPlayer':
                return $this->saveCurrentPlayer($data);
            case 'getCurrentPlayer':
                return $this->getCurrentPlayer();
            case 'getMyCollection':
                return $this->getMyCollection($data);
            case 'getMySubscriptions':
                return $this->getMySubscriptions($data);
            case 'getArtistInfo':
                return $this->getArtistInfo($data);
            case 'getArtistStat':
                return $this->getArtistStat($data);
            case 'getAlbumStat':
                return $this->getAlbumStat($data);
            case 'getTrackStatusInFavourites':
                return $this->getTrackStatusInFavourites($data);
            case 'updatePrivateSettings':
                return $this->updatePrivateSettings($data);
            case 'updateName':
                return $this->updateName($data);
            case 'getCommunitySearch':
                return $this->getCommunitySearch($data);
            case 'sendComment':
                return $this->sendComment($data);
            case 'commentsLazyLoading':
                return $this->commentsLazyLoading($data);
            default:
                return $this->jsonResponse('invalid_action', [], 400);
        }
    }
    private function commentsLazyLoading($data){
        $commentsModel = new CommentsModel($this->commentsApi);
        $comments = $commentsModel->getItemComments($data['object'], (int) $data['limit'], (int) $data['offset']);
        if (count($comments) == 0){
            return $this->jsonResponse('end_of_stack', []);
        }
        $client = $this->twig->render('comments_tree.twig', [
            'comments' => $comments,
            'object' => [
                'id' => $data['object']['id'],
                'type' => $data['object']['type']
            ]
        ]);
        return $this->jsonResponse('success', [
            'comments' => $comments,
            'client' => $client,
            'offset' => (int) $data['offset'] + (int) $data['limit']
        ]);
    }
    private function sendComment($data){
        $accountData = $this->getAccountData(false, false, true);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }
        $commentsModel = new CommentsModel($this->commentsApi);
        if (!$commentsModel->sendMessage($data, $accountData)){ 
            return $this->jsonResponse('error', []);
        }
        return $this->jsonResponse('success', []);
    }
    private function getCommunitySearch($data){
        $usersModel = new UsersModel(
            $this->decoderService,
            $this->usersApi,
            $this->subscriptionsApi,
            $this->collectionsApi,
            $this->favouritesApi,
            $this->releasesApi,
            $this->artistsApi,
            $this->tracksApi
        );

        return $this->jsonResponse('success', [
            'users' => $usersModel->searchUsers([
                'search' => $data['value'],
                'offset' => 0,
                'limit' => 10
            ]),
        ]);
    }
    private function updateName($data){
        $accountData = $this->getAccountData(false, false, false, false);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }
        $accountModel = $this->getAccountModel();
        $result = $accountModel->updateName((string) $data['name']);
        if ($result == false){ return $this->jsonResponse('error', [], 400); }
        return $this->jsonResponse('success', ['action' => $result], 200);
    }
    private function updatePrivateSettings($data){
        $accountData = $this->getAccountData(false, false, false, false);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }
        $accountModel = $this->getAccountModel();
        $result = $accountModel->updatePrivateSettings($data['type'], $data['value']);
        if ($result == false){ return $this->jsonResponse('error', [], 400); }
        return $this->jsonResponse('success', ['action' => $result], 200);
    }
    private function getTrackStatusInFavourites($data){
        $accountData = $this->getAccountData(false, false, true);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }
        $favourites = array_values($accountData['favourites']['tracks']);
        return $this->jsonResponse('success', [
            'isTrackInFavourites' => in_array($data['id'], $favourites)
        ]);
    }
    private function getAlbumStat($data){
        $lastFmAlbumsModel = new LastFMAlbumsModel($this->lastFmAlbumsApi);
        $result = $lastFmAlbumsModel->getAlbumStat($data);
        if ($result == null){return $this->jsonResponse('not_found', []);}
        return $this->jsonResponse('success', [
            'info' => $result
        ]);
    }

    private function getArtistStat($data){
        $lastFmArtistsModel = new LastFMArtistsModel($this->lastFmArtistsApi, $this->artistsApi);
        $result = $lastFmArtistsModel->getArtistStat($data);
        if ($result == null){return $this->jsonResponse('not_found', []);}
        return $this->jsonResponse('success', [
            'info' => $result
        ]);
    }
    private function getArtistInfo($data){
        $geniusModel = new GeniusArtistModel($this->decoderService, $this->geniusApi);
        $result = $geniusModel->getArtist($data['name']);
        if ($result == null){return $this->jsonResponse('not_found', []);}
        return $this->jsonResponse('success', [
            'info' => $result
        ]);
    }
    private function getMySubscriptions($data){
        $accountData = $this->getAccountData(true, false, false);
        if (!$accountData){
            return $this->jsonResponse('autho_error', []);
        }
        $artistsModel = new ArtistsModel(
            $this->artistsApi
        );
        $allSubscriptions = $artistsModel->getArtistsFromMySubs(['user' => $accountData, 'limit' => (int) $data['limit']]);
        return $this->jsonResponse('success', [
            'subscriptions' => $allSubscriptions
        ]);
    }
    private function getMyCollection($data){
        $accountData = $this->getAccountData(false, true, false);
        if (!$accountData){
            return $this->jsonResponse('autho_error', []);
        }
        $releasesModel = new ReleasesModel($this->releasesApi);
        $allCollection = $releasesModel->getReleasesFromMyCollection(['user' => $accountData, 'limit' => (int) $data['limit']]);
        return $this->jsonResponse('success', [
            'collection' => $allCollection
        ]);
    }

    private function saveCurrentPlayer($data) {
        $accountModel = $this->getAccountModel();
        $result = $accountModel->saveCurrentPlayer($data['currentPlayer']);
        if (!$result){
            return $this->jsonResponse('error', []);
        }
        return $this->jsonResponse('success', []);
    }
    private function getCurrentPlayer() {
        $accountData = $this->getAccountData(false, false, false);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }
        $player = $accountData['currentPlayer'];
        if ($player == null){ return $this->jsonResponse('invalid_player', []); }
        return $this->jsonResponse('success', [
            'currentPlayer' => $player
        ]);
    }
    private function getTrackSoundCloudId($data) {
        $soundCloudModel = new SoundCloudModel($this->soundCloudSearchApi);
        $result = $soundCloudModel->getTrackId($data['track_name'], $data['track_artists']);
        if (!$result) {
            return $this->jsonResponse('not_found', ['result' => $result]);
        }
        return $this->jsonResponse('success', ['id' => $result]);
    }
    
    private function lazyLoadingMyFavourites($data) {
        $accountData = $this->getAccountData(false, false, true);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }

        $tracksModel = new TracksModel($this->tracksApi);
        $tracks = $tracksModel->getTracksFromMyFavourites([
            'user' => $accountData,
            'limit' => (int) $data['limit'],
            'offset' => (int) $data['offset']
        ]);

        if ($tracks === null) {
            return $this->jsonResponse('end_stack');
        }

        $client = $this->twig->render('tracks.twig', [
            'tracks' => $tracks,
            'user' => $accountData
        ]);

        return $this->jsonResponse('success', [
            'client' => $client,
            'offset' => (int) $data['offset'] + (int) $data['limit']
        ]);
    }

    private function lazyLoadingMyCollection($data) {
        $accountData = $this->getAccountData(false, true, false);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }

        $releasesModel = new ReleasesModel($this->releasesApi);
        $releases = $releasesModel->getReleasesFromMyCollection([
            'user' => $accountData,
            'limit' => (int) $data['limit'],
            'offset' => (int) $data['offset']
        ]);

        if ($releases === null) {
            return $this->jsonResponse('end_stack');
        }

        $client = $this->twig->render('releases.twig', [
            'releases' => $releases
        ]);

        return $this->jsonResponse('success', [
            'client' => $client,
            'offset' => (int) $data['offset'] + (int) $data['limit']
        ]);
    }

    private function lazyLoadingMyFriends($data) {
        $accountData = $this->getAccountData(false, false, false, true);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }

        if ($accountData['friends']['users'] != null){
            $users = $this->usersApi->getSeveralUsers(['list' => $accountData['friends']['users'], 'limit' =>  (int) $data['limit'], 'offset' => (int) $data['offset']]);
        }

        if ($users === null) {
            return $this->jsonResponse('end_stack');
        }

        $client = $this->twig->render('users.twig', [
            'users' => $users
        ]);

        return $this->jsonResponse('success', [
            'client' => $client,
            'offset' => (int) $data['offset'] + (int) $data['limit']
        ]);
    }

    private function lazyLoadingMySubscriptions($data) {
        $accountData = $this->getAccountData(true, false, false);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }

        $artistsModel = new ArtistsModel($this->artistsApi);
        $artists = $artistsModel->getArtistsFromMySubs([
            'user' => $accountData,
            'limit' => (int) $data['limit'],
            'offset' => (int) $data['offset']
        ]);

        if ($artists === null) {
            return $this->jsonResponse('end_stack');
        }

        $client = $this->twig->render('artists.twig', [
            'artists' => $artists
        ]);

        return $this->jsonResponse('success', [
            'client' => $client,
            'offset' => (int) $data['offset'] + (int) $data['limit']
        ]);
    }

    private function lazyLoadingNewReleases($data) {
        $releasesModel = new ReleasesModel($this->releasesApi);
        $releases = $releasesModel->getNewReleases([
            'limit' => (int) $data['limit'],
            'offset' => (int) $data['offset']
        ]);

        if ($releases === null) {
            return $this->jsonResponse('end_stack');
        }

        $client = $this->twig->render('releases.twig', [
            'releases' => $releases
        ]);

        return $this->jsonResponse('success', [
            'client' => $client,
            'offset' => (int) $data['offset'] + (int) $data['limit']
        ]);
    }

    private function lazyLoadingRecommendedReleases($data) {
        $releasesModel = new ReleasesModel($this->releasesApi);
        $releases = $releasesModel->getRecommendReleases([
            'limit' => (int) $data['limit'],
            'offset' => (int) $data['offset']
        ]);

        if ($releases === null) {
            return $this->jsonResponse('end_stack');
        }

        $client = $this->twig->render('releases.twig', [
            'releases' => $releases
        ]);

        return $this->jsonResponse('success', [
            'client' => $client,
            'offset' => (int) $data['offset'] + (int) $data['limit']
        ]);
    }

    private function recommendPlaylist($data) {
        $playlistsModel = new PlaylistsModel($this->playlistsApi);
        $spotifyPlaylistsModel = new SpotifyPlaylistsModel($this->playlistsApi, $this->spotifyPlaylistsApi, $this->spotifyAuthoApi);

        $result = $playlistsModel->recommendPlaylist($data['id'], $spotifyPlaylistsModel);
        return $this->jsonResponse('success', ['action' => $result]);
    }

    private function recommendAlbum($data) {
        $releasesModel = new ReleasesModel($this->releasesApi);
        $spotifyAlbumsModel = new SpotifyAlbumsModel($this->releasesApi, $this->spotifyAlbumsApi, $this->spotifyAuthoApi);

        $result = $releasesModel->recommendRelease($data['id'], $spotifyAlbumsModel);
        return $this->jsonResponse('success', ['action' => $result]);
    }

    private function recommendArtist($data) {
        $artistsModel = new ArtistsModel($this->artistsApi);
        $spotifyArtistsModel = new SpotifyArtistsModel(
            $this->artistsApi,
            $this->trackersApi,
            $this->spotifyArtistsApi,
            $this->spotifyAuthoApi
        );

        $result = $artistsModel->recommendArtist($data['id'], $spotifyArtistsModel);
        return $this->jsonResponse('success', ['action' => $result]);
    }

    private function likeTrack($data) {
        $accountModel = $this->getAccountModel();
        $spotifyTracksModel = new SpotifyTracksModel($this->tracksApi, $this->spotifyTracksApi, $this->spotifyAuthoApi);

        $result = $accountModel->likeTrack($data['id'], $spotifyTracksModel);
        return $this->jsonResponse('success', ['action' => $result]);
    }

    private function likeArtist($data) {
        $accountModel = $this->getAccountModel();
        $spotifyArtistsModel = new SpotifyArtistsModel(
            $this->artistsApi,
            $this->trackersApi,
            $this->spotifyArtistsApi,
            $this->spotifyAuthoApi
        );

        $result = $accountModel->likeArtist($data['id'], $spotifyArtistsModel);
        return $this->jsonResponse('success', ['action' => $result]);
    }

    private function likeUser($data) {
        $accountModel = $this->getAccountModel();
        $result = $accountModel->likeUser($data['id']);
        return $this->jsonResponse('success', ['action' => $result]);
    }

    private function likeAlbum($data) {
        $accountModel = $this->getAccountModel();
        $spotifyAlbumsModel = new SpotifyAlbumsModel(
            $this->releasesApi,
            $this->spotifyAlbumsApi,
            $this->spotifyAuthoApi
        );

        $result = $accountModel->likeRelease($data['id'], $spotifyAlbumsModel);
        return $this->jsonResponse('success', ['action' => $result]);
    }

    private function getHardSearch($data) {
        $accountData = $this->getAccountData(true, true, true);
        $searchModel = new SearchModel($this->spotifySearchApi, $this->spotifyAuthoApi);
        $searchResults = $searchModel->universalSearch((string) $data['value'], ['artist' => 10, 'track' => 15, 'album' => 10, 'playlist' => 10]);


        $usersModel = new UsersModel(
            $this->decoderService,
            $this->usersApi,
            $this->subscriptionsApi,
            $this->collectionsApi,
            $this->favouritesApi,
            $this->releasesApi,
            $this->artistsApi,
            $this->tracksApi
        );
        $searchResults['users'] = $usersModel->searchUsers([
            'search' => $data['value'],
            'offset' => 0,
            'limit' => 10
        ]);

        $client = $this->twig->render('hardSearch.twig', [
            'user' => $accountData,
            'search' => $searchResults
        ]);

        return $this->jsonResponse('success', [
            'client' => $client,
            'clientData' => [
                'user' => $accountData,
                'search' => $searchResults
            ]
        ]);
    }

    private function getLightSearch($data) {
        $accountData = $this->getAccountData(true, true, true);

        $searchModel = new SearchModel($this->spotifySearchApi, $this->spotifyAuthoApi);
        $searchResults = $searchModel->universalSearch((string) $data['value'], ['artist' => 3, 'track' => 3, 'album' => 3]);


        $usersModel = new UsersModel(
            $this->decoderService,
            $this->usersApi,
            $this->subscriptionsApi,
            $this->collectionsApi,
            $this->favouritesApi,
            $this->releasesApi,
            $this->artistsApi,
            $this->tracksApi
        );
        $searchResults['users'] = $usersModel->searchUsers([
            'search' => $data['value'],
            'offset' => 0,
            'limit' => 3
        ]);

        $client = $this->twig->render('lightSearch.twig', [
            'user' => $accountData,
            'search' => $searchResults
        ]);

        return $this->jsonResponse('success', [
            'client' => $client,
            'clientData' => [
                'user' => $accountData,
                'search' => $searchResults
            ]
        ]);
    }

    private function getNotifications($data) {
        $accountData = $this->getAccountData(true, false, false);
        if (!$accountData) {
            return $this->jsonResponse('autho_error', [], 401);
        }

        $releasesModel = new ReleasesModel($this->releasesApi);
        $notifications = $releasesModel->getNewReleasesByArtists([
            'list' => $accountData['subscriptions']['artists']
        ]);

        $client = $this->twig->render('notifications.twig', [
            'notifications' => $notifications
        ]);

        return $this->jsonResponse('success', [
            'client' => $client
        ]);
    }

    private function getAccountData($isSubs, $isColl, $isFav, $isFriends = false) {
        $accountModel = $this->getAccountModel();
        return $accountModel->getMyAccount($isSubs, $isColl, $isFav, $isFriends);
    }
    private function getAccountModel(){
        $accountModel = new AccountModel(
            $this->accountApi,
            $this->encryptionService,
            $this->subscriptionsApi,
            $this->favouritesApi,
            $this->collectionsApi,
            $this->friendsApi,
            $this->usersApi,
            $this->sessionsApi
        );
        return $accountModel;
    }

    private function jsonResponse($status, $data = [], $error = null) {
        return json_encode([
            'status' => $status,
            'data' => $data,
            'error' => $error
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

// Инициализация зависимостей
$appService = new AppService();
$encryptionService = new EncryptionService();
$decoderService = new DecoderService();

// Определение типа устройства
$viewType = 'desktop';
if ($appService->isMobile()){
    $viewType = 'mobile';
}

$loader = new FilesystemLoader("../Views/$viewType/chunks");
$twig = new Environment($loader);

// Инициализация API
$releasesApi = new ReleasesApi($DBH);
$artistsApi = new ArtistsApi($DBH);
$tracksApi = new TracksApi($DBH);
$trackersApi = new TrackersApi($DBH);
$accountApi = new AccountApi($DBH);
$collectionsApi = new CollectionsApi($DBH);
$favouritesApi = new FavouritesApi($DBH);
$subscriptionsApi = new SubscriptionsApi($DBH);
$friendsApi = new FriendsApi($DBH);
$usersApi = new UsersApi($DBH);
$sessionsApi = new SessionsApi($DBH);
$playlistsApi = new PlaylistsApi($DBH);
$commentsApi = new CommentsApi($DBH);
$spotifyAuthoApi = new SpotifyAuthoApi();
$spotifyArtistsApi = new SpotifyArtistsApi();
$spotifySearchApi = new SpotifySearchApi();
$spotifyAlbumsApi = new SpotifyAlbumsApi();
$spotifyTracksApi = new SpotifyTracksApi();
$spotifyEpisodesApi = new SpotifyEpisodesApi();
$spotifyPlaylistsApi = new SpotifyPlaylistsApi();
$soundCloudSearchApi = new SoundCloudSearchApi();
$geniusApi = new GeniusApi();
$lastFmArtistsApi = new LastFmArtistsApi();
$lastFmAlbumsApi = new LastFmAlbumsApi();

// Создаём экземпляр контроллера
$controller = new AjaxController(
    $appService,
    $encryptionService,
    $decoderService,
    $twig,
    $releasesApi,
    $artistsApi,
    $tracksApi,
    $trackersApi,
    $accountApi,
    $collectionsApi,
    $favouritesApi,
    $subscriptionsApi,
    $spotifyAuthoApi,
    $spotifyArtistsApi,
    $spotifySearchApi,
    $spotifyAlbumsApi,
    $spotifyTracksApi,
    $spotifyEpisodesApi,
    $spotifyPlaylistsApi,
    $soundCloudSearchApi,
    $geniusApi,
    $lastFmArtistsApi,
    $lastFmAlbumsApi,
    $friendsApi,
    $usersApi,
    $sessionsApi,
    $playlistsApi,
    $commentsApi
);


// Обрабатываем запрос
$response = $controller->handleRequest();
echo $response;

