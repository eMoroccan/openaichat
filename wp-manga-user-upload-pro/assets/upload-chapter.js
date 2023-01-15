jQuery(document).ready(function($){

	var _URL = window.URL || window.webkitURL;

	var chapterFiles = [];

	var $ = jQuery;
	
	var uploadForm = $('#madara-user-upload-pro-form');

	uploadForm.alertSuccess = function (message) {
		formAlert(uploadForm, message, 'success');
	};

	uploadForm.alertError = function (message) {
		formAlert(uploadForm, message, 'error');
	};

	$('input[type="file"][name="chapter-images[]"]').on('change', function () {
		if (this.files.length > 0) {

			$(this.files).each(function (i, e) {
				
				var fileIndex = chapterFiles.push(e);
				
				var html = '';
				html += '<li class="col-md-4">';
				html += '<div class="chapter-single-image chapter-item" data-file-index="' + fileIndex + '">';
				html += '<span class="remove-image" title="' + uploadChapter.messages.remove_image + '"><i class="fas fa-times-circle"></i></span>';
				html += '<img src="' + _URL.createObjectURL(e) + '">';
				html += '</div>';
				html += '</li>';

				$(html).insertBefore('#chapter-image-files li:last-child');

			});

			// make it sortable
			dragula([document.getElementById('chapter-image-files')]);
		}
	});

	$(document).on( 'click', '.remove-image', function(e){
		
		e.preventDefault();

		if (confirm(uploadChapter.messages.confirm_remove_image)) {
			var $this = $(this),
				fileIndex = $this.parents('.chapter-single-image').data('file-index'),
				liItem = $this.parents('li');
			
			// remove from chapterFiles
			chapterFiles = chapterFiles.filter(function (value, index) {
				return index !== (fileIndex - 1);
			});

			liItem.remove();
		}
	} );
	
	$('#select-manga').on('change', function(){
		$.ajax({
			url: uploadChapter.url,
			data: {
				manga: $(this).val(),
				action: 'muupro_get_volumes'
			},
			method: 'POST',
			success: function(resp){
				if( resp.success ){
					var options = '<option value="0">' + uploadChapter.messages.no_volume + '</option>';
					for(var i = 0; i < resp.data.length; i++ ){
						options += '<option value="' + resp.data[i].value + '">' + resp.data[i].text + '</option>';
					}
					
					$('#chapter-volume').html(options); 
					
					$('#chapter-volume').select2({});
					
				}
			},
			complete: function( jqXHR ){
				// do something
			}
		});
	});
	
	$('#chapter-create-volume').on('change', function(){
		if($(this).prop('checked')){
			$('#grp-new-volume-name').show();
			$('#chapter-volume').prop('disabled', true);
			$('#group-select-volume').addClass('disabled');
		} else {
			$('#grp-new-volume-name').hide();
			$('#chapter-volume').prop('disabled', false);
			$('#group-select-volume').removeClass('disabled');
		}
	});

	// FORM HANDLE
	uploadForm.on('submit', function(e){
		
		e.preventDefault();

		var manga = uploadForm.find('select[name="manga"]'),
			chapterNumber = uploadForm.find('input[name="chapter-number"]'),
			chapterTitle = uploadForm.find('input[name="chapter-title"]'),
			chapterExtendname = uploadForm.find('input[name="chapter-extendname"]');

		var numberReg = /^\d+$/;

		if( ! numberReg.test( chapterNumber.val() ) ){
			uploadForm.alertError(uploadChapter.messages.invalid_number);
			return false;
		}

		var uploadFormData = new FormData();
		var errMsg = '';

		[ manga, chapterNumber ].forEach(function(e,i){
			if( e.val() == '' ){
				errMsg = uploadChapter.messages.missing_value.replace('%s', e.parents('.form-group').find('label').text());
				return false;
			}else{
				uploadFormData.set( e.attr('name'), e.val() );
			}
		});
		
		var volume_name = '';
		var volume_id = 0;
		if($('#chapter-create-volume').length > 0 && $('#chapter-create-volume').prop('checked')){
			volume_name = $('#chapter-new-volume').val();
			if(volume_name == ''){
				errMsg = uploadChapter.messages.please_enter_volume;
			}
		} else {
			volume_id = $('#chapter-volume').length > 0 ? $('#chapter-volume').val() : 0;
		}
		
		uploadFormData.set('volume_name', volume_name);
		uploadFormData.set('volume_id', volume_id);

		if( errMsg !== '' ){
			uploadForm.alertError( errMsg );
			return false;
		}

		var chapterImages = $('.chapter-single-image');
		if( chapterImages.length == 0 ){
			uploadForm.alertError(uploadChapter.messages.missing_files);
			return false;
		}else{
			chapterImages.each(function(i,e){
				var $self = $(e);
				var fileIndex = $self.data('file-index');

				fileIndex--;

				if (typeof chapterFiles[ fileIndex ] !== 'undefined' ){
					uploadFormData.append( 'file_' + fileIndex, chapterFiles[ fileIndex ] );
				}
			});
		}

		uploadFormData.set('action', 'front_end_upload_chapter');
		uploadFormData.set('nonce', uploadChapter.nonce);
		uploadFormData.set('chapter-title', chapterTitle.val() ? chapterTitle.val() : '');
		uploadFormData.set('chapter-extendname', chapterExtendname.val() ? chapterExtendname.val() : '');
		
		uploadFormData.set('chapter-seo', uploadForm.find('input[name="chapter-seo"]').length > 0 ? uploadForm.find('input[name="chapter-seo"]').val() : '');
		uploadFormData.set('chapter-warning-text', uploadForm.find('input[name="chapter-warning-text"]').length > 0 ? uploadForm.find('input[name="chapter-warning-text"]').val() : '');
		
		// enable hook
		window.muupro_uploadchapters = uploadFormData;
		uploadForm.trigger('muupro_before_upload_chapters', uploadFormData);
		// update formdata
		if(typeof window.muupro_uploadchapters !== 'undefined') uploadFormData = window.muupro_uploadchapters;
		
		$('#muu-loading-screen').show();
		$.ajax({
			url: uploadChapter.url,
			data: uploadFormData,
			contentType: false,
			processData: false,
			enctype: 'multipart/form-data',
			method: 'POST',
			xhr: function () {
				
				var xhr = new window.XMLHttpRequest();
				var percentIcon = userUploadLoading.find('path.circle'),
					percentText = userUploadLoading.find('text.percentage');
				//Upload progress
				xhr.upload.addEventListener("progress", function (evt) {
					if (evt.lengthComputable) {
						var percentComplete = (evt.loaded / evt.total) * 100;
						percentComplete = percentComplete.toFixed();
						percentIcon.attr('stroke-dasharray', percentComplete + ', 100');
						percentText.text( percentComplete + '%' );
					}
				}, false);
				
				return xhr;

			},
			beforeSend: function(){
				userUploadLoading.showLoading();
			},
			success: function(resp){
				if( resp.success ){
					uploadForm.alertSuccess(resp.data.message);

					// reset the form
					uploadForm.find('select,input').val('').trigger('change');
					chapterImages.each(function(i,e){
						$(e).parents('li').remove();
					});
					chapterFiles = [];
				}else{
					uploadForm.alertError(resp.data.message);
				}

				if (typeof resp.data.nonce !== 'undefined') {
					uploadChapter.nonce = resp.data.nonce;
				}
			},
			complete: function( jqXHR ){
				userUploadLoading.hideLoading();
				$('#muu-loading-screen').hide();
				if( jqXHR.status != 200 ){
					uploadForm.alertError( 'ERROR ' + jqXHR.status );
				}
			}
		});

	});
	
	$('#select-manga').select2({
			placeholder: uploadChapter.messages.select_manga
		});
});