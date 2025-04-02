class Api__Model {
    constructor(baseUrl) {
        this.baseUrl = baseUrl; // Базовый URL API
        this.token = AUTHO_TOKEN; // Токен для аутентификации
    }
    async fetchData(action, data) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'App/Controllers/ajaxController',
                method: 'post',
                dataType: 'html',
                data: {
                    action: action,
                    data: data,
                    token: AUTHO_TOKEN
                },
                success: function (data) {
                    try {
                        resolve(JSON.parse(data));
                    } catch (error) {
                        reject('Error parsing JSON');
                    }
                },
                error: function (xhr, status, error) {
                    reject(`AJAX error: ${error}`);
                }
            });
        });
    }
    async getNotifications(query_data) {
        let action = 'getNotifications';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async getLightSearch(query_data) {
        let action = 'getLightSearch';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async getHardSearch(query_data) {
        let action = 'getHardSearch';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async likeAlbum(query_data){
        let action = 'likeAlbum';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async likeArtist(query_data){
        let action = 'likeArtist';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async likeTrack(query_data){
        let action = 'likeTrack';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async likeUser(query_data){
        let action = 'likeUser';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async recommendArtist(query_data){
        let action = 'recommendArtist';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async recommendAlbum(query_data){
        let action = 'recommendAlbum';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async recommendPlaylist(query_data){
        let action = 'recommendPlaylist';
        let data = await this.fetchData(action, query_data);
        return data;
    }

    // For lazyLoading_Service
    async lazyLoading(action, query_data){
        action = 'lazyLoading_' + action;
        let data = await this.fetchData(action, query_data);
        return data;
    }

    // For player module
    async getSoundCloudId(query_data){
        let action = 'getSoundCloudId';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async saveCurrentPlayer(query_data){
        let action = 'saveCurrentPlayer';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async getCurrentPlayer(){
        let action = 'getCurrentPlayer';
        let data = await this.fetchData(action, null);
        return data;
    }
    async getTrackStatusInFavorites(query_data){
        let action = 'getTrackStatusInFavourites';
        let data = await this.fetchData(action, query_data);
        return data;
    }

    // For control tab
    async getMyCollection(query_data){
        let action = 'getMyCollection';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async getMySubscriptions(query_data){
        let action = 'getMySubscriptions';
        let data = await this.fetchData(action, query_data);
        return data;
    }

    // For artist page
    async getArtistInfo(query_data){
        let action = 'getArtistInfo';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async getArtistStat(query_data){
        let action = 'getArtistStat';
        let data = await this.fetchData(action, query_data);
        return data;
    }

    // For album page
    async getAlbumStat(query_data){
        let action = 'getAlbumStat';
        let data = await this.fetchData(action, query_data);
        return data;
    }

    // For profile settings
    async updatePrivateSettings(query_data){
        let action = 'updatePrivateSettings';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async updateName(query_data){
        let action = 'updateName';
        let data = await this.fetchData(action, query_data);
        return data;
    }

    // For community search
    async getCommunitySearch(query_data){
        let action = 'getCommunitySearch';
        let data = await this.fetchData(action, query_data);
        return data;
    }

    // For comments service
    async sendComment(query_data){
        let action = 'sendComment';
        let data = await this.fetchData(action, query_data);
        return data;
    }
    async commentsLazyLoading(query_data){
        let action = 'commentsLazyLoading';
        let data = await this.fetchData(action, query_data);
        return data;
    }
}

// Инициализация
const API = new Api__Model('app/controllers/ajaxController');