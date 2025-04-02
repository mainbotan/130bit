<?php
session_start();

require_once '../../vendor/autoload.php';
require_once '../AppConfiguration.php';

use App\Services\AppService;
use App\Services\EncryptionService;
use App\Services\DecoderService;
use App\Models\Comments as CommentsModel;
use App\Models\Users as UsersModel;
use App\Models\Viewer as ViewerModel;
use App\Models\Album as AlbumModel;
use App\Models\Releases as ReleasesModel;
use App\Models\Artist as ArtistModel;
use App\Models\Artists as ArtistsModel;
use App\Models\Account as AccountModel;
use App\Models\Track as TrackModel;
use App\Models\Tracks as TracksModel;
use App\Models\Search as SearchModel;
use App\Models\LastFMTag as LastFmTagModel;
use App\Models\Playlist as PlaylistModel;
use App\Models\Playlists as PlaylistsModel;
use App\Api\v1\Comments as CommentsApi;
use App\Api\v1\Sessions as SessionsApi;
use App\Api\v1\Users as UsersApi;
use App\Api\v1\Tracks as TracksApi;
use App\Api\v1\Artists as ArtistsApi;
use App\Api\v1\Releases as ReleasesApi;
use App\Api\v1\Playlists as PlaylistsApi;
use App\Api\v1\Account as AccountApi;
use App\Api\v1\Collections as CollectionsApi;
use App\Api\v1\Favourites as FavouritesApi;
use App\Api\v1\Subscriptions as SubscriptionsApi;
use App\Api\v1\Friends as FriendsApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Artists as SpotifyArtistsApi;
use App\Api\v1\Spotify\Search as SpotifySearchApi;
use App\Api\v1\Spotify\Albums as SpotifyAlbumsApi;
use App\Api\v1\Spotify\Tracks as SpotifyTracksApi;
use App\Api\v1\Spotify\Episodes as SpotifyEpisodesApi;
use App\Api\v1\Spotify\Playlists as SpotifyPlaylistsApi;
use App\Api\v1\Genius\Main as GeniusApi;
use App\Api\v1\LastFM\Tags as LastFmTagsApi;


class MainController {
    private $deviceType;
    private $encryptionService;
    private $decoderService;
    private $appService;
    private $twig;
    private $releasesApi;
    private $artistsApi;
    private $tracksApi;
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
    private $geniusApi;
    private $lastFmTagsApi;
    private $playlistsApi;
    private $usersApi;
    private $friendsApi;
    private $sessionsApi;
    private $commentsApi;

    public function __construct(
        $deviceType,
        EncryptionService $encryptionService,
        DecoderService $decoderService,
        AppService $appService,
        \Twig\Environment $twig,
        ReleasesApi $releasesApi,
        ArtistsApi $artistsApi,
        TracksApi $tracksApi,
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
        GeniusApi $geniusApi,
        LastFmTagsApi $lastFmTagsApi,
        PlaylistsApi $playlistsApi,
        UsersApi $usersApi,
        FriendsApi $friendsApi,
        SessionsApi $sessionsApi,
        CommentsApi $commentsApi
    ) {
        $this->deviceType = $deviceType;
        $this->encryptionService = $encryptionService;
        $this->decoderService = $decoderService;
        $this->appService = $appService;
        $this->twig = $twig;
        $this->releasesApi = $releasesApi;
        $this->artistsApi = $artistsApi;
        $this->tracksApi = $tracksApi;
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
        $this->geniusApi = $geniusApi;
        $this->lastFmTagsApi = $lastFmTagsApi;
        $this->playlistsApi = $playlistsApi;
        $this->usersApi = $usersApi;
        $this->friendsApi = $friendsApi;
        $this->sessionsApi = $sessionsApi;
        $this->commentsApi = $commentsApi;
    }

