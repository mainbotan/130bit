<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException


class Trackers{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }
    function dropTrackerByArtistId($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("DELETE FROM `trackers` WHERE `artist_id`=:artist_id");
            $query->execute($data);
            return true;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
    function dropTrackerById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("DELETE FROM `trackers` WHERE `id`=:id");
            $query->execute($data);
            return true;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
    function getFirstTrackers($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `trackers` ORDER BY `time` ASC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => (int) $row['id'],
                    'artist_id' => $row['artist_id'],
                    'time' => $row['time']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getLastTrackers($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `trackers` ORDER BY `time` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => (int) $row['id'],
                    'artist_id' => $row['artist_id'],
                    'time' => $row['time']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getTrackerByArtistId($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `trackers` WHERE `artist_id`=:artist_id");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => (int) $row['id'],
                    'artist_id' => $row['artist_id'],
                    'time' => $row['time']
                ];
            }
            return $out_result[0];
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getTrackerById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `trackers` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => (int) $row['id'],
                    'artist_id' => $row['artist_id'],
                    'time' => $row['time']
                ];
            }
            return $out_result[0];
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
 
    function createTracker($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `trackers`(`artist_id`) VALUES (
                :artist_id
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