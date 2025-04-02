<?php

namespace App\Api\v1\LastFM;

class Tags
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
    private function getTagParams(array $data): array
    {
        if (isset($data['limit'])){
            return ['tag' => (string) $data['name'], 'limit' => (int) $data['limit']];
        }
        return ['tag' => (string) $data['name']];
    }

    public function getTagInfo(array $data): ?array
    {
        return $this->makeRequest('tag.getinfo', $this->getTagParams($data));
    }

    public function getTopArtists(array $data): ?array
    {
        return $this->makeRequest('tag.gettopartists', $this->getTagParams($data));
    }
    public function getTopAlbums(array $data): ?array
    {
        return $this->makeRequest('tag.gettopalbums', $this->getTagParams($data));
    }
    public function getTopTracks(array $data): ?array
    {
        return $this->makeRequest('tag.gettoptracks', $this->getTagParams($data));
    }
    public function getWeeklyChartList(array $data): ?array
    {
        return $this->makeRequest('tag.getweeklychartlist', $this->getTagParams($data));
    }


    /**
     * Не рабочая хуйня
     */
    public function getSimilar(array $data): ?array
    {
        return $this->makeRequest('tag.getsimilar', $this->getTagParams($data));
    }
} 