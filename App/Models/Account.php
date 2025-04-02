<?php

namespace App\Models;

use App\Api\v1\Sessions as SessionsApi;
use App\Api\v1\Users as UsersApi;
use App\Api\v1\Friends as FriendsApi;
use App\Api\v1\Account as AccountApi;
use App\Api\v1\Subscriptions as SubscriptionsApi;
use App\Api\v1\Favourites as FavouritesApi;
use App\Api\v1\Collections as CollectionsApi;
use App\Services\EncryptionService;
use Exception;

class Account {
    private $accountApi;
    private $encryptionService;
    private $subscriptions__API;
    private $favourites__API;
    private $collections__API;
    private $friendsApi;
    private $usersApi;
    private $sessionsApi;

    public function __construct(
        AccountApi $accountApi,
        EncryptionService $encryptionService,
        SubscriptionsApi $subscriptions__API,
        FavouritesApi $favourites__API,
        CollectionsApi $collections__API,
        FriendsApi $friendsApi,
        UsersApi $usersApi,
        SessionsApi $sessionsApi
    ) {
        $this->accountApi = $accountApi;
        $this->encryptionService = $encryptionService;
        $this->subscriptions__API = $subscriptions__API;
        $this->favourites__API = $favourites__API;
        $this->collections__API = $collections__API;
        $this->friendsApi = $friendsApi;
        $this->usersApi = $usersApi;
        $this->sessionsApi = $sessionsApi;
    }
    

    // Сохранение текущего состояния плеера
    // @param string $currentPlayer JSON-строка с данными плеера
    // @return bool Результат сохранения
    public function saveCurrentPlayer(string $currentPlayer) {
        $myAccount = $this->getMyAccount(false, false, false);
        if (!$myAccount) {
            return false;
        }
        // Декодируем JSON и проверяем
        $playerData = json_decode($currentPlayer, true);
        if (!is_array($playerData) || !$this->validatePlayerStructure($playerData)) {
            return false;
        }
        // Экранирование строк перед сохранением
        $this->sanitizePlayerData($playerData);
        $myAccountId = (int) $myAccount['id'];
        // Сохраняем JSON в базу
        if ($this->accountApi->updateCurrentPlayer($myAccountId, json_encode($playerData, JSON_UNESCAPED_UNICODE))) {
            return true;
        }
        return false;
    }
    // Валидация структуры плеера
    private function validatePlayerStructure(array $data): bool {
        $expectedStructure = [
            "id" => "string",
            "uri" => "string",
            "title" => "string",
            "ico" => "string",
            "artists" => "array",
            "sc_id" => "integer"
        ];

        foreach ($expectedStructure as $key => $type) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
            if (gettype($data[$key]) !== $type) {
                return false;
            }
        }

        if (!is_array($data["artists"])) {
            return false;
        }

        foreach ($data["artists"] as $artist) {
            if (!is_array($artist) || !isset($artist["id"], $artist["name"])) {
                return false;
            }
            if (!is_string($artist["id"]) || !is_string($artist["name"])) {
                return false;
            }
        }

