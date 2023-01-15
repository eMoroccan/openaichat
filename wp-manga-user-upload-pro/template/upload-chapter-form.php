<?php

    if( ! defined( 'ABSPATH' ) ){
        exit;
    }
	
	$settings = muupro_get_user_upload_settings();

?>
<div class="tab-pane user-upload-tab-pane <?php echo ( $tab_pane == 'upload-chapter' ) ? 'active' : ''; ?> upload-chapter" id="madara-user-upload-pro">

	<?php if( !empty( $message ) ){ ?>
		<div class="alert alert-info">
			<?php echo wp_kses_post( $message ); ?>
		</div>
	<?php } ?>

	<?php 
	
	if($settings['manga_type'] == 'manga'){
		echo do_shortcode('[muupro_upload_chapters owner="1" title="1" extendname="1"]');
	} else {
		echo do_shortcode('[muupro_upload_novel_chapter owner="1" title="1" extendname="1"]');
	}
	?>
</div>