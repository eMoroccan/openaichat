<?php
	
	namespace MadaraUserUploadPro;

	class UserUploadPermission{
	
		public static $instance;
	
		public static function get_instance(){
			if( ! self::$instance instanceof self ){
				self::$instance = new self();
			}
	
			return self::$instance;
		}
	
		public function __construct(){
			add_filter( 'manage_users_columns', array( $this, 'user_upload_permission_column') );
			add_action( 'manage_users_custom_column', array( $this, 'user_upload_permission_column_data' ), 10, 3 );
			add_action( 'wp_ajax_save_user_upload_permission', array( $this, 'save_user_upload_permission' ) );
		}

		public function user_upload_permission_column_data( $value, $column, $user_id ){
			
			if( current_user_can('administrator') ){
				$is_user_allowed = self::is_user_allowed( $user_id );
				$value = '<label><input type="checkbox" class="user_upload_permission" value="1" data-user-id="' . $user_id . '"' . checked( $is_user_allowed, 1, false ) . '/> Allow</label>';
			}

			return $value;
		}

		public function user_upload_permission_column( $columns ){

			if( ! current_user_can('administrator' ) ){
				return;
			}

			// enqueue script to ajax save user upload permission changes
			add_action( 'admin_footer', function(){
				?>
					<script>
						jQuery(document).ready(function($){
							$('input.user_upload_permission').on('change', function(){
								var userID = $(this).data('user-id'),
									allow = $(this).is(':checked');
								if( userID !== 'undefined' && userID !== '' ){
									$.ajax({
										url: 'admin-ajax.php',
										method: 'POST',
										data: {
											action: 'save_user_upload_permission',
											user_id: userID,
											allow: allow ? 1 : 0
										}
									});
								}
							});
						});
					</script>
				<?php 
			} );

			$index = 1;
			$output_columns = array();

			foreach( $columns as $id => $column ){

				if( $index === 3 ){
					$output_columns['user_upload_permission'] = 'Upload without review';
				}

				$output_columns[ $id ] = $column;

				$index++;
			}

			return $output_columns;

		}

		public function save_user_upload_permission(){
			if( current_user_can('administrator') ){
				if( !empty( $_POST['user_id'] ) && isset( $_POST['allow'] ) ){

					if( !empty( $_POST['allow'] ) ){
						update_user_meta( $_POST['user_id'], 'upload_manga_review_not_require', true );
					}else{
						delete_user_meta( $_POST['user_id'], 'upload_manga_review_not_require' );
						wp_send_json_error( 'Deleted' );
					}

					wp_send_json_success();
				}
			}
		}

		public static function is_user_allowed( $user_id ){
			return !empty( get_user_meta( $user_id, 'upload_manga_review_not_require', true ) );
		}

	}

	UserUploadPermission::get_instance();

?>