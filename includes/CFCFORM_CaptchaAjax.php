<?php


class CFCFORM_CaptchaAjax
{
    protected function ajaxActions(){
        add_action("wp_ajax_save_captcha_keys",[$this,"save_captcha_keys"]);
    }

    public function save_captcha_keys(){

        if(empty($_REQUEST['nonce']) ||  !wp_verify_nonce($_REQUEST['nonce'], "save_captcha"))
            wp_send_json(['response'=>false]);


        if(empty($_REQUEST['site']) || empty($_REQUEST['secret']))
            wp_send_json(['response'=>false]);


        $g_captcha_keys = [];
        $g_captcha_keys['site'] = sanitize_text_field($_REQUEST['site']);
        $g_captcha_keys['secret'] = sanitize_text_field($_REQUEST['secret']);
        if(!get_option("g_captcha_keys")){
            add_option("g_captcha_keys",$g_captcha_keys);
            wp_send_json(['response'=>true]);
        }
        else{
            update_option('g_captcha_keys',$g_captcha_keys);
            wp_send_json(['response'=>true]);
        }
        wp_send_json(['response'=>false]);

    }
}