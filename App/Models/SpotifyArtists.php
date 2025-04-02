<?

namespace App\Models;

use App\Api\v1\Artists as ArtistsApi;
use App\Api\v1\Trackers as TrackersApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Artists as SpotifyArtistsApi;
use DateTime;

class SpotifyArtists{
    private $artists__Api;
    private $trackers__Api;
    private $spotify__Autho;
    private $spotify__Artists;
    public function __construct(
        ArtistsApi $artistsApi, 
        TrackersApi $trackersApi, 
        SpotifyArtistsApi $spotifyArtistsApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->artists__Api = $artistsApi;
        $this->trackers__Api = $trackersApi;
        $this->spotify__Artists = $spotifyArtistsApi;
        $this->spotify__Autho = $spotifyAuthoApi;
    }

    public function getArtistFromStorage($id){
        return $this->artists__Api->getArtistById(['id' => $id]);
    }

    // @param (int) ID альбом
    // @return (bool) Результат сохранения

    public function saveItem($artist_id){
        if ($this->getArtistFromStorage($artist_id)){
            return true;
        }else{
            /*Получение токена*/
            $access_token = $this->spotify__Autho->getToken()['access_token'];

            /*___Получение артиста___*/
            $query_data = [
                'artist_id' => $artist_id,
                'token' => $access_token
            ];
            $artist = $this->spotify__Artists->getArtistInfo($query_data);

            if ($artist['name'] != null){
                $artist_id = $artist['id'];
                $artist_name = $artist['name'];
                $artist_popularity = (int) $artist['popularity'];
                $artist_images = $artist['images'];
                $artist_cover = $artist_images[0]['url'];
                $artist_followers = (int) $artist['followers']['total'];

                /*Преобразование изображений*/
                $upd_images = [];
                foreach ($artist_images as $img){
                    $size = $img['width'];
                    $url = $img['url'];
                    $upd_images[$size] = $url;
                }
                $picture_small = $upd_images['320'];
                $picture_big = $upd_images['640'];

                $data = [
                    'id' => $artist_id,
                    'name' => $artist_name,
                    'popularity' => $artist_popularity,
                    'followers' => $artist_followers,
                    'picture_small' => $picture_small,
                    'picture_big' => $picture_big  
                ];
                

                if ($this->artists__Api->createArtist($data)){
                    // Создание трекера отслеживания релизов
                    $data = [
                        'artist_id' => $artist_id
                    ];
                    $this->trackers__Api->createTracker($data);
                    return true;
                }else{
                    return false;
                }
            }
            return false;
        }
    }

    // Вспомогательные функции 
    // Перенести в сервисы
    function escapeHtml($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    function formatDate($date) {
        $datetime = new DateTime($date);
        return $datetime->format('Y-m-d');
    }
}