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
use App\Api\v1\Spotify\Playlists as SpotifyPlaylistsApi;
use App\Api\v1\Spotify\Tracks as SpotifyTracksApi;
use App\Api\v1\Spotify\Episodes as SpotifyEpisodesApi;
use App\Api\v1\Genius\Main as GeniusApi;
use App\Api\v1\LastFM\Tags as LastFmTagsApi;
use App\Api\v1\Playlists as PlaylistsApi;

// Необходимые зависимости
$PlaylistsApi = new PlaylistsApi($DBH);
$SpotifyAutho = new SpotifyAuthoApi();
$SpotifyPlaylists = new SpotifyPlaylistsApi();


/*[замер времени]*/
$startTime = microtime(true);

/*Получение токена*/
$access_token = $SpotifyAutho->getToken()['access_token'];

/*Получение плейлистов на обновление*/
$data = [
    'offset' => 0,
    'limit' => 1000
];
$playlists = $PlaylistsApi->getPlaylistsForUpdate($data);
$updated_playlists_count = 0;

foreach($playlists as $playlist){
    $playlist_id = $playlist['id'];

    /*___Получение плейлиста___*/
    $query_data = [
        'playlist_id' => $playlist_id,
        'token' => $access_token
    ];
    $playlist = $SpotifyPlaylists->getPlaylist($query_data);

    if ($playlist['id'] != null){

        $tracks = [];
        foreach ($playlist['tracks']['items'] as $track){
            $track = $track['track'];
            $track_artists_upd = [];
            foreach ($track['artists'] as $artist){
                $track_artists_upd[] = [
                    'id' => $artist['id'],
                    'name' => $artist['name']
                ];
            }
            if ($track['id'] != null and $track['name'] != null){
                $tracks[] = [
                    'uri' => $track['uri'],
                    'id' => $track['id'],
                    'name' => $track['name'],
                    'number' => (int) $track['track_number'],
                    'disc_number' => (int) $track['disc_number'],
                    'cover' => $track['album']['images'][0]['url'],
                    'album' => [
                        'id' => $track['album']['id'],
                        'name' => $track['album']['name'],
                    ],
                    'artists' => $track_artists_upd
                ];
            }
        }

        $owner = json_encode($playlist['owner']);

        $data = [
            'id' => $playlist['id'],
            'total_tracks' => count($tracks),
            'cover' => $playlist['images'][0]['url'],
            'description' => $playlist['description'],
            'tracks' => json_encode($tracks, true)
        ];
        $result = $PlaylistsApi->updatePlaylist($data);
        if ($result){
            $updated_playlists_count += 1;
        }
    }
}


// Окончание засекания времени
$endTime = microtime(true);
$executionTime = $endTime - $startTime;
echo "Время скрипта: $executionTime";


/*Сохранение отчётности*/
$filename = ROOT_PATH . '/App/Updates/logs/playlists-history.txt';
$current_date = date('m/d/Y h:i:s a', time());
$data = "playlists count: $updated_playlists_count / " . count($playlists) . "[processing $current_date; script_time: $executionTime]";
file_put_contents($filename, PHP_EOL . $data, FILE_APPEND);