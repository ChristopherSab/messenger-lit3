let currentChatUserName = document.getElementById('currentChatUserName');
let chatSection = document.getElementById('chat-section');
let newMessageArea = document.getElementById('new-message');
let newAttachmentArea = document.getElementById('new-attachment');
let contacts = document.getElementsByClassName('contactName');
let UnreadMessagesArea = document.getElementById('unread-messages');
let contactProfile = document.getElementsByClassName('contact-profile');
let loggedInUser = document.getElementById('user');

// -----  AJAX To Load Unread Messages For Each Contact -----  //
setInterval( ()=>
    $.ajax({
        method: 'GET',
        url: '/chat_home/check_if_unread_messages/' + loggedInUser.innerText,
        success: function(data) {
            displayUnreadMessages(data);
        },
        error: function(){
            if (showAlert == true) {
                alert ("Error receiving messages");
                showAlert = false;
                }
        },
    })
     , 300000);

function displayUnreadMessages(data){

    let usersWithUnreadMessages = [];

    for (let key in data) {
        if (data[key]['read'] === 'false') {
            usersWithUnreadMessages.push(data[key]['receiver']);
        }
    }

    for (let i = 0; i < contacts.length; i++) {
        if (usersWithUnreadMessages.includes(contacts[i].innerText)) {
            contactProfile[i].nextElementSibling.removeAttribute("hidden");
        }
    }
}

function loadSelectedChat(e){
   e.nextElementSibling.style.display = "none";

    currentChatUserName.innerHTML = e.innerText;

    chatSection.removeAttribute("hidden");

    getMessagesFromDatabase();

    setInterval( ()=> getMessagesFromDatabase() , 500000);

    return false;
}

function getMessagesFromDatabase() {
    let receiverTemp = currentChatUserName.innerText;
    $.ajax({
        method: 'GET',
        url: '/chat_home/new_message/' + receiverTemp,
        success: function(data){

            displayMessages(data);
        },
        error: function(){
            if (showAlert == true) {
                alert ("Error receiving message");
                showAlert = false;
                }
        }
    })
}

function displayMessages(data) {
    let message = '';
    for (let key in data) {
        const time = new Date(data[key]['time']).toLocaleTimeString();
        const date = new Date(data[key]['time']).toLocaleDateString();

        if(data[key]['attachments']){
         message += '  <div>\n' +
            '        </div>\n' +
            '        <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">\n' +
            '            <div class="font-weight-bold mb-1">' + data[key]['sender'] + '</div>\n' +
            '        ' + data[key]['message'] + '\n' +
             '          <div class="text-muted small text-nowrap mt-2">' + time + '</div>\n' +
            '        </div>';

            for ( let attachmentKey in data[key]['attachments']) {
                message +=  "<a href='"+data[key]['attachments'][attachmentKey]['signedUrl']+"'>Download: "+data[key]['attachments'][attachmentKey]['originalFileName'] +" </a>";
            }

         } else {
            message += ' <div>\n' +
                '        </div>\n' +
                '        <div class="flex-shrink-1 bg-light rounded py-2 px-3 mr-3">\n' +
                '        <div class="font-weight-bold mb-1">' + data[key]['sender'] + '</div>\n' +
                '        ' + data[key]['message'] + '\n' +
                '         <div class="text-muted small text-nowrap mt-2">' + time + '</div>\n' +
                '        </div>';

        }
    }

    newMessageArea.innerHTML = message;

}

$(function () {
    $(document).on('click', '#form-submit', function(e) {

        let contact = $('#currentChatUserName')[0].innerText;

        e.preventDefault();

        let formElement = $('#form');
        let formData = new FormData(formElement[0]);

        $.ajax({
            method: 'POST',
            url: '/chat_home/' + contact,
            data: formData,
            contentType: false,
            processData: false,
            success: function(){
                console.log('message sent');
                $('#chat_form_message').val('');
                $('#chat_form_attachment').val('');
            },
            error: function(){
                if (showAlert == true) {
                    alert ("error sending message");
                    showAlert = false;
                    }
            }
        })
    });
});
