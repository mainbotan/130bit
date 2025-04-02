class ColorThiefService {
    constructor() {
        this.colorThief = new ColorThief();
    }

    // Промо блоки
    makeGradientBackground(obj) {
        const imgUrl = obj.getAttribute('data-img');
        if (!imgUrl) return; // Если атрибут отсутствует, выходим

        const img = new Image(); // Создаём новое изображение для каждого блока
        img.crossOrigin = 'Anonymous';
        img.src = imgUrl;

        img.onload = () => {
            const dominantColor = this.colorThief.getColor(img);
            const colorString = dominantColor.join(',');
            const gradient = `linear-gradient(120deg, rgba(${colorString},1) 0%, rgba(0,0,0,0) 100%)`;
            obj.style.background = gradient; // Применяем градиент к блоку
        };

        img.onerror = () => {
            // console.error('Ошибка загрузки изображения:', img.src);
        };
    }

    async animatePage(title, imgUrl){
        if (!imgUrl) return; // Если атрибут отсутствует, выходим

        const img = new Image(); // Создаём новое изображение для каждого блока
        img.crossOrigin = 'Anonymous';
        img.src = imgUrl;

        img.onload = () => {
            const dominantColor = this.colorThief.getColor(img);
            const colorString = dominantColor.join(',');
            const rgba_color = `rgba(${colorString},1)`;
            const rgba_color_2 = `rgba(${colorString},1)`;

            $('.__animatedPageHead').css('background', `linear-gradient(180deg, ${rgba_color} 0%, rgba(0,0,0,0) 100%)`);
            $('.__pageState').css('background', `${rgba_color}`);
            $('.__pageState').find('.__captureText').html(title);
        };   
    }
}

// Инициализация
const colorService = new ColorThiefService();





// Функция для обработки новых блоков
function applyGradientToNewBlocks() {
    const newBlocks = document.querySelectorAll('.PromoBlock:not([data-processed])');

    const colorThiefService = new ColorThiefService();

    newBlocks.forEach(block => {
        colorThiefService.makeGradientBackground(block);
        block.setAttribute('data-processed', 'true');
    });
}

// Новый MutationObserver
const gradientObserver = new MutationObserver((mutationsList) => {
    mutationsList.forEach(mutation => {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            applyGradientToNewBlocks(); // Обрабатываем новые блоки
        }
    });
});

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    applyGradientToNewBlocks(); // Обрабатываем существующие блоки

    // Наблюдаем за изменениями в #__ACTIVE__
    const activeContainer = document.querySelector('#__ACTIVE__');
    if (activeContainer) {
        gradientObserver.observe(activeContainer, {
            childList: true, // Отслеживаем добавление/удаление дочерних элементов
            subtree: true   // Отслеживаем изменения во всех вложенных элементах
        });
    }
});

