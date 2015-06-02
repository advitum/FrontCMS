function Flexlist($list) {
	var list = this;
	
	list.$list = $list;
	
	list.$list.find('.fcmsFlexitem').each(function() {
		$(this).append(
			$('<div class="fcmsButtons"></div>')
				.append('<button class="fcmsButton fcmsFlexcontrolUp"><i class="fa fa-arrow-up"></i></button>')
				.append('<button class="fcmsButton fcmsFlexcontrolDown"><i class="fa fa-arrow-down"></i></button>')
				.append('<button class="fcmsButton fcmsFlexcontrolRemove"><i class="fa fa-times"></i></button>')
		);
	});
	
	list.$itemTypes = [];
	list.$list.find('.fcmsFlexitem.empty').each(function() {
		var $type = $(this).detach().removeClass('empty');
		
		list.$itemTypes.push($type);
	});
	
	list.$list.append(
		$('<div class="fcmsFlexcontrol"></div>').append(
			'<div class="fcmsButtons"></div>'
		)
	);
	
	$.each(list.$itemTypes, function(index, $itemType) {
		list.$list.find('.fcmsFlexcontrol .fcmsButtons').append(
			$('<button type="button" class="fcmsButton"><i class="fa fa-plus"></i> ' + $itemType.data('title') + '</button>')
				.on('click', function(e) {
					list.addItem($itemType);
				})
		);
	});
	
	list.$list.on('click', '.fcmsFlexcontrolRemove', function(e) {
		e.preventDefault();
		
		$(this).closest('.fcmsFlexitem').remove();
		list.reIndex();
	}).on('click', '.fcmsFlexcontrolUp', function(e) {
		e.preventDefault();
		
		var $item = $(this).closest('.fcmsFlexitem');
		var $prev = $item.prev('.fcmsFlexitem');
		
		if($prev.size()) {
			$prev.before($item);
		}
		
		list.reIndex();
	}).on('click', '.fcmsFlexcontrolDown', function(e) {
		e.preventDefault();
		
		var $item = $(this).closest('.fcmsFlexitem');
		var $next = $item.next('.fcmsFlexitem');
		
		if($next.size()) {
			$next.after($item);
		}
		
		list.reIndex();
	});
	
	list.reIndex();
}

Flexlist.prototype.addItem = function($itemType) {
	var list = this;
	
	var $newItem = $itemType.clone();
	list.$list.find('.fcmsFlexcontrol').before(
		$newItem
	);
	
	var iframeContext = $('#page').get(0).contentWindow;
	var $richTextEditors = $newItem.find('.fcmsEditable[data-type="rich"]');
	
	if($richTextEditors.size()) {
		iframeContext.fcmsInitTinyMCE(iframeContext.$($richTextEditors));
	}
	
	list.reIndex();
};

Flexlist.prototype.reIndex = function() {
	var list = this;
	
	list.$list.find('.fcmsFlexitem').find('.fcmsFlexcontrolUp, .fcmsFlexcontrolDown').show();
	list.$list.find('.fcmsFlexitem:first-child .fcmsFlexcontrolUp').hide();
	list.$list.find('.fcmsFlexitem:last .fcmsFlexcontrolDown').hide();
	
	var prefix = 'flex_' + list.$list.data('name') + '_';
	var values = {
		items: []
	};
	
	list.$list.find('.fcmsFlexitem').each(function() {
		var index = $(this).prevAll().size();
		
		values.items.push($(this).data('name'));
		
		$(this).find('[data-name^="' + prefix + '"]').each(function() {
			var name = $(this).data('name');
			
			name = prefix + index + name.substr(name.indexOf('_', prefix.length));
			$(this).data('name', name);
		});
	});
	
	list.$list.data('content', JSON.stringify(values));
};