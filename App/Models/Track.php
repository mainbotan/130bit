<?

namespace App\Models;

use App\Services\DecoderService;
use App\Api\v1\Tracks as TracksApi;
use App\Api\v1\Genius\Main as GeniusApi;
use App\Api\v1\Spotify\Tracks as SpotifyTracksApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;

class Track{
    private $decoder__Service;
    private $tracks__Api;
    private $genius__Api;
    private $spotify__Tracks;
    private $spotify__Autho;
    public function __construct(
        DecoderService $decoderService, 
        TracksApi $tracksApi, 
        GeniusApi $geniusApi, 
        SpotifyTracksApi $spotifyTracksApi, 
        SpotifyAuthoApi $spotifyAuthoApi
    ){
        $this->tracks__Api = $tracksApi; 
        $this->genius__Api = $geniusApi;
        $this->spotify__Tracks = $spotifyTracksApi;
        $this->spotify__Autho = $spotifyAuthoApi;
        $this->decoder__Service = $decoderService;
    }  
    public function getTrack(array $data){
        $track_id = $data['id'];
        $track = $this->getFromStorage($track_id);
        if ($track){
            $track = [
                'id' => $track['id'],
                'isrc' => $track['isrc'],
                'artists' => $track['artists'],
                'cover' => $track['cover'],
                'album_name' => $track['album_name'],
                'album_id' => $track['album_id'],
                'explicit' => (bool) $track['explicit'],
                'name' => $track['name'],
                'popularity' => $track['popularity'],
                'release_date' => $track['release_date'],
                'type' => $track['type'],
                'uri' => $track['uri']
            ];
        }else{
            $token = $this->spotify__Autho->getToken()['access_token'];
            $track = $this->spotify__Tracks->getTrack(['track_id' => $track_id, 'token' => $token]);
            if ($track['id'] != null){
                $track = [
                    'id' => $track['id'],
                    'isrc' => $track['external_ids']['isrc'],
                    'artists' => $track['artists'],
                    'cover' => $track['album']['images'][0]['url'],
                    'album_name' => $track['album']['name'],
                    'album_id' => $track['album']['id'],
                    'explicit' => (bool) $track['explicit'],
                    'name' => $track['name'],
                    'popularity' => $track['popularity'],
                    'release_date' => $track['album']['release_date'],
                    'type' => $track['type'],
                    'uri' => $track['uri']
                ];
            }else{
                return false;
            }
        }
        $track_name_count = mb_strlen($track['name']);
        if ($track_name_count >= 14){
            $track_name_size = 'Mini';
        }else{
            if ($track_name_count >= 8){
                $track_name_size = 'Middle';
            }else{
                $track_name_size = 'Max'; 
            }
        }
        $track['name_size'] = $track_name_size; 
        if ($data['isGenius']){
            /*___Получение ID трека по названию+артисту___*/
            $query_data = [
                'song_title' => $this->normalizeName($track['name']),
                'artist_name' => $this->normalizeName($track['artists'][0]['name'])
            ];
            $result = $this->genius__Api->getSongsByTitleAndArtist($query_data);
            $query_data['result'] = $result;
            $song_id = $this->getFirstGeniusSongId($query_data);

            if ($song_id){
                /*________Получение сведений о треке_________*/
                $query_data = [
                    'song_id' => $song_id
                ];
                $genius_track = $this->genius__Api->getSong($query_data);
                if ($genius_track){       
                    if ($genius_track['description'] != null){
                        $genius_track['description'] = $this->decoder__Service->decode($genius_track['description']);
                    }
                    $genius = [
                        'id' => $song_id,
                        'lyrics' => $genius_track['embed_content'],
                        'artists_names' => $genius_track['artist_names'],
                        'description' => $genius_track['description'],
                        'full_title' => $genius_track['full_title'],
                        'primary_color' => $genius_track['song_art_primary_color'],
                        'secondary_color' => $genius_track['song_art_secondary_color'],
                        'text_color' => $genius_track['song_art_text_color'],
                        'release_date_for_display' => $genius_track['release_date_for_display'],
                        'custom_performances' => $genius_track['custom_performances']
                    ];
                    $track['genius'] = $genius;
                }
            }
        }
        return $track;
    }
    public function getFirstGeniusSongId($data){
        $result = $data['result'];
        $artistName = $data['artist_name'];
        $songName = $data['song_title'];
        /*Логика нахождения id*/
        $songId = null;
        foreach ($result as $key => $res2){
            $finger_title = $this->stringFinger($res2['title']);
            $finger_songName = $this->stringFinger($songName);
            if (strpos($finger_title, $finger_songName) !== false){
                foreach ($res2['artists'] as $name){
                    $finger_name = $this->stringFinger($name);
                    $finger_artistName = $this->stringFinger($artistName);

                    if (strpos($finger_name, $finger_artistName) !== false){
                        $songId = $res2['id'];
                        $songInfo = $res2['info'];
                        break;
                    }
                }
            }
            if ($songId != null){
                break;
            }
        }
        return $songId;
    }
    // Промежуточные сервисы, присущие методу
    private function stringFinger($input) {
        $lowercase = mb_strtolower($input);
        $string = str_replace(' ', '', $lowercase);
        return $string;
    }
    private function normalizeName(string $string): string
    {
        $string = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);
        $string = str_replace(["'", '"', '‘', '’', '“', '”'], '', $string);
        $pattern = '/\([^)]*\)/';
        return preg_replace($pattern, '', $string);
    }
    public function getFromStorage($id){
        $track = $this->tracks__Api->getTrackById(['id' => $id]);
        return $track;
    }
}