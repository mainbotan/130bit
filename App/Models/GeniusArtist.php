<?
namespace App\Models;

use App\Services\DecoderService;
use App\Api\v1\Genius\Main as GeniusApi;

class GeniusArtist{
    private $decoder__Service;
    private $genius__Api;
    public function __construct(
        DecoderService $decoderService,
        GeniusApi $geniusApi
    ){ 
        $this->genius__Api = $geniusApi;
        $this->decoder__Service = $decoderService;
    }  
    /**
     * Получает инфу по артисту
     * 
     * @param string $artist_name Ник артиста
     * @return array|null Массив с инфой или null, если не найден
     */
    public function getArtist(string $artist_name){
        if ($artist_name == null){return null; }

        $genius_search = $this->genius__Api->getArtistIdByNickname(['artist_name' => $this->normalizeName($artist_name)]);

        if ((int) $genius_search['meta']['status'] != 200){ return null; }
        
        $hits = $genius_search['response']['hits'];
        $artist_id = $this->getArtistIdFromHitsCollection($artist_name, $hits);

        if ($artist_id == null){ return null; }

        $query_data = [
            'artist_id' => $artist_id
        ];
        $artistInfo = $this->genius__Api->getArtist($query_data);

        // Извлечение информации
        $info = [];
        $info['name'] = $artistInfo['name'] ?? null;
        $info['id'] = $artistInfo['id'] ?? null;
        $info['url'] = $artistInfo['url'] ?? null;
        $info['image_url'] = $artistInfo['image_url'] ?? null;
        $info['header_image_url'] = $artistInfo['header_image_url'] ?? null;
        $info['followers_count'] = $artistInfo['followers_count'] ?? 0;
        $info['is_verified'] = $artistInfo['is_verified'] ?? false;
        $info['description'] = $this->extractDescription($artistInfo['description']);
        $info['alternate_names'] = $artistInfo['alternate_names'] ?? [];
        $info['social_links'] = $this->extractSocialLinks($artistInfo);

        return $info;
    }
    private function compareStrings($str1, $str2) {
        similar_text(strtolower($str1), strtolower($str2), $percent);
        return $percent;
    }
    private function getArtistIdFromHitsCollection(string $goal_artist_name, array $hits){
        $bestMatch = null;
        $bestMatchId = null;
        $bestScore = 0;
        $result = [];
        if ($hits == null){return null;}
        
        // Перебор треков
        foreach ($hits as $hit){
            $artists = $hit['result']['primary_artists'];
            foreach ($artists as $artist){
                $artist_name = $this->normalizeName($artist['name']);
                $artist_percent = $this->compareStrings($goal_artist_name, $artist_name);
                $result[] = [
                    'name' => $artist_name,
                    'id' => $artist['id'],
                    'percent' => $artist_percent
                ];
                if ($artist_percent > $bestScore){
                    $bestMatch = $artist_name;
                    $bestMatchId = $artist['id'];
                    $bestScore = $artist_percent;
                }
            }
        }
        return $bestMatchId;
    }

    // Промежуточные сервисы, присущие методу
    function screening($text){
        $text = str_replace('<', '&lt;', $text);
        $text = str_replace('>', '&gt;', $text);
        return $text;
    }

    private function extractDescription($descriptionArray) {
        if (isset($descriptionArray['dom'])) {
            return $this->parseHtml($descriptionArray['dom']);
        }
        return '';
    }
    private function parseHtml($domArray) {
        $output = '';
        if (isset($domArray['children'])) {
            foreach ($domArray['children'] as $child) {
                $output .= $this->parseNode($child);
            }
        }
        return $output;
    }
    private function parseNode($node) {
        if (isset($node['tag']) && isset($node['children'])) {
            switch ($node['tag']) {
                case 'p':
                    return '<p>' . $this->parseHtml($node) . '</p>';
                case 'em':
                    return '<em>' . $this->parseHtml($node) . '</em>';
                case 'a':
                    $href = $node['attributes']['href'] ?? '#';
                    return '<a href="' . htmlspecialchars($href) . '" target="_blank">' . $this->parseHtml($node) . '</a>';
                default:
                    return $this->parseHtml($node);
            }
        } elseif (is_array($node)) {
            return $this->parseHtml($node);
        } else {
            return $node;
        }
    }

    function extractSocialLinks($artistInfo) {
        $links = [];
        $links['vk'] = $artistInfo['vk_name'] ?? null;
        $links['instagram'] = $artistInfo['instagram_name'] ?? null;
        $links['twitter'] = $artistInfo['twitter_name'] ?? null;
        $links['facebook'] = $artistInfo['facebook_name'] ?? null;
        return $links;
    }
    private function stringFinger($input) {
        $lowercase = mb_strtolower($input);
        $string = str_replace(' ', '', $lowercase);
        return $string;
    }
    private function normalizeName(string $string): string
    {
        $string = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);
        $string = str_replace(["'", '"', '‘', '’', '“', '”'], '', $string);
        $pattern = '/\([^)]*\)/';
        return preg_replace($pattern, '', $string);
    }
}