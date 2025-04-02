var CONTENT_LIMIT = 20;
var LAZY_CONTENT = $('#__ACTIVE-CONTENT-AREA__');
$(document).ready(function() {
    // Флаг для предотвращения многократной подгрузки
    var contentLoadedX = false;
    // Удаляем все предыдущие обработчики события scroll, чтобы избежать накопления
    $(ACTIVE).off('scroll');
    $(ACTIVE).scroll(function() {
        // Проверяем, существует ли элемент Trigger
        var triggerElement = $('#__TRIGGER__');
        if (triggerElement.length > 0) {
            // Проверяем, если пользователь прокрутил до блока Trigger
            if (!contentLoadedX && $(window).scrollTop() + $(window).height() >= triggerElement.offset().top) {
                contentLoadedX = true; // Устанавливаем флаг, чтобы избежать повторной подгрузки

                // Подгружаем контент (например, с сервера)
                loadContent();
            }
        }

        // Проверяем, существует ли элемент Trigger
        var triggerElement = $('#__TRIGGER-COMMENTS__');
        if (triggerElement.length > 0) {
            // Проверяем, если пользователь прокрутил до блока Trigger
            if (!contentLoadedX && $(window).scrollTop() + $(window).height() >= triggerElement.offset().top) {
                contentLoadedX = true; // Устанавливаем флаг, чтобы избежать повторной подгрузки

                // Подгружаем контент (например, с сервера)
                loadComments();
            }
        }

    });
    async function loadComments(){
        let data = getCommentsParams();
        let result = await API.commentsLazyLoading(data);
        console.log(result, contentLoadedX);
        switch (result.status){
            case 'success':
                $(LAZY_CONTENT).append(result.data.client);
                console.log(result.data.comments);
                LAZY_LOADING_CONTENT_OFFSET = Number(result.data.offset);    
                contentLoadedX = false;
            break;
            default: 
                $('#__TRIGGER-COMMENTS__').remove();
        }
    }
    function getCommentsParams(){
        return {
            object: LAZY_LOADING_COMMENTS_OBJECT,
            offset: $(LAZY_CONTENT).find('.__realParentComment').length,
            limit: LAZY_LOADING_COMMENTS_LIMIT
        };
    }

    async function loadContent() {
        console.log('load');
        let query_data = {
            offset: CONTENT_OFFSET,
            limit: CONTENT_LIMIT
        }
        let result = await API.lazyLoading(LAZY_ACTION, query_data);
        switch (result.status){
            case 'success':
                $(LAZY_CONTENT).append(result.data.client);
                CONTENT_OFFSET = Number(result.data.offset);
                contentLoadedX = false;
            break;
            default: 
                $('#__TRIGGER__').remove();
        }
    }
});