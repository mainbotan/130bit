<?php

namespace App\Models;

use App\Api\v1\LastFM\Artists as LastFmArtistsApi;
use App\Api\v1\Artists as ArtistsApi;

class LastFMArtists{
    private $lastFmArtistsApi;
    private $artistsApi;

    public function __construct(
        LastFmArtistsApi $lastFmArtistsApi,
        ArtistsApi $artistsApi
    ){
        $this->lastFmArtistsApi = $lastFmArtistsApi;
        $this->artistsApi = $artistsApi;
    }
    public function getArtistStat(array $data){
        $result = $this->lastFmArtistsApi->getArtistInfo($data);
        if ($result == null){return null;}
        $result = [
            'listeners' => (int) $result['artist']['stats']['listeners'],
            'playcount' => (int) $result['artist']['stats']['playcount'],
            'listeners_for_display' => $this->splitNumber($result['artist']['stats']['listeners']),
            'playcount_for_display' => $this->splitNumber($result['artist']['stats']['playcount']),
            'tags' => $result['artist']['tags']['tag'],
            'bio' => [
                'content' => $result['artist']['bio']['content'],
                'published' => $result['artist']['bio']['published']
            ],
            'similar' => $result['artist']['similar']['artist']
        ];
        if ($result['similar'] != null){
            $upd_artists = [];
            foreach ($result['similar'] as $artist){
                $artist_db = $this->artistsApi->getArtistByName(['name' => (string) $artist['name']]);
                if ($artist_db != null){
                    $upd_artists[] = [
                        'name' => $artist_db['name'],
                        'cover' => $artist_db['picture_big']
                    ];
                }else{
                    $upd_artists[] = [
                        'name' => $artist['name'],
                        'cover' => $artist['image'][3]['#text']
                    ];
                }
            }
            $result['similar'] = $upd_artists;
        }
        return $result;
    }
    private function splitNumber($number) {
        return number_format($number, 0, '.', ' ');
    }
}
