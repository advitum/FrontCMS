var changes = false;
var defaultSettings = {
	navBarOpen: true,
	showDeleted: false,
	edit: false
};
var settings = JSON.parse(localStorage.getItem('settings'));
settings = $.extend({}, defaultSettings, settings);

$(document).ready(function() {
	$('#changePassword').click(function(e) {
		e.preventDefault();
		
		if(changes) {
			confirmBox(languageString('Are you sure you want to discard your changes?'), function() {
				passwordChange();
			});
		} else {
			passwordChange();
		}
	});
	$('#togglePageTree').click(function(e) {
		e.preventDefault();
		
		$('body').toggleClass('pageTreeOpen');
		
		settings.navBarOpen = $('body').hasClass('pageTreeOpen');
		settingsSave();
	});
	$('#toggleShowDeleted').click(function(e) {
		e.preventDefault();
		
		$('body').toggleClass('showDeleted');
		
		settings.showDeleted = $('body').hasClass('showDeleted');
		settingsSave();
	});
	
	$('#add').click(function(e) {
		ajaxReload('page_add');
	});
	
	$('#toggleEdit').click(function(e) {
		e.preventDefault();
		
		if($('body').hasClass('edit')) {
			if(changes) {
				confirmBox(languageString('Are you sure you want to discard your changes?'), function() {
					settings.edit = false;
					settingsSave();
					window.location.href = window.location.href;
				});
			} else {
				settings.edit = false;
				settingsSave();
				window.location.href = window.location.href;
			}
		} else {
			startEditing();
			settings.edit = true;
			settingsSave();
		}
	});
	
	$('#save').click(function(e) {
		e.preventDefault();
		
		pageSave();
	});
	
	$('#abort').click(function(e) {
		e.preventDefault();
		
		if(changes) {
			confirmBox(languageString('Are you sure you want to discard your changes?'), function() {
				window.location.href = window.location.href;
			});
		} else {
			window.location.href = window.location.href;
		}
	});
	
	$('#pageTree').on('contextmenu', 'ul a', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var $page = $(this);
		var title = $page.text();
		var items = [];
		var isRoot = $page.attr('href') === rootUrl;
		var isHidden = $page.hasClass('hidden');
		var isDeleted = $page.hasClass('deleted');
		var isNotInMenu = $page.hasClass('notInMenu');
		
		if(!isDeleted) {
			items.push({
				title: languageString('Properties'),
				callback: function(e) {
					pageEditProperties($page.attr('href'));
				},
				icon: 'fa-wrench'
			});
		}
		
		if(!isRoot) {
			if(isDeleted) {
				items.push({
					title: languageString('Recover'),
					callback: function() {
						ajaxReload('page_restore', {
							url: $page.attr('href')
						});
					},
					icon: 'fa-trash-o'
				});
				items.push({
					title: languageString('Delete permanentyl'),
					callback: function() {
						ajaxReload('page_delete_final', {
							url: $page.attr('href')
						});
					},
					icon: 'fa-trash-o'
				});
			} else {
				if(isHidden) {
					items.push({
						title: languageString('Show'),
						callback: function() {
							ajaxReload('page_show', {
								url: $page.attr('href')
							});
						},
						icon: 'fa-eye'
					});
				} else {
					if(isNotInMenu) {
						items.push({
							title: languageString('Show in navigation'),
							callback: function() {
								ajaxReload('page_show_in_menu', {
									url: $page.attr('href')
								});
							},
							icon: 'fa-bars'
						});
					} else {
						items.push({
							title: languageString('Hide in navigation'),
							callback: function() {
								ajaxReload('page_hide_in_menu', {
									url: $page.attr('href')
								});
							},
							icon: 'fa-bars'
						});
					}
					
					items.push({
						title: languageString('Hide'),
						callback: function() {
							ajaxReload('page_hide', {
								url: $page.attr('href')
							});
						},
						icon: 'fa-eye'
					});
				}
				
				items.push({
					title: languageString('Delete'),
					callback: function() {
						ajaxReload('page_delete', {
							url: $page.attr('href')
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
			$('#pageTree ul a:not(.notInMenu)').each(function() {
				sorting.push({
					url: $(this).attr('href'),
					position: $(this).parent().prevAll().children('a:not(.notInMenu)').size() + 1
				});
			});
			ajax('menu_sort', {
				sorting: JSON.stringify(sorting)
			});
		});
	}).on('click', 'ul a', function(e) {
		var href = $(this).attr('href');
		
		if(changes) {
			e.preventDefault();
			confirmBox(languageString('Are you sure you want to discard your changes?'), function() {
				window.location.href = href;
			});
		}
	});
	
	$('#page').one('load', function() {
		$(this).addClass('loaded');
		$(this).contents().find('head').append('<link rel="stylesheet" type="text/css" href="' + adminUrl + 'css/inject.css" />');
		$(this).contents().find('a').on('click', function(e) {
			e.preventDefault();
			
			var href = $(this).attr('href')
			
			if(changes) {
				confirmBox(languageString('Are you sure you want to discard your changes?'), function() {
					window.location.href = href;
				});
			} else {
				window.location.href = href;
			}
		});
	});
	
	if(settings.navBarOpen) {
		$('body').addClass('pageTreeOpen');
	}
	if(settings.showDeleted) {
		$('body').addClass('showDeleted');
	}
	if(settings.edit) {
		startEditing();
	}
});

function ajax(action, parameters, options) {
	var url = rootUrl + 'ajax?action=' + action;
	
	$.each(parameters, function(key, value) {
		url += '&' + key + '=' + encodeURI(value);
	});
	
	if(typeof parameters == 'undefined') {
		parameters = {};
	}
	if(typeof options == 'undefined') {
		options = {};
	}
	if(typeof options.dataType == 'undefined') {
		options.dataType = 'json';
	}
	if(typeof options.error == 'undefined') {
		options.error = function(data) {
			ajaxError(data);
		};
	}
	
	$.ajax(url, options);
}

function ajaxError(error) {
	console.log(error);
	
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
			headerBox(languageString('Error'), languageString('The page was not found. Maybe it has been deleted in the meantime?'));
			break;
		default:
			headerBox(languageString('Error'), languageString('There was an error while communicating with the server. Please try again.'));
			break;
	}
}

function ajaxReload(action, parameters, options) {
	if(typeof parameters == 'undefined') {
		parameters = {};
	}
	if(typeof options == 'undefined') {
		options = {};
	}
	if(typeof options.success == 'undefined') {
		options.success = function(data) {
			if(data.success) {
				window.location.href = window.location.href;
			} else {
				ajaxError(data);
			}
		};
	}
	
	ajax(action, parameters, options);
}

function pageEditProperties(pageUrl) {
	ajax('page_properties', {
		url: pageUrl
	}, {
		success: function(data) {
			if(data.success) {
				box(data.response, {
					'<i class="fa fa-check"></i>': function() {
						ajax('page_properties', {
							url: pageUrl
						}, {
							success: function(data) {
								if(data.success) {
									window.location.href = window.location.href;
								} else if(data.error === 'validation') {
									$('#properties').replaceWith(data.response);
									boxFunctions();
								} else {
									ajaxError(data);
								}
							},
							type: 'post',
							data: $('#properties').serialize()
						});
						return false;
					},
					'<i class="fa fa-times"></i>': function() { return true; }
				});
			} else {
				ajaxError(data);
			}
		}
	});
}

function pageSave() {
	if($('body').hasClass('edit')) {
		var $form = $('<form action="' + window.location.href + '" method="POST"></form>');
		
		$('#page').contents().find('.fcmsEditable').each(function() {
			var value = '';
			switch($(this).data('type')) {
				case 'rich':
					value = $(this).html();
					break;
				case 'plain':
					value = plainText($(this).get(0));
					break;
			}
			$form.append($('<input type="hidden" name="element[' + $(this).data('name') + ']" />').attr('value', value));
		});
		
		$('#page').contents().find('.fcmsEditableImage:not(.placeholder)').each(function() {
			var image = {
				src: $(this).find('img').data('src'),
				alt: $(this).find('img').attr('alt')
			};
			$form.append($('<input type="hidden" name="element[' + $(this).data('name') + ']" />').attr('value', JSON.stringify(image)));
		});
		
		$('#page').contents().find('.fcmsEditablePlugin:not(.placeholder)').each(function() {
			$form.append($('<input type="hidden" name="element[' + $(this).data('name') + ']" />').attr('value', JSON.stringify($(this).data('content'))));
		});
		
		$('#page').contents().find('.fcmsFlexlist').each(function() {
			var content = $(this).data('content');
			
			if(typeof content == 'undefined') {
				content = JSON.stringify({
					items: []
				});
			}
			
			$form.append($('<input type="hidden" name="element[' + $(this).data('name') + ']" />').attr('value', content));
		});
		
		$form.appendTo('body').submit();
	}
}

function passwordChange() {
	ajax('change_password', {}, {
		success: function(response) {
			if(response.success) {
				box(response.content, {
					'<i class="fa fa-check"></i>': function() {
						ajax('change_password', {}, {
							success: function(response) {
								if(response.success) {
									window.location.href = window.location.href;
								} else if(response.error === 'validation') {
									$('#password').replaceWith(response.content);
									boxFunctions();
								} else {
									ajaxError(response);
								}
							},
							type: 'post',
							data: $('#password').serialize()
						});
						return false;
					},
					'<i class="fa fa-times"></i>': function() { return true; }
				});
			} else {
				ajaxError(response);
			}
		}
	});
}

function plainText(node) {
	function getStyle(n, p) {
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

function settingsSave() {
	localStorage.setItem('settings', JSON.stringify(settings));
}

function startEditing() {
	$('body').addClass('edit');
	
	var readyFrame = function($frame) {
		function loadInOrder(scripts) {
			if(scripts.length) {
				var script = document.createElement('script');
				script.type= 'text/javascript';
				script.src = scripts.shift();
				bodyTag.appendChild(script);
				script.addEventListener('load', function() {
					loadInOrder(scripts);
				});
			}
		}
		
		var $pageContents = $frame.contents();
		
		$pageContents.find('body').addClass('fcmsEdit');
		
		$pageContents.on('focus', '.fcmsEditable', function() {
			changes = true;
		}).on('blur', '.fcmsEditable', function() {
			if($(this).text() === '') {
				$(this).html('');
			}
		});
		
		var bodyTag = $pageContents.find('body').get(0);
		
		var scripts = [
			adminUrl + 'js/languagestring.js',
			adminUrl + 'js/jquery-1.11.2.min.js',
			adminUrl + 'js/tinymce/tinymce.min.js',
			adminUrl + 'js/tinymce/jquery.tinymce.min.js',
			adminUrl + 'js/tinymce.fcmslink.js',
			adminUrl + 'js/inject.js'
		]
		
		if($('script#language').size()) {
			scripts.unshift($('script#language').attr('src'));
		}
		
		loadInOrder(scripts);
		
		$pageContents.find('.fcmsEditable[data-type="plain"]').attr('contenteditable', true);
		$pageContents.on('paste', '.fcmsEditable[data-type="plain"]', function(e) {
			if(e && e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData && $pageContents.get(0).queryCommandSupported('insertText')) {
				e.preventDefault();
				$pageContents.get(0).execCommand('insertText', false, e.originalEvent.clipboardData.getData('text/plain'));
			}
		});
		
		$pageContents.on('click', '.fcmsEditableImage .fcmsButton', function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			changes = true;
			
			var $image = $(this).parent().find('img');
			var placeholder = $(this).parent().hasClass('placeholder');
			
			box('<form><div class="input"><label for="imageSrc">' + languageString('Image url') + '</label><input type="text" id="imageSrc" class="imageSelect" placeholder="' + languageString('Image url') + '"' + (!placeholder ? ' value="' + $image.data('src') + '"' : '') + ' /></div><div class="input"><label for="imageAlt">' + languageString('Alternative text') + '</label><input type="text" id="imageAlt" placeholder="' + languageString('Alternative text') + '"' + (!placeholder ? ' value="' + $image.attr('alt') + '"' : '') + ' /></div></form>', {
				'<i class="fa fa-check">': function() {
					if($('#imageSrc').val() !== '') {
						var src = $('#imageSrc').val();
						var autoimg = rootUrl + 'upload/media/' + src;
						
						if($image.parent().data('autoimg-params')) {
							autoimg = rootUrl + 'autoimg/' + $image.parent().data('autoimg-params') + '/upload/media/' + src;
						}
						
						$image
							.attr('src', autoimg)
							.attr('alt', $('#imageAlt').val())
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
		});
		
		$pageContents.on('click', '.fcmsEditablePlugin .fcmsButton', function(e) {
			var $plugin = $(this).closest('.fcmsEditablePlugin');
			
			var meta = {
				plugin: $plugin.data('plugin'),
				name: $plugin.data('name'),
				content: $plugin.data('content'),
				attributes: $plugin.data('attributes')
			};
			
			ajax('plugin_edit', {}, {
				type: 'post',
				data: {
					_meta: JSON.stringify(meta)
				},
				success: function(response) {
					if(response.success) {
						box(response.content, {
							'<i class="fa fa-check">': function() {
								var data = $('.box').last().find('form').serialize() + '&_meta=' + JSON.stringify(meta);
								
								ajax('plugin_edit', {}, {
									type: 'post',
									data: data,
									success: function(response) {
										if(response.success) {
											$plugin.replaceWith(response.content);
											changes = true;
											$.lightbox.close();
										} else if(response.error === 'validation') {
											$('.box').last().find('.buttonbar').siblings().remove();
											$('.box').last().prepend(response.content);
											boxFunctions();
										} else {
											ajaxError(response);
										}
									}
								});
								
								return false;
							},
							'<i class="fa fa-times">': function() { return true; }
						});
					} else {
						ajaxError(response);
					}
				}
			});
		});
		
		$pageContents.find('.fcmsFlexlist').each(function() {
			new Flexlist($(this));
		});
	};
	
	if($('#page').hasClass('loaded')) {
		readyFrame($('#page'));
	} else {
		$('#page').on('load', function() {
			readyFrame($(this));
		});
	}
}