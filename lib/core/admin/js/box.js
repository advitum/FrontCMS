function box(content, buttons, width) {
	var $dialogue = $('<div class="box"></div>').html(content + '<div class="buttonbar"></div>');
	
	if(typeof width == 'undefined') {
		width = 800;
	}
	
	if(typeof(buttons) != "undefined") {
		for(label in buttons) {
			var callback = buttons[label];
			
			if(typeof(callback) != "function") {
				callback = function() { return true; };
			}
			
			$dialogue.find('.buttonbar').append('<button type="button" class="button">' + label + '</button>');
			
			(function(callback) {
				$dialogue.find('.buttonbar button:last-child').click(function() {
					var result = callback();
					
					if(typeof(result) == 'undefined' || result !== false) {
						$.lightbox.close();
					}
				});
			})(callback);
		}
	} else {
		$dialogue.find('.buttonbar').append('<button type="button" class="button">' + languageString('OK') + '</button>');
		$dialogue.find('.buttonbar button').click(function() {
			$.lightbox.close();
		});
	}
	
	$.lightbox.open($dialogue, {
		type: 'html',
		modular: true,
		width: width
	});
	
	$dialogue.on('click', '.imageSelectButton', function(e) {
		e.preventDefault();
		
		var $input = $(this).parent().siblings('input');
		
		new MediaBrowser(false, function(images) {
			$input.siblings('.imagePreview').remove();
			$input.val('');
			if(images.length) {
				$input.after($('<img class="imagePreview" />').attr('src', rootUrl + 'upload/media/' + images[0]));
				$input.val(images[0]);
			}
		});
	}).on('click', '.imageRemoveButton', function(e) {
		e.preventDefault();
		
		$(this).parent().siblings('input').val('');
		$(this).parent().siblings('.imagePreview').remove();
	}).on('click', '.pageSelectButton', function(e) {
		e.preventDefault();
		
		var $input = $(this).parent().siblings('input');
		new PageBrowser(function(page) {
			$input.val(page);
		});
	});
	
	boxFunctions();
}

function headerBox(header, content, buttons) {
	box('<header>' + header + '</header>' + content, buttons);
}


function confirmBox(question, yesCallback, noCallback) {
	headerBox(languageString('Are you sure'), question, {
		'<i class="fa fa-check"></i>': yesCallback,
		'<i class="fa fa-times"></i>': noCallback
	});
}

function boxFunctions() {
	var $dialogue = $('.box').last();
	
	$dialogue.find('.imageSelect').hide().after('<div class="buttons"><button type="button" class="button imageSelectButton"><i class="fa fa-picture-o"></i></button><button type="button" class="button imageRemoveButton"><i class="fa fa-times"></i></button></div>').each(function() {
		if($(this).val() !== '') {
			$(this).after($('<img class="imagePreview" />').attr('src', rootUrl + 'upload/media/' + $(this).val()));
		}
	});
	$dialogue.find('.pageSelect').after('<div class="buttons"><button type="button" class="button pageSelectButton"><i class="fa fa-file-o"></i></button></div>');
	
	$dialogue.find('ul.repeatable').each(function() {
		new Repeatable($(this));
	});
	
	$dialogue.find('input, textarea').eq(0).focus();
}