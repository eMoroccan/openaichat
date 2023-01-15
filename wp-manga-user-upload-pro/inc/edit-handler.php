<?php

    /**
    * Handle all actions in User Edit Page Manga
    *
    */

    if( !class_exists('WP_MANGA') ){
        return;
    }

    require_once('upload-handler.php');
    require_once('upload-preview.php');

    class MUUPRO_EditHandler extends MUUPRO_UploadHandler {

        static public function save_edit_manga(){

            if( ! isset( $_POST['muupro-edit-page-submit'] ) ){
                return;
            }

            if( !wp_verify_nonce( $_POST['_wpnonce'], 'madara-user-edit-manga') ){
                return;
            }

            $post_id = get_the_ID();

            self::update_manga_post( $post_id );
            self::update_chapters( $post_id );
            self::update_volumes( $post_id );
			
			do_action('muupro_save_manga_info', $post_id);
        }

        static function update_manga_post( $post_id ){

            //update post basic information
            wp_update_post( array(
                'ID'           => $post_id,
                'post_title'   => isset( $_POST['madara-manga-title'] ) ? $_POST['madara-manga-title'] : '',
                'post_content' => isset( $_POST['madara-manga-description'] ) ? $_POST['madara-manga-description'] : '',
            ) );

            self::update_meta_data( $post_id );
            self::update_term_data( $post_id );

        }

        static function update_meta_data( $post_id ){
            //update meta data
            $meta_data = array(
                '_wp_manga_alternative' => isset( $_POST['madara-manga-alternative-name'] ) ? esc_html($_POST['madara-manga-alternative-name']) : '',
                '_wp_manga_status'      => isset( $_POST['madara-manga-status'] ) ? esc_html($_POST['madara-manga-status']) : '',
				'manga_meta_title' => isset( $_POST['madara-seo-meta'] ) ? esc_html($_POST['madara-seo-meta']) : '',
				'manga_meta_desc' => isset( $_POST['madara-seo-desc'] ) ? esc_html($_POST['madara-seo-desc']) : '',
				'_wp_manga_type' => isset( $_POST['madara-manga-type'] ) ? esc_html($_POST['madara-manga-type']) : '',
				'manga_adult_content' => isset( $_POST['madara-manga-adult'] ) ? array(esc_html($_POST['madara-manga-adult'])) : ''
            );

            if( !empty( $_FILES['madara-featured-image'] ) ){
                if( file_exists( $_FILES['madara-featured-image']['tmp_name'] ) ){
                    $thumb_id = self::upload_attachment( 'madara-featured-image', $post_id );

                    if( $thumb_id ){
                        $meta_data['_thumbnail_id'] = $thumb_id;
                    }
                }
            }
			
			if( !empty( $_FILES['madara-horizontal-thumb'] ) ){
                if( file_exists( $_FILES['madara-horizontal-thumb']['tmp_name'] ) ){
                    $thumb_id = self::upload_attachment( 'madara-horizontal-thumb', $post_id );

                    if( $thumb_id ){
                        $meta_data['manga_banner'] = wp_get_attachment_url($thumb_id);
                    }
                }
            }
			
			if(isset($_POST['madara-manga-badge'])){
				// validate
				$badge_choices = function_exists('madara_get_badge_choices') ? madara_get_badge_choices() : array(esc_html__( 'Hot', MUUPRO_TEXTDOMAIN ), esc_html__( 'New', MUUPRO_TEXTDOMAIN ));
				
				if(in_array($_POST['madara-manga-badge'], array_map('strtolower',  $badge_choices))){
					$meta_data['manga_title_badges'] = $_POST['madara-manga-badge'];
				}
			}

            foreach( $meta_data as $key => $value ){
                update_post_meta( $post_id, $key, $value );
            }

        }

        static function update_term_data( $post_id ){

            $manga_terms = array(
                'wp-manga-release' => isset( $_POST['madara-manga-release-year'] ) ? $_POST['madara-manga-release-year'] : null,
                'wp-manga-author'  => isset( $_POST['madara-manga-authors'] ) ? $_POST['madara-manga-authors'] : null,
                'wp-manga-artist'  => isset( $_POST['madara-manga-artists'] ) ? $_POST['madara-manga-artists'] : null,
                'wp-manga-genre'   => isset( $_POST['madara-manga-genres'] ) ? implode( ',', $_POST['madara-manga-genres'] ) : '',
                'wp-manga-tag'     => isset( $_POST['madara-manga-tags'] ) ? $_POST['madara-manga-tags'] : null,
            );

            foreach( $manga_terms as $tax => $term ){

                if( empty( $term ) ){
                    continue;
                }

                $resp = MUUPRO_UploadHandler::add_manga_terms( $post_id, $term, $tax );
            }
        }

        static function upload_handler(){

            if( empty( $_POST['postID'] ) ){
                wp_send_json_error( array(
                    'message'    => esc_html_e('Missing Post ID', MUUPRO_TEXTDOMAIN)
                ) );
            }else{
                $post_id = $_POST['postID'];
            }

            if( empty( $_FILES ) ){
                wp_send_json_error( array(
                    'message' => esc_html_e('Missing upload file', MUUPRO_TEXTDOMAIN)
                ) );
            }else{
                $manga_file = $_FILES['file'];
            }

            $chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );

            $is_valid = MUUPRO_ZIP_PREVIEW::is_zip_valid( $manga_file['tmp_name'], $chapter_type );

            if( !$is_valid['is_valid'] ) {
				wp_send_json_error( $is_valid['message'] );
			}
			
			if($is_valid['data']['zip_type'] == 'single_chapter' ){
                wp_send_json_error( esc_html__('Please check .zip file structure. It must be multi-chapters .zip file', MUUPRO_TEXTDOMAIN) );
            }

            if( $chapter_type == 'manga' ){
                $options = muupro_get_user_upload_settings();
                $storage = isset( $options['default_storage'] ) ? $options['default_storage'] : 'local';

                $resp = self::upload_manga_file_type( $post_id, $manga_file, $storage );
            }else{
                $resp = self::upload_content_file_type( $post_id, $manga_file );
            }

            if( $resp ){

                if( class_exists('WP_MANGA_FUNCTIONS') ){
                    global $wp_manga_functions;
                    $wp_manga_functions->update_latest_meta( $post_id );
                }

                wp_send_json_success();
            }

            wp_send_json_error();

        }

        static function update_chapters( $post_id ){

            global $wp_manga_storage, $wp_manga_chapter;

            $chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );

            //Update chapter names
            if( !empty( $_POST['update_chapters'] ) ){
                $chapters = explode( ',', $_POST['update_chapters'] );
                $update_chapters = array();

                foreach( $chapters as $chapter_id ){

                    if( empty( $chapter_id ) ){
                        continue;
                    }

                    if( isset( $_POST["chapter-{$chapter_id}"] ) ){
                        $update_chapters[$chapter_id] = $_POST["chapter-{$chapter_id}"];
                        self::update_chapter_name( $post_id, $chapter_id, $_POST["chapter-{$chapter_id}"] );
                    }
                }

                if( $chapter_type == 'manga' ){
                    self::update_manga_chapters_name( $post_id, $update_chapters );
                }

            }

            //Update chapter names
            if( !empty( $_POST['delete_chapters'] ) ){
                $delete_chapters = explode( ',', $_POST['delete_chapters'] );

                foreach( $delete_chapters as $chapter_id ){

                    if( empty( $chapter_id ) ){
                        continue;
                    }

                    $wp_manga_storage->delete_chapter( $post_id, $chapter_id );
                }
            }
        }
		
		/**
		 * Ajax get chapter info
		 **/
		static function get_chapter_info(){
			$chapter_id = isset($_GET['chapter']) ? intval($_GET['chapter']) : 0;
			$post_id = isset($_GET['manga']) ? intval($_GET['manga']) : 0;
			if($chapter_id && $post_id){
				global $wp_manga_chapter;
				
				$chapter = $wp_manga_chapter->get_chapter_by_id( $post_id, $chapter_id );
				if($chapter){
					$data = apply_filters('muupro_get_chapter_info', array(
								'id' => $chapter_id,
								'manga' => $post_id,
								'nonce' => wp_create_nonce('muupro_edit_chapter'),
								'title' => $chapter['chapter_name'],
								'index' => $chapter['chapter_index'],
								'extendname' => $chapter['chapter_name_extend'],
								'seo' => $chapter['chapter_seo'],
								'warning' => $chapter['chapter_warning']
							), $chapter);
					wp_send_json_success( $data );
				}
			}
			
			wp_send_json_error( esc_html__('Invalid Data', MUUPRO_TEXTDOMAIN));
		}
		
		/**
		 * Ajax get chapter info
		 **/
		static function get_chapter_content(){
			$chapter_id = isset($_GET['chapter']) ? intval($_GET['chapter']) : 0;
			$post_id = isset($_GET['manga']) ? intval($_GET['manga']) : 0;
			if($chapter_id && $post_id){
				global $wp_manga_chapter;
				
				$chapter_content = new WP_Query( array(
					'post_parent' => $chapter_id,
					'post_type'   => 'chapter_text_content'
				) );
				
				$chapter = $wp_manga_chapter->get_chapter_by_id( $post_id, $chapter_id );
				if($chapter && $chapter_content->have_posts()){
					$content = $chapter_content->posts;
					$content = $content[0]->post_content;
					$data = apply_filters('muupro_get_chapter_content', array(
								'id' => $chapter_id,
								'manga' => $post_id,
								'nonce' => wp_create_nonce('muupro_edit_chapter_content'),
								'title' => $chapter['chapter_name'],
								'content' => $content
							), $chapter, $chapter_content);
					wp_send_json_success( $data );
				}
			}
			
			wp_send_json_error( esc_html__('Invalid Data', MUUPRO_TEXTDOMAIN));
		}
		
		/**
		 * Ajax save chapter info
		 **/
		static function save_chapter_info(){
			$chapter_id = isset($_POST['chapter']) ? intval($_POST['chapter']) : 0;
			$manga_id = isset($_POST['manga']) ? intval($_POST['manga']) : 0;
			$index = isset($_POST['index']) ? intval($_POST['index']) : 0;
			$nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
			$title = isset($_POST['title']) ? $_POST['title'] : '';
			$extendname = isset($_POST['extendname']) ? $_POST['extendname'] : '';
			$seo = isset($_POST['seo']) ? $_POST['seo'] : '';
			$warning = isset($_POST['warning']) ? $_POST['warning'] : '';
			
			if($title && $nonce && $chapter_id && $manga_id){
				if(wp_verify_nonce($nonce, 'muupro_edit_chapter')){
					global $wp_manga_chapter;
				
					$chapter = $wp_manga_chapter->get_chapter_by_id( $manga_id, $chapter_id );
					if($chapter && muupro_current_user_can_upload()){
						// only owner and 'administrator', 'editor' can edit
						if(muupro_get_chapter_author($manga_id, $chapter_id) == get_current_user_id() ||  current_user_can('administrator') || current_user_can('editor')) {
							
							$data = array(
											'chapter_name' => $title,
											'chapter_index' => $index,
											'chapter_name_extend' => $extendname,
											'chapter_seo' => $seo,
											'chapter_warning' => $warning
										);
							$where = array(
											'post_id' => $manga_id,
											'chapter_id' => $chapter_id
										);
							$wp_manga_chapter->update_chapter(
												apply_filters('muupro_edit_chapter_data', $data), $where
							);
							
							do_action('muupro_after_edit_chapter', $data, $where);
							wp_send_json_success( );
						}
					}
				}
			}
			
			wp_send_json_error( esc_html__('Invalid Data', MUUPRO_TEXTDOMAIN));
		}
		
		/**
		 * Ajax save chapter info
		 **/
		static function save_chapter_content(){
			$chapter_id = isset($_POST['chapter']) ? intval($_POST['chapter']) : 0;
			$manga_id = isset($_POST['manga']) ? intval($_POST['manga']) : 0;
			$content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
			$nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
			
			if($content && $nonce && $chapter_id && $manga_id){
				if(wp_verify_nonce($nonce, 'muupro_edit_chapter_content')){
					global $wp_manga_chapter;
				
					$chapter = $wp_manga_chapter->get_chapter_by_id( $manga_id, $chapter_id );
					if($chapter && muupro_current_user_can_upload()){
						// only owner and 'administrator', 'editor' can edit
						if(muupro_get_chapter_author($manga_id, $chapter_id) == get_current_user_id() ||  current_user_can('administrator') || current_user_can('editor')) {
							
							$chapter_content = new WP_Query( array(
								'post_parent' => $chapter_id,
								'post_type'   => 'chapter_text_content'
							) );
							
							if($chapter_content->have_posts()){
								$data = $chapter_content->posts;
								$data = $data[0];
								$data->post_content = $content;
								
								wp_update_post($data);
								
								do_action('muupro_after_edit_chapter_content', $data);
								
								wp_send_json_success(array('message' => esc_html__('Saved successfully', MUUPRO_TEXTDOMAIN), 'manga_id' => $manga_id, 'chapter_id' => $chapter_id));
							}
						}
					}
				}
			}
			
			wp_send_json_error( esc_html__('Invalid Data', MUUPRO_TEXTDOMAIN));
		}

        static function update_volumes( $post_id ){

            global $wp_manga_volume, $wp_manga_storage;

            //Update volumes names
            if( !empty( $_POST['update_volumes'] ) ){
                $volumes = explode( ',', $_POST['update_volumes'] );

                foreach( $volumes as $volume_id ){

                    if( empty( $volume_id ) ){
                        continue;
                    }

                    if( isset( $_POST["volume-{$volume_id}"] ) ){
                        $wp_manga_volume->update_volume(
                            array(
                                'volume_name' => $_POST["volume-{$volume_id}"]
                            ),
                            array(
                    			'volume_id' => $volume_id,
                    		)
                        );
                    }
                }
            }

            if( !empty( $_POST['delete_volumes'] ) ){
                $volumes = explode( ',', $_POST['delete_volumes'] );

                foreach( $volumes as $volume_id ){
                    if( empty( $volume_id ) ){
                        continue;
                    }

                    $wp_manga_storage->delete_volume( $post_id, $volume_id );
                }
            }
        }

        static function update_manga_chapters_name( $post_id, $update_chapters ){

            global $wp_manga, $wp_manga_chapter, $wp_manga_storage;
            $uniqid = $wp_manga->get_uniqid( $post_id );

    		$json_storage = WP_MANGA_JSON_DIR . $uniqid . '/manga.json';

            if ( !file_exists( $json_storage ) ) {
                return;
            }

            $data = json_decode( file_get_contents( $json_storage ), true );

            foreach( $update_chapters as $chapter_id => $name ){

                if( isset( $data['chapters'][ $chapter_id ]['storage']['local'] ) ){
                    $chapter = $wp_manga_chapter->get_chapter_by_id( $post_id, $chapter_id );
                    $new_slug = $wp_manga_storage->slugify( $name );

                    foreach( $data['chapters'][ $chapter_id ]['storage']['local']['page'] as $index => $page ){

                        $data['chapters'][ $chapter_id ]['storage']['local']['page'][ $index ]['src'] = str_replace( $chapter['chapter_slug'], $new_slug, $data['chapters'][ $chapter_id ]['storage']['local']['page'][ $index ]['src'] );

                        $chapter_dir = WP_MANGA_DATA_DIR . $uniqid . '/' . $chapter['chapter_slug'];
        				$new_chapter_dir = WP_MANGA_DATA_DIR . $uniqid . '/' . $new_slug;

        				if( file_exists( $chapter_dir ) ) {
        					$rename = rename( $chapter_dir, $new_chapter_dir );
        				}
                    }
                }
            }

            return file_put_contents( $json_storage, json_encode( $data ) );

        }

        static function update_chapter_name( $post_id, $chapter_id, $new_name ){
            global $wp_manga_chapter, $wp_manga_storage;

    		return $wp_manga_chapter->update_chapter(
                array(
                    'chapter_name' => $new_name,
                    'chapter_slug' => $wp_manga_storage->slugify( $new_name )
                ),
                array(
                    'post_id' => $post_id,
                    'chapter_id' => $chapter_id
                )
            );
        }

        static function refresh_chapter_list(){

            if( empty( $_GET['postID'] ) ){
                wp_send_json_error();
            }

            $post_id = $_GET['postID'] ;

            global $post;
			$post = get_post($post_id);

            $output = '';
			if($post){
				ob_start();

				muupro_template( 'edit-page/chapter', 'listing' );

				$output = ob_get_contents();
				ob_end_clean();
			}

            wp_send_json_success( $output );
        }
    }
