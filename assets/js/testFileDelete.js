

    let currentChatUserName = document.getElementById('currentChatUserName');
    let receiver = document.getElementById('receiver');

    function loadSelectedChat(e){

    currentChatUserName.innerHTML = e.innerText;
    receiver = e.innerText;

    return false;

}


    $(function () {

    let $content = $('#content');
    let $user = $('#user');
    let $receiver = $('#receiver');

    $('#form-submit').on('click', function() {

    let message = {
    content: $content.val(),
    sender: $user.val(),
    receiver: $receiver.val()
};

    $.ajax({

    type: 'POST',
    url: '/chat_session/'+$user,
    data: message,
    success: function() {
    alert('message sent');
},
    error: function(){
    alert('error sending message');
}

})


});


});




    /*
    function sendMessage(){

    let message = document.getElementById('message').value;

    //save into database
    firebase.database().ref('chatMessages').push().set({
    'sender': sender,
    'content': message,
    'chat_time': 'time',
    'chat_date': 'date',
    'email_Sent': 'True/False',
    'Read': 'True/False',
    'Attachment': 'AttachmentID'
});

    firebase.database().ref('chats').push().set({
    'sender': sender,
    'receiver': receiver,
});

    //prevent form from submitting
    return false;

}


    */
