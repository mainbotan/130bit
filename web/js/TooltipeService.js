

// ToolTipes__Service

/*____Tooltips Service____*/
// Функция для инициализации tooltips
function initTooltips(container) {
    const ToolTipe = container.querySelector('.ToolTipe');

    container.addEventListener('mousemove', (e) => {
        // Позиция курсора
        const x = e.clientX;
        const y = e.clientY;

        // Позиция ToolTipe
        ToolTipe.style.left = `${x + 10}px`; // Отступ от курсора
        ToolTipe.style.top = `${y + 10}px`;
        ToolTipe.style.opacity = '1';
        ToolTipe.style.visibility = 'visible';
    });

    container.addEventListener('mouseleave', () => {
        ToolTipe.style.opacity = '0';
        ToolTipe.style.visibility = 'hidden';
    });
}
// Инициализация tooltips для уже существующих элементов
document.querySelectorAll('.ToolTipeWrap').forEach(container => {
    initTooltips(container);
});
// Наблюдатель за изменениями в DOM
const observer = new MutationObserver((mutationsList) => {
    mutationsList.forEach(mutation => {
        // Проверяем, добавлены ли новые узлы
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(node => {
                // Если добавленный узел - элемент и содержит .ToolTipeWrap
                if (node.nodeType === 1 && node.matches('.ToolTipeWrap')) {
                    initTooltips(node);
                }
                // Если добавленный узел - элемент, проверяем его потомков
                if (node.nodeType === 1) {
                    node.querySelectorAll('.ToolTipeWrap').forEach(container => {
                        initTooltips(container);
                    });
                }
            });
        }
    });
});
// Настройка наблюдателя
observer.observe(document.body, {
    childList: true, // Отслеживаем добавление/удаление дочерних элементов
    subtree: true,   // Отслеживаем изменения во всём дереве DOM
});