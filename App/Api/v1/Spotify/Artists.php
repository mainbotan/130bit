<?

namespace App\Api\v1\Spotify;

class Artists{
      function getArtistRelatedArtists($data) {
        $accessToken = $data['token'];
        $artistId = $data['artist_id'];
        $url = 'https://api.spotify.com/v1/artists/' . urlencode($artistId) . '/related-artists';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);
        return json_decode($response, true);
    }
    function getArtistTopTracks($data) {
        $accessToken = $data['token'];
        $artistId = $data['artist_id'];
        $url = 'https://api.spotify.com/v1/artists/' . urlencode($artistId) . '/top-tracks?market=US';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);
        return json_decode($response, true);
    }
    function getArtistAlbums($data) {
        $accessToken = $data['token'];
        $artistId = $data['artist_id'];
        $include_groups = implode(',', $data['groups']);
        $limit = $data['limit'];
        $offset = $data['offset'];
        $url = 'https://api.spotify.com/v1/artists/' . urlencode($artistId) . '/albums' . "?limit=$limit&offset=$offset&include_groups=$include_groups";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);
        return json_decode($response, true);
    }

    function getSeveralArtists($data) {
        $accessToken = $data['token'];
        $ids = implode(',', $data['ids']);
        
        $url = 'https://api.spotify.com/v1/artists?ids=' . urlencode($ids);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);
        return json_decode($response, true);
    }


    function getArtistInfo($data) {
        $curl = curl_init();
      
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.spotify.com/v1/artists/". $data['artist_id'],
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . $data['token']
          ),
        ));
      
        $response = curl_exec($curl);
        $err = curl_error($curl);
      
        curl_close($curl);
        return json_decode($response, true);
      }
}

