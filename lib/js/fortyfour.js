! function(j) {
    "use strict";
    j(document).ready(function() {
        j('#searchform').submit(function(e){
            var data = {
                action: 'fortyfourwp_addkeyword',
                keyword: j(this).find('#s').val(),
                id: j(this).find('#s').attr('data-id'),
            };
            j.post( vars.ajaxurl , data, function(response) {
                if(response){
                    return true;
                }
                else{
                    alert( 'Error' );
                }
            });
            // return false;
        });
    });
}(jQuery);