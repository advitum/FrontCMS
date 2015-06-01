function MediaBrowser(multiple, callback) {
	if(typeof multipe == 'undefined') {
		multiple = false;
	}
	if(typeof callback == 'undefined') {
		callback = null;
	}
	
	ajax('media_browser', {}, {
		success: function(data) {
			if(data.success) {
				box(data.response, {
					'<i class="fa fa-check">': function() {
						var images = [];
						var close;
						
						$('#mediaList li.active').each(function() {
							images.push($(this).data('file'));
						});
						
						if(callback !== null) {
							callback(images);
						}
						
						return true;
					},
					'<i class="fa fa-times">': function() {
						return true;
					}
				}, .8);
				
				$('#mediaUpload').fileupload({
					dataType: 'json',
					url: rootUrl + 'ajax?action=media_upload'
				}).on('fileuploadalways', function (e, data) {
					$.each(data.result.files, function(index, file) {
						if(typeof file.error != 'undefined') {
							$('#errorList').append($('<li></li>').text(file.error));
						} else {
							$('#mediaList').prepend($('<li></li>').append(
								$('<img />').attr('src', rootUrl + 'autoimg/w100-h100-c' + rootUrl + 'upload/media/' + file.name)
							).data('file', file.name));
						}
					});
				});
				
				$('#mediaList').on('click', 'li', function(e) {
					if($(this).hasClass('active')) {
						$(this).removeClass('active');
					} else {
						$(this).addClass('active');
						if(!multiple) {
							$(this).siblings('.active').removeClass('active');
						}
					}
				});
			} else {
				ajaxError(data);
			}
		}
	});
}