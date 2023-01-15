<?php
	
	namespace MadaraUserUploadPro;

	class Ajax{
	
		public static $instance;
	
		public static function get_instance(){
			if( ! self::$instance instanceof self ){
				self::$instance = new self();
			}
	
			return self::$instance;
		}
	
		public function __construct(){
			add_action( 'wp_ajax_front_end_create_manga', array( UserAddManga::class, 'handler') );
			add_action( 'wp_ajax_front_end_upload_chapter', array( UserUploadChapter::class, 'handler') );
			add_action( 'wp_ajax_front_end_upload_novel', array( UserUploadChapter::class, 'upload_novel') );
			
			add_action('wp_ajax_muupro_get_volumes', array($this, 'get_volumes'));
			
			add_action( 'wp_ajax_back_end_accept_request', array( $this, 'admin_accept_request' ) );
			add_action( 'wp_ajax_back_end_refuse_request', array( $this, 'admin_refuse_request' ) );
		}
		
		public function get_volumes(){
			$manga_id = isset($_POST['manga']) ? intval($_POST['manga']) : 0;
			$data = array();
			if($manga_id){
				global $wp_manga_volume;
				$volumes = $wp_manga_volume->get_manga_volumes($manga_id);
				if($volumes){
					foreach($volumes as $vol){
						array_push($data, array('text' => $vol['volume_name'], 'value' => $vol['volume_id']));
					}
					
					wp_send_json_success($data);
				}
			}
			
			wp_send_json_success($data);
		}

		public static function admin_accept_request(){
			
			if( 
				current_user_can( 'administrator' ) 
				&& !empty( $_POST['nonce'] ) && !empty( $_POST['id'] ) && !empty( $_POST['type'] )
			){
				if( ! wp_verify_nonce( $_POST['nonce'], 'admin_review_requests_nonce' ) ){
					return;
				}
				
				if( $_POST['type'] == 'pending_manga' ){
					
					$post_id = $_POST['id'];
					$post = get_post( $post_id );

					if( !empty( $post ) ){
						delete_post_meta( $post->ID, 'pending_manga' );
						wp_publish_post( $post );
					}else{
						$err_msg = 'Manga not found';
					}

				}elseif( $_POST['type'] == 'pending_chapter' ){

					global $wp_manga_chapter;
					$chapter_id = $_POST['id'];
					$chapter = $wp_manga_chapter->get_chapter_by_id( null, $chapter_id );
					
					if( !empty( $chapter ) ){
						delete_post_meta( $chapter['post_id'], 'pending_chapter', $chapter_id );
					}else{
						$err_msg = 'Chapter not found';
						// this chapter may be deleted manually, so we remove the pending item
						global $wpdb;
						
						$sql = $wpdb->prepare(
								"DELETE FROM {$wpdb->prefix}postmeta
								WHERE meta_key = 'pending_chapter' and meta_value = %d",
								$chapter_id
							);
		
						$wpdb->query( $sql );
					}

				}else{
					$err_msg = 'Request type not found';
				}

				if( !empty( $err_msg ) ){
					wp_send_json_error([
						'message' => $err_msg,
						'nonce'   => wp_create_nonce( 'admin_review_requests_nonce' )
					]);
				}else{
					wp_send_json_success([
						'message' => 'Accept successfully!',
						'nonce'   => wp_create_nonce( 'admin_review_requests_nonce' )
					]);
				}
			}

		}

		public static function admin_refuse_request(){
			
			if( 
				current_user_can( 'administrator' ) 
				&& !empty( $_POST['nonce'] ) && !empty( $_POST['id'] ) && !empty( $_POST['type'] )
			){

				if( ! wp_verify_nonce( $_POST['nonce'], 'admin_review_requests_nonce' ) ){
					return;
				}
				
				if( $_POST['type'] == 'pending_manga' ){
					
					$post_id = $_POST['id'];
					$post = get_post( $post_id );

					if( !empty( $post ) ){
						wp_delete_post( $post_id );
					}else{
						$err_msg = 'Manga not found';
					}

				}elseif( $_POST['type'] == 'pending_chapter' ){

					global $wp_manga_chapter, $wp_manga_storage;
					$chapter_id = $_POST['id'];
					$chapter = $wp_manga_chapter->get_chapter_by_id( null, $chapter_id );
					
					if( !empty( $chapter ) ){
						// remove from meta data
						$post_chapter_numbers = get_post_meta( $chapter['post_id'], 'chapter_numbers', true );
						
						if( ! is_array( $post_chapter_numbers ) ){
							$post_chapter_numbers = array();
						}
						if(isset($post_chapter_numbers[ $chapter['chapter_index'] ])){
							unset($post_chapter_numbers[ $chapter['chapter_index'] ]);
							update_post_meta( $chapter['post_id'], 'chapter_numbers', $post_chapter_numbers );
						}
						
						$wp_manga_storage->delete_chapter( $chapter['post_id'], $chapter['chapter_id'] );
					}else{
						$err_msg = 'Chapter not found';
						// this chapter may be deleted manually, so we remove the pending item
						global $wpdb;
						
						$sql = $wpdb->prepare(
								"DELETE FROM {$wpdb->prefix}postmeta
								WHERE meta_key = 'pending_chapter' and meta_value = %d",
								$chapter_id
							);
		
						$wpdb->query( $sql );
					}

				}else{
					$err_msg = 'Request type not found';
				}

				if( !empty( $err_msg ) ){
					wp_send_json_error([
						'message' => $err_msg,
						'nonce'   => wp_create_nonce( 'admin_review_requests_nonce' )
					]);
				}else{
					wp_send_json_success([
						'message' => 'Refuse successfully!',
						'nonce'   => wp_create_nonce( 'admin_review_requests_nonce' )
					]);
				}
			}

		}

	}

	Ajax::get_instance();

?>