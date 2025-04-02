class VolumeService {
    constructor() {
        this.volumeSlider = document.querySelector('.VolumeSlider');
        this.volumeSliderInput = document.getElementById('__PLAYER-VOLUME__');
        this.init();
    }

    init() {
        let localCurrentVolume = localStorage.getItem('currentVolume');
        if (localCurrentVolume != null){
            $(this.volumeSliderInput).val(localCurrentVolume);
            SCEmulation.currentVolume = localCurrentVolume;
        }
        
        // Инициализация обработчика изменения громкости
        this.volumeSlider.addEventListener('input', (event) => {
            this.setVolume(event.target.value);
        });
    }

    updateSliderProgress() {
        const value = this.volumeSliderInput.value;
        this.volumeSliderInput.style.setProperty('--range-progress', `${value}%`);
    }
    setVolume(volume) {
        // Здесь можно добавить логику для изменения громкости (например, для аудио)
        SCEmulation.widget.setVolume(volume);

        this.updateSliderProgress();

        // Сохранение в экземляр scEmulation
        SCEmulation.currentVolume = volume;
        localStorage.setItem('currentVolume', volume);
    }
}

// Инициализация сервиса
const volumeService = new VolumeService();