var changes = false;
var defaultSettings = {
	navBarOpen: true,
	showDeleted: false,
	edit: false
};
var settings = JSON.parse(localStorage.getItem('settings'));
settings = $.extend({}, defaultSettings, settings);

$(document).ready(function() {
	$('#fcmsOpenPageTree').click(function(e) {
		e.preventDefault();
		$('body').toggleClass('fcmsPageTreeClosed');
		
		settings.navBarOpen = !$('body').hasClass('fcmsPageTreeClosed');
		saveSettings();
	});
	$('#fcmsShowDeleted').click(function(e) {
		e.preventDefault();
		$('body').toggleClass('fcmsShowDeleted');
		
		settings.showDeleted = $('body').hasClass('fcmsShowDeleted');
		saveSettings();
	});
	$('#fcmsAdd').click(function(e) {
		e.preventDefault();
		$.ajax(root + 'ajax?action=add', {
			success: function(data) {
				if(data.success) {
					window.location.href = window.location.href;
				} else {
					ajaxError(data);
				}
			},
			error: function() {
				ajaxError();
			},
			dataType: 'json'
		});
	});
	$('#fcmsEdit').click(function(e) {
		e.preventDefault();
		if($('body').hasClass('fcmsEdit')) {
			if(changes) {
				confirmBox('Wollen Sie Ihre Änderungen wirklich verwerfen?', function() {
					settings.edit = false;
					saveSettings();
					window.location.href = window.location.href;
				});
			} else {
				settings.edit = false;
				saveSettings();
				window.location.href = window.location.href;
			}
		} else {
			startEditing();
			settings.edit = true;
			saveSettings();
		}
	});
	$('#fcmsSave').click(function(e) {
		e.preventDefault();
		
		savePage();
	});
	$('#fcmsAbort').click(function(e) {
		e.preventDefault();
		
		if(changes) {
			confirmBox('Wollen Sie Ihre Änderungen wirklich verwerfen?', function() {
				window.location.href = window.location.href;
			});
		} else {
			window.location.href = window.location.href;
		}
	});
	$('body').on('click', '#fcmsSelectFromMedia', function(e) {
		e.preventDefault();
		
		openMedia(false, function(images) {
			if(images.length) {
				$('#fcmsImageSrc').val(root + 'upload/media/' + images[0]);
			}
		});
	}).on('click', '#fcmsSelectFromPageBrowser', function(e) {
		e.preventDefault();
		
		openPageBrowser(function(href) {
			$('#fcmsHref').val(href);
		});
	});
	
	$('#fcmsPageTree').on('contextmenu', 'ul a', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var $page = $(this);
		var title = $page.text();
		var items = [];
		var isRoot = $page.attr('href') === root;
		var isHidden = $page.hasClass('hidden');
		var isDeleted = $page.hasClass('deleted');
		var isNotInMenu = $page.hasClass('notInMenu');
		
		if(!isDeleted) {
			items.push({
				title: 'Eigenschaften',
				callback: function(e) {
					$.ajax(root + 'ajax?action=properties&url=' + encodeURI($page.attr('href')), {
						success: function(data) {
							if(data.success) {
								box(data.response, {
									'<i class="fa fa-check">': function() {
										$.ajax(root + 'ajax?action=properties&url=' + encodeURI($page.attr('href')), {
											success: function(data) {
												if(data.success) {
													window.location.href = window.location.href;
												} else if(data.error === 'validation') {
													$('#properties').replaceWith(data.response);
												} else {
													ajaxError(data);
												}
											},
											error: function() {
												ajaxError();
											},
											dataType: 'json',
											type: 'post',
											data: $('#properties').serialize()
										});
										return false;
									},
									'<i class="fa fa-times">': function() { return true; }
								});
								boxFunctions();
							} else {
								ajaxError(data);
							}
						},
						error: function() {
							ajaxError();
						},
						dataType: 'json'
					});
				},
				icon: 'fa-wrench'
			});
		}
		
		if(!isRoot) {
			if(isDeleted) {
				items.push({
					title: 'Wiederherstellen',
					callback: function() {
						$.ajax(root + 'ajax?action=restore&url=' + encodeURI($page.attr('href')), {
							success: function(data) {
								if(data.success) {
									window.location.href = window.location.href;
								} else {
									ajaxError(data);
								}
							},
							error: function() {
								ajaxError();
							},
							dataType: 'json'
						});
					},
					icon: 'fa-trash-o'
				});
				items.push({
					title: 'Endgültig löschen',
					callback: function() {
						$.ajax(root + 'ajax?action=deletefinal&url=' + encodeURI($page.attr('href')), {
							success: function(data) {
								if(data.success) {
									window.location.href = window.location.href;
								} else {
									ajaxError(data);
								}
							},
							error: function() {
								ajaxError();
							},
							dataType: 'json'
						});
					},
					icon: 'fa-trash-o'
				});
			} else {
				if(isHidden) {
					items.push({
						title: 'Zeigen',
						callback: function() {
							$.ajax(root + 'ajax?action=show&url=' + encodeURI($page.attr('href')), {
								success: function(data) {
									if(data.success) {
										window.location.href = window.location.href;
									} else {
										ajaxError(data);
									}
								},
								error: function() {
									ajaxError();
								},
								dataType: 'json'
							});
						},
						icon: 'fa-eye'
					});
				} else {
					if(isNotInMenu) {
						items.push({
							title: 'Im Menü zeigen',
							callback: function() {
								$.ajax(root + 'ajax?action=showInMenu&url=' + encodeURI($page.attr('href')), {
									success: function(data) {
										if(data.success) {
											window.location.href = window.location.href;
										} else {
											ajaxError(data);
										}
									},
									error: function() {
										ajaxError();
									},
									dataType: 'json'
								});
							},
							icon: 'fa-bars'
						});
					} else {
						items.push({
							title: 'Im Menü verbergen',
							callback: function() {
								$.ajax(root + 'ajax?action=hideInMenu&url=' + encodeURI($page.attr('href')), {
									success: function(data) {
										if(data.success) {
											window.location.href = window.location.href;
										} else {
											ajaxError(data);
										}
									},
									error: function() {
										ajaxError();
									},
									dataType: 'json'
								});
							},
							icon: 'fa-bars'
						});
					}
					
					items.push({
						title: 'Verbergen',
						callback: function() {
							$.ajax(root + 'ajax?action=hide&url=' + encodeURI($page.attr('href')), {
								success: function(data) {
									if(data.success) {
										window.location.href = window.location.href;
									} else {
										ajaxError(data);
									}
								},
								error: function() {
									ajaxError();
								},
								dataType: 'json'
							});
						},
						icon: 'fa-eye'
					});
				}
				
				items.push({
					title: 'Löschen',
					callback: function() {
						$.ajax(root + 'ajax?action=delete&url=' + encodeURI($page.attr('href')), {
							success: function(data) {
								if(data.success) {
									window.location.href = window.location.href;
								} else {
									ajaxError(data);
								}
							},
							error: function() {
								ajaxError();
							},
							dataType: 'json'
						});
					},
					icon: 'fa-trash-o'
				});
			}
		}
		
		contextMenu(e, items, title);
	}).on('mousedown', 'ul a:not(.notInMenu)', function(e) {
		e.preventDefault();
		
		var $page = $(this).parent();
		var offset = $page.offset().top - e.pageY;
		var $marker = null;
		
		$(document).on('mousemove.sorting', function(e) {
			if($marker === null) {
				$marker = $('<i class="fa fa-file-text"></i>');
				$marker.css({
					position: 'absolute',
					zIndex: 999,
					fontSize: '13px'
				});
				$('body').append($marker);
			}
			$marker.css({
				left: e.pageX - 20 + 'px',
				top: e.pageY + 'px'
			});
			
			var $prev = $page.prev().children('a:not(.notInMenu)').parent();
			var $next = $page.next().children('a:not(.notInMenu)').parent();
			
			if($prev.size() && e.pageY + offset < $prev.offset().top) {
				$prev.before($page);
			} else if($next.size() && e.pageY + offset > $next.offset().top) {
				$next.after($page);
			}
		}).one('mouseup', function() {
			$(document).off('mousemove.sorting');
			if($marker !== null) {
				$marker.remove();
			}
			
			var sorting = [];
			$('#fcmsPageTree ul a:not(.notInMenu)').each(function() {
				sorting.push({
					url: $(this).attr('href'),
					position: $(this).parent().prevAll().children('a:not(.notInMenu)').size() + 1
				});
			});
			$.ajax(root + 'ajax?action=sorting&sorting=' + encodeURI(JSON.stringify(sorting)));
		});
	});
	
	if(!settings.navBarOpen) {
		$('body').addClass('fcmsPageTreeClosed');
	}
	if(settings.showDeleted) {
		$('body').addClass('fcmsShowDeleted');
	}
	if(settings.edit) {
		startEditing();
	}
});

