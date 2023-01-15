<?php

    /**
 	 * Template for tab Manga Information
	 */

    global $post, $options;

    $is_edit_page = muupro_is_manga_edit_page();

    if( $is_edit_page ){
        $alter_name = get_post_meta( $post->ID, '_wp_manga_alternative', true );
        $type       = get_post_meta( $post->ID, '_wp_manga_type', true );
        $status     = get_post_meta( $post->ID, '_wp_manga_status', true );
		$seo_meta     = get_post_meta( $post->ID, 'manga_meta_title', true );
		$seo_desc   = get_post_meta( $post->ID, 'manga_meta_desc', true );

        $release_years = strip_tags( get_the_term_list( $post->ID, 'wp-manga-release', '', ', ' ) );
        $authors       = strip_tags( get_the_term_list( $post->ID, 'wp-manga-author', '', ', '  ) );
        $artists       = strip_tags( get_the_term_list( $post->ID, 'wp-manga-artist', '', ', ' ) );
        $genres        = get_the_terms( $post, 'wp-manga-genre' );
        $genres        = !empty( $genres ) ? wp_list_pluck( $genres, 'slug' ) : array();
        $tags          = strip_tags( get_the_term_list( $post->ID, 'wp-manga-tag', '', ', '  ) );
    }
?>

