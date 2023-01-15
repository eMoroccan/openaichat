<div class="tab-item">
    <div class="settings-title">
        <h3>
            <?php esc_html_e('Manga File', MUUPRO_TEXTDOMAIN); ?>
        </h3>
    </div>

    <?php
        global $options;
        
        $chapter_type_permission = isset( $options['chapter_type_permission'] ) ? $options['chapter_type_permission'] : 1;
        $default_chapter_type = isset( $options['default_chapter_type'] ) ? $options['default_chapter_type'] : 'manga';
    ?>

    <?php if( $chapter_type_permission ){ ?>
        <div class="form-group row">
            <label for="name-input" class="col-md-3">
                <?php esc_html_e('Manga Chapter Type', MUUPRO_TEXTDOMAIN); ?>
            </label>
            <div class="col-md-9">
                <select name="madara-manga-chapter-type">
                    <option value="manga" <?php selected( $default_chapter_type, 'manga'); ?>>
                        <?php esc_html_e("Images", MUUPRO_TEXTDOMAIN); ?>
                    </option>
                    <option value="text" <?php selected( $default_chapter_type, 'text'); ?>>
                        <?php esc_html_e("Text", MUUPRO_TEXTDOMAIN); ?>
                    </option>
                    <option value="video" <?php selected( $default_chapter_type, 'video'); ?>>
                        <?php esc_html_e("Video", MUUPRO_TEXTDOMAIN); ?>
                    </option>
                </select>
            </div>
        </div>
    <?php } ?>
    <div class="form-group row">
        <label for="name-input" class="col-md-3">
            <?php esc_html_e('Upload Manga', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <label class="select-avata"><input type="file" accept=".zip" name="madara-file" required="required"></label>
            <?php
                if( class_exists('WP_MANGA') ){ ?>
                    <?php
                        global $wp_manga_functions;
                        $max_upload_file_size = $wp_manga_functions->max_upload_file_size();
                    ?>
                    <br>
                    <span><?php echo esc_html( sprintf( __( 'Maximum upload file size: %dMB.', MUUPRO_TEXTDOMAIN ), $max_upload_file_size['actual_max_filesize_mb'] ) ); ?></span>
                <?php }
            ?>
            <div class="description">
                <?php
                    echo wp_kses_post( __( '<strong>Please note :</strong> Only .zip file allowed. Zip file should contain many folders as each Chapter of Manga. Each Chapter folder should contain content file. <a href="#sample-zip-file"><strong>Show Sample Zip file.</strong></a>', MUUPRO_TEXTDOMAIN ) );
                ?>
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
        </div>
    </div>
    <div class="form-group row manga-preview" style="display:none;">
        <label class="col-md-3">
            <?php esc_html_e('Manga Preview', MUUPRO_TEXTDOMAIN); ?>
        </label>
        <div class="col-md-9">
            <div class="manga-file-preview">
            </div>
        </div>
    </div>
</div>
