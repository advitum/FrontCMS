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
				$('body').removeClass('fcmsEdit');
				settings.edit = false;
				saveSettings();
			}
		} else {
			$('body').addClass('fcmsEdit');
			settings.edit = true;
			saveSettings();
		}
	});
	$('#fcmsSave').click(function(e) {
		e.preventDefault();
		
		if(changes) {
			//Save changes
		}
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
		$('body').addClass('fcmsEdit');
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