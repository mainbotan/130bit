<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException



class Releases{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }
    function getLatestArtistReleases($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `releases` WHERE `primary_artist_id`=:artist_id ORDER BY `release_date` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'total_tracks' => (int) $row['total_tracks'],
                    'cover' => $row['cover'],
                    'name' => $row['name'],
                    'release_date' => $row['release_date'],
                    'uri' => $row['uri'],
                    'artists' => json_decode($row['artists'], true),
                    'primary_artist_name' => $row['primary_artist_name'],
                    'primary_artist_id' => $row['primary_artist_id'],
                    'label' => $row['label'],
                    'popularity' => (int) $row['popularity']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }    
    function getSeveralReleasesByArtistsId($data) {
        global $DBH;
    
        // Проверка, что массив 'list' не пуст
        if (empty($data['list'])) {
            return []; // Возвращаем пустой массив, если нет данных
        }
    
        try {
    
            // Создаем массив для подстановки значений
            $params = [];
            foreach ($data['list'] as $index => $artistId) {
                $params[":artistId$index"] = $artistId; // Используем именованные параметры
            }
    
            // Создаем строку плейсхолдеров для каждого ID
            $placeholders = implode(',', array_keys($params));
    
            // Используем LIMIT и OFFSET в запросе
            $query = $this->DBH->prepare("
                SELECT * FROM `releases` 
                WHERE `primary_artist_id` IN ($placeholders) 
                ORDER BY `release_date` DESC 
                LIMIT :limit OFFSET :offset
            ");
    
            // Привязываем параметры для ID артистов
            foreach ($params as $key => $value) {
                $query->bindValue($key, $value, PDO::PARAM_INT);
            }
    
            // Привязываем параметры для LIMIT и OFFSET
            $query->bindValue(':limit', (int)$data['limit'], PDO::PARAM_INT);
            $query->bindValue(':offset', (int)$data['offset'], PDO::PARAM_INT);
    
            // Выполняем запрос
            $query->execute();
    
            $output = $query->fetchAll(PDO::FETCH_ASSOC); // Получаем ассоциативный массив
    
            $out_result = [];
            foreach ($output as $row) {
                $out_result[] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'total_tracks' => (int) $row['total_tracks'],
                    'cover' => $row['cover'],
                    'name' => $row['name'],
                    'release_date' => $row['release_date'],
                    'uri' => $row['uri'],
                    'artists' => json_decode($row['artists'], true),
                    'primary_artist_name' => $row['primary_artist_name'],
                    'primary_artist_id' => $row['primary_artist_id'],
                    'label' => $row['label'],
                    'popularity' => (int) $row['popularity']
                ];
            }
            return $out_result;
        } catch (PDOException $e) {
            // Записываем ошибку в файл
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);
            return []; // Возвращаем пустой массив в случае ошибки
        }
    }
    function recommendReleaseById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("UPDATE `releases` SET 
                `recommend` = 1
                WHERE `id` = :id
            ");
            $query->execute($data);
            return true;
        }  
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
    function checkReleaseIsRecommend($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `recommend` FROM `releases` WHERE `id`=:id");
            $query->execute($data);
            $output = $query->fetchAll();
            $recommend = $output['recommend'];
            if ($recommend){
                return true;
            }
            return false;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getReleasesByReleaseDate($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `releases` ORDER BY `release_date` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'total_tracks' => (int) $row['total_tracks'],
                    'cover' => $row['cover'],
                    'name' => $row['name'],
                    'release_date' => $row['release_date'],
                    'uri' => $row['uri'],
                    'artists' => json_decode($row['artists'], true),
                    'primary_artist_name' => $row['primary_artist_name'],
                    'primary_artist_id' => $row['primary_artist_id'],
                    'label' => $row['label'],
                    'popularity' => (int) $row['popularity']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getRecommendedReleases($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `releases` WHERE `recommend`=1 ORDER BY `time` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'total_tracks' => (int) $row['total_tracks'],
                    'cover' => $row['cover'],
                    'name' => $row['name'],
                    'release_date' => $row['release_date'],
                    'uri' => $row['uri'],
                    'artists' => json_decode($row['artists'], true),
                    'primary_artist_name' => $row['primary_artist_name'],
                    'primary_artist_id' => $row['primary_artist_id'],
                    'label' => $row['label'],
                    'popularity' => (int) $row['popularity']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }    
    function getReleasesByMeta($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `releases` WHERE `meta`=:meta ORDER BY `release_date` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'total_tracks' => (int) $row['total_tracks'],
                    'cover' => $row['cover'],
                    'name' => $row['name'],
                    'release_date' => $row['release_date'],
                    'uri' => $row['uri'],
                    'artists' => json_decode($row['artists'], true),
                    'primary_artist_name' => $row['primary_artist_name'],
                    'primary_artist_id' => $row['primary_artist_id'],
                    'label' => $row['label'],
                    'popularity' => (int) $row['popularity']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }    
    function getReleasesByPopularity($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `releases` ORDER BY `popularity` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'total_tracks' => (int) $row['total_tracks'],
                    'cover' => $row['cover'],
                    'name' => $row['name'],
                    'release_date' => $row['release_date'],
                    'uri' => $row['uri'],
                    'artists' => json_decode($row['artists'], true),
                    'primary_artist_name' => $row['primary_artist_name'],
                    'primary_artist_id' => $row['primary_artist_id'],
                    'label' => $row['label'],
                    'popularity' => (int) $row['popularity']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function checkReleaseByName($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `id` FROM `releases` WHERE `name`=:name");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 1){
                return true;
            }
            return false;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function checkReleaseById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `id` FROM `releases` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 1){
                return true;
            }
            return false;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getSeveralReleases($data) {
        global $DBH;
    
        // Проверка, что массив 'list' не пуст
        if (empty($data['list'])) {
            return []; // Возвращаем пустой массив, если нет данных
        }
    
        // Перевернем массив ID треков
        $reversedList = array_reverse($data['list']);
    
        // Обрезаем массив с использованием offset и limit
        $slicedList = array_slice($reversedList, $data['offset'], $data['limit']);
    
        // Создаем строку плейсхолдеров для каждого ID
        $placeholders = rtrim(str_repeat('?,', count($slicedList)), ',');
    
        try {
            $query = $this->DBH->prepare("SELECT * FROM `releases` WHERE `id` IN ($placeholders)");
            $query->execute($slicedList);
    
            $output = $query->fetchAll(PDO::FETCH_ASSOC); // Получаем ассоциативный массив
    
            $out_result = [];
            foreach ($output as $row) {
                $out_result[] = [
                    'id' => $row['id'],
                    'type' => $row['type'],
                    'total_tracks' => (int) $row['total_tracks'],
                    'cover' => $row['cover'],
                    'name' => $row['name'],
                    'release_date' => $row['release_date'],
                    'uri' => $row['uri'],
                    'artists' => json_decode($row['artists'], true),
                    'primary_artist_name' => $row['primary_artist_name'],
                    'primary_artist_id' => $row['primary_artist_id'],
                    'tracks' => json_decode($row['tracks'], true),
                    'label' => $row['label'],
                    'meta' => $row['meta'],
                    'recommend' => (bool) $row['recommend'],
                    'popularity' => (int) $row['popularity']
                ];
            }
            return array_reverse($out_result);
        } catch (PDOException $e) {
            // Записываем ошибку в файл
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);
            return []; // Возвращаем пустой массив в случае ошибки
        }
    }
    function getReleaseById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `releases` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 1){
                $output = $query->fetchAll();
                $out_result = [];
                foreach ($output as $row){
                    $out_result[] = [
                        'id' => $row['id'],
                        'type' => $row['type'],
                        'total_tracks' => (int) $row['total_tracks'],
                        'cover' => $row['cover'],
                        'name' => $row['name'],
                        'release_date' => $row['release_date'],
                        'uri' => $row['uri'],
                        'artists' => json_decode($row['artists'], true),
                        'primary_artist_name' => $row['primary_artist_name'],
                        'primary_artist_id' => $row['primary_artist_id'],
                        'tracks' => json_decode($row['tracks'], true),
                        'label' => $row['label'],
                        'meta' => $row['meta'],
                        'recommend' => (bool) $row['recommend'],
                        'popularity' => (int) $row['popularity']
                    ];
                }
                return $out_result[0];
            }else{
                return false;
            }
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function createRelease($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `releases`(`id`, `type`, `total_tracks`, `cover`, `name`, `release_date`, `uri`, `artists`, `primary_artist_name`, `primary_artist_id`, `tracks`, `label`, `popularity`) VALUES (
                :id,
                :type,
                :total_tracks,
                :cover,
                :name,
                :release_date,
                :uri,
                :artists,
                :primary_artist_name,
                :primary_artist_id,
                :tracks,
                :label,
                :popularity
            )");
            $query->execute($data);
            return true;
        }  
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }   
}