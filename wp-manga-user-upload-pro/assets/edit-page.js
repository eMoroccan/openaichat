jQuery(function($){

    /*
    *   Modal JS
    */
    var muuModal = $('#user-upload-modal'),
        mmuUploadArea = muuModal.find('.user-upload-area-content'),
        mmuFileInput = muuModal.find('#manga_upload_input'),
        mmuEditForm = $('#madara-user-edit-form'),
        mmuUploadMsg = muuModal.find('.upload-message'),
        mmuUploadScreen = muuModal.find('.user-upload-screen');

    // UX
    mmuFileInput.on('change', function(){
        if( $(this)[0].files.length != 0 ){
            mmuUploadArea.addClass('file-selected');
            muuModal.find('.preview-section .upload-file-name').text( $(this)[0].files[0].name );
        }else{
            mmmuUploadArea.removeClass('file-selected');
        }
    });

    muuModal.find('.remove-file i').on('click', function(){
        mmuFileInput.val('');
        mmuUploadArea.removeClass('file-selected');
        muuModal.find('.preview-section .upload-file-name').text('');
    });

    // Upload
    muuModal.find('#manga-upload-submit').on('click', function(){

        if( mmuFileInput[0].files.length == 0 ){

            mmuUploadMsg.find('.upload-warning').fadeIn();
            mmuUploadMsg.find('.upload-success').fadeOut();

        }else{

            mmuUploadMsg.find('.upload-warning').fadeOut();

            var fd = new FormData(),
                postID = mmuEditForm.find('input[name="post-id"]').val(),
                wpNonce = mmuEditForm.find('input[name="_wp_nonce"]').val();

            fd.append('file', mmuFileInput[0].files[0]);
            fd.append('postID', postID);
            fd.append('action', 'front_end_edit_upload_handler');

            $.ajax({
                type : 'POST',
                url : manga.ajax_url,
                data : fd,
                cache: false,
                contentType: false,
                processData: false,
                enctype: 'multipart/form-data',
                beforeSend : function(){
                    muuModal.addClass('loading');
                    hideUploadScreen();
                },
                success : function( response ){
                    if( response.success ){

                        mmuFileInput.val('');
                        muuModal.find('.remove-file i').click();

                        muuModal.find('.success-screen').slideDown( 600 );

                        setTimeout(function(){
                            muuModal.find('.success-screen').slideUp( 600 );
                        },2000);

                        setTimeout(function(){
                            showUploadScreen();
                        },2600);
						
						refresh_chapters_listing(postID);
                    }else{
						alert(response.data);
                        showUploadScreen();
                    }
                },
				error: function(err){
					console.log(err);
					showUploadScreen();
				},
                complete : function(){
                    muuModal.removeClass('loading');
                }
            });
        }
    });
	
	var refresh_chapters_listing = function(id){
		$.ajax({
				type : 'GET',
				url : manga.ajax_url,
				data : {
					action : 'madara_user_upload_refresh_list',
					postID : id
				},
				success : function( response ){
					if( response.success ){
						$('.chapter-listing').empty();
						$('.chapter-listing').html( response.data );
						init_events();
					}
				}
			});
	}

    /*
    *   Edit Page JS
    */

    var updateChapters = mmuEditForm.find('input[name="update_chapters"]'),
        updateVolumes = mmuEditForm.find('input[name="update_volumes"]'),
        deleteChapters = mmuEditForm.find('input[name="delete_chapters"]'),
        deleteVolumes = mmuEditForm.find('input[name="delete_volumes"]');

	var init_events = function(){
		$('.muu-edit-actions-btn').on('click', function(e){
			e.preventDefault();
		});
		
		$('.muu-edit-content-btn').on('click', function(e){
			e.preventDefault();
			
			$('#chapter-content-edit-modal').addClass('loading');
			$('#chapter-content-edit-modal').modal();
			
			$.ajax({
				type : 'GET',
				url : manga.ajax_url,
				data : {
					action : 'muupro_get_chapter_content',
					chapter : $(this).data('id'),
					manga: mmuEditForm.find('input[name="post-id"]').val()
				},
				success : function( response ){
					if( response.success ){
						$('#chapter-content-edit-modal').removeClass('loading');
						
						$('#chapter-content-edit-modal input[name="madara-chapter-id"]').val(response.data.id);
						$('#chapter-content-edit-modal input[name="madara-chapter-manga"]').val(response.data.manga);
						$('#chapter-content-edit-modal input[name="madara-chapter-nonce"]').val(response.data.nonce);
						$('#chapter-content-edit-modal .chapter-title').html(response.data.title);
						var converted_content = response.data.content.replace(/(?:\r\n|\r|\n)/g, '<br>');
						tinyMCE.get('manga-chapter-content').setContent(converted_content);
						//$('#chapter-content-edit-modal .chapter-content').html(response.data.content);
						
						$(document).trigger('muupro_get_chapter_content', response.data);
						
					} else {
						alert(response.data);
						$('#chapter-content-edit-modal').modal('hide');
					}
				},
				error: function(err){
					console.log(err);
				}
			});
		});

		$('.muu-edit-name-btn').on('click', function(e){
			e.preventDefault();
			
			$('#chapter-edit-modal').addClass('loading');
			$('#chapter-edit-modal').modal();
			
			$.ajax({
				type : 'GET',
				url : manga.ajax_url,
				data : {
					action : 'muupro_get_chapter_info',
					chapter : $(this).data('id'),
					manga: mmuEditForm.find('input[name="post-id"]').val()
				},
				success : function( response ){
					if( response.success ){
						$('#chapter-edit-modal input[name="madara-chapter-id"]').val(response.data.id);
						$('#chapter-edit-modal input[name="madara-chapter-manga"]').val(response.data.manga);
						$('#chapter-edit-modal input[name="madara-chapter-nonce"]').val(response.data.nonce);
						$('#chapter-edit-modal input[name="madara-chapter-title"]').val(response.data.title);
						$('#chapter-edit-modal input[name="madara-chapter-index"]').val(response.data.index);
						$('#chapter-edit-modal input[name="madara-chapter-extendname"]').val(response.data.extendname);
						$('#chapter-edit-modal textarea[name="madara-chapter-seo"]').val(response.data.seo);
						$('#chapter-edit-modal textarea[name="madara-chapter-warning"]').val(response.data.warning);
						$(document).trigger('muupro_get_chapter_info', response.data);
						
						$('#chapter-edit-modal').removeClass('loading');
					} else {
						alert(response.data);
						$('#chapter-edit-modal').modal('hide');
					}
				},
				error: function(err){
					console.log(err);
				}
			});
		});
		
		$('.muu-edit-page-edit-input > i.fa-save').on('click', function(e){

			e.preventDefault();

			var parentInput = $(this).parents('.muu-edit-page-edit-input'),
				input = parentInput.children('input');

			parentInput.next('a').find('span:not(.muu-edit-actions-btn)').text( input.data('latest-value') );
			parentInput.hide();

			if( input.hasClass('chapter-name-input') ){
				var chapters = updateChapters.val();
				updateChapters.val( updateList( chapters, input.data('chapter-id') ) );
			}else if( input.hasClass('volume-name-input') ){
				var volumes = updateVolumes.val();
				updateVolumes.val( updateList( volumes, input.data('volume-id') ) );
			}

		});

		$('.muu-delete-btn').on('click', function(e){

			e.preventDefault();

			if( confirm("Are you sure to delete this chapter?") ){
				$(this).parents('li').fadeOut();
				var thisInput = $(this).parents('li').find('.muu-edit-page-edit-input input');

				if( thisInput.hasClass('chapter-name-input') ){
					var chapters = deleteChapters.val();
					deleteChapters.val( updateList( chapters, thisInput.data('chapter-id') ) );
				}else if( thisInput.hasClass('volume-name-input') ){
					var volumes = deleteVolumes.val();
					deleteVolumes.val( updateList( volumes, thisInput.data('volume-id') ) );
				}
			}
		});
	
	}
	
	init_events();
	
	$('#muupro-edit-chapter-submit').on('click', function(){
		var chapter = $('#chapter-edit-modal input[name="madara-chapter-id"]').val();
		var manga_id = $('#chapter-edit-modal input[name="madara-chapter-manga"]').val();
		var nonce = $('#chapter-edit-modal input[name="madara-chapter-nonce"]').val();
		var title = $('#chapter-edit-modal input[name="madara-chapter-title"]').val();
		var index = $('#chapter-edit-modal input[name="madara-chapter-index"]').val();
		var extendname = $('#chapter-edit-modal input[name="madara-chapter-extendname"]').val();
		var seo = $('#chapter-edit-modal textarea[name="madara-chapter-seo"]').val();
		var warning = $('#chapter-edit-modal textarea[name="madara-chapter-warning"]').val();
		
		var formData = new FormData();
		formData.set('chapter', chapter);
		formData.set('manga', manga_id);
		formData.set('nonce', nonce);
		formData.set('title', title);
		formData.set('index', index);
		formData.set('extendname', extendname);
		formData.set('seo', seo);
		formData.set('warning', warning);
		formData.set('action', 'muupro_save_chapter');
			
		// enable hook
		window.muupro_editchapter = formData;
		$(document).trigger('muupro_before_save_chapter', formData);
		// update formdata
		if(typeof window.muupro_editchapter !== 'undefined') formData = window.muupro_editchapter;
		
		if(chapter && title){
			$('#chapter-edit-modal').addClass('loading');
			$.ajax({
				method : 'POST',
				url : manga.ajax_url,
				data : formData,
				contentType: false,
				processData: false,
				enctype: 'multipart/form-data',
				success : function( response ){
					if( response.success ){
						refresh_chapters_listing(manga_id);
						$(document).trigger('muupro_after_save_chapter', response.data);
						
						$('#chapter-edit-modal').modal('hide');
					} else {
						alert(response.data);
					}
					
					$('#chapter-edit-modal').removeClass('loading');
				},
				error: function(err){
					alert(err.statusText);
					console.log(err);
					$('#chapter-edit-modal').removeClass('loading');
				}
			});
		}
	})
	
	$('#muupro-edit-content-submit').on('click', function(){
		var chapter = $('#chapter-content-edit-modal input[name="madara-chapter-id"]').val();
		var manga_id = $('#chapter-content-edit-modal input[name="madara-chapter-manga"]').val();
		var nonce = $('#chapter-content-edit-modal input[name="madara-chapter-nonce"]').val();
		var content = tinyMCE.get('manga-chapter-content').getContent();
		
		var formData = new FormData();
		formData.set('chapter', chapter);
		formData.set('manga', manga_id);
		formData.set('nonce', nonce);
		formData.set('content', content);
		formData.set('action', 'muupro_save_chapter_content');
			
		// enable hook
		window.muupro_editchaptercontent = formData;
		$(document).trigger('muupro_before_save_chapter_content', formData);
		// update formdata
		if(typeof window.muupro_editchaptercontent !== 'undefined') formData = window.muupro_editchaptercontent;
		
		if(chapter && content){
			$('#chapter-content-edit-modal').addClass('loading');
			$.ajax({
				method : 'POST',
				url : manga.ajax_url,
				data : formData,
				contentType: false,
				processData: false,
				enctype: 'multipart/form-data',
				success : function( response ){
					if( response.success ){
						$(document).trigger('muupro_after_save_chapter_content', response.data);
						
						alert(response.data.message);
						$('#chapter-content-edit-modal').modal('hide');
					} else {
						alert(response.data);
					}
					
					$('#chapter-content-edit-modal').removeClass('loading');
				},
				error: function(err){
					alert(err.statusText);
					console.log(err);
					$('#chapter-content-edit-modal').removeClass('loading');
				}
			});
		}
	})

    $('.muu-edit-page-edit-input input').on('input', function(){
        $(this).data('latest-value', $(this).val() );
    });

    // $('.muu-edit-page-edit-input input').focusout(function(e){
    //     $(this).parent().hide();
    //     $(this).val( $(this).parent().next('a').find('span:first').text() );
    // });

    $(document).on('click', function(e){

        if( $(e.target).parents('.muu-edit-page-edit-input').length > 0 || $(e.target).hasClass('muu-edit-name-btn') ){
            return false;
        }

        $('.muu-edit-page-edit-input').hide();
        $('.muu-edit-page-edit-input input').val(function(){
            return $(this).parent().next('a').find('span:first').text();
        });

    });

    function updateList( list, value ){

        if( list.indexOf( ',' + value + ',' ) == -1 ){
            if( list == '' ){
                list = ',' + value + ',';
            }else{
                list = list + value + ',';
            }
        }

        return list;

    }

    function hideUploadScreen(){
        mmuUploadScreen.css({
            visibility: "hidden",
            opacity: "0",
        });
    }

    function showUploadScreen(){
        mmuUploadScreen.css({
            visibility: "visible",
            opacity: "1",
        });
    }

});
