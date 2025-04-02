
// comminutySearchService

class CommunitySearchService {
    constructor() {
        this.Api = API;
        this.debounceTimer = null;  // Переменная для хранения таймера
    }
    // Дебаунс для поиска
    debouncedSearch(obj) {
        let Value = $(obj).val();
        this.updateArea = obj.closest('.__communitySearch').getElementsByClassName('__Update')[0];
        if (Value.length > 0) {
            // Сбросить предыдущий таймер
            clearTimeout(this.debounceTimer);
            // Настроить новый таймер
            this.debounceTimer = setTimeout(() => {
                // Выполнить AJAX запрос
                this.searchIt(Value);
            }, 500); // 500 миллисекунд задержки
        }else{
            // $(DISCOVER).find('.__Update').removeClass('Active');
        }
    }
   async searchIt(Val) {
        $(this.updateArea).html(`<div class='Element__LoadingWrap' style='height: 160px;'><div class='Element__CircleLoader Big'></div></div>`);
        let result = await this.Api.getCommunitySearch({value: Val});
        if (result.status == 'success'){
            let users = result.data.users;
            if (users.length > 0){
                $(this.updateArea).html(this.generateUsersHtml(users));
                return true;
            }
        } 
    }
    generateUsersHtml(users){
        return users.map(user => {
            if (user.picture == null){
                user.picture = 'https://130bit.com/web/ico/default/default_user_avatar.jpg';
            }
            return `
                <div x='user' param='id=${user.id}' class='Navigation_UpdateWin FriendItem'>
                    <div class='IcoWrap'><div class='Ico' style='background-image: url(${user.picture});'></div></div>
                    <div class='InfoWrap'><div class='Cut Title'>${user.name}</div></div>
                </div>`;
        }).join('');
    }
}

// Инициализация
const communitySearchService = new CommunitySearchService();
