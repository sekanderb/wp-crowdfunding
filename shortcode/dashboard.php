<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_shortcode( 'wpneo_crowdfunding_dashboard','wpneo_shortcode_crowdfunding_dashboard' );

function wpneo_shortcode_crowdfunding_dashboard( $attr ){

        function dashboard_page_type($typename){
            $type = '';
            $type = add_query_arg( "page_type", $typename, get_permalink(get_option("wpneo_crowdfunding_dashboard_page_id")) );
            return $type;
        }

        function dashboard_page_type_name($typena){
            $type = '';
            $type = $typena;
            return $type;
        }
    
    $html = '';
    $get_id = '';
    if( isset($_GET['page_type']) ){ $get_id = $_GET['page_type']; }
    if ( is_user_logged_in() ) {
        $pagelink = get_permalink( get_the_ID() );

        $dashboard_menus = apply_filters('wpneo_crowdfunding_frontend_dashboard_menus', array(
            'dashboard' =>
                array(
                    'tab'             => 'dashboard',
                    'tab_name'        => __('Dashboard','wp-crowdfunding'),
                    'load_form_file'  => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/dashboard.php'
                ),
            'profile' =>
                array(
                    'tab'             => 'account',
                    'tab_name'        => __('Profile','wp-crowdfunding'),
                    'load_form_file'  => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/profile.php'
                ),
            'contact' =>
                array(
                    'tab'             => 'account',
                    'tab_name'        => __('Contact','wp-crowdfunding'),
                    'load_form_file'  => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/contact.php'
                ),
            'campaign' =>
                array(
                    'tab'             => 'campaign',
                    'tab_name'        => __('My Campaigns','wp-crowdfunding'),
                    'load_form_file'  => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/campaign.php'
                ),
            'backed_campaigns' =>
                array(
                    'tab'             => 'campaign',
                    'tab_name'        => __('My Invested Campaigns','wp-crowdfunding'),
                    'load_form_file'  => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/investment.php'
                ),
            'pledges_received' =>
                array(
                    'tab'            => 'campaign',
                    'tab_name'       => __('Pledges Received','wp-crowdfunding'),
                    'load_form_file' => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/order.php'
                ),
            'bookmark' =>
                array(
                    'tab'            => 'campaign',
                    'tab_name'       => __('Bookmarks','wp-crowdfunding'),
                    'load_form_file' => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/bookmark.php'
                ),
            'password' =>
                array(
                    'tab'            => 'account',
                    'tab_name'       => __('Password','wp-crowdfunding'),
                    'load_form_file' => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/password.php'
                ),
            'rewards' =>
                array(
                    'tab'            => 'account',
                    'tab_name'       => __('Rewards','wp-crowdfunding'),
                    'load_form_file' => WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/rewards.php'
                ),
        ));
        

        /**
         * Print menu with active link marking...
         */
        $html .= '<div class="wpneo-wrapper">';
        $html .= '<div class="wpneo-head wpneo-shadow">';
            $html .= '<div class="wpneo-links clearfix">';

                $dashboard = $account = $campaign = $extra = '';
                foreach ($dashboard_menus as $menu_name => $menu_value){

                    if ( empty($get_id) && $menu_name == 'dashboard'){ $active = 'active';
                    } else { $active = ($get_id == $menu_name) ? 'active' : ''; }

                    $pagelink = add_query_arg( 'page_type', $menu_name , $pagelink );

                    if( $menu_value['tab'] == 'dashboard' ){
                        $dashboard .= '<div class="wpneo-links-list '.$active.'"><a href="'.$pagelink.'">'.$menu_value['tab_name'].'</a></div>';
                    }elseif( $menu_value['tab'] == 'account' ){
                        $account .= '<div class="wpneo-links-lists '.$active.'"><a href="'.$pagelink.'">'.$menu_value['tab_name'].'</a></div>';
                    }elseif( $menu_value['tab'] == 'campaign' ){
                        $campaign .= '<div class="wpneo-links-lists '.$active.'"><a href="'.$pagelink.'">'.$menu_value['tab_name'].'</a></div>';
                    }else{
                        $extra .= '<div class="wpneo-links-list '.$active.'"><a href="'.$pagelink.'">'.$menu_value['tab_name'].'</a></div>';
                    }
                }
                
                $html .= $dashboard;
                $html .= '<div class="wpneo-links-list wp-crowd-parent"><a href="#">'.__("My Account","wp-crowdfunding").'<span class="wpcrowd-arrow-down"></span></a>';
                    $html .= '<div class="wp-crowd-submenu wpneo-shadow">';
                        $html .= $account;
                        $html .= '<div class="wpneo-links-lists"><a href="'.wp_logout_url( home_url() ).'">'.__('Logout','wp-crowdfunding').'</a></div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="wpneo-links-list wp-crowd-parent"><a href="#">'.__("Campaigns","wp-crowdfunding").'<span class="wpcrowd-arrow-down"></span></a>';
                    $html .= '<div class="wp-crowd-submenu wpneo-shadow">';
                        $html .= $campaign;
                    $html .= '</div>';
                $html .= '</div>';
                $html .= $extra;
                $html .= '<div class="wp-crowd-new-campaign"><a class="wp-crowd-btn wp-crowd-btn-primary" href="'.get_permalink(get_option('wpneo_form_page_id')).'">'.__("Add New Campaign","wp-crowdfunding").'</a></div>';

            $html .= '</div>';
        $html .= '</div>';


            $var = '';
            if( isset($_GET['page_type']) ){
                $var = $_GET['page_type'];
            }
    
            ob_start();
            if( $var == 'update' ){
                require_once WPNEO_CROWDFUNDING_DIR_PATH.'includes/woocommerce/dashboard/update.php';
            }else{
                if ( ! empty($dashboard_menus[$get_id]['load_form_file']) ) {
                    if (file_exists($dashboard_menus[$get_id]['load_form_file'])) {
                        include $dashboard_menus[$get_id]['load_form_file'];
                    }
                }else{
                    include $dashboard_menus['dashboard']['load_form_file'];
                }
            }
            $html .= ob_get_clean();
            
        $html .= '</div>';//wpneo-wrapper

    }else{
        $html .= wpneo_crowdfunding_wc_toggle_login_form();
    }

    return $html;
}