function saveSettings() {
	localStorage.setItem('settings', JSON.stringify(settings));
}

function ajaxError(error) {
	if(typeof error == 'undefined') {
		error = {
			error: ''
		};
	}
	
	switch(error.error) {
		case 'authorisation':
			window.location.href = root + 'login?auto';
			break;
		case 'page':
			headerBox('Fehler', 'Die Seite wurde nicht gefunden. Vielleicht wurde sie in der Zwischenzeit schon gelöscht?');
			break;
		default:
			headerBox('Fehler', 'Bei der Anfrage an den Server ist ein Fehler aufgetreten. Versuchen Sie es erneut.');
			break;
	}
}

function startEditing() {
	$('body').addClass('fcmsEdit');
	
	$('.fcmsEditable').on('focus', function() {
		changes = true;
	}).on('blur', function() {
		if($(this).text() === '') {
			$(this).html('');
		}
	});
	
	$('.fcmsEditable[data-type="rich"]').tinymce({
		inline: true,
		language: 'de',
		hidden_input: false,
		menubar: false,
		relative_urls: false,
		toolbar: 'styleselect | undo redo | bold italic | link unlink | bullist numlist | superscript subscript',
		plugins: 'fcmslink',
		style_formats: [{
			title: 'Absatz',
			block: 'p'
		}, {
			title: 'Überschrift 1',
			block: 'h1'
		}, {
			title: 'Überschrift 2',
			block: 'h2'
		}],
		valid_styles: {
			'*': ''
		}
	});
	
	$('.fcmsEditable[data-type="plain"]').attr('contenteditable', true).on('paste', function(e) {
		if(e && e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData) {
			e.preventDefault();
			document.execCommand('inserttext', false, e.originalEvent.clipboardData.getData('text/plain'));
		}
	});
	
	$('.fcmsEditableImage .fcmsButton').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		changes = true;
		
		var $image = $(this).parent().find('img');
		var placeholder = $(this).parent().hasClass('placeholder');
		
		box('<form><div class="input"><label for="fcmsImageSrc">Bild-URL</label><input type="text" id="fcmsImageSrc" class="imageSelect" placeholder="Bild-URL"' + (!placeholder ? ' value="' + $image.data('src') + '"' : '') + ' /></div><div class="input"><label for="fcmsImageAlt">Alternativ-Text</label><input type="text" id="fcmsImageAlt" placeholder="Alternativ-Text"' + (!placeholder ? ' value="' + $image.attr('alt') + '"' : '') + ' /></div></form>', {
			'<i class="fa fa-check">': function() {
				if($('#fcmsImageSrc').val() !== '') {
					var src = $('#fcmsImageSrc').val();
					var autoimg = root + 'upload/media/' + src;
					
					if($image.parent().data('autoimg-params')) {
						autoimg = root + 'autoimg/' + $image.parent().data('autoimg-params') + '/upload/media/' + src;
					}
					
					$image
						.attr('src', autoimg)
						.attr('alt', $('#fcmsImageAlt').val())
						.data('src', src)
						.parent().removeClass('placeholder');
				} else {
					$image
						.attr('src', $image.parent().data('placeholder-url'))
						.attr('alt', '')
						.removeData('src')
						.parent().addClass('placeholder');
				}
				
				return true;
			},
			'<i class="fa fa-times">': function() { return true; }
		});
		boxFunctions();
	});
	
	$('.fcmsEditablePlugin').each(function() {
		var plugin = $(this).data('plugin');
		plugin = plugin.charAt(0).toUpperCase() + plugin.slice(1);
		
		$(this).data('instance', new window['Plugin' + plugin]($(this)));
	});
}

