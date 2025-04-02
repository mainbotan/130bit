class Navigation__Model {
    constructor(){
        this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    handleNavigationClick(element) {
        if (element.classList.contains('Navigation_UpdateWin')) {
            this.updateWin(element);
        } 
    }

    // Пререндеринг
    async preRenderPage() {
        await this.dropModalEnvironment();
        $(ACTIVE).html(LOADING_PLUG_X);
        await this.renderPage();
    }

    // Запрос к контроллеру
    async renderPage() {
        let CurrentUrl = window.location.href;

        try {
            const data = await this.fetchData(CurrentUrl);  // Ожидаем завершения AJAX-запроса

            switch (data.status) {
                case 'success':
                    // Ждем, пока data.client отрендерится в ACTIVE
                    await this.renderClientData(data.data.client);
                    
                    // Обновляем окружение
                    await this.environmentAnimate();

                    // Переустановка меты
                    await this.resetMeta();
                    break;

                case 'invalid_token':
                    window.location.reload();
                    break;
            }
        } catch (error) {
            console.error('Error rendering page:', error);
        }
    }

    // Асинхронный запрос для получения данных
    async fetchData(CurrentUrl) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'App/Controllers/mainController',
                method: 'post',
                dataType: 'html',
                data: {
                    url: CurrentUrl,
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

    // Функция для рендеринга данных в ACTIVE
    async renderClientData(clientData) {
        return new Promise((resolve) => {
            $(ACTIVE).html(clientData);
            resolve();  // Уведомляем, что рендеринг завершен
        });
    }

    // Переустановка меты
    async resetMeta() {
        let MetaTitle = $('#__MetaTitle').html();
        if (MetaTitle != null) {
            $('title').html(`${MetaTitle} | 130bit`);
        }
    }

    // Обновление окружения
    async environmentAnimate() {
        $('html, body').css("overflow", 'auto');
        $(ACTIVE).animate({ scrollTop: 0 }, 600);
    }

    // Сброс модального окружения
    async dropModalEnvironment() {
        $(SEARCH_VALUE).val('');
        $(DISCOVER).removeClass('Active');
        $(SEARCH_BLOCK).removeClass('Active');
        $(SEARCH_BLOCK).addClass('Default');
        $(PLAYER).removeClass('Active');
    }

    // Обновление истории
    pushState(action, parameters, savePrevious) {
        let Url = window.location.href.split('?')[0];
        if (parameters != null) {
            Url = Url + "?x=" + action + '&' + parameters;
        } else {
            Url = Url + "?x=" + action;
        }

        if (savePrevious) {
            // Сохраняем текущий URL как предыдущий
            const currentUrl = window.location.href;
            // Добавляем новый URL в историю
            window.history.pushState({
                url: Url,
                previousUrl: currentUrl // Сохраняем предыдущий URL
            }, '', Url);
        } else {
            window.history.pushState({ url: Url }, '', Url);
        }
    }

    // Переход без obj
    async updateWinWithoutObj(x, param) {
        if (x != null) {
            this.pushState(x, param, true);
            await this.preRenderPage();
        } else {
            console.error("Missing x attribute in Navigation_UpdateWin");
        }
    }

    // Переход по obj
    async updateWin(obj) {
        const x = obj.getAttribute('x');
        const param = obj.getAttribute('param');
        
        if (x != null) {
            this.pushState(x, param, true);
            await this.preRenderPage();
        } else {
            console.error("Missing x attribute in Navigation_UpdateWin");
        }
    }

    // Возврат назад
    async returnBack() {
        const historyState = window.history.state;

        // Если есть предыдущее состояние
        if (historyState && historyState.previousUrl) {
            const previousUrl = new URL(historyState.previousUrl);
            const previousParams = new URLSearchParams(previousUrl.search);
            const previousPath = previousParams.get('x');

            this.pushState(previousPath, previousParams.toString(), false);
        } else {
            // Если истории нет, возвращаемся на главную
            this.pushState('viewer', false, false);
        }

        await this.preRenderPage();
    }
}

// Инициализация модели
const NavigationModel = new Navigation__Model();

/*Слушаем события навигации*/
window.onpopstate = function(event) {
    NavigationModel.preRenderPage();
};

NavigationModel.preRenderPage();
