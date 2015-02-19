function contextMenu(e, items, title) {
	if(typeof title == 'undefined') {
		title = false;
	}
	
	var $contextMenu = $('<ul id="contextMenu"></ul>');
	
	$contextMenu.css({
		top: e.pageY + 'px',
		left: e.pageX + 'px'
	});
	
	if(title !== false) {
		var $item = $('<li></li>');
		$item.addClass('title').text(title);
		$contextMenu.append($item);
	}
	
	$.each(items, function(key, item) {
		var $item = $('<li></li>');
		$item.text(item.title);
		
		if(typeof item.icon != 'undefined') {
			$item.prepend('<i class="fa ' + item.icon + '"></i>');
		}
		
		if(typeof item.callback != 'undefined') {
			$item.click(function(e) {
				item.callback(e);
			}).addClass('callback');
		}
		
		if(typeof item.href != 'undefined') {
			$item.click(function(e) {
				document.location.href = item.href;
			}).addClass('callback');
		}
		
		if(typeof item.sub != 'undefined') {
			var $subMenu = $('<ul></ul>');
			
			$.each(item.sub, function(subKey, subItem) {
				var $subItem = $('<li></li>');
				$subItem.text(subItem.title);
				
				if(typeof subItem.icon != 'undefined') {
					$subItem.prepend('<i class="fa ' + subItem.icon + '"></i>');
				}
				
				if(typeof subItem.callback != 'undefined') {
					$subItem.click(function(e) {
						subItem.callback(e);
					}).addClass('callback');
				}
				
				if(typeof subItem.href != 'undefined') {
					$subItem.click(function(e) {
						document.location.href = subItem.href;
					}).addClass('callback');
				}
				
				$subMenu.append($subItem);
			});
			
			$item.append($subMenu).addClass('sub');
		}
		
		$contextMenu.append($item);
	});
	
	$('#contextMenu').remove();
	$('body').append($contextMenu);
	$(document).one('click', function() {
		$('#contextMenu').remove();
	});
}