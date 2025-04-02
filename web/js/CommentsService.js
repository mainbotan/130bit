
// commentsService

class CommentsService{
    constructor(){
        this.isUser = IS_USER;
        this.message = MessageService;
        this.api = API;

        // Добавляем обработчик нажатия Enter
        this.initEnterHandler();
    }
    
    // Инициализация обработчика Enter
    initEnterHandler() {
        document.addEventListener('keydown', (event) => {
            // Проверяем, что событие произошло на поле ввода с классом .__commentInput
            if (event.target.classList.contains('__commentInput')) {
                // Проверяем, что нажата клавиша Enter
                if (event.key === 'Enter') {
                    // Находим ближайший элемент .SubmitButton и вызываем sendMessage
                    const submitButton = event.target.closest('.__commentWrap').querySelector('.SubmitButton');
                    this.sendMessage(submitButton);
                }
            }
        });
    }

    // Ответ
    makeResponse(obj){
        let parent_obj =  obj.closest('.__commentParent');
        let parent = this.getParent(obj);
        $(parent_obj.getElementsByClassName('__commentResponse')[0]).html(`
        <div data-type='${parent.object_type}' data-id='${parent.object_id}' class='__commentWrap Element__CommentInput'>
            <div class='InputArea'><input class='__commentInput Regular text-face--regular' type='text' placeholder='Ваш ответ...'></div>
            <div class='SubmitArea'>
                <div onclick='commentsService.sendMessage(this)' class='SubmitButton'><img src='web/ico/black/wave.png'></div>
            </div>
        </div>
        `);
        $(obj).html('Скрыть');
        $(obj).attr('onclick', 'commentsService.hideResponse(this)');
    }
    hideResponse(obj){
        let parent = obj.closest('.__commentParent');
        $(parent).find('.__commentResponse').html(``);
        $(obj).html('Ответить');
        $(obj).attr('onclick', 'commentsService.makeResponse(this)');
    }

    // Поиск родителя
    getParent(obj){
        let parent = obj.closest('.__commentParent');
        if (parent == null){ 
            this.parent = {
                id: null,
                object_id: null,
                object_type: null
            };
            this.parent_obj = null;
            return this.parent; 
        }
        this.parent_obj = parent;
        parent= {
            object_id: $(parent).attr('data-id'),
            object_type: $(parent).attr('data-type'),
            id: $(parent).attr('data-comment-id')
        };
        this.parent = parent;
        return parent;
    }

    async sendMessage(obj){ 
        let goal = this.getGoalInfo(obj);  
        let input = this.getInput(obj);
        let input_val = $(input).val();
        let parent = this.getParent(obj);
        if (this.validateInput(input_val)){
            let result = await this.api.sendComment({value: input_val, parent: parent.id, object: goal});
            switch(result.status){
                case 'success':
                    this.message.displayMessage({title: 'Комментарий опубликован', ad: '', status: 'success'});
                    this.animateMessage({value: input_val}, input, goal);
                break;
                default:
                    this.message.displayMessage({title: 'Что-то пошло не так...', ad: '', status: 'bad'});
            }
        }
    }
    animateMessage(message, input, goal){
        $(input).val('');
        if (this.parent.id != null){
            $(this.parent_obj.getElementsByClassName('__commentChildren')[0]).append(
                `
                <div data-comment-id='${this.parent.id}' data-type='${this.parent.object_type}' data-id='${this.parent.object_id}' class='__commentParent Comment'>
                    <div class='User'>
                        <div class='Bar'><div class='Name'>Вы</div></div>
                    </div>
                    <div class='Text'>${message.value}</div>
                </div>
                `
            );
        }else{
            $('.__commentsCanvas').prepend(
                `
                <div data-type='${goal.type}' data-id='${goal.id}' class='__realParentComment __commentParent Comment'>
                    <div class='User'>
                        <div class='Bar'><div class='Name'>Вы</div></div>
                    </div>
                    <div class='Text'>${message.value}</div>
                </div>
                `
            );
        }
    }


    validateInput(input){
        let input_len = input.length;
        if (input_len < 3){
            this.message.displayMessage({title: 'Чуть побольше плез...', ad: 'Недопустимая длина комментария.'});
            return false;
        }
        if (input_len > 128){
            this.message.displayMessage({title: 'Чуть поменьше плез...', ad: 'Недопустимая длина комментария.'});
            return false;
        }
        return true;
    }
    getGoalInfo(obj){
        let parent = obj.closest('.__commentWrap');
        return {
            type: $(parent).attr('data-type'),
            id: $(parent).attr('data-id')
        };
    }
    getInput(obj){
        let parent = obj.closest('.__commentWrap');
        return $(parent).find('.__commentInput');
    }
}

const commentsService = new CommentsService();