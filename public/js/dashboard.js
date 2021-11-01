let currentChatUserName = document.getElementById('currentChatUserName');
let receiver = document.getElementById('receiver');

function loadSelectedChat(e) {
    currentChatUserName.innerHTML = e.innerText;
    receiver = e.innerText;

    return false;
}

$(function () {
    let user = $('#user');

    $('#form-submit').on('click', function() {

        let formElement = $('#form');
        let formData = new FormData(formElement[0]);

        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '/chat_session/'+'{'+user+'}',
            data: formData,
            contentType: false,
            processData: false,
            success: function() {
                alert('message sent');
            },
            error: function(){
                alert('error sending message');
            }
        })
    });
});
