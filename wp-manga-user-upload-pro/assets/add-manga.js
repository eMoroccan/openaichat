jQuery(document).ready(function($){

	var _URL = window.URL || window.webkitURL;

	// Ajax handlers
	var userUploadForm = $('#madara-user-upload-pro-form-submit-manga');

	userUploadForm.alertSuccess = function (message) {
		formAlert(userUploadForm, message, 'success');
	};

	userUploadForm.alertError = function (message) {
		formAlert(userUploadForm, message, 'error');
	};

	userUploadForm.removeAlert = function(){
		removeAlert(userUploadForm);	
	};
	
	$('#madara-user-upload-pro-form-submit-manga input[name="madara-featured-image"]').change(function (e) {

		var file, img, imgWidth, imgHeight, errMsg;

		var $self = $(this),
			messages = $(this).parents('.featured-image-field').data('messages');

		if ((file = this.files[0])) {
			
			if (file.type.indexOf('image/') === -1 ){
				errMsg = messages.invalid_type;
				userUploadForm.alertError(errMsg);
				$self.val('');
			}else{
				img = new Image();
				
				img.onload = function () {
					
					var $img = $(img);

					var ratio = this.width / this.height;
					if( ratio !== 1/1.5 ){
						if( ratio < 1/1.5 ){
							$img.css({
								"width": '100%',
								"max-height": 'unset'
							});
						}else{
							$img.css({
								"height": '100%',
								"max-width": 'unset'
							});
						}
					} 
					
					if( this.width < 300 || this.height < 450 ){
						errMsg = messages.invalid_resolution;
					}else{
						$('.featured-image-preview img').replaceWith($img);
					}

					if (errMsg) {
						userUploadForm.alertError(errMsg);
						$self.val('');
					}
				};
				
				img.src = _URL.createObjectURL(file);
				userUploadForm.removeAlert();
				
			}
		}

	});
	
	if($('#madara-user-upload-pro-form-submit-manga input[name="madara-horizontal-thumb"]').length > 0){
		$('#madara-user-upload-pro-form-submit-manga input[name="madara-horizontal-thumb"]').change(function (e) {

			var file, img, imgWidth, imgHeight, errMsg;

			var $self = $(this),
				messages = $(this).parents('.horizontal-thumb-field').data('messages');

			if ((file = this.files[0])) {
				
				if (file.type.indexOf('image/') === -1 ){
					errMsg = messages.invalid_type;
					userUploadForm.alertError(errMsg);
					$self.val('');
				}else{
					img = new Image();
					
					img.onload = function () {
						
						var $img = $(img);

						var ratio = this.width / this.height;
						if( ratio !== 1.5 ){
							if( ratio < 1.5 ){
								$img.css({
									"height": '100%',
									"max-width": 'unset'
								});
							}else{
								$img.css({
									"width": '100%',
									"max-height": 'unset'
								});
							}
						} 
						
						if( this.width < 450 || this.height < 300 ){
							errMsg = messages.invalid_resolution;
						}else{
							$('.horizontal-thumb-preview img').replaceWith($img);
						}

						if (errMsg) {
							userUploadForm.alertError(errMsg);
							$self.val('');
						}
					};
					
					img.src = _URL.createObjectURL(file);
					userUploadForm.removeAlert();
				}
			}

		});
	}

	$(document).on('click', '#madara-user-upload-pro-form-submit-manga button[type="submit"]', function (e) {

		var checkFormValidity = userUploadForm[0].checkValidity();

		if (checkFormValidity) {
			var title           = userUploadForm.find('input[name="madara-manga-title"]').val(),
			    authors         = userUploadForm.find('input[name="madara-manga-authors"]').val(),
			    artists         = userUploadForm.find('input[name="madara-manga-artists"]').val(),
			    description     = userUploadForm.find('textarea[name="madara-manga-description"]').val(),
			    alternativeName = userUploadForm.find('textarea[name="madara-manga-alternative-name"]').val(),
			    type            = userUploadForm.find('select[name="madara-manga-type"]').val() ? userUploadForm.find('select[name="madara-manga-type"]').val() : userUploadForm.find('input[name="madara-manga-type"]').val(),
				release = userUploadForm.find('input[name="madara-manga-release"]').val()
				adult            = userUploadForm.find('select[name="madara-manga-adult"]').val(),
				badge            = userUploadForm.find('select[name="madara-manga-badge"]').val(),
			    status          = userUploadForm.find('select[name="madara-manga-status"]').val()
			    nonce           = userUploadForm.find('input[name="nonce"]').val();

			var genres = $('input[name="madara-manga-genres[]"]:checked').map(function () {
				return this.value;
			}).get();
			
			var tags = $('input[name="madara-manga-tags[]"]:checked').map(function () {
				return this.value;
			}).get();
			
			if(badge && badge.length > 5) {
				userUploadForm.alertError(muupro_addmangaform.messages.badge_maximum_characters);
				return;
			}

			var formData = new FormData();

			formData.append('action', 'front_end_create_manga');
			formData.append('title', title);
			if(description){
				formData.append('description', description);
			}
			if(alternativeName){
				formData.append('alternativeName', alternativeName);
			}
			if(type){
				formData.append('type', type);
			}
			if(adult){
				formData.append('adult', adult);
			}
			if(status){
				formData.append('status', status);
			}
			if(artists){
				formData.append('artists', artists);
			}
			if(authors){
				formData.append('authors', authors);
			}
			if(genres){
				formData.append('genres', genres);
			}
			if(tags){
				formData.append('tags', tags);
			}
			
			formData.append('nonce', nonce);
			if(badge){
				formData.append('badge', badge);
			}
			if(release){
				formData.append('release', release);
			}

			var featuredImage = userUploadForm.find('input[type="file"][name="madara-featured-image"]');
			if (featuredImage.length > 0) {
				featuredImage = featuredImage[0].files[0];
				formData.append('featuredImage', featuredImage);
			}
			
			var horizontalThumb = userUploadForm.find('input[type="file"][name="madara-horizontal-thumb"]');
			if (horizontalThumb.length > 0) {
				horizontalThumb = horizontalThumb[0].files[0];
				formData.append('horizontalThumb', horizontalThumb);
			}
			
			$('#muu-loading-screen').show();
			
			// enable hook
			window.muupro_uploadmanga = formData;
			userUploadForm.trigger('muupro_before_upload_manga', formData);
			// update formdata
			if(typeof window.muupro_uploadmanga !== 'undefined') formData = window.muupro_uploadmanga;

			$.ajax({
				url: manga.ajax_url,
				type: 'POST',
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
				enctype: 'multipart/form-data',
				beforeSend: function () {
					addDisabled();
					userUploadLoading.showLoading();
				},
				success: function (response) {
					if (response.success) {
						userUploadForm.alertSuccess( response.data.message );
						resetInput();

						var featureImg = $('.featured-image-preview');
						featureImg.find('img').replaceWith('<img />');
						
						// redirect to upload chapters
						if($('#madara-user-upload-pro-form-submit-manga').data('redirect')){
							location.href = insertParam($('#madara-user-upload-pro-form-submit-manga').data('redirect'), 'id', response.data.data);
						}
					} else {
						userUploadForm.alertError(response.data.message);
					}

					if( typeof response.data.nonce !== 'undefined' ){
						userUploadForm.find('input[name="nonce"]').val( response.data.nonce);
					}
				},
				complete: function (jqXHR) {
					removeDisabled();
					userUploadLoading.hideLoading();
					if( jqXHR.status != 200 ){
						userUploadForm.alertError('ERROR ' + jqXHR.status);
					}
					
					$('#muu-loading-screen').hide();
				}
			});
		}

	});

	userUploadForm.on('submit', function (e) {
		e.preventDefault();
	});

	function addDisabled() {
		userUploadForm.find('input').each(function (i, e) {
			$(this).attr('disabled', 'disabled');
		});
		userUploadForm.find('select').each(function (i, e) {
			$(this).attr('disabled', 'disabled');
		});
		userUploadForm.find('textarea').each(function (i, e) {
			$(this).attr('disabled', 'disabled');
		});
	}

	function removeDisabled() {
		userUploadForm.find('input').each(function (i, e) {
			$(this).removeAttr('disabled');
		});
		userUploadForm.find('select').each(function (i, e) {
			$(this).removeAttr('disabled');
		});
		userUploadForm.find('textarea').each(function (i, e) {
			$(this).removeAttr('disabled');
		});
	}

	function resetInput() {

		userUploadForm.find('input, textarea, select').each(function (i, e) {
			
			//by pass the submit input
			if ($(this).is('#madara-upload-submit')) {
				return true;
			}
			//reset checkbox
			if ($(this).attr('type') == 'checkbox') {
				$(this).prop('checked', false);
				return true;
			}

			$(this).val('');
		});

	}
	
	function insertParam(url, key, value)
	{
		key = encodeURI(key); value = encodeURI(value);

		if(url.indexOf('?') != -1) url = url + '&' + key + '=' + value;
		else url = url + '?' + key + '=' + value;
		
		return url;
	}

});	