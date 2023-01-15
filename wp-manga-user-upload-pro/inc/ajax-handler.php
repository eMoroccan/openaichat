<?php
	
	namespace MadaraUserUploadPro;

	class AjaxHandler{

		protected static $action;

		public static function send_error( $message = '', $nonce = false ){
			if( $nonce ){
				wp_send_json_error([
					'message' => $message,
					'nonce'   => self::gen_nonce(),
				]);
			}else{
				wp_send_json_error([
					'message' => $message
				]);
			}
		}

		public static function send_success( $message = '', $nonce = false, $data = null ){
			if( $nonce ){
				wp_send_json_success([
					'message' => $message,
					'nonce'   => self::gen_nonce(),
					'data' => $data
				]);
			}else{
				wp_send_json_success([
					'message' => $message,
					'data' => $data
				]);
			}
		}

		public static function verify_nonce( $nonce ){
			return wp_verify_nonce( $nonce, static::$action );
		}

		public static function gen_nonce(){
			return wp_create_nonce( static::$action );
		}

	}

?>