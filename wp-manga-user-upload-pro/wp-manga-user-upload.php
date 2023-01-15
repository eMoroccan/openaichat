<?php

	/*
	Plugin Name: WP Manga Member Upload PRO
	Description: Enable User Upload feature for WP Manga (Madara)
	Version: 1.2.2.2
	Author: MangaBooth
	Author URI: https://mangabooth.com
	License: Commercial
	*/

	namespace MadaraUserUploadPro;

    if( !defined( 'MDR_USER_UPLOAD_PRO_DIR' ) ){
        define( 'MDR_USER_UPLOAD_PRO_DIR', plugin_dir_path( __FILE__ ) );
    }

    if( !defined( 'MDR_USER_UPLOAD_PRO_URL' ) ){
        define( 'MDR_USER_UPLOAD_PRO_URL', plugin_dir_url( __FILE__ ) );
    }

    if ( ! defined( 'MUUPRO_TEXTDOMAIN' ) ) {
        define( 'MUUPRO_TEXTDOMAIN', 'wp-manga-user-upload-pro' );
    }

    class MadaraUserUploadPro{

		public static $instance;
	
		public static function get_instance(){
			if( ! self::$instance instanceof self ){
				self::$instance = new self();
			}
	
			return self::$instance;
		}

        function __construct(){

            $this->autoload();

            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			
            add_action( 'wp_manga_setting_save', array( $this, 'upload_settings_save') );

			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain') );
			
			add_shortcode('muupro_upload_manga', array($this,'upload_manga_form'));
			add_shortcode('muupro_upload_chapters', array($this,'upload_chapters_form'));
			add_shortcode('muupro_upload_novel_chapter', array($this,'upload_novel_chapter_form'));
			
			add_action('init', array($this, 'init'));
			
			add_action( 'wp_ajax_front_end_edit_upload_handler', array( \MUUPRO_EditHandler::class, 'upload_handler') );
			add_action( 'wp_ajax_nopriv_front_end_edit_upload_handler', array( \MUUPRO_EditHandler::class, 'upload_handler') );

			add_action( 'wp_ajax_madara_user_upload_refresh_list', array( \MUUPRO_EditHandler::class, 'refresh_chapter_list') );
			add_action( 'wp_ajax_nopriv_madara_user_upload_refresh_list', array( \MUUPRO_EditHandler::class, 'refresh_chapter_list') );
			
			add_action('wp_ajax_muupro_get_chapter_info', array(\MUUPRO_EditHandler::class, 'get_chapter_info'));
			add_action('wp_ajax_nopriv_muupro_get_chapter_info', array(\MUUPRO_EditHandler::class, 'get_chapter_info'));
			
			add_action('wp_ajax_muupro_get_chapter_content', array(\MUUPRO_EditHandler::class, 'get_chapter_content'));
			add_action('wp_ajax_nopriv_muupro_get_chapter_content', array(\MUUPRO_EditHandler::class, 'get_chapter_content'));
			
			add_action('wp_ajax_muupro_save_chapter', array(\MUUPRO_EditHandler::class, 'save_chapter_info'));
			add_action('wp_ajax_nopriv_muupro_save_chapter', array(\MUUPRO_EditHandler::class, 'save_chapter_info'));
			
			add_action('wp_ajax_muupro_save_chapter_content', array(\MUUPRO_EditHandler::class, 'save_chapter_content'));
			add_action('wp_ajax_nopriv_muupro_save_chapter_content', array(\MUUPRO_EditHandler::class, 'save_chapter_content'));
			
			add_action( 'wp', array( \MUUPRO_EditHandler::class, 'save_edit_manga') );
			
        }
		
		function init(){
			// do something
			add_post_type_support('wp-manga', 'custom-fields');
		}
		
		function upload_manga_form( $atts, $content = ''){
			
			$html = '';
			
			if(muupro_current_user_can_upload()){
				ob_start();
				include MDR_USER_UPLOAD_PRO_DIR . '/template/shortcodes/upload_manga.php';
				$html = ob_get_contents();
				ob_end_clean();
			}
			
			return $html;
		}
		
		function upload_chapters_form( $atts, $content = ''){
			$html = '';
			
			if(muupro_current_user_can_upload()){
				ob_start();
				include MDR_USER_UPLOAD_PRO_DIR . '/template/shortcodes/upload_chapters.php';
				$html = ob_get_contents();
				ob_end_clean();
			}
			
			return $html;
		}
		
		function upload_novel_chapter_form( $atts, $content = ''){
			$html = '';
			
			if(muupro_current_user_can_upload()){
				ob_start();
				include MDR_USER_UPLOAD_PRO_DIR . '/template/shortcodes/upload_novel.php';
				$html = ob_get_contents();
				ob_end_clean();
			}
			
			return $html;
		}
		
        private function autoload(){

			$inc_files = array(
				'helper',
				'template-include',
				'admin-review-request',
				'ajax-handler',
				'add-manga',
				'upload-chapter',
				'ajax',
				'query-hooks',
				'user-upload-permission',
				'admin-pages',
				'edit-handler'
			);

            foreach( $inc_files as $file ){
                include_once( __DIR__ . '/inc/' . $file . '.php' );
            }

        }

        function wp_enqueue_scripts(){

			//if( muupro_is_user_upload_manga_page() ){
				wp_enqueue_style( 'madara_user_upload_pro_styles', MDR_USER_UPLOAD_PRO_URL . 'assets/styles.css' );
			//}
			
			wp_enqueue_script( 'madara-user-upload-pro-helpers', MDR_USER_UPLOAD_PRO_URL . '/assets/helpers.js', array( 'jquery' ), '1.0' );
			
			wp_enqueue_script( 'muupro-add-manga-form', MDR_USER_UPLOAD_PRO_URL . '/assets/add-manga.js', array( 'jquery', 'madara-user-upload-pro-helpers' ), '1.0' );
			
			wp_localize_script( 'muupro-add-manga-form', 'muupro_addmangaform', array(
				'url'      => admin_url('admin-ajax.php'),
				'messages' => array(
					'badge_maximum_characters'         => esc_html__( 'Maximum 5 characters for badge', MUUPRO_TEXTDOMAIN )
				),
			) );
			
			wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', array('jquery'), '4.0.5', true );
			
			wp_enqueue_script( 'dragula', 'https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.js', array( 'jquery' ), '3.7.2' );
			
			wp_enqueue_script( 'madara-user-upload-pro-helpers', MDR_USER_UPLOAD_PRO_URL . '/assets/helpers.js', array( 'jquery' ), '1.0' );
			
			$settings = muupro_get_user_upload_settings();
			if($settings['manga_type'] == 'manga'){
				wp_enqueue_script( 'muupro-chapter-form', MDR_USER_UPLOAD_PRO_URL . '/assets/upload-chapter.js', array( 'jquery', 'select2' ), '1.0' );
			} else {
				wp_enqueue_script( 'muupro-chapter-form', MDR_USER_UPLOAD_PRO_URL . '/assets/upload-novel.js', array( 'jquery', 'select2' ), '1.0' );
			}
			

			$nonce = wp_create_nonce( UserUploadChapter::$action );
			
			wp_localize_script( 'muupro-chapter-form', 'uploadChapter', array(
				'url'      => admin_url('admin-ajax.php'),
				'nonce'    => $nonce,
				'messages' => array(
					'remove_image'         => esc_html__( 'Remove image', MUUPRO_TEXTDOMAIN ),
					'confirm_remove_image' => esc_html__( 'Confirm to remove this image', MUUPRO_TEXTDOMAIN ),
					'missing_value'        => esc_html__( '%s cannot be empty', MUUPRO_TEXTDOMAIN ),
					'missing_files'        => esc_html__( 'Please select chapter images', MUUPRO_TEXTDOMAIN ),
					'invalid_number'       => esc_html__( 'Chapter number must be numeric', MUUPRO_TEXTDOMAIN ),
					'select_manga'         => esc_html__( 'Select manga', MUUPRO_TEXTDOMAIN ),
					'no_volume' 	   => esc_html__('-- No Volume --', MUUPRO_TEXTDOMAIN),
					'please_enter_volume' => esc_html__('If you want to create a new volume, please enter volume name', MUUPRO_TEXTDOMAIN),
				),
			) );
			
			if(muupro_is_manga_edit_page()){
				wp_enqueue_script( 'madara_user_edit_page_js', MDR_USER_UPLOAD_PRO_URL . 'assets/edit-page.js', array( 'jquery' ), '', true );
			}

        }

        function upload_settings_save(){

            if( isset( $_POST['user_upload_pro_settings'] ) ){
                $wp_manga_settings = get_option( 'wp_manga_settings', array() );
                $wp_manga_settings['user_upload_pro_settings'] = array(
					'add_user_settings' => isset( $_POST['user_upload_pro_settings']['add_user_settings'] ) ? $_POST['user_upload_pro_settings']['add_user_settings'] : 'yes',
                    'user_upload_message'     => isset( $_POST['user_upload_pro_settings']['user_upload_message'] ) ? $_POST['user_upload_pro_settings']['user_upload_message'] : '',
                    'default_storage'         => $_POST['user_upload_pro_settings']['default_storage'],
                    'chapter_type_permission' => isset( $_POST['user_upload_pro_settings']['chapter_type_permission'] ) ? $_POST['user_upload_pro_settings']['chapter_type_permission'] : 0,
                    'post_status'             => $_POST['user_upload_pro_settings']['post_status'],
                    'user_roles'              => isset( $_POST['user_upload_pro_settings']['user_roles'] ) ? $_POST['user_upload_pro_settings']['user_roles'] : array(),
					'edit_manga' => isset( $_POST['user_upload_pro_settings']['edit_manga'] ) ? $_POST['user_upload_pro_settings']['edit_manga'] : 'no',
					'manga_type' => $_POST['user_upload_pro_settings']['manga_type']
                );
                $resp = update_option( 'wp_manga_settings', $wp_manga_settings );
            }

        }

        public function load_plugin_textdomain() {
            load_plugin_textdomain( MUUPRO_TEXTDOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );
		}

    }

    if( class_exists( 'WP_MANGA' ) ){
		require_once('admin/settings-page.php');

		 $license_key = get_option(MDR_USER_UPLOAD_PRO_LICENSE_KEY);
		 if ($license_key) {
			MadaraUserUploadPro::get_instance();
		 } else {
		     add_action('admin_notices', 'madara_user_upload_pro_admin_notice__warning');
		 }
	}