<?php
	
	namespace MadaraUserUploadPro;

	class QueryHooks{
	
		public static $instance;
	
		public static function get_instance(){
			if( ! self::$instance instanceof self ){
				self::$instance = new self();
			}
	
			return self::$instance;
		}
	
		public function __construct(){
			add_filter( 'pre_get_posts', array( $this, 'add_manga_query_hooks' ) );
			add_filter( 'manga_get_chapters_conditions', array( $this, 'exclude_pending_chapters' ), 10, 2 );
			
			add_filter('manga_get_chapters_conditions', array($this,'filter_manga_get_chapters_conditions'), 10, 2);
		}
		
		/**
		 * Exclude pending chapters from chapters list if accessed from FrontEnd
		 **/
		function filter_manga_get_chapters_conditions( $conditions, $args ){
			$new_conditions = array();
			
			if(!is_admin() && isset($args['post_id'])){
				$pending_chapters = get_post_meta( $args['post_id'], 'pending_chapter' );
				
				if($pending_chapters && count($pending_chapters) > 0){
					$conditions[] = sprintf("chapter_id NOT IN (%s)", implode(',', $pending_chapters));
				} else {
					$new_conditions = $conditions;
				}
			} else {
				$new_conditions = $conditions;
			}
			
			return $conditions;
		}
		
		public function add_manga_query_hooks( $query ){
			
			if( ! is_admin() && $query->get('post_type') == 'wp-manga' ){

				global $wp_manga_functions;

				$is_admin_role          = current_user_can('administrator');
				
				if( $query->get('upload_chapters') == 1 ){
					// if this query needs to show pending manga, we do not exclude pending manga
					if((is_array($query->get('post_status')) && in_array('pending', $query->get('post_status'))) || (!is_array($query->get('post_status')) && $query->get('post_status') == 'pending')) {
						// do nothing
					} else {
						$query = $this->query_exclude_pending_manga( $query );
						add_filter( 'posts_where', array( $this, 'listing_page_exclude_no_chapter_manga' ) );
					}
				}elseif( ! $is_admin_role ){
					add_filter( 'posts_where', array( $this, 'posts_where_user_exclude_rules') );
				}

				add_filter( 'posts_results', array( $this, 'remove_manga_query_hooks' ) );
			}

			return $query;

		}

		/**
		 * This filter should exclude 
		 * - pending manga doesn't belong to current user
		 * - manga with no chapters doesn't belong to current user
		 */
		public function posts_where_user_exclude_rules( $where ){
			
			global $wpdb;

			$user_id = get_current_user_id();

			if($user_id){
				$where .= "
					AND
					(
						{$wpdb->prefix}posts.ID NOT IN (
							SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'pending_manga'
						)
						OR {$wpdb->prefix}posts.post_author = {$user_id}
					)
					AND 
					(
						{$wpdb->prefix}posts.ID IN
						(
							SELECT post_id 
							FROM {$wpdb->prefix}manga_chapters
							GROUP BY post_id
						)
						OR {$wpdb->prefix}posts.post_author = {$user_id}
					)
				";
			} else {
				$where .= "
					AND
						{$wpdb->prefix}posts.ID NOT IN (
							SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'pending_manga'
						)
					AND 
						{$wpdb->prefix}posts.ID IN
						(
							SELECT post_id 
							FROM {$wpdb->prefix}manga_chapters
							GROUP BY post_id
						)
				";
			}				
			
			return $where;
		} 

		/**
		 * Set query args to exclude pending manga
		 */
		public function query_exclude_pending_manga( $query ){
			
			$meta_query = $query->get('meta_query');
			
			if( ! is_array( $meta_query ) ){
				$meta_query = array();
			}
			
			$meta_query[] = array(
				'key'     => 'pending_manga',
				'compare' => 'NOT EXISTS'
			);
			
			$query->set( 'meta_query', $meta_query );
		
			return $query;
		}
		
		public function listing_page_exclude_no_chapter_manga( $where ){

			global $wpdb;

			$this_where = "
				{$wpdb->prefix}posts.ID IN
				(
					SELECT post_id 
					FROM {$wpdb->prefix}manga_chapters
					GROUP BY post_id
				)
			";

			$where .= "
				AND ( {$this_where} )
			";
			
			return $where;
		}

		public function remove_manga_query_hooks( $posts ){
			remove_filter( 'posts_where', array( $this, 'listing_page_exclude_no_chapter_manga' ) );
			remove_filter( 'posts_join', array( $this, 'posts_join_user_exclude_rules' ) );
			remove_filter( 'posts_where', array( $this, 'posts_where_user_exclude_rules') );

			return $posts;
		}

		public function exclude_pending_chapters( $conditions, $args ){
			
			global $wp_manga_functions, $post;

			if( 
				isset( $args['post_id'] ) 
				&& ! is_admin() 
				&& ! current_user_can('administrator')
				&& ! muupro_is_manga_author()
			){
				$pending_chapters = UserUploadChapter::get_pending_chapters( $args['post_id'] );
				if( !empty( $pending_chapters ) ){
					$pending_chapters = array_filter( $pending_chapters, 'is_numeric' );
					$conditions[] = sprintf( 'chapter_id NOT IN (%s)', implode( ',', $pending_chapters ) );
				}
			}

			return $conditions;
			
		}

	}

	QueryHooks::get_instance();

?>