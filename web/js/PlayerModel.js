class Player__Model {
    constructor() {
        this.Api = API;
        this.Environment = EnvironmentModel;
        this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        this.playerState = new PlayerState();
        this.scEmulation = new SoundCloudEmulation();
        this.default_music_pic = 'https://130bit.com/open/web/logos/130bit_white_logo.png';
    }

    handlePlayerTrackClick(element) {
        if (element.classList.contains('Player_Track')) {
            this.playTrack(element);
        }
    }

    handlePlayerActionClick(element) {
        const actions = {
            'PlayerAction_CurrentTrack': () => this.refreshToCurrentTrack(),
            'PlayerAction_PlayOrPause': () => this.playOrPause(),
            'PlayerAction_RepeatTrack': () => this.toggleRepeat(),
            'PlayerAction_PreviousTrack': () => this.previousTrack(),
            'PlayerAction_NextTrack': () => this.nextTrack(),
        };

        for (const [className, action] of Object.entries(actions)) {
            if (element.classList.contains(className)) {
                action();
                break;
            }
        }
    }

    refreshToCurrentTrack() {
        if (this.playerState.currentTrackId) {
            NavigationModel.updateWinWithoutObj('track', `id=${this.playerState.currentTrackId}`);
        }
    }

    toggleRepeat() {
        this.playerState.toggleRepeat();
        const src = this.playerState.isRepeatEnabled() ? 'web/ico/1ED760/repeat.png' : 'web/ico/696969/repeat.png';
        $(PLAYER).find('.__RepeatButton img').attr('src', src);
        $(PLAYER_STATE).find('.__RepeatButton img').attr('src', src);
    }

    playOrPause() {
        this.scEmulation.widget.isPaused((paused) => {
            if (paused) {
                this.scEmulation.widget.play();
            } else {
                this.scEmulation.widget.pause();
            }
        });
    }

    findTrackIndexById(trackId, trackList) {
        return trackList.findIndex(track => track.id === trackId);
    }

    changeTrack(direction) {
        const { currentTrackId, currentStack } = this.playerState;
        const currentIndex = this.findTrackIndexById(currentTrackId, currentStack);

        if (currentIndex === -1) {
            // console.error('Текущий трек не найден в очереди.');
            return;
        }

        const newIndex = direction === 'next'
            ? (currentIndex + 1) % currentStack.length
            : (currentIndex - 1 + currentStack.length) % currentStack.length;

        return currentStack[newIndex];
    }

    previousTrack() {
        const newTrack = this.changeTrack('previous');
        this.playTrack(newTrack, true);
    }

    nextTrack() {
        const newTrack = this.changeTrack('next');
        this.playTrack(newTrack, true);
    }
    buildQueueFromTracksStack(track) {
        const tracksStack = track.closest('.__TracksStack');
        if (tracksStack == null){return [];}
        const tracks = tracksStack.querySelectorAll('.Track');
        const queue = [];
    
        tracks.forEach(track => {
            // Извлекаем данные о треке
            const trackData = {
                id: track.dataset.spotifyId,
                uri: track.dataset.spotifyUri,
                title: track.querySelector('.__TrackTitle').textContent,
                artists: this.getArtists(track),
                ico: track.querySelector('.__TrackIco').textContent
            };
    
            // Добавляем трек в очередь
            queue.push(trackData);
        });
    
        return queue;
    }

    async playTrack(track, isObject = false) {
        try {
            const trackInfo = isObject ? track : this.getTrackInfo(track);
            if (isObject == false){
                const queue = this.buildQueueFromTracksStack(track);

                if (queue.length > 0) {
                    this.playerState.setCurrentStack(queue);
                    $(PLAYER_STATE).find('.__StackAction').css('display', 'inline-flex');
                } else {
                    $(PLAYER_STATE).find('.__StackAction').css('display', 'none');
                }
            }

            this.animateTrack(trackInfo.id);

            if (trackInfo.id !== this.playerState.currentTrackId) {
                const result = await this.Api.getSoundCloudId({
                    track_name: trackInfo.title,
                    track_artists: trackInfo.artists,
                });

                if (result.status === 'success') {
                    trackInfo.sc_id = result.data.id;
                    this.updatePlayerState(trackInfo);
                    this.updatePlayer(trackInfo);
                    this.playerState.setCurrentTrack(trackInfo);
                } else {
                    MessageService.displayMessage({ title: 'Трек не найден...' });
                }
            }else{
                this.playOrPause();
            }
        } catch (error) {
            // console.error('Ошибка в playTrack:', error);
        }
    }

    updateLockScreen(trackInfo) {
        const defaultCover = this.default_music_pic;
        const artworkUrl = trackInfo.ico || defaultCover;
      
        // Проверяем URL (если он относительный, делаем абсолютным)
        const fullArtworkUrl = artworkUrl.startsWith('http') 
          ? artworkUrl 
          : `https://${artworkUrl}`;
      
      
        if ('mediaSession' in navigator) {
          navigator.mediaSession.metadata = new MediaMetadata({
            title: trackInfo.title || 'Без названия',
            artist: trackInfo.artists?.[0]?.name || 'Неизвестный исполнитель',
            artwork: [
              { src: fullArtworkUrl},
            ],
          });
      
          // Кнопки управления
          navigator.mediaSession.setActionHandler('play', () => this.playOrPause());
          navigator.mediaSession.setActionHandler('pause', () => this.playOrPause());
          navigator.mediaSession.setActionHandler('previoustrack', () => this.previousTrack());
          navigator.mediaSession.setActionHandler('nexttrack', () => this.nextTrack());
        }
    }

    async recoverPlayer(){
        let trackInfo = null;
        let currentStack = null;

        // if (IS_USER){
        //     let result = await this.Api.getCurrentPlayer();
        //     if (result.status == 'success'){
        //         trackInfo = result.data.currentPlayer;
        //         console.log(trackInfo);
        //     }
        // }else{
        //     trackInfo = localStorage.getItem('currentPlayer');
        // }

        trackInfo = localStorage.getItem('currentPlayer');
        try {
            trackInfo = JSON.parse(trackInfo);
            if (trackInfo.id != null){
                this.updatePlayerState(trackInfo);
                this.updatePlayer(trackInfo);
                this.animatePlayerState();

                globalThis.currentTrackId = trackInfo.id;
                this.currentTrackId = trackInfo.id;
                this.playerState.currentTrackId = trackInfo.id;
            }
        } catch (error) {
        }

        try {
            currentStack = JSON.parse(localStorage.getItem('currentStack'));
            globalThis.currentStack = currentStack;
            if (currentStack.length < 1){
                $(PLAYER_STATE).find('.__StackAction').css('display', 'none');
            }
        } catch (error) {
            $(PLAYER_STATE).find('.__StackAction').css('display', 'none');
        }

        if (localStorage.getItem('repeatTrack') == 'true'){
            $(PLAYER).find('.__RepeatButton img').attr('src', 'web/ico/1ED760/repeat.png');
            $(PLAYER_STATE).find('.__RepeatButton img').attr('src', 'web/ico/1ED760/repeat.png');
            globalThis.repeatTrack = true;
        }else{
            globalThis.repeatTrack = false;
        }
    }
    animateTrack(trackId){
        $('.Track').removeClass('Active');
        $(`.Track-${trackId}`).addClass('Active');
    }
    savePlayer(trackInfo){
        let playerState = JSON.stringify(trackInfo);
    }
    async updatePlayer(trackInfo) {
        /*PLAYER*/
        this.playerState.updateMeta(trackInfo);
        this.updateLockScreen(trackInfo);

        // Генерация URL для iframe
        let iframeUrl = `https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/${trackInfo.sc_id}&` + this.getSoundCloudConfig();

        // Получаем iframe
        const iframe = document.getElementById('__PLAYER-IFRAME__');

        // Обновляем src iframe
        iframe.src = iframeUrl;

        // Переинициализируем SC.Widget после загрузки iframe
        iframe.addEventListener('load', () => {
            setTimeout(() => {
                try {
                    this.scEmulation.widget = SC.Widget(iframe);
                    this.scEmulation.bindEvents(); // Привязываем события заново
                    // console.log('SoundCloud Widget инициализирован:', this.scEmulation.widget.widget);

                    // Обновляем playButton после инициализации
                    this.scEmulation.widget.widget.isPaused((paused) => {
                        this.scEmulation.widget.updatePlayButton(!paused);
                    });
                } catch (error) {
                    // console.error('Ошибка при инициализации SC.Widget:', error);
                }
            }, 500); // Задержка 500 мс
        });

        // Обновляем интерфейс плеера
        $(PLAYER).find('.__Ico').css('background-image', `url(${trackInfo.ico})`);
        $(PLAYER).find('.__Title').html(trackInfo.title);
        $(PLAYER).find('.__Ad').html(this.generateArtistsHTML(trackInfo.artists));

        // Получение статуса трека в избранном
        this.getTrackStatusInFavourites(trackInfo.id);
    }
    async getTrackStatusInFavourites(trackId){
        if (!IS_USER){return null;}
        let result = await this.Api.getTrackStatusInFavorites({id: trackId});
        switch (result.status) {
            case 'success':
                let ico = 'web/ico/1ED760/heart_empty.png';
                if (Boolean(result.data.isTrackInFavourites)){
                    ico = 'web/ico/1ED760/heart.png';
                }
                $(PLAYER_STATE).find('.__LikeArea .__Like').attr('src', ico);
                $(PLAYER_STATE).find('.__LikeArea').attr('data-spotify-id', trackId);
                $(PLAYER_STATE).find('.__LikeArea').addClass('Active');
                $(PLAYER_STATE).find('.__LikeArea div').removeClass();
                $(PLAYER_STATE).find('.__LikeArea div').addClass('Track');
                $(PLAYER_STATE).find('.__LikeArea div').addClass(`Track-${trackId}`);

                $(PLAYER).find('.Focus .__LikeArea .__Like').attr('src', ico);
                $(PLAYER).find('.Focus .__LikeArea').attr('data-spotify-id', trackId);
                $(PLAYER).find('.Focus .__LikeArea').addClass('Active');
                $(PLAYER).find('.Focus .__LikeArea div').removeClass();
                $(PLAYER).find('.Focus .__LikeArea div').addClass('Track');
                $(PLAYER).find('.Focus .__LikeArea div').addClass(`Track-${trackId}`);

                break;
            default:
                $(PLAYER_STATE).find('.__LikeArea').removeClass('Active');
        }
    } 

    animatePlayer(){
        $(PLAYER).removeClass('Active');
        $(PLAYER).addClass('Active');
    }
    updatePlayerState(trackInfo) {
        /*PLAYER STATE*/
        $(PLAYER_STATE).addClass('Active');
        $(PLAYER_STATE).find('.__Ico').css('background-image', `url(${trackInfo.ico})`);
        $(PLAYER_STATE).find('.__Title').html(trackInfo.title);
        $(PLAYER_STATE).find('.__Ad').html(`${this.generateArtistsHTML(trackInfo.artists)} | Сейчас в плеере`);
    
        const colorThief = new ColorThief();
        const img = new Image();
    
        img.crossOrigin = 'Anonymous';
        img.src = trackInfo.ico;
    
        img.onload = function() {
            const dominantColor = colorThief.getColor(img);
            const colorString = dominantColor.join(',');
    
            // Градиент с преобладанием чёрного и лёгким отблеском dominantColor
            const gradient = `linear-gradient(
                -90deg, rgba(0, 0, 0, 1) 0%,   rgba(${colorString}, 1) 50%, rgba(0, 0, 0, 1) 100%   
            )`;
    
            $(PLAYER_STATE_CANVAS).css('background', gradient);
        };
    
        img.onerror = function() {
            // console.error('Ошибка загрузки изображения:', img.src); // Отладочное сообщение
        };
    }
    animatePlayerState(){
        $(PLAYER_STATE).removeClass('Active');
        $(PLAYER_STATE).addClass('Active');
        $(PLAYER_STATE_CANVAS).removeClass('Active');
        $(PLAYER_STATE_CANVAS).addClass('Active');
    }
    getTrackInfo(track){
        return {
            id: $(track).attr('data-spotify-id'),
            uri: $(track).attr('data-spotify-uri'),
            title: $(track).find('.__TrackTitle').html(),
            ico: $(track).find('.__TrackIco').html(),
            artists: this.getArtists(track),
        }
    }
    getArtists(track) {
        const artistsContainer = track.querySelector('.__TrackArtists');
        const artistsArray = [];
        if (artistsContainer) {
            const artistElements = artistsContainer.querySelectorAll('.__Artist');
    
            artistElements.forEach(artistElement => {
                let artistId = $(artistElement).attr('data-artist-id');
                let artistName = $(artistElement).attr('data-artist-name');
    
                artistsArray.push({ id: artistId, name: artistName });
            });
        }
        return artistsArray;
    }
    generateArtistsHTML(artistsArray) {
        return artistsArray.map(artist => {
            return `<div x="artist" param="id=${artist.id}" data-artist-id="${artist.id}" data-artist-name="${artist.name}" class="__Artist Navigation_UpdateWin display--block Element__HoverWhite ToolTipeWrap">
                        ${artist.name}
                    </div>`;
        }).join(' & ');
    }
    getSoundCloudConfig(){
        let Config = {
            color: '#000000',
            auto_play: false,
            loop: true,
            hide_related: true,
            show_comments: false,
            show_user: false,
            show_reposts: false,
            show_teaser: false,
            visual: true,
            download: false
        };
        return Object.entries(Config)
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
        .join('&');
    }
    

    checkUser() {
        return IS_USER;
    }
}

const PlayerModel = new Player__Model;