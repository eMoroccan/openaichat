<?php

    if( !muupro_is_manga_edit_page() ){
        exit;
    }

    get_header();

    global $post;
?>

    <div class="c-page-content style-2 madara-user-edit-page">
        <div class="content-area">
            <div class="container">

                <div class="row">

                    <div class="col-md-12 col-sm-12">

						<?php get_template_part( 'html/main-bodytop' ); ?>

                        <div class="main-col-inner">
                            <h2 class="item-title text-center">
                                <a href="<?php echo esc_url( get_the_permalink() ) ?>" title="<?php esc_html_e('Back To Manga Page', MUUPRO_TEXTDOMAIN); ?>">
                                    <i class="fas fa-arrow-left"></i>
                                </a>

                                <?php esc_html_e('Edit Manga', MUUPRO_TEXTDOMAIN); ?> :
                                <a href="<?php echo esc_url( get_the_permalink() ) ?>">
                                    <?php echo wp_kses_post( $post->post_title ); ?>
                                </a>
                            </h2>
                            <div class="settings-page">
                                <div class="tabs-content-wrap">
                                    <div class="tab-group-item">
                                        <div class="tab-item">
                                            <div class="madara-user-upload-form">
                                                <?php if( !empty( $message ) ){ ?>
                                                    <div class="alert alert-info">
                                                        <?php echo wp_kses_post( $message ); ?>
                                                    </div>
                                                <?php } ?>
                                                <form id="madara-user-edit-form" method="post" enctype="multipart/form-data"
                                                action="<?php echo esc_url( get_the_permalink() ) ?>">
                                                    <input type="hidden" name="post-id" value="<?php echo esc_attr( get_the_ID() ); ?>">
                                                    <?php wp_nonce_field( 'madara-user-edit-manga', '_wpnonce', false ); ?>

                                                    <div class="tab-group-item">
                                                        <?php
                                                            muupro_template( 'tab-items/manga', 'info' );
                                                            muupro_template( 'tab-items/manga', 'extra-info' );
                                                        ?>
                                                        <div class="chapter-listing">
                                                            <?php muupro_template( 'edit-page/chapter', 'listing' ); ?>
                                                        </div>
                                                        <div class="form-group row madara-user-submit">
                                                            <label for="name-input-submit" class="col-md-3"></label>
                                                            <div class="col-md-9">
                                                                <input type="submit" class="form-control button button-primary button-large" id="madara-upload-submit" value="<?php  esc_html_e('Save', MUUPRO_TEXTDOMAIN); ?>" name="muupro-edit-page-submit">
                                                                <div class="loading-section">
                                                                    <div class="loader-inner line-spin-fade-loader">
                                                                        <div></div>
                                                                        <div></div>
                                                                        <div></div>
                                                                        <div></div>
                                                                        <div></div>
                                                                        <div></div>
                                                                        <div></div>
                                                                        <div></div>
                                                                    </div>
                                                                    <span><?php esc_html_e('Uploading... This progress might take a few minutes', MUUPRO_TEXTDOMAIN); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-success" style="display:none;">
                                                        <div class="message-content">
                                                        </div>
                                                    </div>
                                                </form>
                                                <?php muupro_template( 'edit-page/user', 'upload-modal' ); ?>
												
												<?php muupro_template('edit-page/chapter-edit');?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

						<?php get_template_part( 'html/main-bodybottom' ); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
get_footer();
