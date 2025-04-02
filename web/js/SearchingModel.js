
// main searching model

class Searching__Model {
    constructor() {
        this.Api = API;
        this.debounceTimer = null;  // Переменная для хранения таймера
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
            $('.__SearchArea').find('.__Update').removeClass('Active');
        }
    }
   async searchIt(Val) {
        $('.__SearchArea').find('.__Update').html(`<div class='Element__LoadingWrap' style='height: 350px;'><div class='Element__CircleLoader Big'></div></div>`);
        let result = await this.Api.getHardSearch({value: Val});
        if (result.status == 'success'){
            let client = result.data.client;
            $('.__SearchArea').find('.__Update').addClass('Active');
            $('.__SearchArea').find('.__Update').html(client);
        } 
        $('.__SearchArea').find('.__Update').animate({ scrollTop: 0 }, 600);
        $('.__SearchArea').find('.Result').animate({ scrollTop: 0 }, 600);
        $('.__SearchArea').find('.Content').animate({ scrollTop: 0 }, 600);
    }
}
 
// Инициализация
const SearchingModel = new Searching__Model();