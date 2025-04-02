<?php

namespace App\Api\v1\LastFM;

class Albums
{
    private const LASTFM_ENDPOINT = "https://ws.audioscrobbler.com/2.0/";
    private const DEFAULT_LANG = 'RU';

    private function makeRequest(string $method, array $getParams): ?array
    {
        $getParams['lang'] = self::DEFAULT_LANG;
        $getParams['api_key'] = LASTFM_KEY;
        $getParams['format'] = 'json';

        $url = self::LASTFM_ENDPOINT . '?method=' . $method . '&' . http_build_query($getParams);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Логируем ошибку cURL
            error_log('cURL error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            // Логируем HTTP-ошибку
            error_log('HTTP error: ' . $httpCode);
            return null;
        }

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            // Логируем ошибку Last.fm
            error_log('Last.fm error: ' . $data['message']);
            return null;
        }

        return $data;
    }
    private function getAlbumParams(array $data): array
    {
        if (isset($data['mbid'])) {
            return ['mbid' => (string) $data['mbid']];
        }
        return [
            'album' => (string) $data['name'],
            'artist' => (string) $data['artist'],
        ];
    }
    public function getTopTags(array $data): ?array
    {
        return $this->makeRequest('album.gettoptags', $this->getAlbumParams($data));
    }

    public function getAlbumInfo(array $data): ?array
    {
        return $this->makeRequest('album.getinfo', $this->getAlbumParams($data));
    }
}