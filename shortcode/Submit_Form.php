<?php
/**
 * WP-Crowdfunding Form Shortcodes
 *
 * @category   Crowdfunding
 * @package    Shortcode
 * @author     Themeum <www.themeum.com>
 * @copyright  2019 Themeum <www.themeum.com>
 * @since      2.1.0
 */

namespace WPCF\shortcode;
defined( 'ABSPATH' ) || exit;

class Campaign_Submit_Form {

    function __construct() {
        add_action('wp_enqueue_scripts',    array($this, 'campaign_form_assets'));
        add_shortcode('wpcf_form',          array($this, 'campaign_form_callback'));
    }

    function campaign_form_assets() {
        $api_namespace = WPCF_API_NAMESPACE . WPCF_API_VERSION;
        $page_id = get_option('wpneo_form_page_id');

        $currency_symbol = (get_woocommerce_currency_symbol()) ? get_woocommerce_currency_symbol() : '$';
        $decimal_separator = (wc_get_price_decimal_separator()) ? wc_get_price_decimal_separator() : '.';
        $thousand_separator = (wc_get_price_thousand_separator()) ? wc_get_price_thousand_separator() : ',';
        $decimals = (wc_get_price_decimals()) ? wc_get_price_decimals() : 2;

        if( get_the_ID() && get_the_ID() == $page_id ) {
            wp_enqueue_style( 'wpcf-campaign-style', WPCF_DIR_URL.'assets/css/campaign-form.css', false, WPCF_VERSION );
//            wp_enqueue_style( 'jquery-ui-base', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.min.css', false, '1.12.1' );
            wp_enqueue_script( 'wpcf-campaign-script', WPCF_DIR_URL.'assets/js/campaign-form.js', array('jquery', 'jquery-ui-datepicker', 'jquery-ui-slider'), WPCF_VERSION, true );
            wp_localize_script( 'wpcf-campaign-script', 'WPCF', array (
                'site_url'      => site_url(),
                'rest_url'      => rest_url( $api_namespace ),
                'assets'        => WPCF_DIR_URL.'assets/',
                'nonce'         => wp_create_nonce( 'wpcf_form_nonce' ),
                'currency'      => array(
                    'symbol'        => $currency_symbol,
                    'd_separator'  	=> $decimal_separator,
                    't_separator' 	=> $thousand_separator,
                    'decimals'      => $decimals,
                )
            ) );
        }
    }

    // Shortcode for Forntend Submission Form
    function campaign_form_callback($atts) {
        $action = ( isset($_GET['action']) ) ? $_GET['action'] : 0;
        $postid = ( isset($_GET['postid']) ) ? $_GET['postid'] : 0;
        if ( !is_user_logged_in() ) {
            auth_redirect();
        }
        return "<div id='wpcf-campaign-builder' postId='".$postid."'></div>";
    }
}


