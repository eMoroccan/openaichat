<?php

	namespace MadaraUserUploadPro;

    class UserUploadChapter extends AjaxHandler{

		public static $action = 'madara_user_upload_pro_chapter_frontend';

		public static $instance;

		public static $pending_chapters;
	
		public static function get_instance(){
			if( ! self::$instance instanceof self ){
				self::$instance = new self();
			}
	
			return self::$instance;
		}

		public function __construct(){
			add_action( 'wp_manga_upload_completed', array( __CLASS__, 'add_chapter_author'), 10, 5);
			add_action( 'madara_chapter_content_li_html', array( $this, 'admin_pending_chapter_html' ), 10, 3 );
		}
		
		// upload Text Chapter
		static function upload_novel(){
			/**
			 * Validate request
			 */
			if( empty( $_POST['nonce'] ) ){
				self::send_error();
			}elseif( ! self::verify_nonce( $_POST['nonce'] ) ){
				self::send_error( __( 'Session expired, please refresh page and try again', MUUPRO_TEXTDOMAIN ) );
			}

			/**
			 * Validate posted data
			 */
			if( empty( $_POST['manga'] ) ){
				self::send_error( __( 'Please select manga for this chapter', MUUPRO_TEXTDOMAIN ), true );
			}elseif( ! isset( $_POST['chapter-number'] ) || $_POST['chapter-number'] == '' ){
				self::send_error( __( 'Chapter number cannot be empty', MUUPRO_TEXTDOMAIN ), true );
			}

			if( ! is_numeric( $_POST['manga'] ) ){
				self::send_error( 'Invalid request' );
			}elseif( ! is_numeric( $_POST['chapter-number'] ) ){
				self::send_error( __( 'Chapter number must be numberic', MUUPRO_TEXTDOMAIN ), true );
			}

			global $wp_manga, $wp_manga_functions, $wp_manga_storage;

			$post_id = $_POST['manga'];
			$uniqid = $wp_manga->get_uniqid( $post_id );

			if( empty( $uniqid ) ){
				self::send_error( __( 'Invalid manga, please try again later', MUUPRO_TEXTDOMAIN ) );
			}

			$chapter_number      = intval($_POST['chapter-number']);
			$chapter_name        = isset( $_POST['chapter-title'] ) ? stripslashes( $_POST['chapter-title'] ) : '';
			if(!$chapter_name){
				$chapter_name = sprintf(esc_html__('Chapter %s', MUUPRO_TEXTDOMAIN), $chapter_number);
			}
			
			$chapter_extend_name = isset( $_POST['chapter-extendname'] ) ? stripslashes( $_POST['chapter-extendname'] ) : '';
			$chapter_seo = !empty( $_POST['chapter-seo'] ) ? stripslashes( $_POST['chapter-seo'] ) : '';
			$chapter_warning_text = !empty( $_POST['chapter-warning-text'] ) ? stripslashes( $_POST['chapter-warning-text'] ) : '';
			$chapter_slug        = $wp_manga_functions->unique_slug( $post_id, $chapter_name );
			$chapter_dir_name    = $wp_manga_storage->get_uniq_dir_slug( serialize( $chapter_name . $chapter_extend_name ) );

			/**
			 * Make sure chapter number is unique
			 */
			$post_chapter_numbers = get_post_meta( $post_id, 'chapter_numbers', true );
			if( isset( $post_chapter_numbers[ $chapter_number ] ) ){
				// chapter maybe exist. We double check if it does really exist
				// first, check if there is pending request for it
				global $wpdb;
				$sql = $wpdb->prepare(
								"SELECT COUNT(*) FROM {$wpdb->prefix}manga_chapters
								WHERE chapter_index = %d AND and post_id = %d",
								$chapter_number,
								$post_id
							);
				$found = $wpdb->get_var($sql);
				if($found){
					self::send_error( esc_html__( 'This chapter number is already existed. Please try to upload another chapter', true ) );
				}
			}

			$options = muupro_get_user_upload_settings();
			
			$volume_id = 0;
			if(isset($_POST['volume_name']) && $_POST['volume_name'] != ''){
				// create new volume
				global $wp_manga_volume;
				$volume_id = $wp_manga_volume->insert_volume(array('post_id' => $post_id, 'volume_name' => $_POST['volume_name']));
			} else {
				$volume_id = intval($_POST['volume_id']);
				if($volume_id){
					// make sure this $volume_id belong to this post_id
					global $wp_manga_volume;
					$vol = $wp_manga_volume->get_volume_by_id($post_id, $volume_id);
					if(!$vol){
						// invalid volume id
						self::send_error( esc_html__( 'Invalid volume', MUUPRO_TEXTDOMAIN ), true );
					}
				}
			}

			$chapter_args = array(
				'post_id'             => $post_id,
				'volume_id'           => $volume_id,
				'chapter_index' 		=> $chapter_number,
				'chapter_name'        => $chapter_name,
				'chapter_name_extend' => $chapter_extend_name,
				'chapter_slug'        => $chapter_slug,
				'chapter_seo' => $chapter_seo,
				'chapter_warning' => $chapter_warning_text,
				'chapter_content'     => stripslashes(isset( $_POST['content'] ) ? $_POST['content'] : '')
			);
			
			do_action('muupro_before_add_chapter', $chapter_args);
			
			$chapter_args = apply_filters('muupro_before_add_chapter', $chapter_args);

			global $wp_manga_text_type;
			$chapter_id = $wp_manga_text_type->insert_chapter( $chapter_args );
			
			do_action('muupro_after_add_chapter', $chapter_args, $chapter_id);
			
			do_action( 'wp_manga_upload_completed', $chapter_id, $post_id, '', '', '');

			if( !empty( $chapter_id ) ) {
				if( is_wp_error( $chapter_id ) ){
					self::send_error( $chapter_id->get_error_message(), true );
				} else if( isset( $chapter_id['error'] ) ) {
					self::send_error( $chapter_id, true );
				} else {
					$user_id = get_current_user_id();
					if($options['post_status'] == 'pending' && ! $published = UserUploadPermission::is_user_allowed( $user_id )){
						self::add_pending_chapter( $chapter_id, $post_id );
					}
					self::add_chapter_number( $chapter_id, $chapter_number, $post_id );
					self::send_success( __( 'Chapter is uploaded successfully. Your chapter is being reviewed', MUUPRO_TEXTDOMAIN ), true );
				}
			} else {
				self::send_error( esc_html__( 'Upload failed. Please try again later', MUUPRO_TEXTDOMAIN ), true );
			}
		}

		// upload Manga Chapter
		static function handler(){
			/**
			 * Validate request
			 */
			if( empty( $_POST['nonce'] ) ){
				self::send_error();
			}elseif( ! self::verify_nonce( $_POST['nonce'] ) ){
				self::send_error( __( 'Session expired, please refresh page and try again', MUUPRO_TEXTDOMAIN ) );
			}

			/**
			 * Validate posted data
			 */
			if( empty( $_POST['manga'] ) ){
				self::send_error( __( 'Please select manga for this chapter', MUUPRO_TEXTDOMAIN ), true );
			}elseif( ! isset( $_POST['chapter-number'] ) || $_POST['chapter-number'] == '' ){
				self::send_error( __( 'Chapter number cannot be empty', MUUPRO_TEXTDOMAIN ), true );
			}

			if( ! is_numeric( $_POST['manga'] ) ){
				self::send_error( 'Invalid request' );
			}elseif( ! is_numeric( $_POST['chapter-number'] ) ){
				self::send_error( __( 'Chapter number must be numberic', MUUPRO_TEXTDOMAIN ), true );
			}

			if( empty( $_FILES ) ){
				self::send_error( __( 'Please attach images to chapter', MUUPRO_TEXTDOMAIN ), true );
			}

			global $wp_manga, $wp_manga_functions, $wp_manga_storage;

			$post_id = $_POST['manga'];
			$uniqid = $wp_manga->get_uniqid( $post_id );

			if( empty( $uniqid ) ){
				self::send_error( __( 'Invalid manga, please try again later', MUUPRO_TEXTDOMAIN ) );
			}

			$chapter_number      = intval($_POST['chapter-number']);
			$chapter_name        = isset( $_POST['chapter-title'] ) ? stripslashes( $_POST['chapter-title'] ) : '';
			if(!$chapter_name){
				$chapter_name = sprintf(esc_html__('Chapter %s', MUUPRO_TEXTDOMAIN), $chapter_number);
			}
			
			$chapter_extend_name = isset( $_POST['chapter-extendname'] ) ? stripslashes( $_POST['chapter-extendname'] ) : '';
			$chapter_seo = !empty( $_POST['chapter-seo'] ) ? stripslashes( $_POST['chapter-seo'] ) : '';
			$chapter_warning_text = !empty( $_POST['chapter-warning-text'] ) ? stripslashes( $_POST['chapter-warning-text'] ) : '';
			$chapter_slug        = $wp_manga_functions->unique_slug( $post_id, $chapter_name );
			$chapter_dir_name    = $wp_manga_storage->get_uniq_dir_slug( serialize( $chapter_name . $chapter_extend_name ) );

			/**
			 * Make sure chapter number is unique
			 */
			$post_chapter_numbers = get_post_meta( $post_id, 'chapter_numbers', true );
			if( isset( $post_chapter_numbers[ $chapter_number ] ) ){
				// chapter maybe exist. We double check if it does really exist
				// first, check if there is pending request for it
				global $wpdb;
				$sql = $wpdb->prepare(
								"SELECT COUNT(*) FROM {$wpdb->prefix}manga_chapters
								WHERE chapter_index = %d AND and post_id = %d",
								$chapter_number,
								$post_id
							);
				$found = $wpdb->get_var($sql);
				if($found){
					self::send_error( esc_html__( 'This chapter number is already existed. Please try to upload another chapter', true ) );
				}
			}

			$options = muupro_get_user_upload_settings();

			// get storage from admin setting
			$storage = $options['default_storage'];

			//put files in correct folders
			if ( $storage == 'local' ) {
				$extract       = WP_MANGA_DATA_DIR . $uniqid . '/' . $chapter_dir_name;
				$final_extract = WP_MANGA_DATA_DIR . $uniqid;
				$extract_uri   = WP_MANGA_DATA_URL;
			}else{
				$extract     = WP_MANGA_DIR . 'extract/temp/' . $uniqid . '/' . $chapter_dir_name;
				$extract_uri = WP_MANGA_URI . 'extract/temp/' . $uniqid . '/' . $chapter_dir_name;
			}
			
			if( ! file_exists( $extract ) ){
				wp_mkdir_p( $extract );
			}

			/**
			 * Loop on images 
			 * validate and place in extract directory
			 */
			foreach( $_FILES as $index => $file ){
				$is_valid_index_name = strpos( $index, 'file_' ) !== false;
				$is_image_file = strpos( $file['type'], 'image/' ) !== false;
				$has_no_error = $file['error'] === 0;
				
				if( $is_valid_index_name && $is_image_file && $has_no_error ){
					move_uploaded_file( $file['tmp_name'], $extract . '/' . $file['name'] );
				}
			}
			
			do_action( 'wp_manga_upload_after_extract', $post_id, $chapter_dir_name, $extract, $storage );
			
			$volume_id = 0;
			if(isset($_POST['volume_name']) && $_POST['volume_name'] != ''){
				// create new volume
				global $wp_manga_volume;
				$volume_id = $wp_manga_volume->insert_volume(array('post_id' => $post_id, 'volume_name' => $_POST['volume_name']));
			} else {
				$volume_id = intval($_POST['volume_id']);
				if($volume_id){
					// make sure this $volume_id belong to this post_id
					global $wp_manga_volume;
					$vol = $wp_manga_volume->get_volume_by_id($post_id, $volume_id);
					if(!$vol){
						// invalid volume id
						self::send_error( esc_html__( 'Invalid volume', MUUPRO_TEXTDOMAIN ), true );
					}
				}
			}

			$chapter_args = array(
				'post_id'             => $post_id,
				'volume_id'           => $volume_id,
				'chapter_index' => $chapter_number,
				'chapter_name'        => $chapter_name,
				'chapter_name_extend' => $chapter_extend_name,
				'chapter_slug'        => $chapter_slug,
				'chapter_seo' => $chapter_seo,
				'chapter_warning' => $chapter_warning_text
			);
			
			do_action('muupro_before_add_chapter', $chapter_args);
			$chapter_args = apply_filters('muupro_before_add_chapter', $chapter_args);

			//upload chapter
			$chapter_id = $wp_manga_storage->wp_manga_upload_single_chapter( 
				$chapter_args, 
				$extract, 
				$extract_uri, 
				$storage 
			);
			
			do_action('muupro_after_add_chapter', $chapter_args, $chapter_id);
			
			do_action( 'wp_manga_upload_completed', $chapter_id, $post_id, $extract, $extract_uri, $storage );

			if( $storage !== 'local' ){
				$wp_manga_storage->local_remove_storage( $extract );
			}

			if( !empty( $chapter_id ) ) {
				if( is_wp_error( $chapter_id ) ){
					self::send_error( $chapter_id->get_error_message(), true );
				}else if( isset( $chapter_id['error'] ) ) {
					self::send_error( $chapter_id, true );
				}else{
					$user_id = get_current_user_id();
					if($options['post_status'] == 'pending' && ! $published = UserUploadPermission::is_user_allowed( $user_id )){
						self::add_pending_chapter( $chapter_id, $post_id );
					}
					self::add_chapter_number( $chapter_id, $chapter_number, $post_id );
					self::send_success( __( 'Chapter is uploaded successfully. Your chapter is being reviewed', MUUPRO_TEXTDOMAIN ), true );
				}
			} else {
				self::send_error( esc_html__( 'Upload failed. Please try again later', MUUPRO_TEXTDOMAIN ), true );
			}
		}
		
		static function add_chapter_number( $chapter_id, $chapter_number, $post_id ){
			$post_chapter_numbers = get_post_meta( $post_id, 'chapter_numbers', true );
			if( ! is_array( $post_chapter_numbers ) ){
				$post_chapter_numbers = array();
			}
			$post_chapter_numbers[ $chapter_number ] = $chapter_id;
			return update_post_meta( $post_id, 'chapter_numbers', $post_chapter_numbers );
		}

		static function add_chapter_author( $uploaded_chapters, $post_id , $extract, $extract_uri, $storage){

			if( ! is_user_logged_in() ){
				return;
			}

			$multi_authors = get_post_meta($post_id, '_chapter_authors', true);
			
			if(!is_array($multi_authors)) $multi_authors = array();
			
			if(is_array($uploaded_chapters)){
				// in case upload multi chapters
				$keys = array_values($uploaded_chapters);
				foreach($keys as $chapter_id){
					$multi_authors[$chapter_id] = get_current_user_id();
				}
			} else {
				$multi_authors[$uploaded_chapters] = get_current_user_id();
			}
			
			update_post_meta($post_id, '_chapter_authors', $multi_authors);

		}

		static function add_pending_chapter( $chapter_id, $post_id ){
			return add_post_meta( $post_id, 'pending_chapter', $chapter_id );
		}

		static function remove_pending_chapter( $chapter_id, $post_id ){
			return delete_post_meta( $post_id, 'pending_chapter', $chapter_id );
		}

		static function get_pending_chapters( $post_id ){
			return get_post_meta( $post_id, 'pending_chapter' );
		}

		function admin_pending_chapter_html( $html, $chapter_id, $chapter ){
			
			$post_id = $chapter['post_id'];

			if( ! isset( self::$pending_chapters[ $post_id ] ) ){
				self::$pending_chapters[ $post_id ] = self::get_pending_chapters( $post_id );
			}

			$pending_chapters = self::$pending_chapters[ $post_id ];

			if( is_array( $pending_chapters ) && in_array( $chapter_id, $pending_chapters ) ){
				$html_parts = explode( '</a><a', $html );
				$html_parts[0] .= '<span class="pending-chapter">Pending</span>';
				$html = implode( '</a><a', $html_parts );
			}
			

			return $html;
		}

		
	}

	UserUploadChapter::get_instance();