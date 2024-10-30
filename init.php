<?php
/**
 * Plugin Name: Captcha for comments form
 * Author: Milankumar Kyada
 * Description: This is a very basic plugin and it uses google recaptcha to void spam comments.
 * Tags: Avoid spam comments, captcha, comment form captcha
 * Version: 1.0
 */
if (!defined('ABSPATH')) exit;
if ( ! defined( 'WPINC' ) ) die;
define("CFCFORM_PLUGIN_DIR",__DIR__);
define("CFCFORM_PLUGIN_URL",plugin_dir_url(__FILE__));

require_once("includes/CFCFORM_CaptchaAjax.php");
include_once(ABSPATH . 'wp-includes/pluggable.php');
final class CFCFORM_Init extends CFCFORM_CaptchaAjax {

    public static function Instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new CFCFORM_Init();
        }
        return $inst;
    }

    private function __construct()
    {
        self::handleHooks();
        self::ajaxActions();
    }
    
    private function handleHooks(){
        add_action( 'admin_menu', array( $this, 'google_recapthca_settings' ) );

        add_action("admin_enqueue_scripts",[$this,'enqueueScripts']);
        add_action("wp_enqueue_scripts",[$this,'add_recaptch_js']);

        if (!is_user_logged_in()) {
            add_action('pre_comment_on_post', array( $this, 'verify_google_recaptcha' ));
            add_filter('comment_form_defaults',array($this,'add_google_recaptcha'));
        }
    }

    function enqueueScripts(){
        wp_register_script( 'captcha-inno-js', CFCFORM_PLUGIN_URL."js/ajax-call.js");

    }
    function add_recaptch_js(){
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js');
    }

    function save_google_recaptcha_keys(){
        wp_enqueue_script("captcha-inno-js");
        $keys = get_option("g_captcha_keys");

        ?>
        <div class="wrap">
            <h1>Comment for recaptcha settings</h1>
            <form class="inno-g-captcha-form">
                <input type="hidden" class="ajax-url" name="ajax_url" value="<?= admin_url('admin-ajax.php'); ?>">
                <?php wp_nonce_field( 'save_captcha', 'save_google_captcha_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="g-inno-site-key">Site Key</label></th>
                        <td>
                            <input type="text" name="g_inno_site_key" class="g-inno-site-key" id="g-inno-site-key" value="<?= @$keys['site']?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label id="g-inno-secret-key">Secret Key</label></th>
                        <td>
                            <input type="text" name="g_inno_secret_key" class="g-inno-secret-key" id="g-inno-secret-key" value="<?= @$keys['secret']?>">
                        </td>
                    </tr>
                </table>
                <p class="inno-submit submit">
                    <input type="submit" name="submit" id="inno-submit-btn" class="button button-primary" value="Save Changes">
                </p>
            </form>
        </div>
        <?php
    }

     function google_recapthca_settings(){
        add_options_page(
            'Comments Form Captcha',
            'Comments Form Captcha',
            'manage_options',
            'for-captcha-setting',
            array( $this, 'save_google_recaptcha_keys' )
        );
    }

    function verify_google_recaptcha() {
        $recaptcha = $_POST['g-recaptcha-response'];
        if (empty($recaptcha))
            wp_die( "<b>ERROR:</b> Please verify captcha! <p><a href='javascript:history.back()'>« Back</a></p>");
        else if (!$this->is_valid_captcha($recaptcha))
            wp_die( "<b>Robot verification failed!</b>");
    }

    function is_valid_captcha($captcha) {
        $keys = get_option("g_captcha_keys");
        if(!empty($keys)){
            $secret = $keys['secret'];
            $response = wp_remote_request( 'https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);

            $result = wp_remote_retrieve_body($response);

            $captcha_response = json_decode($result,true);
            if ($captcha_response['success'])
                return true;
            else
                return false;
        }
        return false;
    }

    function add_google_recaptcha($submit_field) {
        $keys = get_option("g_captcha_keys");
        if(!empty($keys))
            $submit_field['submit_field'] = '<div class="g-recaptcha" data-sitekey="'.$keys["site"].'"></div><br>' . $submit_field['submit_field'];
        return $submit_field;
    }

}
CFCFORM_Init::Instance();