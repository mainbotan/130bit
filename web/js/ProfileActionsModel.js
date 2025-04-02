
class ProfileActionsModel {
    constructor(){
        this.isUser = IS_USER;
        this.Api = API;
        this.message = MessageService;
    }

    async updateName(){
        let name = $('#__newName').val(); 
        if (this.validateNewName(name)){
            let result = await this.Api.updateName({name: name});
            switch (result.status){
                case 'success':
                    this.message.displayMessage({title: 'Имя обновлено', status: 'success'});
                break;
                default:
                    this.message.displayMessage({title: 'Что-то пошло не так', status: 'bad'});
                break;
            }
        }
    }
    validateNewName(name){
        let name_len = name.length;
        if (name_len < 3){
            this.message.displayMessage({title: 'Чуть побольше...', status: 'bad'});
            return false;
        }
        if (name_len > 32){
            this.message.displayMessage({title: 'Чуть поменьше...', status: 'bad'});
            return false;
        }
        return true;
    }

    async resetSetting(obj){
        let name = obj.getAttribute('data-setting-name');
        let current_state = Boolean($(obj).hasClass('Active')); 
        let new_state = '';
        if (current_state){ new_state = false; }else{ new_state = true; }

        let result = await this.Api.updatePrivateSettings({type: name, value: new_state});

        this.animateSetting(obj, current_state);
    }
    animateSetting(obj, state){
        if (state){
            $(obj).removeClass('Active');
        }else{
            $(obj).addClass('Active');
        }
    }
    logOut() {
        window.location.href = 'https://my.130bit.com/logOut';
    }
    totalLogOut() {
        window.location.href = 'https://my.130bit.com/totalLogOut';
    }
    
    delete_cookie(name) {
        // Указываем domain, path и флаг Secure, если куки были установлены с этими параметрами
        document.cookie = `${name}=; path=/; domain=.130bit.ru; expires=Thu, 01 Jan 1970 00:00:01 GMT; Secure; SameSite=None`;
    }

}

const profileActionsModel = new ProfileActionsModel();