function Repeatable($list) {
	var list = this;
	
	list.$list = $list;
	
	list.$list.append($('<li class="buttons"><button type="button" class="button repeatableAdd"><i class="fa fa-plus"></i></button></li>'));
	
	list.$list.find('li:not(:last-child)').append(
		$('<div class="buttons"></div>')
			.append('<button type="button" class="button repeatableUp"><i class="fa fa-arrow-up"></i></button>')
			.append('<button type="button" class="button repeatableDown"><i class="fa fa-arrow-down"></i></button>')
			.append('<button type="button" class="button repeatableRemove"><i class="fa fa-times"></i></button>')
		);
		
	
	list.$list.on('click', '.repeatableAdd', function(e) {
		e.preventDefault();
		
		var $parent = $(this).closest('li');
		var $newLi = $parent.prev().clone();
		
		$newLi.find('input, textarea').val('');
		$newLi.find('.imageSelect').siblings('img').remove();
		
		$parent.before($newLi);
		
		list.reIndex();
	}).on('click', '.repeatableRemove', function(e) {
		e.preventDefault();
		
		var $parent = $(this).closest('li');
		
		if($parent.siblings().size() <= 1) {
			$parent.find('input, textarea').val('');
			$parent.find('.imageSelect').siblings('img').remove();
		} else {
			$parent.remove();
			list.reIndex();
		}
	}).on('click', '.repeatableUp', function(e) {
		e.preventDefault();
		
		var $parent = $(this).closest('li');
		var $prev = $parent.prev();
		
		if($prev.size()) {
			$prev.before($parent);
		}
		
		list.reIndex();
	}).on('click', '.repeatableDown', function(e) {
		e.preventDefault();
		
		var $parent = $(this).closest('li');
		var $next = $parent.next();
		
		if($next.size()) {
			$next.after($parent);
		}
		
		list.reIndex();
	});
	
	list.reIndex();
}

Repeatable.prototype.reIndex = function() {
	var list = this;
	
	list.$list.find('li').each(function() {
		var index = $(this).prevAll().size();
		
		$(this).find('.repeatableUp, .repeatableDown').show();
		
		$(this).find('input, label, textarea, select').each(function() {
			var attr = $(this).get(0).tagName === 'LABEL' ? 'for' : 'id';
			
			var id = $(this).attr(attr);
			id = id.substr(0, id.indexOf('-') + 1);
			$(this).attr(attr, id + index);
		});
	});
	
	list.$list.find('li:first-child .repeatableUp').hide();
	list.$list.find('li:last-child').prev().find('.repeatableDown').hide();
};