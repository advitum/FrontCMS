tinymce.PluginManager.add('fcmslink', function(editor) {
	function linkBox() {
		var anchorElement = editor.dom.getParent(editor.selection.getNode(), 'a[href]');
		var href = anchorElement ? editor.dom.getAttrib(anchorElement, 'href') : '';
		var title = anchorElement ? editor.dom.getAttrib(anchorElement, 'title') : '';
		var target = anchorElement ? editor.dom.getAttrib(anchorElement, 'target') : '';
		box('<form><div class="input"><label for="fcmsHref">URL</label><input type="text" id="fcmsHref" placeholder="URL" value="' + href + '"><button id="fcmsSelectFromPageBrowser" class="fcmsButton"><i class="fa fa-file"></i></button></div><div class="input"><label for="fcmsTitle">Titel</label><input type="text" id="fcmsTitle" placeholder="Titel" value="' + title + '"></div><div class="input checkbox"><input type="checkbox" id="fcmsTarget"' + (target === '_blank' ? ' checked="checked"' : '') + '><label for="fcmsTarget">In neuem Fenster öffnen</label></div></form>', {
			'<i class="fa fa-check">': function() {
				var href = $('#fcmsHref').val();
				var title = $('#fcmsTitle').val();
				var target = $('#fcmsTarget').prop('checked');
				
				editor.undoManager.add();
				if(href == '') {
					editor.execCommand('unlink');
				} else {
					if(anchorElement) {
						editor.focus();
						
						editor.dom.setAttribs(anchorElement, {
							href: href,
							title: title,
							target: (target ? '_blank' : '')
						});
						
						console.log(editor.dom.getAttrib(anchorElement, 'href'));
						
						editor.selection.select(anchorElement);
					} else {
						editor.execCommand('mceInsertLink', false, {
							href: href,
							title: title,
							target: (target ? '_blank' : '')
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
