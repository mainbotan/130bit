class EventDelegator {
    constructor() {
        this.initEventListeners();
        this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    initEventListeners() {
        document.body.addEventListener('click', (event) => {
            // Ищем элементы с нужными классами
            const quickActionElement = event.target.closest('.QuickAction_ExternalLikeArtist, .QuickAction_RecommendPlaylist, .QuickAction_LikeUser, .QuickAction_LikeAlbum, .QuickAction_LikeArtist, .QuickAction_LikeTrack, .QuickAction_RecommendArtist, .QuickAction_RecommendAlbum');
            const playerTrackElement = event.target.closest('.Player_Track');
            const playerActionElement = event.target.closest('.PlayerAction_NextTrack, .PlayerAction_PreviousTrack, .PlayerAction_RepeatTrack, .PlayerAction_PlayOrPause, .PlayerAction_CurrentTrack');
            const environmentElement = event.target.closest('.EnvirHandler_displayDiscover, .EnvirHandler_displayPicture, .EnvirHandler_displayNotify, .EnvirHandler_autho, .EnvirHandler_displayPlayer');
            const navigationElement = event.target.closest('.Navigation_UpdateWin');
            const controlPanelElement = event.target.closest('.ControlPanel_Section');

            // Если клик на QuickAction элементе
            if (quickActionElement) {
                event.stopPropagation(); // Останавливаем всплытие для QuickActions
                QuickActionsModel.handleQuickActionClick(quickActionElement);
            } 
            // Если клик на Player_Track элементе, но не внутри дочерних элементов (например, Navigation_UpdateWin)
            else if (playerTrackElement && !navigationElement) {
                event.stopPropagation(); // Останавливаем всплытие для Player_Track
                PlayerModel.handlePlayerTrackClick(playerTrackElement);
            } 
            else if (playerActionElement) {
                PlayerModel.handlePlayerActionClick(playerActionElement);
            } 
            // Если клик на Environment элементе
            else if (environmentElement) {
                event.stopPropagation(); // Останавливаем всплытие для Environment
                EnvironmentModel.handleEnvironmentClick(environmentElement);
            } 
            // Если клик на Navigation элементе
            else if (navigationElement) {
                event.stopPropagation(); // Останавливаем всплытие для Navigation
                NavigationModel.handleNavigationClick(navigationElement);
            }
            // Если клик на Control Panel элементе
            else if (controlPanelElement) {
                ControlPanelModel.handleClick(controlPanelElement);
            }
        });
    }
}

const eventDelegator = new EventDelegator();
