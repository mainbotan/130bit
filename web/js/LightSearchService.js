
// LightSearch__Service

class LightSearch__Service {
    constructor() {
        this.Api = API;
        this.debounceTimer = null;  // Переменная для хранения таймера
    }
    // Метод для установки обработчика с дебаунсом
    setUpSearchInput() {
        // Устанавливаем обработчик события onkeyup с дебаунсом
        $(SEARCH_VALUE).on('keyup', (event) => {
            this.debouncedSearchIt(event.target);  // Вызываем debouncedSearchIt
        });
    }
    // Дебаунс для поиска
    debouncedSearchIt(obj) {
        let Value = $(obj).val();
        if (Value.length > 0) {
            $(SEARCH_BLOCK).addClass('Active');
            // Сбросить предыдущий таймер
            clearTimeout(this.debounceTimer);
            // Настроить новый таймер
            this.debounceTimer = setTimeout(() => {
                // Выполнить AJAX запрос
                this.searchIt(Value);
            }, 500); // 500 миллисекунд задержки
        } else {
            if ($(SEARCH_BLOCK).hasClass('Active')) {
                $(SEARCH_BLOCK).removeClass('Active');
                $(SEARCH_VALUE).val('');
            }
        }
    }
   async searchIt(Val) {
        $(SEARCH_BLOCK).html(`<div class='Element__LoadingWrap' style='height: 350px;'><div class='Element__CircleLoader Big'></div></div>`);
        let result = await this.Api.getLightSearch({value: Val});
        if (result.status == 'success'){
            let client = result.data.client;
            $(SEARCH_BLOCK).html(client);
            console.log(result);
        } 
        $(SEARCH_BLOCK).animate({ scrollTop: 0 }, 600);
    }
}

// Инициализация
const LightSearch = new LightSearch__Service();
LightSearch.setUpSearchInput(); 
