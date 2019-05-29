<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if (! class_exists('WPNEO_WC_Admin_Dashboard')) {

    class WPNEO_WC_Admin_Dashboard{

        protected static $_instance;
        public static function instance(){
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function __construct(){
            add_action( 'wp_dashboard_setup',                   array( $this, 'init' ) );
            add_filter( 'manage_edit-product_columns',          array($this, 'wpneo_crowdfunding_order_custom_column'));
            add_action( 'manage_product_posts_custom_column' ,  array($this, 'wpneo_woocommerce_show_campaign_data_in_product_column'), 10, 2 );
            add_action( 'add_meta_boxes',                       array($this, 'wpneo_crowdfunding_register_meta_boxes') );
            add_action( 'add_meta_boxes',                       array($this, 'wpneo_crowdfunding_selected_reward_meta_box') );
            add_action( 'wp_ajax_wpcf_order_action',            array($this, 'wpneo_order_campaign_action') );
        }

        public function init(){
            wp_add_dashboard_widget('wpneo_crowdfunding_overview', __('CrowdFunding Overview', 'wp-crowdfunding'), array($this, 'wpneo_crowdfunding_overview'));
        }

        /**
        * Get this info to wordpress dashboard
        */
        public function wpneo_crowdfunding_overview(){
            
            global $wpdb;
            $totalCampaigns = $total_orders = $on_hole_total_orders = $total_campaign_orders = 0;

            $query_args = array(
                'post_type' => 'product',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_type',
                        'field'    => 'slug',
                        'terms'    => 'crowdfunding',
                    ),
                ),
            );

            $campaigns = new WP_Query($query_args);

            $campaign_ids = array();
            if ($campaigns->have_posts()){
                global $post;
                while ($campaigns->have_posts()){ 
                    $campaigns->the_post();
                    $campaign_ids[] = $post->ID;
                }
            }
            
            if (count($campaign_ids) > 0) {
                $campaign_ids_string     = implode(',', $campaign_ids);
                $wp_sql                 = $this->totalOrdersSalesAmount($campaign_ids_string);
                $wp_sql_on_hold         = $this->totalOrdersSalesAmount($campaign_ids_string, 'wc-on-hold');
                $total_campaign_orders   = $wp_sql->total_sales_amount;
                $total_orders           = $wp_sql->total_orders ? $wp_sql->total_orders : 0;
                $totalCampaigns          = $campaigns->post_count ;
                $on_hole_total_orders   = $wp_sql_on_hold->total_orders;
            }

            $html = '';
            $html .= '<ul class="wpneo_d_status_list">';
                $html .= '<li><span> <strong>'.$totalCampaigns.' </strong> '.__( "Total Campaign","wp-crowdfunding" ).'</span></li>';
                $html .= '<li><span><strong>'.$total_orders .' </strong> '.__( "Completed Orders","wp-crowdfunding" ).'</span></li>';
                $html .= '<li><span><strong>'. $on_hole_total_orders.' </strong> '.__( "On-Hold Orders","wp-crowdfunding" ).'</span></li>';
                $html .= '<li><span><strong>'.get_woocommerce_currency_symbol().$total_campaign_orders.' </strong> '.__( "Total Donation Raised","wp-crowdfunding" ).'</span></li>';
            $html .= '</ul>';
            echo $html;
        }

        /**
         * @param $in_campaign_ids_string
         * @param string $order_status
         * @return array|null|object|void
         *
         * Get total order and amount by campaigns/products ids
         */
        public function totalOrdersSalesAmount($in_campaign_ids_string, $order_status = 'wc-completed'){
            
            global $wpdb;
            $query ="SELECT 
                        SUM(ltoim.meta_value) as total_sales_amount, COUNT(ltoim.meta_value) as total_orders 
                    FROM 
                        {$wpdb->prefix}woocommerce_order_itemmeta woim 
			        LEFT JOIN 
                        {$wpdb->prefix}woocommerce_order_items oi ON woim.order_item_id = oi.order_item_id
			        LEFT JOIN 
                        {$wpdb->prefix}posts wpposts ON order_id = wpposts.ID
			        LEFT JOIN 
                        {$wpdb->prefix}woocommerce_order_itemmeta ltoim ON ltoim.order_item_id = oi.order_item_id AND ltoim.meta_key = '_line_total'
			        WHERE 
                        woim.meta_key = '_product_id' AND woim.meta_value IN ({$in_campaign_ids_string}) AND wpposts.post_status = '{$order_status}';";

            $wp_sql = $wpdb->get_row($query);
            return $wp_sql;
        }


        /**
         * @param $columns
         * @return mixed
         *
         * Campaign owner column
         */
        public function wpneo_crowdfunding_order_custom_column( $columns ) {
            $date = $columns['date'];
            unset($columns['date']);
            $columns['campaign_owner'] = __('Owner', 'wp-crowdfunding');
            $columns['campaign_status'] = __('Status', 'wp-crowdfunding');
            $columns['date'] = $date;
            return $columns;
        }

        /**
         * @param $column
         * @param $post_id
         */

        function wpneo_woocommerce_show_campaign_data_in_product_column( $column, $post_id ) {

            switch ( $column ) {
                case 'campaign_owner':
	                $post =  get_post($post_id);
	                $user = get_userdata($post->post_author);

	                $dashboard_page_id = get_option('wpneo_crowdfunding_dashboard_page_id');
					$query_args= array(
						'show_user_id'  => $user->ID,
						'page_type'     => 'profile',
					);
                	$dashboard_url = add_query_arg($query_args, get_permalink($dashboard_page_id));

                	if (user_can(get_current_user_id(), 'manage_options')){
		                echo "<a href='{$dashboard_url}' target='_blank'>{$user->display_name}</a>";
	                }else{
                		echo $user->display_name;
	                }
                    break;
                case 'campaign_status':
                    $product = wc_get_product($post_id);
                    if( $product->get_type() === 'crowdfunding' ){
                        if( WPNEOCF()->is_campaign_started() ){
                            if( WPNEOCF()->campaignValid() ){
                                echo '<span class="wpcf-reached">'.__('Reached','wp-crowdfunding').'</span>';
                            }else{
                                echo '<span class="wpcf-not-reached">'.__('Not reached','wp-crowdfunding').'</span>';
                            }
                        }else{
                        echo '<span class="wpcf-not-started">'.__('Not Started','wp-crowdfunding').'</span>';
                        }
                    }
                break;
            }
        }

        /**
         * Register meta box(es).
         */
        function wpneo_crowdfunding_register_meta_boxes() {
            add_meta_box( 'meta-box-id', __( 'Campaign Summary', 'wp-crowdfunding' ), array($this, 'wpneo_crowdfunding_metabox_display_callback'), 'product', 'side', 'high' );
        }

        /**
         * Meta box display callback.
         *
         * @param WP_Post $post Current post object.
         */
        function wpneo_crowdfunding_metabox_display_callback( $post ) {
            include WPNEO_CROWDFUNDING_DIR_PATH.'admin/view/product_metabox_campaign_info.php';
        }

        /**
         * Reward meta box in shop order page right side
         */

        function wpneo_crowdfunding_selected_reward_meta_box(){
            global $post;
            //Check is reward selected
            $r = get_post_meta($post->ID, 'wpneo_selected_reward', true);
            if ( ! empty($r) && is_array($r) ) {
                add_meta_box('meta-box-selected-reward', __('Selected Reward', 'wp-crowdfunding'), array($this, 'wpneo_crowdfunding_selected_reward_meta_box_display_callback'), 'shop_order', 'side', 'high');
            }
        }

        function wpneo_crowdfunding_selected_reward_meta_box_display_callback(){
            include WPNEO_CROWDFUNDING_DIR_PATH.'admin/view/order_selected_reward_meta_box.php';
        }


        // Odrer Data View 
        public function wpneo_order_campaign_action(){
            if ( ! is_user_logged_in()){
                die(json_encode(array('success'=> 0, 'message' => __('Please Sign In first', 'wp-crowdfunding') )));
            }
            
            $html = '';
            $order_id         = sanitize_text_field($_POST['orderid']);
            if( $order_id ){
                $order = new WC_Order( $order_id );
                $html .= '<div>';
                $html .= '<div><span>'.__("Order ID","wp-crowdfunding").':</span> '.$order->get_ID().'</div>';
                $html .= '<div><span>'.__("Order Date","wp-crowdfunding").':</span> '.wc_format_datetime($order->get_date_created()).'</div>';
                $html .= '<div><span>'.__("Order Status","wp-crowdfunding").':</span> '.wc_get_order_status_name($order->get_status()).'</div>';
                
                $html .= '<table>';
                $html .= '<thead>';
                $html .= '<tr>';
                $html .= '<th>'.__( "Product", "woocommerce" ).'</th>';
                $html .= '<th>'.__( "Total", "woocommerce" ).'</th>';
                $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
        
                foreach ( $order->get_items() as $item_id => $item ) {
                    $product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
                    $html .= '<tr>';
                        $html .= '<td>';
                            $is_visible        = $product && $product->is_visible();
                            $product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
                            $html .= apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible );
                            $html .= apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item->get_quantity() ) . '</strong>', $item );
                            do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );
                            wc_display_item_meta( $item );
                            wc_display_item_downloads( $item );
                            do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );






	                $html .= '</td>';
                            $html .= '<td class="woocommerce-table__product-total product-total">';
                            $html .= $order->get_formatted_line_subtotal( $item );
                        $html .= '</td>';
                    $html .= '</tr>';
                }

	            ob_start();
	            $r = get_post_meta($order_id, 'wpneo_selected_reward', true);
	            if ( ! empty($r) && is_array($r) ){
		            ?>
		            <tr>
			            <td>
				            <h4><?php _e('Selected Reward', 'wp-crowdfunding'); ?> </h4>
				            <?php
				            if ( ! empty($r['wpneo_rewards_description'])){
					            echo "<div>{$r['wpneo_rewards_description']}</div>";
				            }

				            if ( ! empty($r['wpneo_rewards_pladge_amount'])){ ?>
					            <?php echo sprintf('Amount : %s, Delivery : %s', wc_price($r['wpneo_rewards_pladge_amount']), $r['wpneo_rewards_endmonth'].', '.$r['wpneo_rewards_endyear'] ); ?>
				            <?php } ?>
			            </td>
			            <td> </td>
		            </tr>
	            <?php }
	            $html .= ob_get_clean();

	            $html .= '<tr>';
                $html .= '<td>'.__('Subtotal:','wp-crowdfunding').'</td>';
                $html .= '<td>'.wc_price($order->get_subtotal()).'</td>';
                $html .= '</tr>';
        
                $html .= '<tr>';
                $html .= '<td>'.__('Payments Method:','wp-crowdfunding').'</td>';
                $html .= '<td>'.$order->get_payment_method_title().'</td>';
                $html .= '</tr>';
        
                $html .= '<tr>';
                $html .= '<td>'.__('Total:','wp-crowdfunding').'</td>';
                $html .= '<td>'.wc_price($order->get_total()).'</td>';
                $html .= '</tr>';
                $html .= '</tbody>';
                $html .= '</table>';
        
                // Customer Details
                $html .= '<h3>'.__( "Customer details", "wp-crowdfunding" ).'</h3>';
                $html .= '<table>';
                if ( $order->get_customer_note() ) :
                    $html .= '<tr>';
                        $html .= '<th>'.__( "Note:", "wp-crowdfunding" ).'</th>';
                        $html .= '<td>'.wptexturize( $order->get_customer_note() ).'</td>';
                    $html .= '</tr>';
                endif;
                if ( $order->get_billing_email() ) :
                    $html .= '<tr>';
                        $html .= '<th>'.__( "Email:", "wp-crowdfunding" ).'</th>';
                        $html .= '<td>'.esc_html__( $order->get_billing_email() ).'</td>';
                    $html .= '</tr>';
                endif;
                if ( $order->get_billing_phone() ) :
                    $html .= '<tr>';
                        $html .= '<th>'.__( "Phone:", "wp-crowdfunding" ).'</th>';
                        $html .= '<td>'.esc_html__( $order->get_billing_phone() ).'</td>';
                    $html .= '</tr>';
                endif;
                $html .= '</table>';
        
        
                // Billings Address
                $html .= '<h3>'.__('Billing Address:','wp-crowdfunding').'</h3>';
                $html .= '<address>';
                $html .= ( $address = $order->get_formatted_billing_address() ) ? $address : __( 'N/A', 'woocommerce' );
                $html .= '</address>';
        
                $html .= '</div>';
            }
            die(json_encode(array('success'=> 1, 'message' => $html )));
        }

    }
}
WPNEO_WC_Admin_Dashboard::instance();