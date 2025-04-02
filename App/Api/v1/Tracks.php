<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException


class Tracks{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }
    function checkTrackById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `id` FROM `tracks` WHERE `id`=:id");
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
    function getTrackById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `tracks` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 1){
                $output = $query->fetchAll();

                $out_result = [];
                foreach ($output as $row){
                    $out_result[] = [
                        'id' => $row['id'],
                        'uri' => $row['uri'],
                        'isrc' => $row['isrc'],
                        'type' => $row['type'],
                        'popularity' => $row['popularity'],
                        'cover' => $row['cover'],
                        'name' => $row['name'],
                        'artists' => json_decode($row['artists'], true),
                        'primary_artist_name' => $row['primary_artist_name'],
                        'primary_artist_id' => $row['primary_artist_id'],
                        'album_id' => $row['album_id'],
                        'album_name' => $row['album_name'],
                        'release_date' => $row['release_date'],
                        'explicit' => $row['explicit'],
                        'time' => $row['time']
                    ];
                }
                return $out_result[0];
            }
            return false;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function createTrack($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `tracks`(`id`, `uri`, `isrc`, `type`, `popularity`, `cover`, `name`, `artists`, `primary_artist_name`, `primary_artist_id`, `album_id`, `album_name`, `release_date`, `explicit`) VALUES (
                :id,
                :uri,
                :isrc,
                :type,
                :popularity,
                :cover,
                :name,
                :artists,
                :primary_artist_name,
                :primary_artist_id,
                :album_id,
                :album_name,
                :release_date,
                :explicit
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
    function getSeveralTracks($data) {
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
            $query = $this->DBH->prepare("SELECT * FROM `tracks` WHERE `id` IN ($placeholders)");
            $query->execute($slicedList);
    
            $output = $query->fetchAll(PDO::FETCH_ASSOC); // Получаем ассоциативный массив
    
            $out_result = [];
            foreach ($output as $row) {
                $out_result[] = [
                    'id' => $row['id'],
                    'uri' => $row['uri'],
                    'isrc' => $row['isrc'],
                    'type' => $row['type'],
                    'popularity' => $row['popularity'],
                    'cover' => $row['cover'],
                    'name' => $row['name'],
                    'artists' => json_decode($row['artists'], true),
                    'primary_artist_name' => $row['primary_artist_name'],
                    'primary_artist_id' => $row['primary_artist_id'],
                    'album_id' => $row['album_id'],
                    'album_name' => $row['album_name'],
                    'release_date' => $row['release_date'],
                    'explicit' => $row['explicit'],
                    'time' => $row['time']
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
}

