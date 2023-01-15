<?php

	namespace MadaraUserUploadPro;

    class UserAddManga extends AjaxHandler{

		protected static $action = 'madara_user_create_manga_frontend';

        static function handler(){

            if( ! class_exists('WP_MANGA') ){
                return;
            }

            if( ! is_user_logged_in() || ! muupro_current_user_can_upload( get_current_user_id() ) ){
                return;
			}
			
			if( empty( $_POST['nonce'] ) ){
				self::send_error();
			}elseif( ! self::verify_nonce( $_POST['nonce'] ) ){
				self::send_error( __( 'Session expired, please refresh page and try again', MUUPRO_TEXTDOMAIN ) );
			}

			// validate featured image
            if( empty( $_FILES['featuredImage'] ) ){
                self::send_error( esc_html__( 'Please attach feature image.', MUUPRO_TEXTDOMAIN ), true );
			} else {

				$image_src = $_FILES['featuredImage']['tmp_name'];

				if( strpos( mime_content_type( $image_src ), 'image/' ) === false ){
					self::send_error( esc_html__( 'Invalid image file, please check again.', MUUPRO_TEXTDOMAIN ), true );
				} else {

					$sizes  = getimagesize( $image_src );
	
					$width  = $sizes[0];
					$height = $sizes[1];
					
					if( $width < 300 || $height < 450 ){
						self::send_error( esc_html__( 'Image resolution is not valid, please check again', MUUPRO_TEXTDOMAIN ), true );
					}
				}
			}

            $default_values = array(
                'title'           => '',
                'description'     => '',
                'featuredImage'   => '',
                'alternativeName' => '',
                'type'            => '',
                'status'          => '',
                'releaseYears'    => '',
                'authors'         => '',
                'artists'         => '',
                'genres'          => '',
                'tags'            => '',
                'chapterType'     => '',
				'manga_adult_content' => '',
				'badge' => ''
            );
			
            $data = array_merge( $default_values, $_POST );

            $options = muupro_get_user_upload_settings();

			$data['chapterType'] = (!isset( $options['manga_type'] ) ? 'manga' : $options['manga_type']);
			
            $data['post_status'] = isset( $options['post_status'] ) ? $options['post_status'] : 'pending';
			$data['userID'] = $user_id = get_current_user_id();
			do_action('muupro_before_create_manga', $data);
			
			$data = apply_filters('muupro_before_create_manga', $data);

            $post_id = self::create_post( $data );

			$published = UserUploadPermission::is_user_allowed( $user_id );
			
			if( $data['post_status'] == 'pending' && !$published ){
				// flag pending manga
				update_post_meta( $post_id, 'pending_manga', 1 );
			}
			
			do_action('muupro_after_create_manga', $data, $post_id);

            if( ! $post_id ){
                self::send_error( esc_html__('There was something wrong, please contact Website Administrator for more information', MUUPRO_TEXTDOMAIN), true );
            } else {
				if( $published ){
					self::send_success( esc_html__( 'Manga added successfully!', MUUPRO_TEXTDOMAIN ), true, $post_id );
				}else{
					self::send_success( esc_html__( 'Manga added successfully! Your manga will be reviewed before published.', MUUPRO_TEXTDOMAIN ), true, $post_id );
				}
			}

        }

        static function create_post( $args ){

            //1. insert post main data
            $post_args = array(
                'post_author'  => !empty( $args['userID'] ) ? $args['userID'] : '',
                'post_title'   => !empty( $args['title'] ) ? $args['title'] : '',
                'post_content' => !empty( $args['description'] ) ? $args['description'] : '',
                'post_type'    => 'wp-manga',
                'post_status' => isset( $args['post_status'] ) ? $args['post_status'] : 'pending',
            );
            $post_id = wp_insert_post( $post_args );

            if( ! $post_id && is_wp_error( $post_id ) ){
                return new \WP_Error( 404, esc_html__('Create Post failed', MUUPRO_TEXTDOMAIN) );
            }

			//2. add metadata
			if(!isset($_FILES['featuredImage'])){
				return new \WP_Error( 500, esc_html__('Please choose Feature Image', MUUPRO_TEXTDOMAIN) );
			}
			
			$thumb_id = self::upload_image( 'featuredImage', $post_id, 1 );
			if(isset($_FILES['horizontalThumb'])){
				$horizontalImage = self::upload_image( 'horizontalThumb', $post_id, 2 );
			}
			
            $meta_data = array(
                '_thumbnail_id'          => $thumb_id,
                '_wp_manga_alternative'  => isset( $args['alternativeName'] ) ? $args['alternativeName'] : '',
                '_wp_manga_type'         => isset( $args['type'] ) ? $args['type'] : '',
                '_wp_manga_status'       => isset( $args['status'] ) ? $args['status'] : '',
                '_wp_manga_chapter_type' => isset( $args['chapterType'] ) ? $args['chapterType'] : 'manga',
            );
			
			if(is_array($args['manga_adult_content']) && $args['manga_adult_content'] == 'yes'){
				$meta_data['manga_adult_content'] = array('yes');
			}
			
			if(isset($args['badge']) && $args['badge'] != ''){
				$badge_choices = function_exists('madara_get_badge_choices') ? madara_get_badge_choices() : array(esc_html__( 'Hot', MUUPRO_TEXTDOMAIN ), esc_html__( 'New', MUUPRO_TEXTDOMAIN ));
				if(in_array($args['badge'], $badge_choices)){
					$meta_data['manga_title_badges'] = sanitize_title($args['badge']);
				}
			}
			
			if(isset($horizontalImage) && $horizontalImage) {
				$meta_data['manga_banner'] = wp_get_attachment_url($horizontalImage);
			}

            foreach( $meta_data as $key => $value ){
                update_post_meta( $post_id, $key, $value );
            }

            //3.update terms
            $manga_terms = array(
                'wp-manga-release' => isset( $args['releaseYears'] ) ? $args['releaseYears'] : null,
                'wp-manga-author'      => isset( $args['authors'] ) ? $args['authors'] : null,
                'wp-manga-artist'      => isset( $args['artists'] ) ? $args['artists'] : null,
                'wp-manga-genre'       => isset( $args['genres'] ) ? $args['genres'] : '',
                'wp-manga-tag'         => isset( $args['tags'] ) ? $args['tags'] : null,
				'wp-manga-release' => isset( $args['release'] ) ? $args['release'] : null
            );

            foreach( $manga_terms as $tax => $term ){

                if( empty( $term ) ){
                    continue;
				}
				
				$resp = self::add_manga_terms( $post_id, $term, $tax );
				
            }

            return $post_id;

        }

		/**
		 * $file_POST_key - Form key of file input
		 * $post_id - Post ID to attach Image
		 * $ratio - 1: Thumb (1:1.5) ; 2: Horizontal (1.5:1)
		 **/
        static function upload_image( $file_POST_key, $post_id, $ratio = 1 ){
			
			// validate image
			$image_src = $_FILES[$file_POST_key]['tmp_name'];

			if( strpos( mime_content_type( $image_src ), 'image/' ) === false ){
				self::send_error( esc_html__( 'Invalid image file, please check again.', MUUPRO_TEXTDOMAIN ), true );
			}

            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $thumb_id = media_handle_upload( $file_POST_key, $post_id );

            if( ! $thumb_id && is_wp_error( $thumb_id ) ){
                return false;
            }

			$thumb_file = get_attached_file( $thumb_id );
			
			// validate and crop with ratio
			$sizes = getimagesize( $thumb_file );
			
			$width  = $sizes[0];
			$height = $sizes[1];
			$img_ratio = $width / $height;
			$required_ratio = ($ratio == 1 ? 1/1.5 : 1.5/1);

			if( $img_ratio !== $required_ratio ){
				
				if( $img_ratio > $required_ratio ){
					$resize_width = intval( $required_ratio * $height );
					$resize_height = $height;
				}else{
					$resize_height = intval( $width / $required_ratio );
					$resize_width = $width;
				}
				
				$image_src = get_attached_file( $thumb_id );
				
				$image_editor = wp_get_image_editor( $image_src );
				$image_editor->resize( $resize_width, $resize_height, true );
				$image_editor->save( $image_src );
				
				$metadata = wp_generate_attachment_metadata( $thumb_id, $image_src );
				wp_update_attachment_metadata( $thumb_id, $metadata );
				
			} 

            return $thumb_id;

        }

        static function add_manga_terms( $post_id, $terms, $taxonomy ){

            $terms = explode(',', $terms);

            if( empty( $terms ) ){
                return false;
            }

            $resp = wp_set_post_terms( $post_id, $terms, $taxonomy );

            return $resp;

        }

		static function crop_thumbnail(){

		}
    }

	