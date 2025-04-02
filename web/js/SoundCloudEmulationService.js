class SoundCloudEmulation {
    constructor() {
        this.widget = null;
        this.progressBar = document.getElementById('progress-bar');
        this.playerProgress = document.getElementById('__PLAYER-PROGRESS__');
        this.realPlayer_progressBar = document.getElementById('real-player-progress-bar');
        this.realPlayer_playerProgress = document.getElementById('__REAL-PLAYER-PROGRESS__');
        this.playerState = new PlayerState();
        this.init();
    }

    init() {
        const iframe = document.getElementById('__PLAYER-IFRAME__');
        iframe.addEventListener('load', () => {
            setTimeout(() => {
                this.widget = SC.Widget(iframe);
                this.bindEvents();
                // console.log('SoundCloud Widget инициализирован:', this.widget);
            }, 1000);
        });

        this.playerProgress.addEventListener('input', () => this.seekTrack());
        this.realPlayer_playerProgress.addEventListener('input', () => this.seekTrack(true));
    }
    setCurrentVolume(){
        this.currentVolume = localStorage.getItem('currentVolume');
        if (this.currentVolume == null || this.currentVolume == undefined){
            this.widget.setVolume(50);
        }  else{
            this.widget.setVolume(this.currentVolume);
        }
    }
    bindEvents() {
        if (this.widget) {
            this.setCurrentVolume();

            this.widget.bind(SC.Widget.Events.READY, () => {
                this.widget.play();
            });

            this.widget.bind(SC.Widget.Events.PLAY, () => {
                this.updatePlayButton(true);
            });

            this.widget.bind(SC.Widget.Events.PAUSE, () => {
                this.updatePlayButton(false);
            });

            this.widget.bind(SC.Widget.Events.FINISH, () => {
                if (this.playerState.isRepeatEnabled()) {
                    this.widget.play();
                } else {
                    PlayerModel.nextTrack();
                }
            });

            this.widget.bind(SC.Widget.Events.PLAY_PROGRESS, (progress) => {
                if (typeof progress.relativePosition === 'number') {
                    const progressPercent = progress.relativePosition * 100;
                    this.progressBar.style.width = `${progressPercent}%`;
                    this.playerProgress.value = progressPercent;
                    this.realPlayer_progressBar.style.width = `${progressPercent}%`;
                    this.realPlayer_playerProgress.value = progressPercent;
                }
            });
        }
    }

    updatePlayButton(isPlaying) {
        const src = isPlaying ? 'web/ico/black/pause.png' : 'web/ico/black/play.png';
        $(PLAYER).find('.__PlayButton img').attr('src', src);
        $(PLAYER_STATE).find('.__PlayButton img').attr('src', src);
    }

    seekTrack(isRealPlayer = false) {
        if (this.widget) {
            this.widget.getDuration((duration) => {
                if (typeof duration === 'number') {
                    const seekTo = (isRealPlayer ? this.realPlayer_playerProgress.value : this.playerProgress.value) / 100 * duration;
                    this.widget.seekTo(seekTo);
                }
            });
        }
    }
}

const SCEmulation = new SoundCloudEmulation();