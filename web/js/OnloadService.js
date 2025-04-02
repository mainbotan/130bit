class OnloadService {
    constructor(callback) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", callback);
        } else {
            callback();
        }
    }
}

new OnloadService(() => {
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    // Восстановление плеера
    PlayerModel.recoverPlayer();
    // Подгрузка табов
    if (IS_USER & !isMobile){
        EnvironmentModel.updateCollectionTab();
        EnvironmentModel.updateSubscriptionsTab();
    }
});
