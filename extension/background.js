const id = "YTIyMzY3MTlmMTZmNmQyMTg1ZWYxOTQxOTEyMjE2YmVkNzY0MWIzMmI5Yjk2NjkxYjMyNWYwZjUzMGFkYjQ4ZjeHRUJD0DiddbjWLnY0/KBZt3B22yVpwthLc7WjGXGQ";
const key = "WnZr4t7w";

chrome.browserAction.onClicked.addListener(function (tab) {

    chrome.tabs.query({active: true, currentWindow:true}, function (tabs) {
        let activeTab = tabs[0]
        var req = new XMLHttpRequest()
        req.onreadystatechange = function() {
            if (req.readyState === 4 && req.status === 200) {
                chrome.tabs.sendMessage(activeTab.id, {"message": JSON.parse(req.responseText)});
            }
        }
        let url = activeTab.url;
        req.open("POST", "http://secure.local/ajax", true);
        req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        req.send('id='+id+'&url='+url+'&key='+key);
    });
});




