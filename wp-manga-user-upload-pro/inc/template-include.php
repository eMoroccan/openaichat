<?php

    /**
 	 * Template hooks
 	 *
	 */

    class MadaraUserUploadProTemplate{

        public function __construct(){
			$user_upload_pro__settings = muupro_get_user_upload_settings();
			
			if($user_upload_pro__settings['add_user_settings'] == 'yes'){
				add_action( 'madara_user_nav_tabs', array( $this, 'nav_tab' ) );
				add_action( 'madara_user_nav_contents', array( $this, 'nav_content' ) );
			}

            add_action( 'after_madara_settings_page', array( $this, 'muupro_admin_settings') );
			
			if(isset($user_upload_pro__settings['edit_manga']) && $user_upload_pro__settings['edit_manga'] == 'yes') {
				add_action( 'madara_single_manga_action', array( $this, 'madara_edit_button') );
				add_action( 'template_include', array( $this, 'madara_edit_page' ), 100 );
			}
        }
		
		function madara_edit_page( $template ){

            if( muupro_is_manga_edit_page() ){
                if( muupro_is_manga_author() || current_user_can('administrator') || current_user_can('editor') ){
                    return muupro_template('edit', 'manga');
                } else {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header( 404 );
                    get_template_part( 404 );
                    exit();
                }
            }

            return $template;
        }
		
		function muupro_template( $filename, $extend = false, $variables = array() ){

			if( $extend ){
				$filename .= '-' . $extend;
			}

			// allow override template from theme
			$theme_template_file = get_stylesheet_directory() . '/muu-pro/' . $filename . '.php';
			
			if( file_exists( $theme_template_file ) ){
				$file = $theme_template_file;
			}else{
				$file = MDR_USER_UPLOAD_DIR . 'template/' . $filename . '.php';
			}

			if( file_exists( $file ) ){
				extract( $variables );
				include( $file );
			}

		}

        function nav_tab( $tab_pane ){
			
            if( muupro_current_user_can_upload() ){

				global $wp_manga_user_actions;
				$add_manga = $wp_manga_user_actions->get_user_tab_url( 'add-manga' );
				$upload_chapter = $wp_manga_user_actions->get_user_tab_url( 'upload-chapter' );

                ?>
                    <li class="<?php echo ( $tab_pane == 'add-manga' ) ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url( $add_manga ); ?>"><i class="icon ion-md-add-circle"></i><?php esc_html_e( 'Add Manga', MUUPRO_TEXTDOMAIN ); ?>
                        </a>
                    </li>
					<li class="<?php echo ( $tab_pane == 'upload-chapter' ) ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url( $upload_chapter ); ?>"><i class="icon ion-md-cloud-upload"></i><?php esc_html_e( 'Upload Chapter', MUUPRO_TEXTDOMAIN ); ?>
                        </a>
                    </li>
                <?php
            }

        }

        function nav_content( $tab_pane ){
            if( muupro_current_user_can_upload() ){
                if( $tab_pane == 'add-manga' ){
					muupro_template( 'add-manga', 'form', compact(['tab_pane']) );
				}elseif( $tab_pane == 'upload-chapter' ){
					muupro_template( 'upload-chapter', 'form', compact(['tab_pane']) );
				}
            }
        }

        function muupro_admin_settings(){
            muupro_template('upload', 'settings');
        }

        function madara_edit_button(){

            global $current_user; 
			$current_user = wp_get_current_user(); 
			
            if ( muupro_is_manga_author() || user_can( $current_user, "administrator" ) || user_can( $current_user, "editor" ) ){ 
                ?>
                <div class="edit-btn">
                    <div class="action_icon">
                        <a href="<?php echo esc_url( add_query_arg( array( 'edit' => 1 ), get_the_permalink() ) ); ?>" title="<?php esc_html_e('Edit Manga', MUUPRO_TEXTDOMAIN); ?>">
                            <i class="icon ion-md-create"></i>
                        </a>
                    </div>
                    <div class="action_detail">
                        <span><?php esc_html_e('Edit', MUUPRO_TEXTDOMAIN); ?></span>
                    </div>
                </div>
                <?php
            }
        }

    }

    $muupro_template = new MadaraUserUploadProTemplate();

