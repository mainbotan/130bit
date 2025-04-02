<?php


namespace App\Models;

use App\Api\v1\LastFM\Tags as LastFmTagsApi;
use DateTime;

class LastFMTag{
    private $lastFmTagsApi;
    public function __construct(LastFmTagsApi $lastFmTagsApi){
        $this->lastFmTagsApi = $lastFmTagsApi;
    }
    public function getTag(string $tag_name) {
        if ($tag_name == null){return null;}

        $result = [];
        $tag = $this->lastFmTagsApi->getTagInfo(['name' => $tag_name]);
        if ($tag == null){ return null; }

        $result['info'] = $tag['tag'] ?? [];
        $result['top_albums'] = $this->getTagTopAlbums($tag_name);
        $result['top_artists'] = $this->getTagTopArtists($tag_name);
        $result['top_tracks'] = $this->getTagTopTracks($tag_name);
        return $result;
    }
    public function getTagTopAlbums(string $tag_name) {
        return $this->lastFmTagsApi->getTopAlbums(['name' => $tag_name, 'limit' => 50])['albums']['album'] ?? [];
    }
    public function getTagTopArtists(string $tag_name){
        return $this->lastFmTagsApi->getTopArtists(['name' => $tag_name, 'limit' => 20])['topartists']['artist'] ?? [];
    }
    public function getTagTopTracks(string $tag_name){
        return $this->lastFmTagsApi->getTopTracks(['name' => $tag_name, 'limit' => 20])['tracks']['track'] ?? [];
    }
}