
// ControlResizer__Service

/*____Hide/Show Control____*/
const Envir__HideControl = () => {
    $(CONTROL).css('width', '0px');
    $(CONTROL).css('min-width', '0px');
    localStorage.setItem('controlWidth', '0');
}

document.addEventListener('DOMContentLoaded', () => {
    const resizers = document.querySelectorAll('.resizer');
    let isResizing = false;
    let startX, startWidth, currentResizer;

    // Добавляем обработчики для каждого ресайзера
    resizers.forEach(resizer => {
        resizer.addEventListener('mousedown', initResize);
        resizer.addEventListener('touchstart', initResize, { passive: true });
    });

    // Инициализация ресайза
    function initResize(e) {
        e.preventDefault(); // Предотвращаем стандартное поведение

        // Проверяем, есть ли элемент для изменения ширины
        if (!this.previousElementSibling) {
            console.error('Элемент для изменения ширины не найден!');
            return;
        }

        isResizing = true;
        currentResizer = this; // Сохраняем текущий ресайзер
        startX = e.clientX || e.touches[0].clientX; // Поддержка touch-событий
        startWidth = parseInt(window.getComputedStyle(this.previousElementSibling).width, 10);

        // Добавляем обработчики для движения и завершения
        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('touchmove', handleMouseMove, { passive: true });
        document.addEventListener('mouseup', stopResize);
        document.addEventListener('touchend', stopResize);
    }

    // Обработка движения мыши/пальца
    function handleMouseMove(e) {
        if (!isResizing) return;

        const clientX = e.clientX || e.touches[0].clientX; // Поддержка touch-событий
        const dx = clientX - startX;
        const newWidth = startWidth + dx;

        // Ограничиваем минимальную и максимальную ширину
        if (newWidth < 100) return; // Минимальная ширина 100px
        if (newWidth > window.innerWidth * 0.5) return; // Максимальная ширина 50% от экрана

        // Изменяем ширину целевого блока
        const target = currentResizer.previousElementSibling;
        $(target).css('width', `${newWidth}px`);
        $(target).css('min-width', `${newWidth}px`);

        // Сохраняем новую ширину в localStorage
        saveWidthToLocalStorage(newWidth);
    }

    // Завершение ресайза
    function stopResize() {
        isResizing = false;
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('touchmove', handleMouseMove);
        document.removeEventListener('mouseup', stopResize);
        document.removeEventListener('touchend', stopResize);
    }

    // Сохранение ширины в localStorage
    function saveWidthToLocalStorage(width) {
        localStorage.setItem('controlWidth', width);
    }

    // Восстановление ширины из localStorage при загрузке страницы
    function restoreWidthFromLocalStorage() {
        const savedWidth = localStorage.getItem('controlWidth');
        if (savedWidth) {
            $(CONTROL).css('width', `${savedWidth}px`);
            $(CONTROL).css('min-width', `${savedWidth}px`);
            setTimeout(()=>{
                $(CONTROL).css('transition', 'all 0.3s ease');
                $(CONTROL).css('opacity', 1);
            }, 200);
        }else{
            $(CONTROL).css('width', `18vw`);
            $(CONTROL).css('min-width', `18vw`);
            $(CONTROL).css('opacity', 1);
            setTimeout(()=>{
                $(CONTROL).css('transition', 'all 0.3s ease');
                $(CONTROL).css('opacity', 1);
            }, 200);
        }
    }

    // Восстанавливаем ширину при загрузке страницы
    restoreWidthFromLocalStorage();
});