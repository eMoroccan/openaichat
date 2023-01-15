<?php
	
	namespace MadaraUserUploadPro;

	wp_enqueue_script( 'admin-review-requests', MDR_USER_UPLOAD_PRO_URL . '/assets/admin-review-requests.js', array( 'jquery' ) );
	wp_localize_script( 'admin-review-requests', 'admin_review_requests', array(
		'url'   => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'admin_review_requests_nonce' )
	) );


?>
<style>
	td.actions.column-actions .button {
		margin-right: 10px;
	}
</style>
<div class="wrap wp-manga-studio admin-filter">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Review Requests', MUUPRO_TEXTDOMAIN ) ?></h1>
	
	<?php
		$table = new Review_Request_List_Table();
		$table->prepare_items();
		$table->display();
	?>
		
</div>
