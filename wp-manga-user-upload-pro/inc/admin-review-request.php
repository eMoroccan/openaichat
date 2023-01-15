<?php
	
	namespace MadaraUserUploadPro;
	
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	}

	class Review_Request_List_Table extends \WP_List_Table {

		public static $per_page = 20;

		public function __construct() {

			parent::__construct(
				array(
					'singular' => 'user-upload-review-request',
					'plural'   => 'user-upload-review-requests',
					'ajax'     => false,
					'screen'   => get_current_screen()
				)
			);

		}

		/**
		 * Get list columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'type'           => __( 'Type', MUUPRO_TEXTDOMAIN ),
				'manga'          => __( 'Manga', MUUPRO_TEXTDOMAIN ),
				'chapter_number' => __( 'Chapter number', MUUPRO_TEXTDOMAIN ),
				'chapter_title'  => __( 'Chapter title', MUUPRO_TEXTDOMAIN ),
				'user'           => __( 'User', MUUPRO_TEXTDOMAIN ),
				'actions'        => __( 'Actions', MUUPRO_TEXTDOMAIN ),
			);

			return $columns;
		}

		protected function get_sortable_columns() {
			
			$columns = array(
				// 'datetime' => array( 'datetime', true ),
				// 'type'     => array( 'type', true )
			);

			return $columns;
		}

		public function column_type( $entry ) {
			echo sprintf(
				'<span class="request-type %s">%s</span>',
				$entry->meta_key,
				$entry->meta_key == 'pending_manga' ? __( 'Manga', MUUPRO_TEXTDOMAIN ) : __( 'Chapter', MUUPRO_TEXTDOMAIN )
			);
		}

		public function column_manga( $entry ) {
			echo sprintf(
				'<a href="%s" title="Preview manga" target="_blank">%s</a>',
				get_the_permalink( $entry->post_id ),
				get_the_title( $entry->post_id )
			);
		}

		public function column_chapter_number( $entry ) {

			$output = '';

			if( $entry->meta_key == 'pending_chapter' ){
				
				if( ! isset( $entry->chapter ) ){
					global $wp_manga_chapter;
	
					$post_id = $entry->post_id;
					$chapter_id = $entry->meta_value;
					$entry->chapter = $wp_manga_chapter->get_chapter_by_id( $post_id, $chapter_id );
				}
				
				if( $entry->chapter ){
					global $wp_manga_functions;

					$output = sprintf(
						'<a href="%s" title="Preview chapter" target="_blank">%d</a>',
						$wp_manga_functions->build_chapter_url( $post_id, $entry->chapter['chapter_slug'] ),
						preg_replace('/[^0-9.]+/', '', $entry->chapter['chapter_name'] )
					);
				}

			}

			echo $output;

		}

		public function column_chapter_title( $entry ) {

			$output = '';

			if( $entry->meta_key == 'pending_chapter' ){
				
				if( ! isset( $entry->chapter ) ){
					global $wp_manga_chapter;
	
					$post_id = $entry->post_id;
					$chapter_id = $entry->meta_value;
					$entry->chapter = $wp_manga_chapter->get_chapter_by_id( $post_id, $chapter_id );
				
				}
			
				if( $entry->chapter ){
					$output = sprintf(
						'<span>%s</span>',
						$entry->chapter['chapter_name_extend']
					);
				}
			}

			echo $output;

		}

		public function column_user( $entry ){
			
			if( $entry->meta_key == 'pending_manga' ){
				$author_user_id = $entry->post_author;
			}elseif( $entry->meta_key == 'pending_chapter' ){
				$author_user_id = muupro_get_chapter_author( $entry->post_id, $entry->meta_value );
			}else{
				return;
			}

			echo sprintf(
				'<span>%s</span>',
				get_the_author_meta( 'user_login', $author_user_id )
			);
		}

		public function column_actions( $entry ){

			$data_id = $entry->meta_key == 'pending_manga' ? $entry->post_id : $entry->meta_value;

			$output = sprintf(
				'<span class="button button-primary request-action" data-action="accept" data-type="%s" data-id="%d">%s</span>',
				$entry->meta_key,
				$data_id, 
				__( 'Accept', MUUPRO_TEXTDOMAIN )
			);

			$output .= sprintf(
				'<span class="button request-action" data-action="refuse" data-type="%s" data-id="%d">%s</span>',
				$entry->meta_key,
				$data_id, 
				__( 'Refuse', MUUPRO_TEXTDOMAIN )
			);

			echo $output;
		}

		public function _column_comic_total( $item, $classes, $data, $primary ){

			if( !empty( $item->total_items ) && !empty( $item->comic_total ) ){

				if( !empty( $item->last_group ) ){
					$classes.= ' last-group';
				}

				$attributes = "class='$classes' $data rowspan='$item->total_items'";

				echo "<td $attributes>";

				echo sprintf(
					'<span>%d</span>',
					$item->comic_total
				);

				echo $this->handle_row_actions( $item, 'comic', $primary );

				echo "</td>";
			}

		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination.
		 */
		protected function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				$output = '';

				echo $output;
			}
		}

		/**
		 * Prepare table list items.
		 */
		public function prepare_items() {

			$this->prepare_column_headers();

			$this->items = $this->get_items();
			
			$total_items = self::get_total_items();

			$args = array(
				'total_items' => $total_items,
				'per_page'    => self::$per_page,
				'total_pages' => intval( round( $total_items / self::$per_page, 0, PHP_ROUND_HALF_UP ) ),
			);
			
			$this->set_pagination_args( $args );

		}

		public function get_items(){

			$paged = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
			$offset = ( $paged - 1 ) * self::$per_page;

			global $wpdb;
		
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT postmeta.*, posts.post_author 
					FROM {$wpdb->prefix}postmeta as postmeta
					JOIN {$wpdb->prefix}posts as posts
					ON posts.ID = postmeta.post_id
					WHERE 
					(
						meta_key = 'pending_manga'
						AND meta_value = 1
					)
					OR 
					(
						meta_key = 'pending_chapter'
					)
					ORDER BY meta_id DESC
					LIMIT %d, %d",
					$offset,
					self::$per_page
				)
			);

			return $results;

		}

		public static function get_total_items(){

			global $wpdb;
		
			return (int) $wpdb->get_var(
				"SELECT count(*) 
				FROM {$wpdb->prefix}postmeta
				WHERE 
				(
					meta_key = 'pending_manga'
					AND meta_value = 1
				)
				OR 
				(
					meta_key = 'pending_chapter'
				)"
			);

		}

		public function prepare_column_headers() {
			$this->_column_headers = array(
				$this->get_columns(),
				array(),
				$this->get_sortable_columns(),
			);
		}

	}


?>