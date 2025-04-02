<?php
namespace App\Services;

use App\Services\AppService;
use App\Services\EncryptionService;
use App\Services\DecoderService;
use App\Models\Comments as CommentsModel;
use App\Models\Users as UsersModel;
use App\Models\Viewer as ViewerModel;
use App\Models\Album as AlbumModel;
use App\Models\Releases as ReleasesModel;
use App\Models\Artist as ArtistModel;
use App\Models\Artists as ArtistsModel;
use App\Models\Account as AccountModel;
use App\Models\Track as TrackModel;
use App\Models\Tracks as TracksModel;
use App\Models\Search as SearchModel;
use App\Models\LastFMTag as LastFmTagModel;
use App\Models\Playlist as PlaylistModel;
use App\Models\Playlists as PlaylistsModel;
use App\Api\v1\Comments as CommentsApi;
use App\Api\v1\Sessions as SessionsApi;
use App\Api\v1\Users as UsersApi;
use App\Api\v1\Tracks as TracksApi;
use App\Api\v1\Artists as ArtistsApi;
use App\Api\v1\Releases as ReleasesApi;
use App\Api\v1\Playlists as PlaylistsApi;
use App\Api\v1\Account as AccountApi;
use App\Api\v1\Collections as CollectionsApi;
use App\Api\v1\Favourites as FavouritesApi;
use App\Api\v1\Subscriptions as SubscriptionsApi;
use App\Api\v1\Friends as FriendsApi;
use App\Api\v1\Spotify\Autho as SpotifyAuthoApi;
use App\Api\v1\Spotify\Artists as SpotifyArtistsApi;
use App\Api\v1\Spotify\Search as SpotifySearchApi;
use App\Api\v1\Spotify\Albums as SpotifyAlbumsApi;
use App\Api\v1\Spotify\Tracks as SpotifyTracksApi;
use App\Api\v1\Spotify\Episodes as SpotifyEpisodesApi;
use App\Api\v1\Spotify\Playlists as SpotifyPlaylistsApi;
use App\Api\v1\Genius\Main as GeniusApi;
use App\Api\v1\LastFM\Tags as LastFmTagsApi;

class MetaService{
    private $defaultMeta = [
        'title' => '130bit',
        'icon' => 'https://130bit.com/open/web/logos/130bit_white_logo.png',
        'description' => 'Отслеживайте новые релизы на 130bit. Комментируйте, делитесь музыкой, создавайте свою коллекцию.',
        'keywords' => 'music, free, spotify',
        'canonical' => 'https://130bit.com',
        'robots' => 'index, follow',
        
        // Open Graph
        'og' => [
            'title' => '130bit',
            'description' => 'Отслеживайте новые релизы на 130bit. Комментируйте, делитесь музыкой, создавайте свою коллекцию.',
            'url' => 'https://130bit.com',
            'image' => 'https://130bit.com/open/web/logos/130bit_white_logo.png',
            'type' => 'website',
            'site_name' => '130bit'
        ],
        
        // VK
        'vk' => [
            'title' => '130bit',
            'description' => 'Отслеживайте новые релизы на 130bit. Комментируйте, делитесь музыкой, создавайте свою коллекцию.',
            'image' => 'https://130bit.com/open/web/logos/130bit_white_logo.png'
        ],
        
        // Twitter
        'twitter' => [
            'card' => 'summary_large_image',
            'title' => '130bit',
            'description' => 'Отслеживайте новые релизы на 130bit. Комментируйте, делитесь музыкой, создавайте свою коллекцию.',
            'image' => 'https://130bit.com/open/web/logos/130bit_white_logo.png',
            'site' => '@130bit'
        ],
        
        // Telegram
        'telegram' => [
            'title' => '130bit',
            'description' => 'Отслеживайте новые релизы на 130bit. Комментируйте, делитесь музыкой, создавайте свою коллекцию.',
            'image' => 'https://130bit.com/open/web/logos/130bit_white_logo.png'
        ]
    ];
    private $encryptionService;
    private $decoderService;
    private $releasesApi;
    private $artistsApi;
    private $tracksApi;
    private $spotifyAuthoApi;
    private $spotifyArtistsApi;
    private $spotifySearchApi;
    private $spotifyAlbumsApi;
    private $spotifyTracksApi;
    private $spotifyEpisodesApi;
    private $spotifyPlaylistsApi;
    private $playlistsApi;
    private $usersApi;
    private $geniusApi;
    private $collectionsApi;
    private $favouritesApi;
    private $subscriptionsApi;

