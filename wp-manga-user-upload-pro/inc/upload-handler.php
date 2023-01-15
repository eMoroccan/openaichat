<?php

    class MUUPRO_UploadHandler{

        static function upload_handler(){

            if( ! class_exists('WP_MANGA') ){
                return;
            }

            if( ! is_user_logged_in() || ! current_user_can_upload() ){
                return;
            }

            if( empty( $_FILES ) ){
                return;
            }

            $default_values = array(
                'title'           => '',
                'description'     => '',
                'featuredImage'   => '',
                'alternativeName' => '',
                'type'            => '',
                'status'          => '',
                'releaseYears'    => '',
                'authors'         => '',
                'artists'         => '',
                'genres'          => '',
                'tags'            => '',
                'chapterType'     => '',
                'userID'          => get_current_user_id(),
            );

            $data = array_merge( $default_values, $_POST );

            $options = muu_get_user_upload_settings();
			
            if( !empty( $data['chapterType'] ) ){
                $chapter_type = $data['chapterType'];
            }elseif( isset( $options['chapter_type'] ) ){
                $chapter_type = $options['chapter_type'];
            }else{
                wp_send_json_error( esc_html__('Missing Chapter Type', MUUPRO_TEXTDOMAIN ) );
            }

            $data['post_status'] = apply_filters( 'muu_post_status', isset( $options['post_status'] ) ? $options['post_status'] : 'pending', $data );

            //1.create manga post
            $post_id = MadaraUploadHandler::create_post( $data );

            if( ! $post_id ){
                wp_send_json_error( esc_html__('There was something wrong, please contact Website Administrator for more information', MUUPRO_TEXTDOMAIN) );
            }

            // Check if zip file contains invalid file
            $validation = MADARA_ZIP_VALIDATION::get_zip_structure( $_FILES['mangaFile']['tmp_name'] );

            if( is_wp_error( $validation ) ){
                return wp_send_json_error( $validation->get_error_message() );
            }

            //2. start extracting and save manga data
            if( $chapter_type == 'manga' ){
                $storage = isset( $options['default_storage'] ) ? $options['default_storage'] : 'local';

                $response = MadaraUploadHandler::upload_manga_file_type( $post_id, $_FILES['mangaFile'], $storage );
            }else{
                $response = MadaraUploadHandler::upload_content_file_type( $post_id, $_FILES['mangaFile'] );
            }

            if( !empty( $response['success'] ) ){
                $message = __('<strong>Upload Successfully!</strong><br />');

                if( $data['post_status'] == 'publish' ){
                    $message .=  wp_kses_post(
                        sprintf(
                            __( 'Your post is now published in <a href="%s">here</a>', MUUPRO_TEXTDOMAIN ),
                            get_the_permalink( $post_id )
                        )
                    );
                }else{
                    $message .= wp_kses_post(
                        sprintf(
                            __( 'Your post is pending, please wait for website admin approve and pubish your post. Meanwhile, you can preview your post in <a href="%s">here</a>', MUUPRO_TEXTDOMAIN ),
                            get_the_permalink( $post_id )
                        )
                    );
                }

                if( method_exists('WP_MANGA_FUNCTIONS', 'update_latest_meta') ){
                    global $wp_manga_functions;
                    $wp_manga_functions->update_latest_meta( $post_id );
                }

                wp_send_json_success( array(
                    'message'    => $message
                ) );

            }else{
                wp_send_json_error( esc_html__('Upload Failed', MUUPRO_TEXTDOMAIN) );
            }

        }

        static function create_post( $args ){

            $args = apply_filters( 'muu_manga_create_args', $args );

            if( ! $args ){
                return false;
            }

            //1. insert post main data
            $post_args = array(
                'post_author'  => !empty( $args['userID'] ) ? $args['userID'] : '',
                'post_title'   => !empty( $args['title'] ) ? $args['title'] : '',
                'post_content' => !empty( $args['description'] ) ? $args['description'] : '',
                'post_type'    => 'wp-manga',
                'post_status' => isset( $args['post_status'] ) ? $args['post_status'] : 'pending',
            );
            
            $post_id = wp_insert_post( $post_args );

            if( ! $post_id && is_wp_error( $post_id ) ){
                wp_send_json_error( esc_html__('Create Post failed', MUUPRO_TEXTDOMAIN) );
            }

            //2. add metadata
            $thumb_id = MadaraUploadHandler::upload_featured_image( 'featuredImage', $post_id );

            $meta_data = array(
                '_thumbnail_id'               => $thumb_id,
                '_wp_manga_alternative'  => isset( $args['alternativeName'] ) ? $args['alternativeName'] : '',
                '_wp_manga_type'         => isset( $args['type'] ) ? $args['type'] : '',
                '_wp_manga_status'       => isset( $args['status'] ) ? $args['status'] : '',
                '_wp_manga_chapter_type' => isset( $args['chapterType'] ) ? $args['chapterType'] : 'manga',
            );

            foreach( $meta_data as $key => $value ){
                update_post_meta( $post_id, $key, $value );
            }

            //3.update terms
            $manga_terms = array(
                'wp-manga-release' => isset( $args['releaseYears'] ) ? $args['releaseYears'] : null,
                'wp-manga-author'      => isset( $args['authors'] ) ? $args['authors'] : null,
                'wp-manga-artist'      => isset( $args['artists'] ) ? $args['artists'] : null,
                'wp-manga-genre'       => isset( $args['genres'] ) ? $args['genres'] : '',
                'wp-manga-tag'         => isset( $args['tags'] ) ? $args['tags'] : null,
            );

            foreach( $manga_terms as $tax => $term ){

                if( empty( $term ) ){
                    continue;
                }
                $resp = MadaraUploadHandler::add_manga_terms( $post_id, $term, $tax );
            }

            do_action( 'muu_manga_created', $post_id, $args );

            return $post_id;

        }

        static function upload_attachment( $form_POST_file_key, $post_id ){

            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $thumb_id = media_handle_upload( $form_POST_file_key, $post_id );

            if( !$thumb_id && is_wp_error( $thumb_id ) ){
                return false;
            }

            return $thumb_id;

        }

        static function add_manga_terms( $post_id, $terms, $taxonomy ){

            $terms = explode(',', $terms);

            // foreach( $terms as $current_term ){
            //     //check if term is exist
            //     $term = term_exists( $current_term, $taxonomy );
            //
            //     //then add if it isn't
            //     if( ! $term || is_wp_error( $term ) ){
            //         $term = wp_insert_term( $current_term, $taxonomy );
            //         if( !is_wp_error( $term ) && isset( $term['term_id'] ) ){
            //             $term = intval( $term['term_id'] );
            //
            //         }else{
            //             continue;
            //         }
            //     }else{
            //         $term = intval( $term['term_id'] );
            //     }
            //
            //     $output_terms[] = $term;
            // }

            if( empty( $terms ) ){
                return false;
            }

            $resp = wp_set_post_terms( $post_id, $terms, $taxonomy );

            return $resp;

        }

        static function upload_manga_file_type( $post_id, $manga_file, $storage ){

            if( !class_exists('WP_MANGA') ){
                return;
            }

            global $wp_manga_storage;

            return $wp_manga_storage->manga_upload( $post_id, $manga_file, $storage );

        }

        static function upload_content_file_type( $post_id, $zip_file){

            if( !class_exists('WP_MANGA') ){
                return;
            }

            global $wp_manga_text_type;

    		return $wp_manga_text_type->upload_handler( $post_id, $zip_file );

        }
    }
