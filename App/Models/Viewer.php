<?php

namespace App\Models;

use App\Api\v1\Artists as ArtistsApi;
use App\Api\v1\Playlists as PlaylistsApi;
use DateTime;

class Viewer{
    private $artistsApi;
    private $playlistsApi;
    public function __construct(
        ArtistsApi $artistsApi,
        PlaylistsApi $playlistsApi
    ){
        $this->artistsApi = $artistsApi;
        $this->playlistsApi = $playlistsApi;
    }
    public function getArtistsZone(array $data){
        return $this->artistsApi->getRandomArtists(['limit' => (int) $data['limit']]);
    }
    private function splitNumber($number) {
        return number_format($number, 0, '.', ' ');
    }
}
