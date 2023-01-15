<?php
	
	if( ! isset( $loading_inner ) ){
		$loading_inner = '<i class="fas fa-spinner fa-4x fa-spin"></i>';
	}

?>
<div id="muu-loading-screen" style="display:none;">
	<div class="loading-screen-content">
		<div class="loading-icon">
			<?php echo $loading_inner; ?>
		</div>
	</div>
</div>