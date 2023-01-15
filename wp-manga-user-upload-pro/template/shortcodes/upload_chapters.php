<?php

$current_user = get_current_user_id();

if(!$current_user) return;

$args = [
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'upload_chapters' => 1,
		'meta_query' => array(array('key' => '_wp_manga_chapter_type',
									'value' => 'manga'
									))
	];
	
// is require owner?
if(isset($atts['owner']) && $atts['owner']){
	$args['author'] = $current_user;
	$args['post_status'] = array('publish', 'pending');
}

$mangas = madara_manga_query($args);
?>
<div id="muupro_form">
<form id="madara-user-upload-pro-form" class="madara-user-upload-pro-form" method="post">

		<?php $loading_inner = '<div class="single-chart">
		<svg viewBox="0 0 36 36" class="circular-chart orange">
		<path class="circle-bg" d="M18 2.0845
				a 15.9155 15.9155 0 0 1 0 31.831
				a 15.9155 15.9155 0 0 1 0 -31.831"></path>
		<path class="circle" stroke-dasharray="30, 100" d="M18 2.0845
				a 15.9155 15.9155 0 0 1 0 31.831
				a 15.9155 15.9155 0 0 1 0 -31.831"></path>
		<text x="18" y="20.35" class="percentage">30%</text>
		</svg>
		</div>'; ?>
		
		<?php muupro_template( 'loading', false, compact(['loading_inner']) ); ?>

		<input type="hidden" name="isZipFileValid" value="0">
		<input type="hidden" name="userID" value="<?php echo esc_attr( $current_user ); ?>">
		
		<div class="form-group-wrapper">

			<!-- Select manga -->
			<div class="form-group row">
				<label class="col-md-3">
					<?php esc_html_e('Manga name', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<select name="manga" class="form-group select2">
						<?php if( $mangas->have_posts() ){ ?>
							<option></option>
							<?php foreach( $mangas->posts as $post ){
								?>
								<option value="<?php echo esc_attr( $post->ID ) ?>" <?php echo (isset($_GET['id']) && $_GET['id'] == $post->ID) ? 'selected="selected"' : '';?>>
									<?php echo esc_html( $post->post_title ); ?>
									<?php if( !empty( get_post_meta( $post->ID, 'pending_manga', true ) ) ){ ?>
										<?php printf( __( '[Pending]', MUUPRO_TEXTDOMAIN ) ); ?>
									<?php } ?>
								</option>
							<?php } ?>	
						<?php } ?>
					</select>
				</div>
			</div>

			<!-- Chapter number -->
			<div class="form-group row title-field">
				<label class="col-md-3">
					<?php esc_html_e('Chapter number', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<input class="form-control" type="text" name="chapter-number" required="required" value="" placeholder="<?php esc_attr_e( 'Chapter number here', MUUPRO_TEXTDOMAIN ); ?>">
				</div>
			</div>
			
			<?php if(!isset($atts['volume']) || $atts['volume']) {?>
			<!-- Chapter volume -->
			<div class="form-group row title-field" id="group-select-volume">
				<label class="col-md-3">
					<?php esc_html_e('Chapter Volume', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<select name="chapter-volume" id="chapter-volume" class="form-group select2" value="">
						<option value="0"><?php esc_html_e('-- No Volume --', MUUPRO_TEXTDOMAIN); ?></option>
					</select>
					<label for="chapter-create-volume">
					<input type="checkbox" style="display:inline-block" name="chapter-create-volume" id="chapter-create-volume" value="1"/> <?php echo esc_html__('Create New?', MUUPRO_TEXTDOMAIN);?></label>
				</div>
			</div>
			<div class="form-group row title-field" style="display:none" id="grp-new-volume-name">
				<label class="col-md-3">
					<?php esc_html_e('New Volume Name', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<input type="text" class="form-control" name="volume-name" id="chapter-new-volume"/>
				</div>
			</div>
			<?php }?>

			<?php if(!isset($atts['title']) || $atts['title']) {?>
			<!-- Chapter title -->
			<div class="form-group row title-field">
				<label class="col-md-3">
					<?php esc_html_e('Chapter title', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<input class="form-control" type="text" name="chapter-title" value="" placeholder="<?php esc_attr_e( 'Optional', MUUPRO_TEXTDOMAIN ); ?>">
				</div>
			</div>
			<?php }?>
			
			<?php if(isset($atts['extendname']) && $atts['extendname']) {?>
			<!-- Chapter extend title -->
			<div class="form-group row extendname-field">
				<label class="col-md-3">
					<?php esc_html_e('Chapter Extend Name', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<input class="form-control" type="text" name="chapter-extendname" value="" placeholder="<?php esc_attr_e( 'Optional', MUUPRO_TEXTDOMAIN ); ?>">
				</div>
			</div>
			<?php } ?>
			
			<?php if(isset($atts['seo']) && $atts['seo']) {?>
			<!-- Chapter SEO -->
			<div class="form-group row title-field">
				<label class="col-md-3">
					<?php esc_html_e('Chapter SEO Description', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<input class="form-control" type="text" name="chapter-seo" value="" placeholder="<?php esc_attr_e( 'Optional', MUUPRO_TEXTDOMAIN ); ?>">
				</div>
			</div>
			<?php }?>
			
			<?php if(isset($atts['warning']) && $atts['warning']) {?>
			<!-- Chapter Warning Text -->
			<div class="form-group row title-field">
				<label class="col-md-3">
					<?php esc_html_e('Chapter Warning Text', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<input class="form-control" type="text" name="chapter-warning-text" value="" placeholder="<?php esc_attr_e( 'Optional', MUUPRO_TEXTDOMAIN ); ?>">
				</div>
			</div>
			<?php }?>
			
			<?php do_action('muupro_upload_chapter_fields');?>

			<div class="form-group row">
				<label class="col-md-3">
					<?php esc_html_e('Chapter Images', MUUPRO_TEXTDOMAIN); ?>
				</label>
				<div class="col-md-9">
					<ul id="chapter-image-files" class="row">
						<li class="col-md-4">
							<div id="add-chapter-image-file" class="chapter-item">
								<div class="adding">
									<i class="fas fa-plus-square"></i>
									<input type="file" name="chapter-images[]" accept=".jpeg,.jpg,.png,.bmp,.webp,.gif" multiple="multiple">
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
			
		</div>
		
		<div class="form-group row">
			<div class="col-md-3"></div>
			<div class="col-md-9 submit-row">
				<button type="submit"><i class="icon ion-md-cloud-upload"></i> <?php esc_html_e( 'Upload', MUUPRO_TEXTDOMAIN ); ?></button>
			</div>
		</div>
	</form>
</div>