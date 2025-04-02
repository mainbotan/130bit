class ControlPanel__Model{
    constructor(){
        this.navigationModel = new Navigation__Model();
        this.init(); // Инициализация после создания экземпляра
    }

    init() {
        // Вытаскиваем GET-параметр 'x' из URL
        const urlParams = new URLSearchParams(window.location.search);
        const x = urlParams.get('x');

        this.animateIconByParam(x);
    }

    animateIconByParam(param) {
        if (param == null){ param = 'viewer'; }
        // Находим элемент (иконку) с атрибутом data-section, равным параметру
        const targetElement = document.querySelector(`[data-section="${param}"]`);

        // Если элемент найден, анимируем его
        if (targetElement) {
            this.resetSectionsState(); // Сбрасываем состояние всех секций
            $(targetElement).addClass('Active'); // Добавляем класс Active
        } else {
            // console.warn(`Иконка с data-section="${param}" не найдена.`);
        }
    }
    handleClick(element) {
        if (element.classList.contains('ControlPanel_Section')) {
            this.openSection(element);
        }
    }
    resetSectionsState(){
        $('.Envir__Control .Section').removeClass('Active');
    }
    openSection(e){
        this.resetSectionsState();
        $(e).addClass('Active');
        let x = e.getAttribute('data-section');
        this.navigationModel.updateWinWithoutObj(x);
    }
}

/*Инициализация*/
const ControlPanelModel = new ControlPanel__Model();