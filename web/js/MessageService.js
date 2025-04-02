
// Modal Message Service

class Message__Service{
    async displayMessage(data){
        switch (data.status){
            case 'bad':
                $('#__MESSAGE__').addClass('Bad');
            break;
            default:
                $('#__MESSAGE__').removeClass('Bad');
        }
        if ($('#__MESSAGE__').hasClass('Active')){
            $('#__MESSAGE__').removeClass('Active');
        }
        $('#__MESSAGE__').find('.__Title').html(data.title)
        $('#__MESSAGE__').addClass('Active');
        setTimeout(() => {
            $('#__MESSAGE__').removeClass('Active');
        }, 1400);
    }
}

const MessageService = new Message__Service();