function savePage() {
	var $form = $('<form action="' + window.location.href + '" method="POST"></form>');
	
	$('.fcmsEditable').each(function() {
		var value = '';
		switch($(this).data('type')) {
			case 'rich':
				value = $(this).html();
				break;
			case 'plain':
				value = plainText($(this).get(0));
				break;
		}
		$form.append($('<input type="hidden" name="element[' + $(this).data('id') + ']" />').attr('value', value));
	});
	
	$('.fcmsEditableImage:not(.placeholder)').each(function() {
		var image = {
			src: $(this).find('img').data('src'),
			alt: $(this).find('img').attr('alt')
		};
		$form.append($('<input type="hidden" name="element[' + $(this).data('id') + ']" />').attr('value', JSON.stringify(image)));
	});
	
	$('.fcmsEditablePlugin').each(function() {
		$(this).data('instance').save($form);
	});
	
	$form.appendTo('body').submit();
}

function plainText(node) {
	function  getStyle(n, p) {
	  return n.currentStyle ?
	    n.currentStyle[p] :
	    document.defaultView.getComputedStyle(n, null).getPropertyValue(p);
	}
	
	var result = '';
	
	if(node.nodeType == document.TEXT_NODE) {
		result = node.nodeValue.replace(/\s+/g, ' ');
	} else {
		for(var i =	0, j = node.childNodes.length; i < j; i++) {
			var content = plainText(node.childNodes[i]);
			if(node.childNodes[i].nodeType != document.TEXT_NODE && (getStyle(node.childNodes[i], 'display').match(/^block/) || node.childNodes[i].tagName === 'BR')) {
				content = '\n' + content + '\n';
			}
			result += content;
		}
	}
	
	result = result.replace(/\n+/g, '\n').replace(/^\n/, '').replace(/\n$/, '');
	
	return result;
}

