<div class="modal fade" id="user-upload-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="background:#FFF">
        <div class="modal-content">
            <div class="modal-body">
                <div class="user-upload-screen">
                    <div class="modal-title text-center">
                        <h3><?php esc_html_e('Upload Chapters', MUUPRO_TEXTDOMAIN); ?></h3>
                    </div>
                    <div class="description">
                        <p>
                            <?php echo wp_kses_post( __('Please choose .zip file to upload that contains <strong>Manga Chapters in Volumes folder</strong> or <strong>Manga Chapters folders only</strong>. <strong>Single Chapter zip file</strong> that contains images only <strong>won\'t be accepted</strong>.', MUUPRO_TEXTDOMAIN) ); ?>
                        </p>
                        <p>
                            <?php echo wp_kses_post( __('In case you want to <strong>upload Chapters in existed Volume</strong>, please place the Chapters folder in a Volume folder has the same name with the existed Volume that you want to upload.', MUUPRO_TEXTDOMAIN) ); ?>
                        </p>
                        <a href="#sample-zip-file"><strong><?php esc_html_e('Show Sample Zip file.', MUUPRO_TEXTDOMAIN);?></strong></a>
                        <div id="sample-zip-file">
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="<?php echo esc_url( MDR_USER_UPLOAD_PRO_URL . 'assets/img/multi-chapters-volumes.png' ); ?>" alt="<?php esc_html_e('Multi Chapters with Volumes', MUUPRO_TEXTDOMAIN); ?>" title="<?php esc_html_e('Multi Chapters with Volumes', MUUPRO_TEXTDOMAIN); ?>">
                                </div>
                                <div class="col-md-4">
                                    <img src="<?php echo esc_url( MDR_USER_UPLOAD_PRO_URL . 'assets/img/multi-chapters-no-volumes.png' ); ?>" alt="<?php esc_html_e('Multi Chapters with No Volumes', MUUPRO_TEXTDOMAIN ); ?>" title="<?php esc_html_e('Multi Chapters with No Volumes', MUUPRO_TEXTDOMAIN ); ?>">
                                </div>
                                <div class="col-md-4">
                                    <img src="<?php echo esc_url( MDR_USER_UPLOAD_PRO_URL . 'assets/img/chapter-text.png' ) ?>" alt="<?php esc_html_e('Chapter Text/Video'); ?>" title="<?php esc_html_e('Chapter Text/Video'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="user-upload-area">
                        <div class="user-upload-area-content text-center">
                            <div class="upload-section">
                                <h4><?php esc_html_e('Drop files here to upload', MUUPRO_TEXTDOMAIN); ?></h4>
                                <button class="btn btn-default"><?php esc_html_e('Select file to upload', MUUPRO_TEXTDOMAIN); ?></button>
                                <div class="upload-message">
                                    <span class="upload-warning"><?php esc_html_e('Please select file to upload', MUUPRO_TEXTDOMAIN); ?></span>
                                    <span class="upload-success"><?php esc_html_e('Chapters uploaded successfully!') ?></span>
                                </div>
                                <input type="file" name="manga_upload" id="manga_upload_input" accept=".zip">
                            </div>
                            <div class="preview-section">
                                <div class="file-preview">
                                    <i class="far fa-file-archive"></i>
                                    <span class="upload-file-name"></span>
                                </div>
                                <div class="remove-file">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="user-upload-submit">
                        <input type="submit" id="manga-upload-submit" value="<?php esc_html_e('Upload', MUUPRO_TEXTDOMAIN); ?>">
                    </div>
                </div>
                <div class="loading-screen">
                    <div class="loading-icon"><i class="fas fa-spinner fa-spin"></i></div>
                    <div class="loading-text">
                        <p><?php esc_html_e('Please stay tuned and do not exit this page till the upload progress is completed', MUUPRO_TEXTDOMAIN); ?></p>
                        <p><?php esc_html_e('Upload progress might take serveral minutes...', MUUPRO_TEXTDOMAIN); ?></p>
                    </div>
                </div>
                <div class="success-screen">
                    <i class="far fa-check-circle"></i>
                    <p><?php esc_html_e('Upload Successfully!', MUUPRO_TEXTDOMAIN); ?></p>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
