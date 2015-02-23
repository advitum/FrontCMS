tinymce.PluginManager.add('fcmslink', function(editor) {
	function linkBox() {
		var anchorElement = editor.dom.getParent(editor.selection.getNode(), 'a[href]');
		var href = anchorElement ? editor.dom.getAttrib(anchorElement, 'href') : '';
		box('<form><div class="input"><label for="fcmsHref">URL</label><input type="text" id="fcmsHref" placeholder="URL" value="' + href + '"><button id="fcmsSelectFromPageBrowser" class="fcmsButton"><i class="fa fa-file"></i></button></div></form>', {
			'<i class="fa fa-check">': function() {
				var href = $('#fcmsHref').val();
				
				editor.undoManager.add();
				if(href == '') {
					editor.execCommand('unlink');
				} else {
					if(anchorElement) {
						editor.focus();
						
						editor.dom.setAttribs(anchorElement, {
							href: href
						});
						
						editor.selection.select(anchorElement);
					} else {
						editor.execCommand('mceInsertLink', false, {
							href: href
						});
					}
				}
				
				return true;
			},
			'<i class="fa fa-times">': function() { return true; }
		});
	}
	
	editor.addButton('link', {
		icon: 'link',
		tooltip: 'Insert/edit link',
		shortcut: 'Ctrl+K',
		onclick: linkBox,
		stateSelector: 'a[href]'
	});
	
	editor.addButton('unlink', {
		icon: 'unlink',
		tooltip: 'Remove link',
		cmd: 'unlink',
		stateSelector: 'a[href]'
	});
});