function openMedia(multiple, callback) {
	if(typeof multipe == 'undefined') {
		multiple = false;
	}
	if(typeof callback == 'undefined') {
		callback = null;
	}
	
	if($('#lightbox').size()) {
		var $previous = $('#lightbox').detach();
	}
	
	$.ajax(root + 'ajax?action=media', {
		success: function(data) {
			if(data.success) {
				box(data.response, {
					'<i class="fa fa-check">': function() {
						var images = [];
						var close;
						
						$('#fcmsMediaList li.active').each(function() {
							images.push($(this).data('file'));
						});
						
						if(typeof $previous != 'undefined') {
							$('#lightbox').replaceWith($previous);
							close = false;
						} else {
							close = true;
						}
						
						if(callback !== null) {
							callback(images);
						}
						
						return close;
					},
					'<i class="fa fa-times">': function() {
						if(typeof $previous != 'undefined') {
							$('#lightbox').replaceWith($previous);
							return false;
						} else {
							return true;
						}
					}
				}, .8);
				$('#fcmsMediaUpload').fileupload({
					dataType: 'json',
					url: root + 'ajax?action=media-upload'
				}).on('fileuploadalways', function (e, data) {
					$.each(data.result.files, function(index, file) {
						if(typeof file.error != 'undefined') {
							$('#fcmsErrorList').append($('<li></li>').text(file.error));
						} else {
							$('#fcmsMediaList').prepend($('<li></li>').append(
								$('<img />').attr('src', root + 'autoimg/w100-h100-c' + root + 'upload/media/' + file.name)
							).data('file', file.name));
						}
					});
				});
				$('#fcmsMediaList').on('click', 'li', function(e) {
					if($(this).hasClass('active')) {
						$(this).removeClass('active');
					} else {
						$(this).addClass('active');
						if(!multiple) {
							$(this).siblings('.active').removeClass('active');
						}
					}
				});
			} else {
				ajaxError(data);
			}
		},
		error: function() {
			ajaxError();
		},
		dataType: 'json'
	});
}

