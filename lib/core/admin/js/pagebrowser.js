function PageBrowser(callback) {
	if(typeof callback == 'undefined') {
		callback = null;
	}
	
	ajax('page_browser', {}, {
		success: function(data) {
			if(data.success) {
				box(data.response, {
					'<i class="fa fa-times">': function() {
						return true;
					}
				});
				$('#pageBrowser a').click(function(e) {
					e.preventDefault();
					
					var href =  $(this).attr('href');
					
					$.lightbox.close();
					
					callback(href);
				});
			} else {
				ajaxError(data);
			}
		}
	});
}