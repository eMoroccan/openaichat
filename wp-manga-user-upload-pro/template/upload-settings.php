<?php

    if( !defined( 'ABSPATH' ) ){
        exit;
    }

    if( !isset( $GLOBALS['wp_manga'] ) ){
        return;
    }

    $user_upload_pro__settings = muupro_get_user_upload_settings();
	
    extract( $user_upload_pro__settings );

    $all_user_roles = get_editable_roles();
	
	$add_user_settings = isset($add_user_settings) ? $add_user_settings : 'yes';

?>
<div class="section">
<h2 class="title"><?php esc_html_e( 'Manga User Upload PRO - Settings', MUUPRO_TEXTDOMAIN ); ?></h2>
<table class="form-table">
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Enable Upload Form in "User Settings" page', MUUPRO_TEXTDOMAIN ); ?>
        </th>
        <td>
            <p>
                <select name="user_upload_pro_settings[add_user_settings]" value="<?php echo esc_attr(  $add_user_settings ); ?>">
                    <option value="yes" <?php selected( $add_user_settings, 'yes' ); ?>><?php esc_html_e('Yes', MUUPRO_TEXTDOMAIN); ?></option>
                    <option value="no" <?php selected( $add_user_settings, 'no' ); ?>><?php esc_html_e('No', MUUPRO_TEXTDOMAIN); ?></option>
                </select>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php esc_html_e( 'Message', MUUPRO_TEXTDOMAIN ); ?>
        </th>
        <td>
            <p>
                <textarea name="user_upload_pro_settings[user_upload_message]" style="width: 500px;" rows="3"><?php echo wp_kses_post( $message ); ?></textarea>
                <br />
                <span class="description"> <?php esc_html_e( 'This message will be displayed in the top of user upload form in "User Settings"', MUUPRO_TEXTDOMAIN); ?> </span>
            </p>
        </td>
    </tr>
	
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Manga (Images Chapter) or Novel/Drama (Text Chapter)?', MUUPRO_TEXTDOMAIN ); ?>
        </th>
        <td>
            <p>
                <select name="user_upload_pro_settings[manga_type]">
                    <option value="manga" <?php selected( $manga_type, 'manga' ); ?>><?php esc_html_e('Manga'); ?></option>
					<option value="text" <?php selected( $manga_type, 'text' ); ?>><?php esc_html_e('Text/Novel'); ?></option>
					<option value="video" <?php selected( $manga_type, 'video' ); ?>><?php esc_html_e('Video/Drama'); ?></option>
                </select>
                <br />
                <span class="description"> <?php esc_html_e( 'What is your site\'s main Manga Type? This will be used to show the correct Upload Chapters form in User Settings page', MUUPRO_TEXTDOMAIN); ?> </span>
            </p>
        </td>
    </tr>
	
    <tr>
        <th scope="row">
            <?php esc_html_e( 'Manga - Upload Default Storage', MUUPRO_TEXTDOMAIN ); ?>
        </th>
        <td>
            <p>
                <select name="user_upload_pro_settings[default_storage]">
                    <?php
                        $available_host = $GLOBALS['wp_manga']->get_available_host();
                        foreach( $available_host as $host ) {
                            ?>
                    <option value="<?php echo esc_attr( $host['value'] ); ?>" <?php selected( $default_storage, $host['value'] ); ?>><?php echo esc_attr( $host['text'] ); ?></option>
                            <?php
                        }
                    ?>
                </select>
                <br />
                <span class="description"> <?php esc_html_e( 'Change default storage to upload Manga (Images Chapter)', MUUPRO_TEXTDOMAIN); ?> </span>
            </p>
        </td>
    </tr>
    
    <tr>
        <th scope="row">
            <?php esc_html_e( 'Default Manga Post Status', MUUPRO_TEXTDOMAIN ); ?>
        </th>
        <td>
            <p>
                <select name="user_upload_pro_settings[post_status]" value="<?php echo esc_attr( $post_status ); ?>">
                    <option value="publish" <?php selected( $post_status, 'publish' ); ?>><?php esc_html_e('Publish', MUUPRO_TEXTDOMAIN); ?></option>
                    <option value="pending" <?php selected( $post_status, 'pending' ); ?>><?php esc_html_e('Pending', MUUPRO_TEXTDOMAIN); ?></option>
                </select>
                <br />
                <span class="description"> <?php _e( 'Change default post status for front-end upload Manga', MUUPRO_TEXTDOMAIN); ?> </span>
            </p>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <?php esc_html_e( 'Users Roles to upload', MUUPRO_TEXTDOMAIN ); ?>
        </th>
        <td>
            <p>
                <?php foreach( $all_user_roles as $role => $role_data ){ ?>
                    <input type="checkbox" name="user_upload_pro_settings[user_roles][]" value="<?php echo esc_attr( $role ); ?>" <?php echo in_array( $role, $user_roles ) || empty( $user_roles ) ? 'checked' : ''; ?> ><?php echo esc_html( $role_data['name'] ); ?><br>
                <?php } ?>
                <br />
                <span class="description"> <?php esc_html_e( 'Select User Roles who are allowed to upload Manga in Front-end', MUUPRO_TEXTDOMAIN); ?> </span>
            </p>
        </td>
    </tr>
	
	<tr>
        <th scope="row">
            <?php esc_html_e( 'Enable "Edit Manga" page in Front-end', MUUPRO_TEXTDOMAIN ); ?>
        </th>
        <td>
            <p>
                <select name="user_upload_pro_settings[edit_manga]" value="<?php echo esc_attr(  $edit_manga ); ?>">
                    <option value="yes" <?php selected( $edit_manga, 'yes' ); ?>><?php esc_html_e('Yes', MUUPRO_TEXTDOMAIN); ?></option>
                    <option value="no" <?php selected( $edit_manga, 'no' ); ?>><?php esc_html_e('No', MUUPRO_TEXTDOMAIN); ?></option>
                </select>
				<br />
                <span class="description"> <?php esc_html_e( 'Manga owner and Editor, Administrator roles can edit Manga', MUUPRO_TEXTDOMAIN); ?> </span>
            </p>
        </td>
    </tr>
</table>
</div>
<?php