    public function __construct(
        EncryptionService $encryptionService,
        ReleasesApi $releasesApi,
        ArtistsApi $artistsApi,
        TracksApi $tracksApi,
        SpotifyAuthoApi $spotifyAuthoApi,
        SpotifyArtistsApi $spotifyArtistsApi,
        SpotifySearchApi $spotifySearchApi,
        SpotifyAlbumsApi $spotifyAlbumsApi,
        SpotifyTracksApi $spotifyTracksApi,
        SpotifyEpisodesApi $spotifyEpisodesApi,
        SpotifyPlaylistsApi $spotifyPlaylistsApi,
        PlaylistsApi $playlistsApi,
        UsersApi $usersApi,
        GeniusApi $geniusApi,
        DecoderService $decoderService,
        CollectionsApi $collectionsApi,
        FavouritesApi $favouritesApi,
        SubscriptionsApi $subscriptionsApi
    ) {
        $this->encryptionService = $encryptionService;
        $this->releasesApi = $releasesApi;
        $this->artistsApi = $artistsApi;
        $this->tracksApi = $tracksApi;
        $this->spotifyAuthoApi = $spotifyAuthoApi;
        $this->spotifyArtistsApi = $spotifyArtistsApi;
        $this->spotifySearchApi = $spotifySearchApi;
        $this->spotifyAlbumsApi = $spotifyAlbumsApi;
        $this->spotifyTracksApi = $spotifyTracksApi;
        $this->spotifyEpisodesApi = $spotifyEpisodesApi;
        $this->spotifyPlaylistsApi = $spotifyPlaylistsApi;
        $this->playlistsApi = $playlistsApi;
        $this->usersApi = $usersApi;
        $this->geniusApi = $geniusApi;
        $this->decoderService = $decoderService;
        $this->collectionsApi = $collectionsApi;
        $this->favouritesApi = $favouritesApi;
        $this->subscriptionsApi = $subscriptionsApi;
    }
    
