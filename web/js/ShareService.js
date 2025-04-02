
// shareService
class ShareService{
    constructor(){

    }
    shareFromObject(obj){
        let shareData = {
            title: $(obj).attr('data-share-title'),
            text: $(obj).attr('data-share-text'),
            url: $(obj).attr('data-share-url') || window.location
        };
        this.share(shareData);
    }
    share(shareData){
        if (navigator.share) {
            navigator.share(shareData)
                .then(() => console.log('Успешно поделились'))
                .catch((error) => console.log('Ошибка при попытке поделиться', error));
        } else {
            // Если Web Share API не поддерживается, можно использовать другой метод (например, копирование ссылки)
            alert('Ваш браузер не поддерживает функцию поделиться.');
        }
    }
}
const shareService = new ShareService();