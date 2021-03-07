chrome.runtime.onMessage.addListener(gotMessage);

function gotMessage(request) {
    var login = request.message;

    document.getElementById("login_field").focus();
    document.getElementById("login_field").value = login.email;
    document.getElementById("password").focus();
    document.getElementById("password").value = password_content;
}