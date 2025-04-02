class PlayerState {
    constructor() {
        this.currentTrackId = null;
        this.currentStack = [];
        this.repeatTrack = false;
    }

    setCurrentTrack(track) {
        this.currentTrackId = track.id;
        localStorage.setItem('currentPlayer', JSON.stringify(track));
    }

    setCurrentStack(stack) {
        this.currentStack = stack;
        localStorage.setItem('currentStack', JSON.stringify(stack));
    }

    toggleRepeat() {
        this.repeatTrack = !this.repeatTrack;
        localStorage.setItem('repeatTrack', this.repeatTrack);
    }

    getCurrentTrack() {
        return JSON.parse(localStorage.getItem('currentPlayer'));
    }

    getCurrentStack() {
        return JSON.parse(localStorage.getItem('currentStack'));
    }

    isRepeatEnabled() {
        return localStorage.getItem('repeatTrack') === 'true';
    }
    
    updateMeta(trackInfo){
        $('title').html(`${trackInfo.title} by ${trackInfo.artists[0].name} | 130bit`);
    }
}