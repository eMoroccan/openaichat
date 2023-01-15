<?php

$current_user = get_current_user_id();

if(!$current_user) return;

?>
<div id="muupro_form">
<form id="madara-user-upload-pro-form-submit-manga" class="madara-user-upload-pro-form" method="post" data-redirect="<?php echo isset($atts['redirect']) ? esc_url($atts['redirect']) : '';?>">

			<?php muupro_template( 'loading' ); ?>

            <input type="hidden" name="isZipFileValid" value="0">
            <input type="hidden" name="userID" value="<?php echo esc_attr( get_current_user_id() ); ?>">
			
			<div class="form-group-wrapper">
				
				<!-- Title -->
				<div class="form-group row title-field">
					<label for="madara-manga-title" class="col-md-3">
						<?php esc_html_e('Title', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="madara-manga-title" required="required" required placeholder="<?php esc_attr_e( 'Chapter Title', MUUPRO_TEXTDOMAIN ); ?>">
					</div>
				</div>
				
				<?php if(isset($atts['other_name']) && $atts['other_name']) {?>
				<!-- Alternative names -->
				<div class="form-group row alternative-names-field">
					<label for="muupro_upload_manga" class="col-md-3">
						<?php esc_html_e('Alternative Name(s)', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<textarea class="form-control" name="madara-manga-alternative-name" rows="5" placeholder="<?php esc_attr_e( 'Use new line for each entry', MUUPRO_TEXTDOMAIN ); ?>"><?php echo isset( $alter_name ) ? esc_attr( $alter_name ) : '' ?></textarea>
					</div>
				</div>
				<?php } ?>
				
				<?php if(isset($atts['description']) && $atts['description']) {?>
				<!-- Description -->
				<div class="form-group row description-field">
					<label for="madara-manga-description" class="col-md-3">
						<?php esc_html_e('Description', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<textarea class="form-control" type="text" name="madara-manga-description" rows="10" placeholder="<?php esc_attr_e( 'Optional', MUUPRO_TEXTDOMAIN ); ?>"></textarea>
					</div>
				</div>
				<?php } ?>

				<!-- Feature Image -->
				<?php  
					$messages = array(
						'invalid_ratio'      => esc_attr( 'Image ratio is not valid, please check again.', MUUPRO_TEXTDOMAIN ),
						'invalid_resolution' => esc_attr( 'Image resolution is not valid, please check again', MUUPRO_TEXTDOMAIN ),
						'invalid_type'       => esc_attr( 'Invalid image file, please check again.', MUUPRO_TEXTDOMAIN ),
					);
				?>
				<div class="form-group row featured-image-field" data-messages="<?php echo esc_attr( json_encode( $messages ) ); ?>">
					<label for="madara-featured-image" class="col-md-3">
						<?php esc_html_e('Manga Thumbnail', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<div class="featured-image-preview">
							<img />
						</div>
						<span><?php esc_html_e( 'Minimum aspect ratio: 1:1.5, ideal minimum resolution: 300x450 [Required]', MUUPRO_TEXTDOMAIN ); ?></span>
						<label class="browser-file select-avata">
							<i class="icon ion-md-folder"></i> <?php esc_html_e( 'Browse', MUUPRO_TEXTDOMAIN ); ?>
							<input type="file" name="madara-featured-image" required accept=".jpg,.jpeg,.png,.gif">
						</label>
					</div>
				</div>
				
				<?php 
				if(isset($atts['horizontalthumb']) && $atts['horizontalthumb']){?>
				<div class="form-group row horizontal-thumb-field" data-messages="<?php echo esc_attr( json_encode( $messages ) ); ?>">
					<label for="madara-horizontal-thumb" class="col-md-3">
						<?php esc_html_e('Horizontal Thumbnail', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<div class="horizontal-thumb-preview">
							<img />
						</div>
						<span><?php esc_html_e( 'Minimum aspect ratio: 1.5:1, ideal minimum resolution 450:300', MUUPRO_TEXTDOMAIN ); ?></span>
						<label class="browser-file select-horizontal-thumb">
							<i class="icon ion-md-folder"></i> <?php esc_html_e( 'Browse', MUUPRO_TEXTDOMAIN ); ?>
							<input type="file" name="madara-horizontal-thumb" <?php if(isset($atts['horizontalThumb_required']) && $atts['horizontalThumb_required']) echo 'required';?> accept=".jpg,.jpeg,.png,.gif">
						</label>
					</div>
				</div>
				<?php } ?>
				
				<?php if(isset($atts['author']) && $atts['author']) {?>
				<!-- Authors -->
				<div class="form-group row">
					<label for="madara-manga-authors" class="col-md-3">
						<?php esc_html_e('Authors', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<input class="form-control" type="text" value="<?php echo isset( $authors ) ? esc_attr( $authors ) : '' ?>" name="madara-manga-authors" <?php if(isset($atts['author_required']) && $atts['author_required']) {?>required="required"<?php }?>>
						<div class="description">
							<?php esc_html_e('separate with commas', MUUPRO_TEXTDOMAIN); ?>
						</div>
					</div>
				</div>
				<?php } ?>

				<?php if(isset($atts['artist']) && $atts['artist']) {?>
				<!-- Artists -->
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
				<?php } ?>

				<?php if(isset($atts['type']) && $atts['type']) {?>
				<!-- Type -->
				<div class="form-group row type-field">
					<label for="name-input" class="col-md-3">
						<?php esc_html_e('Type', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<?php if(isset($atts['types'])) {
							$types = explode(',', $atts['types']);
							
							?>
						<select name="madara-manga-type" class="form-control" <?php if(isset($atts['type_required']) && $atts['type_required']) {?>required<?php }?>>
							<?php foreach($types as $t){?>
							<option value="<?php echo sanitize_title($t);?>" <?php echo isset( $def_type ) && $def_type == $t ? 'selected' : '' ?>><?php echo esc_html( $t ); ?></option>
							<?php }?>
						</select>
						<span class="select-icon"><i class="icon ion-md-arrow-dropdown"></i></span>
						<?php } else { ?>
						<input type="text" name="madara-manga-type" class="form-control" <?php if(isset($atts['type_required']) && $atts['type_required']) {?>required<?php }?>/>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
				
				<?php if(isset($atts['release']) && $atts['release']) {?>
				<!-- Release -->
				<div class="form-group row release-field">
					<label for="name-input" class="col-md-3">
						<?php esc_html_e('Release Year', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<input type="text" name="madara-manga-release" class="form-control" <?php if(isset($atts['release_required']) && $atts['release_required']) {?>required<?php }?>/>
					</div>
				</div>
				<?php } ?>

				<?php if(isset($atts['status']) && $atts['status']) {?>
				<!-- Status -->
				<div class="form-group row">
					<label for="name-input" class="col-md-3">
						<?php esc_html_e('Status', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<select name="madara-manga-status" class="form-control" required>
							<option value="on-going" <?php echo isset( $manga_status ) && $manga_status == 'on-going' ? 'selected' : ''; ?>>
								<?php esc_html_e("On Going", MUUPRO_TEXTDOMAIN); ?>
							</option>
							<option value="end" <?php echo isset( $manga_status ) && $manga_status == 'end' ? 'selected' : ''; ?>>
								<?php esc_html_e("Completed", MUUPRO_TEXTDOMAIN); ?>
							</option>
						</select>
						<span class="select-icon"><i class="icon ion-md-arrow-dropdown"></i></span>
					</div>
				</div>
				<?php } ?>
				
				<?php if(isset($atts['adult']) && $atts['adult']) {?>
				<!-- adult -->
				<div class="form-group row">
					<label for="adult-input" class="col-md-3">
						<?php esc_html_e('Adult Content', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<select name="madara-manga-adult" class="form-control" required>
							<option value="no" <?php echo isset( $manga_adult ) && $manga_adult == 'no' ? 'selected' : ''; ?>>
								<?php esc_html_e("No", MUUPRO_TEXTDOMAIN); ?>
							</option>
							<option value="yes" <?php echo isset( $manga_adult ) && $manga_adult == 'yes' ? 'selected' : ''; ?>>
								<?php esc_html_e("Yes", MUUPRO_TEXTDOMAIN); ?>
							</option>
						</select>
						<span class="select-icon"><i class="icon ion-md-arrow-dropdown"></i></span>
					</div>
				</div>
				<?php } ?>
				
				<?php if(isset($atts['badge']) && $atts['badge']) {?>
				<!-- Badge -->
				<div class="form-group row">
					<label for="name-input" class="col-md-3">
						<?php esc_html_e('Badge', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<?php 
						
						$badge_choices = function_exists('madara_get_badge_choices') ? madara_get_badge_choices() : array(esc_html__( 'Hot', MUUPRO_TEXTDOMAIN ), esc_html__( 'New', MUUPRO_TEXTDOMAIN ));
						?>
						<select class="form-control" name="madara-manga-badge">
							<option value=""></option>
							<?php
							foreach($badge_choices as $badge){
								?>
								<option value="<?php echo esc_attr($badge);?>"><?php echo esc_html($badge);?></option>
								<?php
							}
							?>
						</select>
						<span class="select-icon"><i class="icon ion-md-arrow-dropdown"></i></span>
						<div class="description">
							<?php esc_html_e('Maximum 5 characters', MUUPRO_TEXTDOMAIN); ?>
						</div>
					</div>
				</div>
				<?php } ?>
				
				<?php if(isset($atts['tags']) && $atts['tags']) {?>
				<!-- Tags -->
				<div class="form-group row">
					<label for="name-input" class="col-md-3">
						<?php esc_html_e('Tags', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<div class="form-group checkbox-group row">
							<?php
								$all_tags = get_terms( 'wp-manga-tag', array( 'hide_empty' => false ) );

								foreach( $all_tags as $tag ){ ?>
									<div class="checkbox col-xs-6 col-sm-4 col-md-4">
										<input
										id="tag-<?php echo esc_attr( $tag->slug ); ?>"
										value="<?php echo esc_attr( $tag->name ); ?>"
										name="madara-manga-tags[]" type="checkbox"
										<?php echo !empty( $manga_tags ) && in_array( $tag->slug, $manga_tags ) ? 'checked' : ''; ?>
										>
										<label for="tag-<?php echo esc_attr( $tag->slug ); ?>"> <?php echo esc_attr( $tag->name ); ?> </label>
									</div>
								<?php }
							?>
						</div>
					</div>
				</div>
				<?php }?>

				<?php if(isset($atts['genres']) && $atts['genres']) {?>
				<!-- Genres -->
				<div class="form-group row">
					<label for="name-input" class="col-md-3">
						<?php esc_html_e('Genres', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<div class="form-group checkbox-group row">
							<?php
								$all_genres = get_terms( 'wp-manga-genre', array( 'hide_empty' => false ) );

								foreach( $all_genres as $genre ){ ?>
									<div class="checkbox col-xs-6 col-sm-4 col-md-4">
										<input
										id="genre-<?php echo esc_attr( $genre->slug ); ?>"
										value="<?php echo esc_attr( $genre->term_id ); ?>"
										name="madara-manga-genres[]" type="checkbox"
										<?php echo !empty( $manga_genres ) && in_array( $genre->slug, $manga_genres ) ? 'checked' : ''; ?>
										>
										<label for="genre-<?php echo esc_attr( $genre->slug ); ?>"> <?php echo esc_attr( $genre->name ); ?> </label>
									</div>
								<?php }
							?>
						</div>
					</div>
				</div>
				<?php }?>

				<?php do_action('muupro_form_upload_manga_fields'); ?>
				
			</div>

			<div class="form-group row">
				<label for="name-input-submit" class="col-md-3"></label>
				<div class="col-md-9 submit-row">
					<?php wp_nonce_field( 'madara_user_create_manga_frontend', 'nonce', false, true ); ?>
					<button type="submit"><i class="icon ion-md-add-circle"></i> <?php esc_html_e( 'Add new title', MUUPRO_TEXTDOMAIN ); ?></button>
				</div>
			</div>
        </form>
</div>