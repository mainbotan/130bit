class Environment__Model {
    constructor() {
        this.Api = API;
        this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    handleEnvironmentClick(element) {
        if (element.classList.contains('EnvirHandler_displayPicture')) {
            this.displayPicture(element);
        } else if (element.classList.contains('EnvirHandler_displayNotify')) {
            this.displayNotifications(element);
        } else if (element.classList.contains('EnvirHandler_displayDiscover')) {
            this.displayDiscover(element);
        } else if (element.classList.contains('EnvirHandler_autho')) {
            this.authoRefresh(element);
        } else if (element.classList.contains('EnvirHandler_displayPlayer')) {
            this.displayPlayer(element);
        } else if (element.classList.contains('.EnvirHandler_displayDiscover')) {
            this.displayDiscover();
        }
    }

    async updateSubscriptionsTab(){
        let result = await this.Api.getMySubscriptions({limit: 6});
        switch (result.status){
            case 'success':
                let subscriptions = result.data.subscriptions;
                $(ACCOUNT_CONTROL).find('.__Subscriptions').html(this.generateSubscriptionsTab(subscriptions));
            break;  
        }
    }
    generateSubscriptionsTab(collection){
        return collection.map(artist => {
            return `<div x='artist' param='id=${artist.id}' class='Navigation_UpdateWin Box'>
                <div class='Dash'></div>
                <div class='Pin Artist ToolTipeWrap' style='background-image: url(${artist.picture_small});'>
                    <div class='ToolTipe' style='color: #1ED760;'>${artist.name}<div class='AdTxt'>Вы подписаны</div></div>
                </div>
            </div>`;
        }).join('');
    }
    async updateCollectionTab(){
        let result = await this.Api.getMyCollection({limit: 10});
        switch (result.status){
            case 'success':
                let collection = result.data.collection;
                $(ACCOUNT_CONTROL).find('.__Collection').html(this.generateCollectionTab(collection));
            break;  
        }
    }
    generateCollectionTab(collection, type){
        return collection.map(album => {
            return `<div x='album' param='id=${album.id}' class='Navigation_UpdateWin Box'>
                <div class='Dash'></div>
                <div class='Pin Album ToolTipeWrap' style='background-image: url(${album.cover});'>
                    <div class='ToolTipe' style='color: #1ED760;'>${album.name}<div class='AdTxt'>В вашей коллекции</div></div>
                </div>
            </div>`;
        }).join('');
    }

    displayPicture(obj) {
        const pictureUrl = obj.getAttribute('data-picture-url');
        const showIcoElement = document.querySelector('.Envir__Show_Ico');
        if (showIcoElement.classList.contains('Active')) {
            showIcoElement.classList.remove('Active');
            setTimeout(() => {
                showIcoElement.querySelector('.Ico').style.backgroundImage = '';
            }, 200);
        } else {
            showIcoElement.querySelector('.Ico').style.backgroundImage = `url(${pictureUrl})`;
            showIcoElement.classList.add('Active');
        }
    }

    async displayNotifications() {
        if ($(ACCOUNT_NOTIFICATIONS).hasClass('Active') == false) {
            $(ACCOUNT_NOTIFICATIONS).addClass('Active');
            $(ACCOUNT_NOTIFICATIONS).find('.__Update').html(`<div class="Element__LoadingWrap" style='height: 400px; min-height: 400px;'><div class="Element__CircleLoader Big"></div></div>`);
            const result = await this.Api.getNotifications({ limit: 5, offset: 0 });
            if (result.status == 'success') {
                $(ACCOUNT_NOTIFICATIONS).find('.__Update').html(result.data.client);
            }
        }else{
            $(ACCOUNT_NOTIFICATIONS).removeClass('Active');
        }
    }

    displayDiscover() {
        if ($(DISCOVER).hasClass('Active') == false) {
            $(DISCOVER).find('.__Update').animate({ scrollTop: 0 }, 600);
            $(DISCOVER).find('.Result').animate({ scrollTop: 0 }, 600);
            $(DISCOVER).find('.Content').animate({ scrollTop: 0 }, 600);
            $(DISCOVER).addClass('Active');
            $(DISCOVER_VALUE).focus();
        }else{
            $(DISCOVER).removeClass('Active');
        }
    }

    displayPlayer() {
        if ($(PLAYER).hasClass('Active') == false) {
            if (this.isMobile){
                $('.Envir__PlayerCanvas').addClass('Active');
            }
            $(PLAYER).addClass('Active');
            $(PLAYER).focus();
        }else{
            $(PLAYER).removeClass('Active');
            if (this.isMobile){
                $('.Envir__PlayerCanvas').removeClass('Active');
            }
        }
    }
    displayPlayerState() {
        if ($(PLAYER_STATE).hasClass('Active') == false) {
            $(PLAYER_STATE).addClass('Active');
        }else{
            $(PLAYER_STATE).removeClass('Active');
        }
    }

    authoRefresh() {
        window.location.href = 'https://my.130bit.com';
    }
}

const EnvironmentModel = new Environment__Model;