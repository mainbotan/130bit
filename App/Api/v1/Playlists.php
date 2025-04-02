<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException



class Playlists{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }

    /**
     * For updates
     */

    function updatePlaylist($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("UPDATE `playlists` SET 
                `description` = :description,
                `total_tracks` = :total_tracks,
                `cover` = :cover,
                `tracks` = :tracks
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
    function getPlaylistsForUpdate($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `playlists` WHERE `isUpdate`=1 ORDER BY `time` DESC LIMIT :offset, :limit");
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
                    'owner' => json_decode($row['owner'], true),
                    'uri' => $row['uri'],
                    'description' => $row['description'],
                    'recommend' => $row['recommend']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }     

    /**
     * Select from playlists
     */
    function getPlaylistsByMeta($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `playlists` WHERE `meta`=:meta ORDER BY `time` DESC LIMIT :offset, :limit");
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
                    'owner' => json_decode($row['owner'], true),
                    'uri' => $row['uri'],
                    'description' => $row['description'],
                    'recommend' => $row['recommend']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }     
    function getRecommendedPlaylists($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `playlists` WHERE `recommend`=1 ORDER BY `time` DESC LIMIT :offset, :limit");
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
                    'owner' => json_decode($row['owner'], true),
                    'uri' => $row['uri'],
                    'description' => $row['description'],
                    'recommend' => $row['recommend']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }     

    function recommendPlaylistById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("UPDATE `playlists` SET 
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
    function checkPlaylistIsRecommend($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `recommend` FROM `playlists` WHERE `id`=:id");
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
    function checkPlaylistByName($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `id` FROM `playlists` WHERE `name`=:name");
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
    function checkPlaylistById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `id` FROM `playlists` WHERE `id`=:id");
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
    function getSeveralPlaylists($data) {
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
            $query = $this->DBH->prepare("SELECT * FROM `playlists` WHERE `id` IN ($placeholders)");
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
                    'owner' => json_decode($row['owner'], true),
                    'uri' => $row['uri'],
                    'description' => $row['description'],
                    'recommend' => $row['recommend']
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
    function getPlaylistById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `playlists` WHERE `id`=:id");
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
                        'owner' => json_decode($row['owner'], true),
                        'tracks' => json_decode($row['tracks'], true),
                        'uri' => $row['uri'],
                        'description' => $row['description'],
                        'recommend' => $row['recommend']
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
    function createPlaylist($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `playlists`(`id`, `type`, `total_tracks`, `cover`, `name`, `description`, `owner`, `uri`, `tracks`) VALUES (
                :id,
                :type,
                :total_tracks,
                :cover,
                :name,
                :description,
                :owner,
                :uri,
                :tracks
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