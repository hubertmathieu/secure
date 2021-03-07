$( window ).on( "load", function() {
    $(".copy").click(
        function() {
            /* Get the text field */
            console.log(this);
            var copyText = $(this).parent().parent().find(".password").html();
            console.log(copyText);

            var input = document.body.appendChild(document.createElement("input"));
            input.value = copyText;
            /* Select the text field */
            input.focus();
            input.select();
            alert("Copied the text: " + copyText);
            document.execCommand('copy');
            input.parentNode.removeChild(input);


            /* Alert the copied text */

        });

});