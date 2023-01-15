jQuery(document).ready(function($){

	$(document).on('click', '.request-action', function(e){

		var $self      = $(this),
		    action     = $self.data('action'),
		    selfHTMl   = $self.html(),
		    $thisRow   = $self.parents('tr'),
		    $tdActions = $self.parents('.column-actions'),
		    $buttons   = $tdActions.find('.button');
		
		$.ajax({
			url: admin_review_requests.url,
			method: 'POST',
			data: {
				action: 'back_end_' + action + '_request',
				nonce: admin_review_requests.nonce,
				type: $self.data('type'),
				id: $self.data('id'),
			},
			beforeSend: function(){
				$buttons.attr('disabled', 'disabled');
				$tdActions.prepend( '<img src="images/spinner.gif">' );
			},
			success: function( resp ){
				
				if( resp.success ){

					$thisRow.fadeOut();
					$thisRow.remove();

					alert( resp.data.message );

					if( ! $('table.user-upload-review-requests tbody tr').length ){
						location.reload();
					}

				}else{
					alert( resp.data.message );
				}

				if( typeof resp.data.nonce != 'undefined' ){
					admin_review_requests.nonce = resp.data.nonce;
				}

			},
			complete: function( jqXHR ){
				$tdActions.find('img').remove();
				$buttons.removeAttr('disabled');
				if( jqXHR.status !== 200 ){
					alert( 'ERROR ' + jqXHR.status );
				}
			}
		});

	});
	
});