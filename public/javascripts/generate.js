$( window ).on( "load", function() {

    function randomPassword(length) {
        var chars = "abcdefghijklmnopqrstuvwxyz!@#$&ABCDEFGHIJKLMNOP1234567890";
        var pass = "";
        for (var x = 0; x < length; x++) {
            var i = Math.floor(Math.random() * chars.length);
            pass += chars.charAt(i);
        }
        return pass;
    }

    $("#generate").click(
        function () {
            var input = document.getElementById('password')
            input.setAttribute('value', randomPassword(15));
        });
});