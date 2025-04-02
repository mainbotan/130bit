<?

// Настройка среды
putenv('APP_ENV=production');
$_SERVER['SCRIPT_NAME'] = basename(__FILE__);

// Логирование
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/cron_errors.log');

define("ROOT_PATH", '...');

// Подключаем конфиги и автозагрузчик
require_once ROOT_PATH. '/vendor/autoload.php';
require_once ROOT_PATH. '/App/AppConfiguration.php';

use App\Services\AppService;
use App\Services\EncryptionService;
use App\Services\DecoderService;
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
use App\Api\v1\Tracks as TracksApi;
use App\Api\v1\Trackers as TrackersApi;
use App\Api\v1\Artists as ArtistsApi;
use App\Api\v1\Releases as ReleasesApi;
use App\Api\v1\Account as AccountApi;
use App\Api\v1\Collections as CollectionsApi;
use App\Api\v1\Favourites as FavouritesApi;
use App\Api\v1\Subscriptions as SubscriptionsApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Artists as SpotifyArtistsApi;
use App\Api\v1\Spotify\Search as SpotifySearchApi;
use App\Api\v1\Spotify\Albums as SpotifyAlbumsApi;
use App\Api\v1\Spotify\Tracks as SpotifyTracksApi;
use App\Api\v1\Spotify\Episodes as SpotifyEpisodesApi;
use App\Api\v1\Spotify\Playlists as SpotifyPlaylistsApi;
use App\Api\v1\Genius\Main as GeniusApi;
use App\Api\v1\LastFM\Tags as LastFmTagsApi;

// Необходимые зависимости
$TrackersApi = new TrackersApi($DBH);
$ReleasesApi = new ReleasesApi($DBH);
$SpotifyAutho = new SpotifyAuthoApi();
$SpotifyAlbums = new SpotifyAlbumsApi();
$SpotifyArtists = new SpotifyArtistsApi();


/*[замер времени]*/
$startTime = microtime(true);

/*Получение токена*/
$access_token = $SpotifyAutho->getToken()['access_token'];

$new_releases = [];

/*Получение последних трекеров*/
$data = [
    'offset' => 0,
    'limit' => 1000
];
$trackers = $TrackersApi->getLastTrackers($data);

foreach ($trackers as $tracker){
    /*Получение последних релизов артиста*/
    $artist_id = $tracker['artist_id'];

    /*___Получение альбомов и синглов артиста___*/
    $query_data = [
        'artist_id' => $artist_id,
        'groups' => [
            'album',
            'single'
        ],
        'token' => $access_token,
        'limit' => 2,
        'offset' => 0
    ];
    $albums = $SpotifyArtists->getArtistAlbums($query_data)['items'];
    foreach ($albums as $album){
        $album_id = $album['id'];
        $album_name = $album['name'];
        /* */
        if ((!$ReleasesApi->checkReleaseById(['id' => $album_id])) & (!$ReleasesApi->checkReleaseByName(['name' => $album_name]))){
            $query_data_2 = [
                'album_id' => $album_id,
                'token' => $access_token
            ];
            $album = $SpotifyAlbums->getAlbum($query_data_2);

            $artists = [];
            foreach ($album['artists'] as $artist){
                $id = $artist['id'];
                $name = $artist['name'];
                $artists[] = [
                    'id' => $id,
                    'name' => $name
                ];
            }
            $artists = json_encode($artists, true);

            $tracks = [];
            foreach ($album['tracks']['items'] as $track){
                $track_artists_upd = [];
                foreach ($track['artists'] as $artist){
                    $track_artists_upd[] = [
                        'id' => $artist['id'],
                        'name' => $artist['name']
                    ];
                }
                $tracks[] = [
                    'uri' => $track['uri'],
                    'id' => $track['id'],
                    'name' => $track['name'],
                    'number' => (int) $track['track_number'],
                    'disc_number' => (int) $track['disc_number'],
                    'artists' => $track_artists_upd
                ];
            }
            $tracks = json_encode($tracks, true);
            $data = [
                'id' => $album['id'],
                'type' => $album['type'],
                'total_tracks' => $album['total_tracks'],
                'cover' => $album['images'][0]['url'],
                'name' => $album['name'],
                'release_date' => $album['release_date'],
                'uri' => $album['uri'],
                'artists' => $artists,
                'primary_artist_name' => $album['artists'][0]['name'],
                'primary_artist_id' => $album['artists'][0]['id'],
                'tracks' => $tracks,
                'label' => $album['label'],
                'popularity' => $album['popularity']
            ];
            $result = $ReleasesApi->createRelease($data);
            var_dump($result);
            $new_releases[] = $data;
        }
    }
}

// Окончание засекания времени
$endTime = microtime(true);
$executionTime = $endTime - $startTime;
echo "Время скрипта: $executionTime";

/*Сохранение отчётности*/
$filename = ROOT_PATH . '/App/Updates/logs/trackers-history.txt';
$current_date = date('m/d/Y h:i:s a', time());
$new_releases_count = count($new_releases);
$data = "trackers new releases count: $new_releases_count [processing $current_date; script_time: $executionTime]";
file_put_contents($filename, PHP_EOL . $data, FILE_APPEND);

