<?php
session_start();

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// Подключение конфига
require_once __DIR__ . '/App/AppConfiguration.php';

// Подключаем конфиги и автозагрузчик
require_once __DIR__ . '/vendor/autoload.php';

use App\Services\AppService;
use App\Services\EncryptionService;
use App\Services\MetaService;
use App\Services\DecoderService as DecoderService;
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

// Инициализируем сервисы
$AppService = new AppService();
$encryptionService = new EncryptionService();
$decoderService = new DecoderService();

// Отдельно для Meta конструктора
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
$MetaService = new MetaService(
    $encryptionService,
    $releasesApi,
    $artistsApi,
    $tracksApi,
    $spotifyAuthoApi,
    $spotifyArtistsApi,
    $spotifySearchApi,
    $spotifyAlbumsApi,
    $spotifyTracksApi,
    $spotifyEpisodesApi,
    $spotifyPlaylistsApi,
    $playlistsApi,
    $usersApi,
    $geniusApi,
    $decoderService,
    $collectionsApi, 
    $favouritesApi,
    $subscriptionsApi
);

// Создаём токен сессии
$AppService->createSessionToken();

// Инициализируем модель
$accountModel = new AccountModel($accountApi, $encryptionService, $subscriptionsApi, $favouritesApi, $collectionsApi, $friendsApi, $usersApi, $sessionsApi);

// Получаем данные аккаунта
$accountData = $accountModel->getMyAccount(false, false, false, false);


// Определяем тип устройства
$isMobile = $AppService->isMobile();
$isIphone = $AppService->isIphone();
if ($isMobile){
    $view_type = 'mobile';
}else{
    $view_type = 'desktop';
}

// echo "<pre>";
// var_dump($MetaService->handleRequest());
// echo "</pre>";

// Рендерим окружение
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/App/Views/$view_type");
$twig = new \Twig\Environment($loader);

echo $twig->render('environment.twig', [
    'isMobile' => $isMobile,
    'isIphone' => $isIphone,
    'accountData' => $accountData,
    'isUser' => (bool) $accountData,
    'sessionToken' => $_SESSION['token'],
    'meta' => $MetaService->handleRequest()
]);
