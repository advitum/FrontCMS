function Repeatable($list) {
	var list = this;
	
	list.$list = $list;
	
	list.$list.append($('<li><button type="button" class="button repeatableAdd"><i class="fa fa-plus"></i></button></li>'));
	
	list.$list.find('li:not(:last-child)').append('<button type="button" class="button repeatableRemove"><i class="fa fa-times"></i></button>');
	
	list.$list.on('click', '.repeatableAdd', function(e) {
		e.preventDefault();
		
		var $parent = $(this).closest('li');
		var $newLi = $parent.prev().clone();
		
		$newLi.find('input, textarea').val('');
		$newLi.find('.imageSelect').siblings('img').remove();
		
		$parent.before($newLi);
		
		list.reNumberIds();
	}).on('click', '.repeatableRemove', function(e) {
		e.preventDefault();
		
		var $parent = $(this).closest('li');
		
		if($parent.siblings().size() <= 1) {
			$parent.find('input, textarea').val('');
			$parent.find('.imageSelect').siblings('img').remove();
		} else {
			$parent.remove();
			list.reNumberIds();
		}
	});
}

Repeatable.prototype.reNumberIds = function() {
	var list = this;
	
	list.$list.find('li').each(function() {
		var index = $(this).prevAll().size();
		
		$(this).find('input, label, textarea, select').each(function() {
			var attr = $(this).get(0).tagName === 'LABEL' ? 'for' : 'id';
			
			var id = $(this).attr(attr);
			id = id.substr(0, id.indexOf('-') + 1);
			$(this).attr(attr, id + index);
		});
	});
};