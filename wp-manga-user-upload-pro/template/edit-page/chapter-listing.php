<?php

    /**
 	 * Chapter listing in Edit Page 	 
	 *
	 */

    if( ! ( class_exists( 'App\Madara' ) && class_exists( 'WP_MANGA') ) ){
        return;
    }

    use App\Madara;

    global $wp_manga_functions;

    $chapters_order = Madara::getOption( 'manga_chapters_order', 'desc' );
	$chapter_type = get_post_meta( get_the_ID(), '_wp_manga_chapter_type', true );
?>

<div class="tab-item">

    <div class="form-group row madara-user-submit">
        <label for="name-input-submit" class="col-md-3"><?php esc_html_e('Manga Chapters', MUUPRO_TEXTDOMAIN); ?></label>
        <div class="col-md-9">
            <div class="page-content-listing single-page">
                <div class="listing-chapters_wrap">
                    <?php $manga = $wp_manga_functions->get_all_chapters( get_the_ID() ); ?>
                    <?php if ( $manga ) : ?>

                        <ul class="main version-chap">
                            <?php
                                $single   = isset( $manga['0']['chapters'] ) ? $manga['0']['chapters'] : null;
                                if ( $single ) : ?><?php foreach ( $single as $chapter ) :
                                    $style = $wp_manga_functions->get_reading_style();
                                    $link = $wp_manga_functions->build_chapter_url( get_the_ID(), $chapter['chapter_slug'], $style );
                                    ?>
                                    <li class="wp-manga-chapter">
                                        <div class="muu-edit-page-edit-input">
                                            <input type="text" class="chapter-name-input" name="chapter-<?php echo esc_attr( $chapter['chapter_id'] ) ?>" value="<?php echo esc_attr( $chapter['chapter_name'] ) ?>" data-chapter-id="<?php echo esc_attr( $chapter['chapter_id'] ) ?>">
                                            <i class="far fa-save"></i>
                                        </div>
                                        <a href="<?php echo esc_url( $link ); ?>">
                                            <span><?php echo isset( $chapter['chapter_name'] ) ? wp_kses_post( $chapter['chapter_name'] . $wp_manga_functions->filter_extend_name( $chapter['chapter_name_extend'] ) ) : ''; ?></span>
                                            <span class="muu-edit-actions-btn">
                                                <i class="fas fa-pencil-alt muu-edit-name-btn" data-id="<?php echo esc_attr( $chapter['chapter_id'] ) ?>"></i>
												<?php
												if($chapter_type == 'text'){?>
												<i class="fas fa-edit muu-edit-content-btn" data-id="<?php echo esc_attr( $chapter['chapter_id'] ) ?>" title="<?php esc_html_e('Edit Content',MUUPRO_TEXTDOMAIN);?>"></i>
												<?php }?>
                                                <i class="fas fa-trash-alt muu-delete-btn"></i>
                                            </span>
                                        </a>
                                        <span class="chapter-release-date"><i><?php echo isset( $chapter['date'] ) ? $wp_manga_functions->get_time_diff( $chapter['date'] ) : ''; ?></i></span>
                                    </li>
                                <?php endforeach; ?><?php unset( $manga['0'] ); endif;
                            ?>

                            <?php

                                if ( ! empty( $manga ) ) {

                                    if ( $chapters_order == 'desc' ) {
                                        $manga = array_reverse( $manga );
                                    }

                                    foreach ( $manga as $vol_id => $vol ) {

                                        $chapters = isset( $vol['chapters'] ) ? $vol['chapters'] : null;

                                        $chapters_parent_class = $chapters ? 'parent has-child' : 'parent no-child';
                                        $chapters_child_class  = $chapters ? 'has-child' : 'no-child';
                                        $first_volume_class    = isset( $first_volume ) ? '' : ' active';
                                        ?>

                                        <li class="<?php echo esc_attr( $chapters_parent_class . ' ' . $first_volume_class ); ?>">

                                            <div class="muu-edit-page-edit-input">
                                                <input type="text" class="volume-name-input" name="volume-<?php echo esc_attr( $vol_id ) ?>" value="<?php echo esc_attr( $vol['volume_name'] ) ?>" data-volume-id="<?php echo esc_attr( $vol_id ) ?>">
                                                <i class="far fa-save"></i>
                                            </div>

                                            <?php echo isset( $vol['volume_name'] ) ? '<a href="javascript:void(0)" class="' . $chapters_child_class . '"><span>' . $vol['volume_name'] . '</span><span class="muu-edit-actions-btn"><i class="fas fa-pencil-alt muu-edit-name-btn"></i><i class="fas fa-trash-alt muu-delete-btn"></i></span></a>' : ''; ?>


                                            <?php

                                                if ( $chapters ) { ?>
                                                    <ul class="sub-chap list-chap" <?php echo isset( $first_volume ) ? '' : ' style="display: block;"'; ?> >

                                                        <?php if ( $chapters_order == 'desc' ) {
                                                            $chapters = array_reverse( $chapters );
                                                        } ?>

                                                        <?php foreach ( $chapters as $chapter ) {
                                                            $style = $wp_manga_functions->get_reading_style();

                                                            $link          = $wp_manga_functions->build_chapter_url( get_the_ID(), $chapter['chapter_slug'], $style );
                                                            $c_extend_name = madara_get_global_wp_manga_functions()->filter_extend_name( $chapter['chapter_name_extend'] );
                                                            ?>
                                                            <li class="wp-manga-chapter">
                                                                <div class="muu-edit-page-edit-input">
                                                                    <input type="text" class="chapter-name-input" name="chapter-<?php echo esc_attr( $chapter['chapter_id'] ) ?>" value="<?php echo esc_attr( $chapter['chapter_name'] ) ?>" data-chapter-id="<?php echo esc_attr( $chapter['chapter_id'] ) ?>">
                                                                    <i class="far fa-save"></i>
                                                                </div>

                                                                <a href="<?php echo esc_url( $link ); ?>" target="_blank">
                                                                    <span><?php echo wp_kses_post( $chapter['chapter_name'] . $c_extend_name ) ?></span>
                                                                    <span class="muu-edit-actions-btn">
                                                                        <i class="fas fa-pencil-alt muu-edit-name-btn" data-id="<?php echo esc_attr( $chapter['chapter_id'] ) ?>"></i>
																		<?php
																		if($chapter_type == 'text'){?>
																		<i class="fas fa-edit muu-edit-content-btn" data-id="<?php echo esc_attr( $chapter['chapter_id'] ) ?>" title="<?php esc_html_e('Edit Content',MUUPRO_TEXTDOMAIN);?>"></i>
																		<?php }?>
                                                                        <i class="fas fa-trash-alt muu-delete-btn"></i>
                                                                    </span>
                                                                </a>
                                                                <span class="chapter-release-date">
                                                                    <i>
                                                                        <?php
                                                                            echo wp_kses_post( $wp_manga_functions->get_time_diff( $chapter['date'] ) );
                                                                        ?>
                                                                    </i>
                                                                </span>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                <?php } else { ?>
                                                    <span class="no-chapter"><?php echo esc_html__( 'There is no chapters', MUUPRO_TEXTDOMAIN ); ?></span>
                                                <?php } ?>
                                        </li>
                                        <?php $first_volume = false; ?>

                                    <?php } //endforeach; ?>

                                <?php } //endif-empty( $volume);
                            ?>
                        </ul>

                    <?php endif; ?>
                    <input type="hidden" name="update_chapters" value="">
                    <input type="hidden" name="update_volumes" value="">
                    <input type="hidden" name="delete_chapters" value="">
                    <input type="hidden" name="delete_volumes" value="">

                    <div class="upload-modal-btn madara-user-upload btn btn-default btn-active-modal" data-toggle="modal" data-target="#user-upload-modal">
                        <i class="fas fa-plus-square"></i> <?php esc_html_e('Add Chapters', MUUPRO_TEXTDOMAIN); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
