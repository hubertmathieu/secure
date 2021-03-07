chrome.runtime.onMessage.addListener(gotMessage);

function gotMessage(request) {
    var login = request.message;


    document.getElementById("id_userLoginId").focus();
    document.getElementById("id_userLoginId").value = login.email;
    document.getElementById("id_password").focus();
    document.getElementById("id_password").value = login.password_content;
}
