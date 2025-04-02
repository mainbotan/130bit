class QuickActions__Model {
    constructor() {
        this.Api = API;
    }

    handleQuickActionClick(element) {
        if (element.classList.contains('QuickAction_LikeAlbum')) {
            this.likeAlbum(element);
        } else if (element.classList.contains('QuickAction_LikeArtist')) {
            this.likeArtist(element);
        } else if (element.classList.contains('QuickAction_LikeTrack')) {
            this.likeTrack(element);
        } else if (element.classList.contains('QuickAction_RecommendArtist')) {
            this.recommendArtist(element);
        } else if (element.classList.contains('QuickAction_RecommendAlbum')) {
            this.recommendAlbum(element);
        } else if (element.classList.contains('QuickAction_LikeUser')) {
            this.likeUser(element);
        } else if (element.classList.contains('QuickAction_RecommendPlaylist')) {
            this.recommendPlaylist(element);
        } else if (element.classList.contains('QuickAction_ExternalLikeArtist')) {
            this.externalLikeArtist(element);
        }
    }

    async likeUser(obj) {
        if (this.checkUser()) {
            let id = obj.getAttribute('data-user-id');
            let result = await this.Api.likeUser({ id });
            if (result.status == 'success') {
                this.updateLikeStatus(result.data.action, id, 'User');
                switch(result.data.action){
                    case 'added':
                        MessageService.displayMessage({title: 'Вы отслеживаете'});
                    break;
                }
            }
        }
    }

    async likeArtist(obj) {
        if (this.checkUser()) {
            let id = obj.getAttribute('data-spotify-id');
            let result = await this.Api.likeArtist({ id });
            if (result.status == 'success') {
                this.updateLikeStatus(result.data.action, id, 'Artist');
                EnvironmentModel.updateSubscriptionsTab();
                switch(result.data.action){
                    case 'added':
                        MessageService.displayMessage({title: 'Добавлен в подписки'});
                    break;
                }
            }
        }
    }

    async externalLikeArtist(obj) {
        if (this.checkUser()) {
            let id = obj.getAttribute('data-spotify-id');
            let result = await this.Api.likeArtist({ id });
            if (result.status == 'success') {
                EnvironmentModel.updateSubscriptionsTab();
                switch(result.data.action){
                    case 'added':
                        $(obj).addClass('Active');
                        $(obj).html('Followed');
                    break;
                    default:
                        $(obj).removeClass('Active');
                        $(obj).html('Follow');
                    break;
                }
            }
        }
    }

    async likeAlbum(obj) {
        if (this.checkUser()) {
            let id = obj.getAttribute('data-spotify-id');
            let result = await this.Api.likeAlbum({ id });
            if (result.status == 'success') {
                this.updateLikeStatus(result.data.action, id, 'Album');
                EnvironmentModel.updateCollectionTab();
                switch(result.data.action){
                    case 'added':
                        MessageService.displayMessage({title: 'Альбом в коллекции'});
                    break;
                }
            }
        }
    }

    async likeTrack(obj) {
        if (this.checkUser()) {
            let id = obj.getAttribute('data-spotify-id');
            let result = await this.Api.likeTrack({ id });
            if (result.status == 'success') {
                this.updateLikeStatus(result.data.action, id, 'Track');
            }
        }
    }

    async recommendArtist(obj) {
        let id = obj.getAttribute('data-spotify-id');
        let result = await this.Api.recommendArtist({ id });
        if (result.status == 'success') {
            if (result.data.action) {
                obj.classList.add('Active');
                obj.innerHTML = 'В рекомендациях';
            }
        }
    }

    async recommendAlbum(obj) {
        let id = obj.getAttribute('data-spotify-id');
        let result = await this.Api.recommendAlbum({ id });
        if (result.status == 'success') {
            if (result.data.action) {
                obj.classList.add('Active');
                obj.innerHTML = 'В рекомендациях';
            }
        }
    }

    async recommendPlaylist(obj) {
        let id = obj.getAttribute('data-spotify-id');
        let result = await this.Api.recommendPlaylist({ id });
        if (result.status == 'success') {
            if (result.data.action) {
                obj.classList.add('Active');
                obj.innerHTML = 'В рекомендациях';
            }
        }
    }

    updateLikeStatus(action, id, type) {
        const element = document.querySelector(`.${type}-${id} .__Like`);
        if (action === 'added') {
            // element.setAttribute('src', 'web/ico/1ED760/heart.png');
            $(`.${type}-${id} .__Like`).attr('src', 'web/ico/1ED760/heart.png');
        } else if (action === 'deleted') {
            // element.setAttribute('src', 'web/ico/1ED760/heart_empty.png');
            $(`.${type}-${id} .__Like`).attr('src', 'web/ico/1ED760/heart_empty.png');
        }
    }

    checkUser() {
        return IS_USER;
    }
}


const QuickActionsModel = new QuickActions__Model;