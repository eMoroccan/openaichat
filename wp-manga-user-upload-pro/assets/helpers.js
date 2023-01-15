
	var $ = jQuery;

	function formAlert(parent, message, alertType) {

		var submitRow = parent.find('.submit-row');
		
		if( submitRow.length ){
			
			submitRow.find('.alert').remove();

			switch (alertType) {
				case 'error':
					alertClass = 'alert-danger';
					message = '<i class="fa fa-exclamation-triangle"></i>' + message;
					break;
				case 'success':
					alertClass = 'alert-success';
					message = '<i class="far fa-check-square"></i>' + message;
					break;
				default:
					alertClass = '';
					message = '';
			}

			var alert = '<div class="alert ' + alertClass + '" role="alert">';
			alert += message;
			alert += '</div>';

			var $alert = $(alert);

			$alert.prependTo(submitRow);
			formScrollTo($alert);
		}

	}

	function removeAlert($parent){
		$parent.find('.alert').remove();
	}

	function formScrollTo( $element ){
		$([document.documentElement, document.body]).animate({
			scrollTop: $element.offset().top - (window.innerHeight / 2)
		}, 500);
	}

	var userUploadLoading = $('#muu-loading-screen');

	userUploadLoading.showLoading = function () {
		this.show();
		this.scrollToLoading();
	};

	userUploadLoading.hideLoading = function () {
		this.hide();
	};

	userUploadLoading.scrollToLoading = function () {
		formScrollTo( $("#muu-loading-screen .loading-icon") );
	};

