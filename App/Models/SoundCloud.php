<?php

namespace App\Models;

use \App\Api\v1\SoundCloud\Search as SoundCloudSearchApi;

class SoundCloud {
    private $sc__Search;

    public function __construct(
        SoundCloudSearchApi $soundCloudSearchApi
    ) {
        $this->sc__Search = $soundCloudSearchApi;
    }

    // @param (string) Название трека, (array) Артисты на треке
    // @return null - трек не найден, (int) - id трека sc
    public function getTrackId($track_name, $track_artists) {
        $track_name = (string) $track_name;
        if (empty($track_name) || empty($track_artists) || !isset($track_artists[0]['name'])) {
            return null;
        }

        $query = $this->normalizeName($track_name . ' - ' . $track_artists[0]['name']);
        $tracks = $this->sc__Search->searchSoundCloudTrack($query, SOUNDCLOUD_CLIENT_ID, SOUNDCLOUD_AUTHO_TOKEN);
        if ($tracks === null) {
            error_log("SoundCloud API returned no results for query: $query");
            return null;
        }
        $track_id = $this->findBestMatch($track_name, $track_artists, $tracks)['id'];
        return $track_id;
    }

    // Функция для сравнения строк
    private function compareStrings($str1, $str2) {
        similar_text(strtolower($str1), strtolower($str2), $percent);
        return $percent;
    }

    // Поиск наиболее подходящего трека
    private function findBestMatch($spotifyTrackName, $spotifyArtists, $soundcloudData) {
        $bestMatch = null;
        $bestScore = 0;

        $spotifyTrackName = $this->normalizeName($spotifyTrackName);
        $spotifyArtists = array_map(function($artist) {
            return $this->normalizeName($artist['name']);
        }, $spotifyArtists);

        foreach ($soundcloudData['collection'] as $track) {
            $trackName = $this->normalizeName($track['title']);
            $trackArtist = $this->normalizeName($track['publisher_metadata']['artist']);

            // Сравниваем название трека
            $nameScore = $this->compareStrings($spotifyTrackName, $trackName);

            // Сравниваем артистов
            $artistScore = 0;
            foreach ($spotifyArtists as $artist) {
                $artistScore += $this->compareStrings($artist, $trackArtist);
            }
            $artistScore /= count($spotifyArtists);

            // Общий вес (можно настроить коэффициенты)
            $totalScore = $nameScore * 0.7 + $artistScore * 0.3;

            // Если текущий трек лучше предыдущего, обновляем лучший результат
            if ($totalScore > $bestScore) {
                $bestScore = $totalScore;
                $bestMatch = $track;
            }
        }

        return $bestMatch;
    }

    // Нормализация строк
    private function normalizeName($string) {
        // Убираем лишние пробелы в начале и конце
        $string = trim($string);
    
        // Заменяем кавычки и апострофы на обычные
        $string = str_replace(["'", '"', '‘', '’', '“', '”'], '', $string);
    
        // Убираем текст в скобках (например, "(Official Video)")
        $string = preg_replace('/\([^)]*\)/', '', $string);
    
        // Нормализуем символы из разных алфавитов, кроме кириллицы
        $string = preg_replace_callback('/[^\p{Cyrillic}\s\-\.@$&]/u', function($matches) {
            // Преобразуем символы с диакритиками в ASCII, но не трогаем кириллицу
            return transliterator_transliterate('Any-Latin; Latin-ASCII', $matches[0]);
        }, $string);
    
        // Убираем все символы, кроме букв (включая кириллицу), цифр, пробелов, дефисов, точек, амперсандов, собачек и долларов
        $string = preg_replace('/[^\p{L}\p{N}\s\-\.@$&]/u', '', $string);
    
        // Убираем лишние пробелы (двойные, тройные и т.д.)
        $string = preg_replace('/\s+/', ' ', $string);

        $string = str_replace('$', 's', $string);    
        return $string;
    }
}