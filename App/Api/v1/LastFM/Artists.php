<?php

namespace App\Api\v1\LastFM;

class Artists
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
    private function getArtistParams(array $data): array
    {
        if (isset($data['mbid'])) {
            return ['mbid' => (string) $data['mbid']];
        }
        $res = [
            'artist' => (string) $data['artist'],
        ];
        if (isset($data['limit'])){
            $res['limit'] = (int) $data['limit'];
        }
        return $res;
    }

    public function getArtistInfo(array $data): ?array
    {
        return $this->makeRequest('artist.getinfo', $this->getArtistParams($data));
    }
    public function getTopTracks(array $data): ?array
    {
        return $this->makeRequest('artist.gettoptracks', $this->getArtistParams($data));
    
    }
    public function getCorrection(array $data): ?array
    {   
        return $this->makeRequest('artist.getcorrection', $this->getArtistParams($data));
    }
    public function getTopAlbums(array $data): ?array
    {   
        return $this->makeRequest('artist.gettopalbums', $this->getArtistParams($data));
    }

    // не рекомендуется
    public function getSimilar(array $data): ?array
    {   
        return $this->makeRequest('artist.getsimilar', $this->getArtistParams($data));
    }
} 