function openPageBrowser(callback) {
	if(typeof callback == 'undefined') {
		callback = null;
	}
	
	if($('#lightbox').size()) {
		var $previous = $('#lightbox').detach();
	}
	
	$.ajax(root + 'ajax?action=pagebrowser', {
		success: function(data) {
			if(data.success) {
				box(data.response, {
					'<i class="fa fa-times">': function() {
						if(typeof $previous != 'undefined') {
							$('#lightbox').replaceWith($previous);
							return false;
						} else {
							return true;
						}
					}
				});
				$('#fcmsPageBrowser a').click(function(e) {
					e.preventDefault();
					
					var href = $(this).attr('href');
					
					if(typeof $previous != 'undefined') {
						$('#lightbox').replaceWith($previous);
					} else {
						$.lightbox.close();
					}
					
					callback(href);
				});
			} else {
				ajaxError(data);
			}
		},
		error: function() {
			ajaxError();
		},
		dataType: 'json'
	});
}

function boxFunctions() {
	$('.lightboxContent .box .imageSelect').hide().after('<i class="fa fa-picture-o imageSelectButton"></i> <i class="fa fa-times imageSelectButton"></i>').each(function() {
		if($(this).val() !== '') {
			$(this).after($('<img class="imagePreview" />').attr('src', root + 'upload/media/' + $(this).val()));
		}
	});
	$('.lightboxContent .box').on('click', '.imageSelectButton.fa-picture-o', function() {
		var $input = $(this).siblings('input');
		openMedia(false, function(images) {
			$input.siblings('.imagePreview').remove();
			$input.val('');
			if(images.length) {
				$input.after($('<img class="imagePreview" />').attr('src', root + 'upload/media/' + images[0]));
				$input.val(images[0]);
			}
		});
	}).on('click', '.imageSelectButton.fa-times', function() {
		$(this).siblings('input').val('');
		$(this).siblings('.imagePreview').remove();
	});
	
	$('.lightboxContent .box .pageSelect').after('<i class="fa fa-file-o pageSelectButton"></i>');
	$('.lightboxContent .box').on('click', '.pageSelectButton.fa-file-o', function() {
		var $input = $(this).siblings('input');
		openPageBrowser(function(page) {
			$input.val(page);
		});
	});
	
	$('.lightboxContent .box ul.repeatable').each(function() {
		function reNumberIds() {
			$list.find('li').each(function() {
				var index = $(this).prevAll().size();
				
				$(this).find('input, label, textarea, select').each(function() {
					var attr = $(this).get(0).tagName === 'LABEL' ? 'for' : 'id';
					var id = $(this).attr(attr);
					id = id.substr(0, id.indexOf('-') + 1);
					$(this).attr(attr, id + index);
				});
			});
		}
		
		var $list = $(this);
		
		$list.append($('<li><i class="fa fa-plus repeatableButton"></i></li>'));
		
		$list.find('li:not(:last-child)').append('<i class="fa fa-times repeatableButton"></i>');
		
		$list.on('click', '.repeatableButton.fa-plus', function() {
			var $parent = $(this).closest('li');
			var $new = $parent.prev().clone();
			
			$new.find('input, textarea').val('');
			$new.find('.imageSelect').siblings('img').remove();
			
			$parent.before($new);
			
			reNumberIds();
		}).on('click', '.repeatableButton.fa-times', function() {
			var $parent = $(this).closest('li');
			
			if($parent.siblings().size() <= 1) {
				$parent.find('input, textarea').val('');
				$parent.find('.imageSelect').siblings('img').remove();
			} else {
				$parent.remove();
				reNumberIds();
			}
		});
	});
}