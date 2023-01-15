<?php

    /**
    * Helper Functions for Madara User Upload
    *
    */

    function muupro_current_user_can_upload( $user_id = null ){

        if( $user_id ){
            $current_user = get_user_by( 'id', $user_id );
        }else{
            if( !is_user_logged_in() ){
                return false;
            }

            $current_user = wp_get_current_user();
        }

        $options = get_option( 'wp_manga_settings', array() );
        $options = isset( $options['user_upload_pro_settings'] ) ? $options['user_upload_pro_settings'] : '';

        return !isset( $options['user_roles'] ) || !empty( array_intersect( $current_user->roles, $options['user_roles'] ) );

    }

    function muupro_get_user_upload_settings(){

        $options = get_option( 'wp_manga_settings', array() );
        $options = isset( $options['user_upload_pro_settings'] ) ? $options['user_upload_pro_settings'] : '';

        return array(
            'message'                 => isset( $options['user_upload_message'] ) ? $options['user_upload_message'] : '',
			'add_user_settings'                 => isset( $options['add_user_settings'] ) ? $options['add_user_settings'] : 'yes',
            'default_storage'         => isset( $options['default_storage'] ) ? $options['default_storage'] : 'local',
            'chapter_type'            => isset( $options['default_chapter_type'] ) ? $options['default_chapter_type'] : 'manga',
            'chapter_type_permission' => isset( $options['chapter_type_permission'] ) ? $options['chapter_type_permission'] : 1,
            'post_status'             => isset( $options['post_status'] ) ? $options['post_status'] : 'pending',
            'user_roles'              => isset( $options['user_roles'] ) ? $options['user_roles'] : array(),
			'edit_manga' => isset( $options['edit_manga'] ) ? $options['edit_manga'] : 'no',
			'manga_type' => isset( $options['manga_type'] ) ? $options['manga_type'] : 'manga',
        );

    }
	
	function muupro_is_manga_edit_page(){
        return function_exists('is_manga_single') && is_manga_single() && isset( $_GET['edit'] ) && $_GET['edit'] == 1;
    }

	function muupro_is_manga_author(){

        $user_id = get_current_user_id();

        if( empty( $user_id ) ){
            return false;
        }

        global $post;

        return $post->post_author == $user_id;

	}
	
	function muupro_is_chapter_author(){
		return true;
		
		// TEST
		global $post, $wp_manga_functions;

		if( $post->post_type == 'wp-manga' && $wp_manga_functions->is_manga_reading_page() ){
			$cur_chap = get_query_var( 'chapter' );
			$chapter = $wp_manga_functions->get_chapter_by_slug( $manga_id, $cur_chap );
			
			if( !empty( $chapter ) ){
				$chapter_id = $chapter['chapter_id'];
				muupro_get_chapter_author( $post->ID, $chapter_id ) === get_current_user_id();
			}
		}

		return false;

	}

	function muupro_get_chapter_author($manga_id, $chapter_id){
		$multi_authors = get_post_meta($manga_id, '_chapter_authors', true);
		
		if(!is_array($multi_authors)) $multi_authors = array();
		
		if(isset($multi_authors[$chapter_id])){
			return (int) $multi_authors[$chapter_id];
		}
		
		return 0;
	}

    function muupro_template( $filename, $extend = false, $variables = array() ){

        if( $extend ){
            $filename .= '-' . $extend;
        }

        $file_dir = MDR_USER_UPLOAD_PRO_DIR . 'template/' . $filename . '.php';

        if( file_exists( $file_dir ) ){
            extract( $variables );
            include( $file_dir );
        }

	}
	
	function muupro_is_user_upload_manga_page( $tab = null ){
		
		global $wp_manga_setting;
		
		if( ! isset( $wp_manga_setting->muupro_is_user_upload_manga_page ) ){

			$user_page = $wp_manga_setting->get_manga_option( 'user_page' );
		
			if( get_queried_object_id() == $user_page ){
				if( $tab ){
					if( isset( $_GET['tab'] ) && $_GET['tab'] == $tab ){
						$wp_manga_setting->muupro_is_user_upload_manga_page = true;
					}
				}else{
					$wp_manga_setting->muupro_is_user_upload_manga_page = true;
				}
			}

		}

		if( empty( $wp_manga_setting->muupro_is_user_upload_manga_page ) ){
			$wp_manga_setting->muupro_is_user_upload_manga_page = false;
		}
		
		return $wp_manga_setting->muupro_is_user_upload_manga_page;

	}