    public function handleRequest(){
        $uri = $_SERVER['REQUEST_URI'];
        $request = $this->parseQueryString($uri);


        switch ($request['x']){
            case null:
                return $this->defaultMeta;
            break;
            case 'artist':
                $artist_id = $request['id'];
                if ($artist_id == null){
                    $searchModel = new SearchModel($this->spotifySearchApi, $this->spotifyAuthoApi);
                    $artist_id = $searchModel->universalSearch((string) $request['name'], ['artist' => 1])['artists'][0]['id'];
                }
                $artistModel = new ArtistModel(
                    $this->artistsApi,
                    $this->spotifyAlbumsApi,
                    $this->spotifyArtistsApi,
                    $this->spotifyAuthoApi
                );
                $data = [
                    'id' => $artist_id,
                    'isReleases' => false,
                    'isTopTracks' => false,
                    'isAppearsOn' => false
                ];
                $artistData = $artistModel->getArtist($data);
                if ($artistData['info']['id'] == null){ return $this->defaultMeta; }

                $title = $artistData['info']['name'];
                $description = $artistData['info']['name'] . " | Отслеживайте новые релизы на 130bit.";
                $icon = $artistData['info']['cover'];
                $meta = $this->updateMeta($title, $description, $icon);
            break;  
            case 'album':
                $album_id = $request['id'];
                if ($album_id == null){
                    $searchModel = new SearchModel($this->spotifySearchApi, $this->spotifyAuthoApi);
                    $album_id = $searchModel->universalSearch((string) $request['name'] . ' - ' . (string) $request['artist'], ['album' => 1])['albums'][0]['id'];
                }

                $albumModel = new AlbumModel(
                    $this->releasesApi,
                    $this->spotifyAlbumsApi,
                    $this->spotifyArtistsApi,
                    $this->spotifyAuthoApi
                );
                $albumData = $albumModel->getAlbum(['id' => $album_id]);
                if ($albumData['id'] == null){ return $this->defaultMeta; }

                $title = $albumData['name'] . ' by ' . $albumData['artists'][0]['name'];
                $description = $title . " | Слушать бесплатно на 130bit.";
                $icon = $albumData['album_image_big'];
                $meta = $this->updateMeta($title, $description, $icon);
            break;
            case 'track':
                $trackModel = new TrackModel(
                    $this->decoderService,
                    $this->tracksApi,
                    $this->geniusApi,
                    $this->spotifyTracksApi,
                    $this->spotifyAuthoApi
                );
                $data = [
                    'id' => $request['id'],
                    'isGenius' => false
                ];
                $trackData = $trackModel->getTrack($data);
                if ($trackData['id'] == null){ return $this->defaultMeta; }

                $title = $trackData['name'] . ' by ' . $trackData['artists'][0]['name'];
                $description = $title . " | Слушать бесплатно на 130bit.";
                $icon = $trackData['cover'];
                $meta = $this->updateMeta($title, $description, $icon);
            break; 
            case 'playlist':
                $playlist_id = $request['id'];
                $playlistModel = new PlaylistModel(
                    $this->playlistsApi,
                    $this->spotifyPlaylistsApi,
                    $this->spotifyArtistsApi,
                    $this->spotifyAuthoApi
                );
                $playlistData = $playlistModel->getPlaylist(['id' => $playlist_id]);
                if ($playlistData['id'] == null){ return $this->defaultMeta; }

                $title = $playlistData['name'];
                $description = $title . " | Публичный плейлист на 130bit.";
                $icon = $playlistData['cover'];
                $meta = $this->updateMeta($title, $description, $icon);
            break;
            case 'user':
                $usersModel = new UsersModel(
                    $this->decoderService,
                    $this->usersApi,
                    $this->subscriptionsApi,
                    $this->collectionsApi,
                    $this->favouritesApi,
                    $this->releasesApi,
                    $this->artistsApi,
                    $this->tracksApi
                );
                if ($request['id'] == null){
                    $userData = $usersModel->getUser(['name' => $request['name']]);   
                }else{
                    $userData = $usersModel->getUser(['id' => $request['id']]);   
                }
                if ($userData['id'] == null){ return $this->defaultMeta; }

                $title = $userData['name'];
                $description = "Музыка ". $title ." на 130bit.";
                $icon = $userData['picture'];
                if ($icon == null){ $icon = $this->defaultMeta['icon']; }
                $meta = $this->updateMeta($title, $description, $icon);
            break;
            default: 
                $meta = $this->defaultMeta;
        }

        /*Возвращаем готовую мету*/
        return $meta;
    }  
    private function updateMeta(string $title, string $description, string $image, string $url = null) {
        $meta = $this->defaultMeta;
        
        // Базовые мета
        $meta['title'] = $title;
        $meta['description'] = $description;
        $meta['og']['image'] = $image;
        $meta['vk']['image'] = $image;
        $meta['twitter']['image'] = $image;
        $meta['telegram']['image'] = $image;
        
        // Open Graph
        $meta['og']['title'] = $title;
        $meta['og']['description'] = $description;
        $meta['og']['url'] = $url ?? $this->defaultMeta['og']['url'];
        
        // VK
        $meta['vk']['title'] = $title;
        $meta['vk']['description'] = $description;
        
        // Twitter
        $meta['twitter']['title'] = $title;
        $meta['twitter']['description'] = $description;
        
        // Telegram
        $meta['telegram']['title'] = $title;
        $meta['telegram']['description'] = $description;
        
        return $meta;
    }

    private function parseQueryString($queryString) {
        $params = [];
        parse_str(ltrim($queryString, '/?'), $params);
        return $params;
    }
}