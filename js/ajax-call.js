jQuery(document).ready(function(){

    jQuery(document).on("submit",".inno-g-captcha-form",function (e) {
        e.preventDefault();
        var ajaxUrl = jQuery('.ajax-url').val();
        var ajaxData = {};
        ajaxData.action = "save_captcha_keys";
        ajaxData.site = jQuery(".g-inno-site-key").val();
        ajaxData.secret = jQuery(".g-inno-secret-key").val();
        ajaxData.nonce = jQuery("#save_google_captcha_nonce").val();
        jQuery.ajax({
            url: ajaxUrl,
            data: ajaxData,
            type:"POST"
        }).done(function(res){
            var resp = res;

            if(res.response)
                alert("Keys saved successfully!");
            else
                alert("Problem occurred when trying to save keys!");
        }).fail(function (res) {
            alert("Problem occurred while saving keys. \nIf it is keep showing error then contact plugin author. ");
        })
    });

});