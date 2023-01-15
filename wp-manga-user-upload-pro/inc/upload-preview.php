<?php

    if( !class_exists( 'MADARA_ZIP_VALIDATION' ) ){
        return;
    }

    class MUUPRO_ZIP_PREVIEW extends MADARA_ZIP_VALIDATION{

        function __construct(){
            add_action( 'wp_ajax_zip_file_preview', array( $this, 'zip_file_validate') );
            add_action( 'wp_ajax_nopriv_zip_file_preview', array( $this, 'zip_file_validate') );
        }

        function zip_file_validate(){

            if( !isset( $_FILES['zip_file'] ) ){
                wp_send_json_error('Missing Zip File');
            }

            if( !isset( $_POST['chapter_type'] ) ){
                wp_send_json_error('Missing Chapter Type');
            }

            $zip_file = $_FILES['zip_file']['tmp_name'];
            $chapter_type = $_POST['chapter_type'];

            $is_valid = self::is_zip_valid( $zip_file, $chapter_type );
            if( $is_valid['is_valid'] && $is_valid['data']['zip_type'] !== 'single_chapter' ){

                $zip_data = $is_valid['data']['data'];

                $returnHTML = '';
                $returnHTML .= '<ul>';
                foreach( $zip_data as $key_1 => $value_1 ){

                    $returnHTML .= '<li>';
                    $returnHTML .= esc_html( $key_1 );

                    if( $is_valid['data']['zip_type'] == 'multi_chapters_with_volumes' ){
                        $returnHTML .= '<ul>';
                        foreach( $value_1 as $key_2 => $value_2 ){
                            $returnHTML .= esc_html( $key_2 );
                        }
                        $returnHTML .= '</ul>';
                    }

                    $returnHTML .= '</li>';

                }
                $returnHTML .= '</ul>';

                $is_valid['data']['data'] = $returnHTML;

                wp_send_json_success( $is_valid['data'] );
            }elseif( is_array($is_valid['data']) && $is_valid['data']['zip_type'] == 'single_chapter' ){
                wp_send_json_error( esc_html__('This zip file only contains single chapter, please upload a valid zip file for multi chapters.', MUUPRO_TEXTDOMAIN) );
            }else{
                wp_send_json_error( $is_valid['message'] );
            }
        }
    }

    new MUUPRO_ZIP_PREVIEW();
