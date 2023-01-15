<?php
	
	namespace MadaraUserUploadPro;

	class AdminPages{
	
		public static $instance;
	
		public static function get_instance(){
			if( ! self::$instance instanceof self ){
				self::$instance = new self();
			}
	
			return self::$instance;
		}
	
		public function __construct(){
			add_action( 'admin_menu', array( $this, 'review_pending_request_page' ) );
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu'), 90 );
			add_action( 'admin_head', array( $this, 'admin_styles' ) );
			add_action( 'wp_head', array( $this, 'admin_styles' ) );
		}

		public function review_pending_request_page(){

			$total_items = Review_Request_List_Table::get_total_items();
			add_submenu_page(
				'edit.php?post_type=wp-manga', 
				sprintf(
					'%s %s',
					__( 'Review Requests' , 'simply-show-hooks' ),
					!empty( $total_items ) ? '<span class="awaiting-mod">(' . $total_items . ')</span>' : ''
				), 
				sprintf(
					'%s %s',
					__( 'Review Requests' , 'simply-show-hooks' ),
					!empty( $total_items ) ? '<span class="awaiting-mod">' . $total_items . '</span>' : ''
				), 
				'edit_posts', 
				'manga-review-request', 
				function(){
					muupro_template( 'admin', 'review-request' );
				} 
			);
		}

		public function admin_bar_menu( $wp_admin_bar ){

			if( current_user_can( 'edit_posts' ) ){

				$total_items = Review_Request_List_Table::get_total_items();

				$wp_admin_bar->add_menu( array(
					'title'		=> sprintf(
						'%s %s',
						__( 'Review Requests' , 'simply-show-hooks' ),
						!empty( $total_items ) ? '<span class="awaiting-mod">' . $total_items . '</span>' : ''
					),
					'id'		=> 'user-upload-review-request',
					'parent'	=> false,
					'href'		=> add_query_arg(
						array(
							'post_type' => 'wp-manga',
							'page'      => 'manga-review-request'
						),
						admin_url( 'edit.php' )
					),
				) );
			}
		}

		public function admin_styles(){
			
			if( is_admin() ){
				global $wp_manga;

				if( $wp_manga->is_manga_edit_page() ){
					?>
						<style>
							.wp-manga-edit-chapter span.pending-chapter {
								display: inline-block;
								background: #e1e1e1;
								color: #787878;
								padding: 0px 5px;
								margin-left: 10px;
							}
						</style>
					<?php 
				}
				?>
					<style>
						table.user-upload-review-requests .column-actions img {
							vertical-align: -webkit-baseline-middle;
							margin-right: 10px;
						}

						table.user-upload-review-requests span.request-type {
							color: #ffff;
							background: #d73e3e;
							padding: 6px;
							margin: 5px;
							border-radius: 3.5px;
						}

						table.user-upload-review-requests span.request-type.pending_chapter {
							background: #009688;
						}
					</style>
				<?php 
			}
			
			if( current_user_can('administrator') ){
				?>
					<style>
						#wp-admin-bar-user-upload-review-request > a {
							background-color: #525860;
							padding-left: 12px !important;
						}
						#wp-admin-bar-user-upload-review-request .awaiting-mod {
							display: inline-block;
							vertical-align: text-bottom;
							margin: 1px 0 0 2px;
							padding: 0 5px;
							min-width: 7px;
							height: 17px;
							border-radius: 11px;
							background-color: #ca4a1f;
							color: #fff;
							font-size: 9px;
							line-height: 17px;
							text-align: center;
							z-index: 26;
						}
					</style>
				<?php
			}
			
		}

		public function get_page_title(){

		}
	}

	AdminPages::get_instance();
	
?>