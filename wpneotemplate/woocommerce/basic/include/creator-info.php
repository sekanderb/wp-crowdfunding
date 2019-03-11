<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $post;
$user_info = get_user_meta($post->post_author);
$creator = get_user_by('id', $post->post_author);

?>

<div class="wpneo-campaign-creator-info-wrapper">
    <div class="wpneo-campaign-creator-avatar">
        <?php if ( $post->post_author ) {
            $img_src    = '';
            $image_id = get_user_meta( $post->post_author,'profile_image_id', true );
            if( $image_id != '' ){
                $img_src = wp_get_attachment_image_src( $image_id, 'backer-portfo' )[0];
            } ?>
            <?php if( $img_src ){ ?>
                <img width="80" height="80" src="<?php echo $img_src; ?>" alt="">
            <?php } else { ?>
                <?php  echo get_avatar($post->post_author, 80); ?>
            <?php } ?>
        <?php } ?>
    </div>
    <div class="wpneo-campaign-creator-details">
        <p><a href="javascript:;" data-author="<?php echo $post->post_author; ?>" class="wpneo-fund-modal-btn" ><?php echo wpneo_crowdfunding_get_author_name();
        ?></a> </p>
        <p><?php echo wpneo_crowdfunding_author_all_campaigns($post->post_author)->post_count; ?> <?php _e("Campaigns","wp-crowdfunding"); ?> | <?php echo wpneo_loved_campaign_count(); ?> <?php _e("Loved campaigns","wp-crowdfunding"); ?> </p>
        <?php if ( ! empty($user_info['profile_website'][0])){ ?>
            <p><a href="<?php echo wpneo_crowdfunding_add_http($user_info['profile_website'][0]); ?>"><strong> <?php echo wpneo_crowdfunding_add_http($user_info['profile_website'][0]); ?></strong></a></p>
        <?php } ?>
        <p><a href="javascript:;" data-author="<?php echo $post->post_author; ?>" class="wpneo-fund-modal-btn wpneo-link-style1"><strong><?php _e('See full bio.', 'wp-crowdfunding'); ?></strong></a></p>
    </div>
</div>