<div class="tab-item">

    <div class="settings-title">
        <h3>
            <?php esc_html_e('Manga Extra Information', MUUPRO_TEXTDOMAIN); ?>
        </h3>
    </div>
    <div class="form-group row">
        <label for="madara-manga-alternative-name" class="col-md-3">
            <?php esc_html_e('Alternative Name', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <input class="form-control" type="text" value="<?php echo isset( $alter_name ) ? esc_attr( $alter_name ) : '' ?>" name="madara-manga-alternative-name">
        </div>
    </div>
    <div class="form-group row">
        <label for="madara-manga-type" class="col-md-3">
            <?php esc_html_e('Type', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <input class="form-control" type="text" value="<?php echo isset( $type ) ? esc_attr( $type ) : '' ?>" name="madara-manga-type">
        </div>
    </div>
    <div class="form-group row">
        <label for="madara-manga-status" class="col-md-3">
            <?php esc_html_e('Status', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <select name="madara-manga-status">
                <option value="on-going" <?php echo isset( $status ) && $status == 'on-going' ? 'selected' : ''; ?>>
                    <?php esc_html_e("On Going", MUUPRO_TEXTDOMAIN); ?>
                </option>
                <option value="end" <?php echo isset( $status ) && $status == 'end' ? 'selected' : ''; ?>>
                    <?php esc_html_e("Completed", MUUPRO_TEXTDOMAIN); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label for="madara-manga-release-year" class="col-md-3">
            <?php esc_html_e('Release Year', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <input class="form-control" type="text" value="<?php echo isset( $release_years ) ? esc_attr( $release_years ) : '' ?>" name="madara-manga-release-year">
        </div>
    </div>
    <div class="form-group row">
        <label for="madara-manga-authors" class="col-md-3">
            <?php esc_html_e('Authors', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <input class="form-control" type="text" value="<?php echo isset( $authors ) ? esc_attr( $authors ) : '' ?>" name="madara-manga-authors">
            <div class="description">
                <?php esc_html_e('separate with commas', MUUPRO_TEXTDOMAIN); ?>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="madara-manga-artists" class="col-md-3">
            <?php esc_html_e('Artists', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <input class="form-control" type="text" value="<?php echo isset( $artists ) ? esc_attr( $artists ) : '' ?>" name="madara-manga-artists">
            <div class="description">
                <?php esc_html_e('separate with commas', MUUPRO_TEXTDOMAIN); ?>
            </div>
        </div>
    </div>

    <?php if( ( !empty( $options['genres'] ) && is_array( $options['genres'] ) ) || ! isset( $options['genres'] ) ){ ?>
        
        <div class="form-group row">
            <label class="col-md-3">
                <?php esc_html_e('Genres', MUUPRO_TEXTDOMAIN); ?>
            </label>
            <div class="col-md-9">
                <div class="form-group checkbox-group row">
                    <?php

                        $all_genres = get_terms([
                            'taxonomy'   => 'wp-manga-genre',
                            'include' => isset( $options['genres'] ) ? implode( ',', $options['genres'] ) : array()
                        ]);

                        foreach( $all_genres as $genre ){ ?>
                            <div class="checkbox col-xs-6 col-sm-4 col-md-4 ">
                                <input
                                id="<?php echo esc_attr( $genre->slug ); ?>"
                                value="<?php echo esc_attr( $genre->term_id ); ?>"
                                name="madara-manga-genres[]" type="checkbox"
                                <?php echo !empty( $genres ) && in_array( $genre->slug, $genres ) ? 'checked' : ''; ?>
                                >
                                <label for="<?php echo esc_attr( $genre->slug ); ?>"> <?php echo esc_attr( $genre->name ); ?> </label>
                            </div>
                        <?php }
                    ?>
                </div>
            </div>
        </div>
    <?php } ?>
    
    <div class="form-group row">
        <label for="madara-manga-tags" class="col-md-3">
            <?php esc_html_e('Tags', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <input class="form-control" type="text" value="<?php echo isset( $tags ) ? esc_attr( $tags ) : '' ?>" name="madara-manga-tags">
            <div class="description">
                <?php esc_html_e('separate with commas', MUUPRO_TEXTDOMAIN); ?>
            </div>
        </div>
    </div>
	
	<div class="form-group row">
        <label for="madara-manga-adult" class="col-md-3">
            <?php esc_html_e('Adult Content', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <select name="madara-manga-adult" class="form-control" required>
				<?php
				$manga_adult = get_post_meta($post->ID, 'manga_adult_content', true);
				
				?>
				<option value="no">
					<?php esc_html_e("No", MUUPRO_TEXTDOMAIN); ?>
				</option>
				<option value="yes" <?php echo (!empty( $manga_adult ) && $manga_adult[0] == 'yes') ? 'selected' : ''; ?>>
					<?php esc_html_e("Yes", MUUPRO_TEXTDOMAIN); ?>
				</option>
			</select>
        </div>
    </div>
	
	<div class="form-group row">
        <label for="name-input" class="col-md-3">
            <?php esc_html_e('Badge', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <?php 
						
			$badge_choices = function_exists('madara_get_badge_choices') ? madara_get_badge_choices() : array(esc_html__( 'Hot', MUUPRO_TEXTDOMAIN ), esc_html__( 'New', MUUPRO_TEXTDOMAIN ));
			
			$manga_badge = trim(get_post_meta($post->ID, 'manga_title_badges', true));
			?>
			<select class="form-control" name="madara-manga-badge">
				<option value=""></option>
				<?php
				foreach($badge_choices as $badge){
					?>
					<option value="<?php echo sanitize_title($badge);?>" <?php echo ($manga_badge == sanitize_title($badge) ? 'selected="selected"' : '');?>><?php echo esc_html($badge);?></option>
					<?php
				}
				?>
			</select>
        </div>
    </div>
	
	<div class="form-group row">
        <label for="madara-seo-meta" class="col-md-3">
            <?php esc_html_e('SEO - Manga Meta Title', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <textarea style="width:100%;max-width:100%" class="form-control" type="text" name="madara-seo-meta"><?php echo isset( $seo_meta ) ? esc_html( $seo_meta ) : '' ?></textarea>
        </div>
    </div>
	
	<div class="form-group row">
        <label for="madara-seo-description" class="col-md-3">
            <?php esc_html_e('SEO - Manga Meta Description', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <textarea style="width:100%;max-width:100%" class="form-control" type="text" name="madara-seo-desc"><?php echo isset( $seo_desc ) ? esc_html( $seo_desc ) : '' ?></textarea>
        </div>
    </div>
	<?php do_action('muupro_edit_manga_extra_info'); ?>
</div>
