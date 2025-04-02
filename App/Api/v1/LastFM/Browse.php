<?php

namespace App\Api\v1\LastFM;

class Browse{
    public function getTopTags(array $data) {
        $url = "https://ws.audioscrobbler.com/2.0/?method=tag.getTopTags&api_key=" . LASTFM_KEY . "&format=json";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Выполнение запроса
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return 'Ошибка cURL: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            // Проверка на ошибки от Last.fm
            if (isset($data['error'])) {
                return 'Last.fm error: ' . $data['message'];
            }
            return $data;
        }
    }
    public function getArtist(array $data) {
        $artist_mbid = $data['mbid'];
        $url = "https://ws.audioscrobbler.com/2.0/?method=artist.gettoptracks&mbid=$artist_mbid&api_key=" . LASTFM_KEY . "&format=json";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Выполнение запроса
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return 'Ошибка cURL: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            // Проверка на ошибки от Last.fm
            if (isset($data['error'])) {
                return 'Last.fm error: ' . $data['message'];
            }
            return $data;
        }
    }
    public function getTopTracks(array $data) {
        $url = "https://ws.audioscrobbler.com/2.0/?method=chart.gettoptracks&api_key=" . LASTFM_KEY . "&format=json";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Выполнение запроса
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return 'Ошибка cURL: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            // Проверка на ошибки от Last.fm
            if (isset($data['error'])) {
                return 'Last.fm error: ' . $data['message'];
            }
            return $data;
        }
    }
    public function getTopAlbums(array $data){
        $tag = urlencode('hip hop'); // Тег
        $page = 1; // Страница
        $url = "https://ws.audioscrobbler.com/2.0/?method=tag.gettopalbums&tag=$tag&api_key=". LASTFM_KEY ."&format=json&page=$page";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Выполнение запроса
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return 'Ошибка cURL: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            return $data;
        }
    }
    public function getWeeklyChartList(array $data){
        $tag = urlencode('hip hop'); // Тег
        $page = 1; // Страница
        $url = "https://ws.audioscrobbler.com/2.0/?method=tag.getweeklychartlist&tag=$tag&api_key=". LASTFM_KEY ."&format=json";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Выполнение запроса
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return 'Ошибка cURL: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            return $data;
        }
    }
}