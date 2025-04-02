
class HorizontalScrollService{
    scroll(obj){
        let step = obj.getAttribute('data-scroll-step');
        let scrollArea = this.getScrollArea(obj);
        scrollArea.scrollBy({
            left: Number(step), 
            behavior: 'smooth' 
        });
        
    }
    getScrollArea(obj){
        return obj.closest('.__HorizontalScrollWrap').getElementsByClassName('__HorizontalScroll')[0];
    }
}   
const horizontalScroll = new HorizontalScrollService();