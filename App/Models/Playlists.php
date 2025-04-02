<?

namespace App\Models;

use App\Api\v1\Playlists as PlaylistsApi;

class Playlists{
    const AppleMusicPlaylistId = '2khn1j2muqFCP1bZveUgEt';
    private $playlistsApi;
    public function __construct(
        PlaylistsApi $playlistsApi
    ){
        $this->playlistsApi = $playlistsApi;
    }

    /**
     * Получение чарта us с Apple Music
     */
    public function getAppleMusicChart(){
        $result = $this->playlistsApi->getPlaylistById(['id' => self::AppleMusicPlaylistId]);
        return $result;
    }


    /**
     * Получение плейлистов по ключевому слову
     */
    public function getPlaylistsByMeta(string $meta, int $limit, int $offset){
        return $this->playlistsApi->getPlaylistsByMeta(
            [
                'limit' => $limit, 'offset' => $offset, 'meta' => $meta
            ]
        );
    }
    public function getRecommendedPlaylists(int $limit, int $offset){
        return $this->playlistsApi->getRecommendedPlaylists(['limit' => $limit, 'offset' => $offset]);
    }


    // Рекомендация релиза
    public function recommendPlaylist($playlist_id, $spotifyPlaylists__Model){
        if ($playlist_id == null){ return null; }
        $playlist = $this->playlistsApi->getPlaylistById(['id' => $playlist_id]);
        if (!$playlist){
            if (!$spotifyPlaylists__Model->saveItem($playlist_id)){
                return false;
            }
        }
        // Добавляем флаг recommend
        if ($this->playlistsApi->recommendPlaylistById(['id' => $playlist_id])){
            return true;
        }
        return false;
    }
}