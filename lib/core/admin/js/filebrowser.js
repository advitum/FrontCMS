function FileBrowser(callback) {
	if(typeof callback == 'undefined') {
		callback = null;
	}
	
	ajax('file_browser', {}, {
		success: function(data) {
			if(data.success) {
				box(data.response, {
					'<i class="fa fa-times">': function() {
						return true;
					}
				});
				
				$('#fileUpload').fileupload({
					dataType: 'json',
					url: rootUrl + 'ajax?action=file_upload'
				}).on('fileuploadalways', function (e, data) {
					$.each(data.result.files, function(index, file) {
						if(typeof file.error != 'undefined') {
							$('#errorList').append($('<li></li>').text(file.error));
						} else {
							console.log(file);
							$('#fileList').prepend($('<li></li>').append(
								'<strong>' + file.name + '</strong><br />' + file.size[1]
							).data('file', file.name));
						}
					});
				});
				
				$('#fileBrowser #fileList').on('click', 'li', function(e) {
					e.preventDefault();
					
					var href = 'intern://upload/files/' + $(this).data('file');
					
					$.lightbox.close();
					
					callback(href);
				});
			} else {
				ajaxError(data);
			}
		}
	});
}