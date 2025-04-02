<?php

namespace App\Models;

use App\Api\v1\LastFM\Albums as LastFmAlbumsApi;
use DateTime;

class LastFMAlbums{
    private $lastFmAlbumsApi;
    public function __construct(
        LastFmAlbumsApi $lastFmArtistsApi
    ){
        $this->lastFmAlbumsApi = $lastFmArtistsApi;
    }
    public function getAlbumStat(array $data){
        $result = $this->lastFmAlbumsApi->getAlbumInfo($data);
        if ($result == null){return null;}
        return [
            'listeners' => (int) $result['album']['listeners'],
            'playcount' => (int) $result['album']['playcount'],
            'listeners_for_display' => $this->splitNumber($result['album']['listeners']),
            'playcount_for_display' => $this->splitNumber($result['album']['playcount']),
            'tags' => $result['album']['tags']['tag']
        ];
    }
    private function splitNumber($number) {
        return number_format($number, 0, '.', ' ');
    }
}
