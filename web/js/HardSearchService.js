
// HardSearch__Service

class HardSearch__Service {
    constructor() {
        this.Api = API;
        this.debounceTimer = null;  // Переменная для хранения таймера
    }
    // Метод для установки обработчика с дебаунсом
    setUpSearchInput() {
        // Устанавливаем обработчик события onkeyup с дебаунсом
        $(DISCOVER_VALUE).on('keyup', (event) => {
            this.debouncedSearchIt(event.target);  // Вызываем debouncedSearchIt
        });
    }
    // Дебаунс для поиска
    debouncedSearchIt(obj) {
        let Value = $(obj).val();
        if (Value.length > 0) {
            // Сбросить предыдущий таймер
            clearTimeout(this.debounceTimer);
            // Настроить новый таймер
            this.debounceTimer = setTimeout(() => {
                // Выполнить AJAX запрос
                this.searchIt(Value);
            }, 500); // 500 миллисекунд задержки
        }else{
            $(DISCOVER).find('.__Update').removeClass('Active');
        }
    }
   async searchIt(Val) {
        $(DISCOVER).find('.__Update').html(`<div class='Element__LoadingWrap' style='height: 350px;'><div class='Element__CircleLoader Big'></div></div>`);
        let result = await this.Api.getHardSearch({value: Val});
        if (result.status == 'success'){
            let client = result.data.client;
            $(DISCOVER).find('.__Update').addClass('Active');
            $(DISCOVER).find('.__Update').html(client);
        } 
        $(DISCOVER).find('.__Update').animate({ scrollTop: 0 }, 600);
        $(DISCOVER).find('.Result').animate({ scrollTop: 0 }, 600);
        $(DISCOVER).find('.Content').animate({ scrollTop: 0 }, 600);
    }
}

// Инициализация
const HardSearch = new HardSearch__Service();
HardSearch.setUpSearchInput(); 
