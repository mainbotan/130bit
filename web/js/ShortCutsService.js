
// ShortCuts__Serviice

/*____ShortCuts____*/
$(document).on('keydown', function (event) {
    // Проверяем, нажаты ли Ctrl (или Cmd на Mac) + K
    if ((event.ctrlKey || event.metaKey)) {
        let key = event.key;
        switch (key) {
            case 'k':
                event.preventDefault();
                EnvironmentModel.displayDiscover();
            break;
            case 'p':
                event.preventDefault();
                EnvironmentModel.displayPlayer();
            break;
            case 'n':
                event.preventDefault();
                EnvironmentModel.displayNotifications();
            break;
            case 'a':
                event.preventDefault();
                if (IS_USER){
                    NavigationModel.updateWinWithoutObj('profile');
                }
            break;
            case 'h':
                event.preventDefault();
                NavigationModel.updateWinWithoutObj('viewer');
            break;
        }
    }
});