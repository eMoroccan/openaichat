<?php

    if( ! defined( 'ABSPATH' ) ){
        exit;
    }

    $options = get_option( 'wp_manga_settings', array() );
    $options = isset( $options['user_upload_pro_settings'] ) ? $options['user_upload_pro_settings'] : array();
	$message = isset( $options['user_upload_pro_message'] ) ? $options['user_upload_pro_message'] : '';
	
?>
    <div class="tab-pane user-upload-tab-pane <?php echo ( $tab_pane == 'add-manga' ) ? 'active' : ''; ?> add-manga" id="madara-user-upload-pro">

        <?php if( !empty( $message ) ){ ?>
            <div class="alert alert-info">
                <?php echo wp_kses_post( $message ); ?>
            </div>
        <?php } ?>

        <?php echo do_shortcode('[muupro_upload_manga horizontalthumb="1" author="1" artist="1" description="1" other_name="1" type=
		"1" status="1" genres="1" adult="1" tags="1" badge="1"]');?>
    </div>