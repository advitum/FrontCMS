tinymce.PluginManager.add('fcmslink', function(editor) {
	function linkBox() {
		var anchorElement = editor.dom.getParent(editor.selection.getNode(), 'a[href]');
		var href = anchorElement ? editor.dom.getAttrib(anchorElement, 'href') : '';
		var title = anchorElement ? editor.dom.getAttrib(anchorElement, 'title') : '';
		var target = anchorElement ? editor.dom.getAttrib(anchorElement, 'target') : '';
		
		parent.box('<form><div class="input"><label for="href">' + languageString('URL') + '</label><input type="text" id="href" placeholder="' + languageString('URL') + '" value="' + href + '" class="pageSelect fileSelect"></div><div class="input"><label for="title">' + languageString('Title') + '</label><input type="text" id="title" placeholder="' + languageString('Title') + '" value="' + title + '"></div><div class="input checkbox"><input type="checkbox" id="target"' + (target === '_blank' ? ' checked="checked"' : '') + '><label for="target">' + languageString('Open in new window') + '</label></div></form>', {
			'<i class="fa fa-check">': function() {
				var href = $('#href', window.parent.document).val();
				var title = $('#title', window.parent.document).val();
				var target = $('#target', window.parent.document).prop('checked');
				
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
		tooltip: languageString('Insert/edit link'),
		shortcut: 'Ctrl+K',
		onclick: linkBox,
		stateSelector: 'a[href]'
	});
	
	editor.addButton('unlink', {
		icon: 'unlink',
		tooltip: languageString('Remove link'),
		cmd: 'unlink',
		stateSelector: 'a[href]'
	});
});