        return true;
    }
    // Экранирование данных плеера
    private function sanitizePlayerData(array &$data): void {
        foreach ($data as $key => &$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                $this->sanitizePlayerData($value); // Рекурсивно обрабатываем вложенные массивы
            }
        }
    }

    
    // Обновление имени
    public function updateName(string $name){
        $account = $this->getMyAccount(false, false, false, false);
        if (!$account){ return false; }
        if (!$this->validateName($name)){ return false; } 
        $name = trim(preg_replace('/\s+/', ' ', $name));
        if ($this->accountApi->updateName((string) $name, (int) $account['id'])){
            return true;
        }
        return false;
    }
    private function validateName(string $name){
        $name_length = mb_strlen($name);
        if ($name_length < 3 or $name_length > 32){
            return false;
        }
        $name = preg_replace('/\s+/', '', $name);
        if (mb_strlen($name) < 3) {
            return false;
        }
        return true;
    }
    
    // Обновление настроек приватности
    public function updatePrivateSettings($collection_type, $new_setting){
        $account = $this->getMyAccount(true, true, true, true);
        if (!$account){ return false; }
        if (!in_array($collection_type, ['collection', 'subscriptions', 'favourites', 'friends'])){
            return false;
        }
        // !!!!--Узкое место, сравнение строки--!!!!!
        if ($new_setting == 'true'){ $new_setting = true; }else{ $new_setting = false; }
        if ($new_setting){ $new_access = 'public'; }else{ $new_access = 'private'; }
        switch ($collection_type){
            case 'subscriptions':
                if ($this->subscriptions__API->updateAccess(['access' => $new_access, 'id' => $account['subscriptions']['id']])){
                    $result = $new_access;
                }else{ $result = false; }
            break;
            case 'collection':
                if ($this->collections__API->updateAccess(['access' => $new_access, 'id' => $account['collection']['id']])){
                    $result = $new_access;
                }else{ $result = false; }
            break;
            case 'favourites':
                if ($this->favourites__API->updateAccess(['access' => $new_access, 'id' => $account['favourites']['id']])){
                    $result = $new_access;
                }else{ $result = false; }
            break;
            case 'friends':
                if ($this->friendsApi->updateAccess(['access' => $new_access, 'id' => $account['friends']['id']])){
                    $result = $new_access;
                }else{ $result = false; }
            break;
        }
        return $result;
    }


    // Взаимодействие с коллекциями
    public function likeUser($user_id) {
        if ($this->usersApi->getUserById(['id' => $user_id]) == null){
            return false;
        }
        $account = $this->getMyAccount(false, false, false, true);
        if ($user_id == $account['id']){
            return false;
        }
        if (!$account){ return false; }
        if (in_array($user_id, $account['friends']['users'])){
            $myItems = array_diff($account['friends']['users'], [$user_id]);
            $status = 'deleted';
        }else{
            $myItems = $account['friends']['users'];
            $myItems[] = $user_id;
            $status = 'added';
        }
        $myItems = json_encode($myItems, JSON_UNESCAPED_UNICODE);
        if ($this->friendsApi->updateItems(['id' => $account['friends']['id'], 'users' => $myItems])){
            return $status;
        }
        return false;
    }
    public function likeTrack($track_id, $spotifyTracks__Model) {
        // Используем общий метод toggleItem для работы с треками
        return $this->toggleItem($track_id, $spotifyTracks__Model, $this->favourites__API, 'favourites');
    }
    public function likeArtist($artist_id, $spotifyArtists__Model) {
        // Используем общий метод toggleItem для работы с артистами
        return $this->toggleItem($artist_id, $spotifyArtists__Model, $this->subscriptions__API, 'subscriptions');
    }
    public function likeRelease($album_id, $spotifyAlbums__Model) {
        // Используем общий метод toggleItem для работы с альбомами
        return $this->toggleItem($album_id, $spotifyAlbums__Model, $this->collections__API, 'collection');
    }


    // Общий метод для добавления/удаления элементов из коллекции (треков, артистов, альбомов)
    private function toggleItem($item_id, $model, $api, $itemType) {
        $user = $this->getMyAccount(true, true, true);
        if (!$user) {
            return false;
        }

        // Получаем данные о коллекции в зависимости от типа
        $collectionData = $this->getCollectionData($user, $itemType);
        $myItems = $collectionData['items'];  // Массив треков/артистов/альбомов
        $myItemsId = $collectionData['id'];  // ID коллекции

        // Проверяем, есть ли элемент в коллекции
        if ($this->isItemInCollection($item_id, $myItems)) {
            return $this->removeItemFromCollection($item_id, $myItems, $myItemsId, $api, $itemType);
        } else {
            return $this->addItemToCollection($item_id, $model, $myItems, $myItemsId, $api, $itemType);
        }
    }
    // Получаем данные о коллекции (трек, артист, альбом) в зависимости от типа
    private function getCollectionData($user, $itemType) {
        switch ($itemType) {
            case 'favourites':
                return [
                    'items' => $user['favourites']['tracks'],  // Данные по трекам
                    'id' => $user['favourites']['id']          // ID коллекции избранных
                ];
            case 'subscriptions':
                return [
                    'items' => $user['subscriptions']['artists'],  // Данные по артистам
                    'id' => $user['subscriptions']['id']            // ID коллекции подписок
                ];
            case 'collection':
                return [
                    'items' => $user['collection']['albums'],  // Данные по альбомам
                    'id' => $user['collection']['id']          // ID коллекции
                ];
            default:
                return ['items' => [], 'id' => null];
        }
    }
    // Проверка, есть ли элемент в коллекции
    private function isItemInCollection($item_id, $myItems) {
        return in_array($item_id, $myItems);
    }
    // Удаление элемента из коллекции
    private function removeItemFromCollection($item_id, $myItems, $myItemsId, $api, $itemType) {
        $myItems = array_diff($myItems, [$item_id]);
        $myItems = json_encode($myItems, JSON_UNESCAPED_UNICODE);

        $itemsName = $this->getApiItemsName($itemType);
        if (!$itemsName){ return false; }
        $data = [
            $itemsName => $myItems,
            'id' => $myItemsId
        ];

        if ($api->updateItems($data)) {
            return 'deleted';
        }
        return false;
    }
    // Добавление элемента в коллекцию
    private function addItemToCollection($item_id, $model, $myItems, $myItemsId, $api, $itemType) {
        $isItemSaved = $model->saveItem($item_id);
        if (!$isItemSaved) {
            return false;
        }

        $myItems[] = $item_id;
        $myItems = json_encode($myItems, JSON_UNESCAPED_UNICODE);

        $itemsName = $this->getApiItemsName($itemType);
        if (!$itemsName){ return false; }
        $data = [
            $itemsName => $myItems,
            'id' => $myItemsId
        ];

        if ($api->updateItems($data)) {
            return 'added';
        }
        return false;
    }
    private function getApiItemsName($type){
        switch ($type){
            case ('favourites'):
                return 'tracks';
            case ('subscriptions'):
                return 'artists';
            case ('collection'):
                return 'albums';
            default:
                return false;
        }
    }


    

    // Получение базовой информации по аккаунту
    // @param (bool) подписки, (bool) коллекция альбомов, (bool) избранное
    // @return (bool) false - завал авторизации, (array) - инфа по аккаунту
    public function getMyAccount($isSubs, $isColl, $isFav, $isFriends = false) {
        if (!isset($_COOKIE['session'])) {
            return false;
        }
        try {
            $user = $this->checkSessionByCookie();
            if ($user == false){
                return false;
            }
        
            $data = [
                'id' => $user['id'],
                'name' => $user['name'],
                'picture' => $user['picture'],
                'ip' => $user['ip'],
                'country' => $user['country'],
                'currentPlayer' => $user['currentPlayer'],
                'time' => $user['time']
            ];
            
            if ($isSubs){
                $data['subscriptions'] = $this->getMySubcriptions($user['id']);
            }
            if ($isColl){
                $data['collection'] = $this->getMyCollection($user['id']);
            }
            if ($isFav){
                $data['favourites'] = $this->getMyFavourites($user['id']);
            }
            if ($isFriends){
                $data['friends'] = $this->getMyFriends($user['id']);
            }
            return $data;
        } catch (Exception $e) {
            error_log('Ошибка в AccountModel: ' . $e->getMessage());
            return false;
        }
    }
    private function getMyFriends($id){
        $friends = $this->friendsApi->getFriends(['owner_id' => $id]);
        return $friends;
    }
    private function getMyFavourites($id){
        $favourites = $this->favourites__API->getFavourites(['owner_id' => $id]);
        return $favourites;
    }
    private function getMyCollection($id){
        $collection = $this->collections__API->getCollection(['owner_id' => $id]);
        return $collection;
    }
    private function getMySubcriptions($id){
        $subscriptions = $this->subscriptions__API->getSubscriptions(['owner_id' => $id]);
        return $subscriptions;
    }

    // Главная функция проверки сессии
    private function checkSessionByCookie(){
        $autho_data = json_decode((string)$_COOKIE['session'], JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Некорректные данные в куке.');
        }
        $id = $autho_data['id'];
        $client_secret = $autho_data['secret'];
        $client_secret = $this->encryptionService->mc_decrypt($client_secret, CLIENT_ENCRYPTION_KEY);
        $session = $this->sessionsApi->getSessionById(['id' => $id]);
        if ($session == null){ return false; }
        if ($session['status'] != 'active'){ return false; }

        $server_secret = $this->encryptionService->mc_decrypt($session['secret'], SYSTEM_ENCRYPTION_KEY);
        if ($server_secret != $client_secret){ return false; }

        $user = $this->accountApi->getUserById($session['account']);
        return $user;
    }
}