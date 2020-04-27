<?php
namespace WPCF\blocks;

defined( 'ABSPATH' ) || exit;

class Search{
    
    public function __construct(){
        $this->register_search();
    }

    public function register_search(){
        register_block_type(
            'wp-crowdfunding/search',
            array(
                'attributes' => array(
                    'formSize'   => array(
                        'type'      => 'string',
                        'default'   => 'small'
                    ),
                    'bgColorpalette'    => array(
                        'type'          => 'string',
                        'default'       => '#0073a8',
                    ),
                    'titlecolor'    => array(
                        'type'          => 'string',
                        'default'       => '#ffffff',
                    ),
                    'fontSize'    => array(
                        'type'          => 'number',
                        'default'       => 16,
                    ),
                    'fontWeight'    => array(
                        'type'          => 'number',
                        'default'       => 400,
                    ),
                    'SearchfontSize' => array(
                        'type'          => 'number',
                        'default'       => 14,
                    ),
                ),
                'render_callback' => array( $this, 'search_block_callback' ),
            )
        );
    }

    public function search_block_callback( $att ){
        $formSize           = isset($att['formSize']) ? $att['formSize'] : '';
        $bgColor            = isset( $att['bgColorpalette']) ? $att['bgColorpalette'] : 'all';
        $titlecolor         = isset( $att['titlecolor']) ? $att['titlecolor'] : 'all';
        $fontSize 		    = isset( $att['fontSize']) ? $att['fontSize'] : '16';
        $fontWeight 	    = isset( $att['fontWeight']) ? $att['fontWeight'] : '400';
        $SearchfontSize     = isset( $att['SearchfontSize']) ? $att['SearchfontSize'] : '14';
    
        $html = '';
        $html .= '<div class="wpcf-form-field '. $formSize .'">';
            $html .= '<form role="search" method="get" action="'.esc_url(home_url('/')).'">';
                $html .= '<input type="search" class="search-field" placeholder="'.__("Search", "wp-crowdfunding").'" value="'. $_GET['s'] .'" name="s" style="font-size: '. $SearchfontSize .'px;">';
                $html .= '<input type="hidden" name="post_type" value="product">';
                $html .= '<input type="hidden" name="product_type" value="croudfunding">';
                $html .= '<button type="submit" style="background: '.$bgColor.'; color: '.$titlecolor.'; font-size: '. $fontSize .'px; font-weight: '.$fontWeight.'">'.__("Search", "wp-crowdfunding").'</button>';
            $html .= '</form>';
        $html .= '</div>';

        return $html;
    }
}