chrome.runtime.onMessage.addListener(gotMessage);

function gotMessage(request) {
    var login = request.message;

    document.getElementById("email").focus();
    document.getElementById("email").value = login.email;
    document.getElementById("pass").focus();
    document.getElementById("pass").value = login.password_content;
}