    public function handleRequest($token, $url) {
        // if (!$this->appService->checkSessionToken($token)) {
        //     return $this->jsonResponse('invalid_token');
        // }

        $parts = parse_url($url);
        if ($parts === false) {
            return $this->jsonResponse('invalid_url');
        }

        parse_str($parts['query'], $urlParam);
        $goal = $urlParam['x'] ?? null;

        switch ($goal) {
            case null:
            case 'viewer':
                $accountData = $this->getAccountData(true, true, true);
                if (!$accountData) {
                    $my_releases = [];
                }else{
                    $releasesModel = new ReleasesModel($this->releasesApi);
                    $my_releases = $releasesModel->getNewReleasesByArtists([
                        'list' => $accountData['subscriptions']['artists']
                    ]);
                }
                $viewerModel = new ViewerModel($this->artistsApi, $this->playlistsApi);
                $releasesModel = new ReleasesModel($this->releasesApi);
                $artistsModel = new ArtistsModel($this->artistsApi);
                $playlistsModel = new PlaylistsModel($this->playlistsApi);

                $data = [
                    'user' => $accountData,
                    'artists_zone' => $viewerModel->getArtistsZone(['limit' => 8]),
                    'new_releases' => $releasesModel->getNewReleases(['limit' => 10]),
                    'recommend_releases' => $releasesModel->getRecommendReleases(['limit' => 12]),
                    'my_releases' => $my_releases,
                    'top_artists_releases' => $releasesModel->getLatestReleasesByArtists($artistsModel->getArtistsByPopularity(10)),
                    'recommend_playlists' => $playlistsModel->getRecommendedPlaylists(10, 0),
                    'playlists_by_countries' => $playlistsModel->getPlaylistsByMeta('country', 10, 0),
                    'playlists_by_genres' => $playlistsModel->getPlaylistsByMeta('genre', 10, 0),
                    'playlists_by_artists' => $playlistsModel->getPlaylistsByMeta('artist', 10, 0),
                    'playlists_by_mood' => $playlistsModel->getPlaylistsByMeta('mood', 10, 0),
                    'playlists_by_unreleased' => $playlistsModel->getPlaylistsByMeta('unreleased', 10, 0),
                    'usa_chart' => $playlistsModel->getAppleMusicChart()
                ];
                $client = $this->twig->render('viewer.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            case 'discover':
                $accountData = $this->getAccountData(true, false, false, false);
                $viewerModel = new ViewerModel($this->artistsApi, $this->playlistsApi);
                $releasesModel = new ReleasesModel($this->releasesApi);

                $data = [
                    'artists_zone' => $viewerModel->getArtistsZone(['limit' => 30]),
                    'user' => $accountData
                ];
                $client = $this->twig->render('discover.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            case 'new-releases':
                $releasesModel = new ReleasesModel($this->releasesApi);
                $startOffset = 40;
                $data = [
                    'new_releases' => $releasesModel->getNewReleases(['limit' => $startOffset]),
                    'start_offset' => $startOffset
                ];
                $client = $this->twig->render('new_releases.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);

            case 'recommend-releases':
                $releasesModel = new ReleasesModel($this->releasesApi);
                $startOffset = 40;
                $data = [
                    'recommend_releases' => $releasesModel->getRecommendReleases(['limit' => $startOffset]),
                    'start_offset' => $startOffset
                ];
                $client = $this->twig->render('recommend_releases.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);

            case 'album':
                $album_id = $urlParam['id'];
                if ($album_id == null){
                    $searchModel = new SearchModel($this->spotifySearchApi, $this->spotifyAuthoApi);
                    $album_id = $searchModel->universalSearch((string) $urlParam['name'] . ' - ' . (string) $urlParam['artist'], ['album' => 1])['albums'][0]['id'];
                    if ($album_id == null){
                        return $this->jsonResponse('not_found', []);
                    }
                }

                $albumModel = new AlbumModel(
                    $this->releasesApi,
                    $this->spotifyAlbumsApi,
                    $this->spotifyArtistsApi,
                    $this->spotifyAuthoApi
                );
                $albumData = $albumModel->getAlbum(['id' => $album_id]);

                $accountData = $this->getAccountData(true, true, true);

                $commentsModel = new CommentsModel($this->commentsApi);

                $data = [
                    'album' => $albumData,
                    'user' => $accountData,
                    'comments' => $commentsModel->getItemComments(['id' => $album_id, 'type' => 'album'], 3, 0)
                ];
                $client = $this->twig->render('album.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);

            case 'playlist':
                $playlist_id = $urlParam['id'];

                $playlistModel = new PlaylistModel(
                    $this->playlistsApi,
                    $this->spotifyPlaylistsApi,
                    $this->spotifyArtistsApi,
                    $this->spotifyAuthoApi
                );
                $playlistData = $playlistModel->getPlaylist(['id' => $playlist_id]);

                $accountData = $this->getAccountData(true, true, true);

                $data = [
                    'playlist' => $playlistData,
                    'user' => $accountData
                ];
                $client = $this->twig->render('playlist.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            case 'artist':
                $artist_id = $urlParam['id'];
                if ($artist_id == null){
                    $searchModel = new SearchModel($this->spotifySearchApi, $this->spotifyAuthoApi);
                    $artist_id = $searchModel->universalSearch((string) $urlParam['name'], ['artist' => 1])['artists'][0]['id'];
                    if ($artist_id == null){
                        return $this->jsonResponse('not_found', []);
                    }
                }

                $artistModel = new ArtistModel(
                    $this->artistsApi,
                    $this->spotifyAlbumsApi,
                    $this->spotifyArtistsApi,
                    $this->spotifyAuthoApi
                );
                $data = [
                    'id' => $artist_id,
                    'isReleases' => true,
                    'isTopTracks' => true,
                    'isAppearsOn' => true
                ];
                $artistData = $artistModel->getArtist($data);

                $accountData = $this->getAccountData(true, false, true);

                $data = [
                    'artist' => $artistData,
                    'user' => $accountData
                ];
                $client = $this->twig->render('artist.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);

            case 'track':
                $trackModel = new TrackModel(
                    $this->decoderService,
                    $this->tracksApi,
                    $this->geniusApi,
                    $this->spotifyTracksApi,
                    $this->spotifyAuthoApi
                );
                $data = [
                    'id' => $urlParam['id'],
                    'isGenius' => true
                ];
                $trackData = $trackModel->getTrack($data);

                $accountData = $this->getAccountData(false, false, true);

                $data = [
                    'track' => $trackData,
                    'user' => $accountData
                ];
                $client = $this->twig->render('track.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);

            case 'profile':
                $accountData = $this->getAccountData(true, true, true, true);
                if ($accountData) {
                    $artistsModel = new ArtistsModel(
                        $this->artistsApi
                    );
                    $releasesModel = new ReleasesModel($this->releasesApi);
                    $tracksModel = new TracksModel(
                        $this->tracksApi
                    );
                }
                if ($accountData['friends']['users'] != null){
                    $accountData['friends']['users'] = $this->usersApi->getSeveralUsers(['list' => $accountData['friends']['users'], 'limit' => 20, 'offset' => 0]);
                }
                $data = [
                    'user' => $accountData,
                    'subscriptions' => $accountData['subscriptions'] ?? null,
                    'collection' => $accountData['collection'] ?? null,
                    'favourites' => $accountData['favourites'] ?? null
                ];
                $client = $this->twig->render('profile.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            case 'profile-edit':
                $accountData = $this->getAccountData(false, false, false, false);
                $data = [
                    'user' => $accountData
                ];
                $client = $this->twig->render('profile-edit.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            case 'collection':
                $accountData = $this->getAccountData(true, true, true, true);
                if ($accountData) {
                    $artistsModel = new ArtistsModel(
                        $this->artistsApi
                    );
                    $releasesModel = new ReleasesModel($this->releasesApi);
                    $tracksModel = new TracksModel(
                        $this->tracksApi
                    );

                    $startOffset = 20;
                    $allSubscriptions = $artistsModel->getArtistsFromMySubs(['user' => $accountData]);
                    $allCollection = $releasesModel->getReleasesFromMyCollection(['user' => $accountData]);
                    $allFavourites = $tracksModel->getTracksFromMyFavourites(['user' => $accountData, 'limit' => $startOffset]);

                    if ($accountData['friends']['users'] != null){
                        $accountData['friends']['users'] = $this->usersApi->getSeveralUsers(['list' => $accountData['friends']['users'], 'limit' => 20, 'offset' => 0]);
                    }
                    $allFriends = $accountData['friends']['users'];
                }

                $data = [
                    'user' => $accountData,
                    'all_subscriptions' => $allSubscriptions ?? null,
                    'all_collection' => $allCollection ?? null,
                    'all_favourites' => $allFavourites ?? null,
                    'all_friends' => $allFriends ?? null,
                    'start_offset' => $startOffset
                ];
                $client = $this->twig->render('collection.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            case 'my-subscriptions':
                $accountData = $this->getAccountData(true, false, false);
                if ($accountData) {
                    $artistsModel = new ArtistsModel(
                        $this->artistsApi
                    );
                    $startOffset = 40;
                    $allSubscriptions = $artistsModel->getArtistsFromMySubs(['user' => $accountData, 'limit' => $startOffset]);
                }

                $data = [
                    'user' => $accountData,
                    'start_offset' => $startOffset,
                    'all_subscriptions' => $allSubscriptions ?? null
                ];
                $client = $this->twig->render('my_subscriptions.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);

            case 'my-collection':
                $accountData = $this->getAccountData(false, true, false);
                if ($accountData) {
                    $releasesModel = new ReleasesModel($this->releasesApi);
                    $startOffset = 40;
                    $allCollection = $releasesModel->getReleasesFromMyCollection(['user' => $accountData, 'limit' => $startOffset]);
                }

                $data = [
                    'user' => $accountData,
                    'start_offset' => $startOffset,
                    'all_collection' => $allCollection ?? null
                ];
                $client = $this->twig->render('my_collection.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client,
                    'clientData' => $data
                ]);
            case 'tag':
                $tagModel = new LastFmTagModel($this->lastFmTagsApi);
                $tag = $tagModel->getTag($urlParam['name']);
                if ($tag['info'] == null){
                    return $this->jsonResponse('success', []);
                }else{
                    $client = $this->twig->render('tag.twig', $tag);
                }
                
                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            case 'user':
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
                if ($urlParam['id'] == null){
                    $userData = $usersModel->getUser(['name' => $urlParam['name']]);   
                }else{
                    $userData = $usersModel->getUser(['id' => $urlParam['id']]);   
                }

                $accountData = $this->getAccountData(false, false, false, true);

                $data = [
                    'user' => $userData,
                    'account' => $accountData
                ];
                $client = $this->twig->render('user.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            
            case 'my-friends':
                $accountData = $this->getAccountData(true, true, true, true);
                if ($accountData) {
                    $artistsModel = new ArtistsModel(
                        $this->artistsApi
                    );
                    $releasesModel = new ReleasesModel($this->releasesApi);
                    $tracksModel = new TracksModel(
                        $this->tracksApi
                    );
                }
                if ($accountData['friends']['users'] != null){
                    $startOffset = 20;
                    $allFriends = $this->usersApi->getSeveralUsers(['list' => $accountData['friends']['users'], 'limit' => $startOffset, 'offset' => 0]);
                }
                $data = [
                    'user' => $accountData,
                    'all_friends' => $allFriends ?? null,
                    'start_offset' => $startOffset ?? null
                ];
                $client = $this->twig->render('my_friends.twig', $data);

                return $this->jsonResponse('success', [
                    'client' => $client
                ]);
            default:
                return $this->jsonResponse('not_found', [], 404);
        }
    }

    private function getAccountData($isSubs, $isColl, $isFav, $isFriends = false) {
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
        return $accountModel->getMyAccount($isSubs, $isColl, $isFav, $isFriends);
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
$encryptionService = new EncryptionService();
$decoderService = new DecoderService(); 
$appService = new AppService();
$viewType = 'desktop';
if ($appService->isMobile()){
    $viewType = 'mobile';
}

$loader = new \Twig\Loader\FilesystemLoader("../Views/$viewType");
$twig = new \Twig\Environment($loader);

// Инициализация API
$releasesApi = new ReleasesApi($DBH);
$artistsApi = new ArtistsApi($DBH);
$tracksApi = new TracksApi($DBH);
$accountApi = new AccountApi($DBH);
$collectionsApi = new CollectionsApi($DBH);
$favouritesApi = new FavouritesApi($DBH);
$subscriptionsApi = new SubscriptionsApi($DBH);
$playlistsApi = new PlaylistsApi($DBH);
$usersApi = new UsersApi($DBH);
$friendsApi = new FriendsApi($DBH);
$sessionsApi = new SessionsApi($DBH);
$commentsApi = new CommentsApi($DBH);
$spotifyAuthoApi = new SpotifyAuthoApi();
$spotifyArtistsApi = new SpotifyArtistsApi();
$spotifySearchApi = new SpotifySearchApi();
$spotifyAlbumsApi = new SpotifyAlbumsApi();
$spotifyTracksApi = new SpotifyTracksApi();
$spotifyEpisodesApi = new SpotifyEpisodesApi();
$spotifyPlaylistsApi = new SpotifyPlaylistsApi();
$geniusApi = new GeniusApi();
$lastFmTagsApi = new LastFmTagsApi();

// Создаём экземпляр контроллера
$controller = new MainController(
    $viewType,
    $encryptionService,
    $decoderService,
    $appService,
    $twig,
    $releasesApi,
    $artistsApi,
    $tracksApi,
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
    $geniusApi,
    $lastFmTagsApi,
    $playlistsApi,
    $usersApi,
    $friendsApi,
    $sessionsApi,
    $commentsApi
);

// Обрабатываем запрос
$response = $controller->handleRequest($_POST['token'] ?? '', $_POST['url'] ?? '');
echo $response;