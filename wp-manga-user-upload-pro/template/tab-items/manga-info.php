<?php

    /**
 	 * Template for tab Manga Information
	 */

    global $post;
    $is_edit_page = muupro_is_manga_edit_page();
?>

<div class="tab-item">

    <div class="settings-title">
        <h3>
            <?php esc_html_e('Manga Information', MUUPRO_TEXTDOMAIN); ?>
        </h3>
    </div>
    <div class="form-group row">
        <label for="madara-manga-title" class="col-md-3">
            <?php esc_html_e('Title', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <input class="form-control" style="width:100%;max-width:100%" type="text" name="madara-manga-title" value="<?php echo $is_edit_page ? esc_attr( $post->post_title ) : ''; ?>" required>
        </div>
    </div>
    <div class="form-group row">
        <label for="name-input" class="col-md-3">
            <?php esc_html_e('Description', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <textarea class="form-control" type="text" name="madara-manga-description" rows="<?php echo $is_edit_page ? '10' : '5'; ?>"><?php echo $is_edit_page ? wp_kses_post( $post->post_content ) : ''; ?></textarea>
        </div>
    </div>
    <div class="form-group row">
        <label for="madara-featured-image" class="col-md-3">
            <?php esc_html_e('Featured Image', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <?php if( $is_edit_page ) { ?>
                <div class="featured-image">
                    <img src="<?php echo get_the_post_thumbnail_url( $post->ID, 'medium') ?>">
                </div>
            <?php } ?>
            <label class="select-avata"><input type="file" name="madara-featured-image" <?php echo !$is_edit_page ? 'required="required"' : '' ?> accept=".jpg,.jpeg,.png,.gif"></label>
        </div>
    </div>
	<div class="form-group row">
        <label for="name-input" class="col-md-3">
            <?php esc_html_e('Horizontal Thumbnail', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <?php if( $is_edit_page ) { 
				$image = get_post_meta($post->ID, 'manga_banner', true);
				if($image){
			?>
                <div class="featured-image">
                    <img src="<?php echo esc_url($image); ?>">
                </div>
            <?php }
			}?>
            <label class="select-avata"><input type="file" name="madara-horizontal-thumb" accept=".jpg,.jpeg,.png,.gif"></label>
        </div>
    </div>
	
	<?php do_action('muupro_edit_manga_info'); ?>
</div>
