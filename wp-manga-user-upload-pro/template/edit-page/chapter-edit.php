<div class="modal fade loading" id="chapter-edit-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="background:#FFF">
        <div class="modal-content">
            <div class="modal-body">
				<h3><?php esc_html_e('Edit Chapter Info', MUUPRO_TEXTDOMAIN);?></h3>
				<input type="hidden" name="madara-chapter-id"/>
				<input type="hidden" name="madara-chapter-manga"/>
				<input type="hidden" name="madara-chapter-nonce"/>
				<div class="form-group row">
					<label for="madara-chapter-title" class="col-md-3">
						<?php esc_html_e('Title', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="madara-chapter-title" required="required">
					</div>
				</div>
				
				<div class="form-group row">
					<label for="madara-chapter-index" class="col-md-3">
						<?php esc_html_e('Index', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="madara-chapter-index">
					</div>
				</div>
				
				<div class="form-group row">
					<label for="madara-chapter-extendname" class="col-md-3">
						<?php esc_html_e('Other Name', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<input class="form-control" type="text" name="madara-chapter-extendname">
					</div>
				</div>
				
				<div class="form-group row">
					<label for="madara-chapter-seo" class="col-md-3">
						<?php esc_html_e('SEO Description', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<textarea class="form-control" type="text" name="madara-chapter-seo"></textarea>
					</div>
				</div>
				
				<div class="form-group row">
					<label for="madara-chapter-warning" class="col-md-3">
						<?php esc_html_e('Warning Text', MUUPRO_TEXTDOMAIN); ?>
					</label>
					<div class="col-md-9">
						<textarea class="form-control" type="text" name="madara-chapter-warning"></textarea>
					</div>
				</div>
				
				<?php do_action('muupro_edit_chapter_modal_fields');?>
				
				<div class="form-group row madara-user-submit">
					<label for="name-input-submit" class="col-md-3"></label>
					<div class="col-md-9">
						<input type="submit" class="form-control button button-primary button-large" id="muupro-edit-chapter-submit" value="<?php  esc_html_e('Save', MUUPRO_TEXTDOMAIN); ?>" name="muupro-edit-chapter-submit">
					</div>
				</div>
				<div class="loading">
					<i class="fas fa-spinner fa-spin"></i>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade loading" id="chapter-content-edit-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="background:#FFF">
        <div class="modal-content">
            <div class="modal-body">
				<h3 class="chapter-title"></h3>
				<input type="hidden" name="madara-chapter-id"/>
				<input type="hidden" name="madara-chapter-manga"/>
				<input type="hidden" name="madara-chapter-nonce"/>
				<div class="form-group row">
					<div class="col-12">
						<?php wp_editor('', 'manga-chapter-content', array('teeny' => true, 'media_buttons' => false, 'textarea_rows' => 40, 'quicktags' => false, 'editor_height' => 300));?>
					</div>
				</div>
				<div class="form-group row madara-user-submit">
					<div class="col-12">
						<input type="submit" class="form-control button button-primary button-large" id="muupro-edit-content-submit" value="<?php  esc_html_e('Save', MUUPRO_TEXTDOMAIN); ?>" name="muupro-edit-content-submit">
					</div>
				</div>
				<div class="loading">
					<i class="fas fa-spinner fa-spin"></i>
				</div>
			</div>
		</div>
	</div>
</div>