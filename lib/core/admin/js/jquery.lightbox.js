(function($, undefined) {
	var 
		lightbox = $.lightbox = function() {
			lightbox.open.apply(this, arguments);
		},
		isQuery = function(object) {
			return object && object.hasOwnProperty && object instanceof $;
		},
		isString = function(string) {
			return string && $.type(string) == 'string';
		},
		slideshowControls = function($lightbox) {
			$lightbox.find('.lightboxNext, .lightboxPrev').remove();
			
			var state = $lightbox.data('state');
			
			if(state.type == 'slideshow' && state.current.attr('rel')) {
				var $slides = $('a[rel="' + state.current.attr('rel') + '"]');
				if($slides.size() > 1) {
					var current = false;
					for(var i = 0; i < $slides.size(); i++) {
						if($slides.eq(i).is(state.current)) {
							current = i;
							break;
						}
					}
					
					if(current !== false) {
						if(current < $slides.size() - 1) {
							$lightbox.find('.lightboxContent').append('<div class="lightboxNext"><div class="circle"></div></div>');
							$lightbox.find('.lightboxNext').one('click', function(e) {
								e.preventDefault();
								lightbox.changeTo($slides.eq(current + 1));
							});
						}
						if(current > 0) {
							$lightbox.find('.lightboxContent').append('<div class="lightboxPrev"><div class="circle"></div></div>');
							$lightbox.find('.lightboxPrev').one('click', function(e) {
								e.preventDefault();
								lightbox.changeTo($slides.eq(current - 1));
							});
						}
					}
				}
			}
		},
		resizeImage = function($lightbox) {
			var state = $lightbox.data('state');
			
			if(state !== null && (state.type == 'image' || state.type == 'slideshow')) {
				var maxWidth = .9 * $(window).width(),
					maxHeight = .9 * $(window).height(),
					$lightboxContent = $lightbox.find('.lightboxContent'),
					$image = $lightboxContent.children('img'),
					image = $image.get(0),
					imageWidth = image.width,
					imageHeight = image.height,
					factor = Math.min(1, Math.min(maxWidth / imageWidth, maxHeight / imageHeight));
				
				$lightboxContent.width(imageWidth * factor);
				$image.width(imageWidth * factor);
			}
		}
	;
	
	$.extend(lightbox, {
		defaults: {
			modular: false,
			type: 'image',
			width: .9,
			fadeInDuration: 200,
			fadeOutDuration: 200
		},
		
		open: function(content, options) {
			if(!content) {
				return;
			}
			
			options = $.extend({}, this.defaults, options);
			
			var state = {
				options: options
			};
			
			var $lightbox = $('<div class="lightbox"><div class="lightboxOuter"><div class="lightboxInner"><div class="lightboxContent"></div></div></div><div class="lightboxLoading"></div></div>').appendTo('body');
			$lightbox.find('.lightboxContent').hide().css({
				maxHeight: $(window).height() + 'px'
			});
			
			if(options.type == 'image') {
				var href = content;
				
				if($.type(content) == 'object') {
					if(content.nodeType) {
						content = $(content);
					}
					if(isQuery(content)) {
						href = content.attr('href');
					} else if(isString(content.href)) {
						href = content.href;
					}
				};
				
				if(isQuery(content) && content.attr('rel') && $('a[rel="' + content.attr('rel') + '"]').size() > 1) {
					$.extend(state, {
						type: 'slideshow',
						current: content
					});
				} else {
					$.extend(state, {
						type: 'image'
					});
				}
				$lightbox.data('state', state);
				
				$lightbox.find('.lightboxContent').append($('<img />').one('load', function() {
					resizeImage();
					lightbox.show($lightbox);
				}).attr('src', href));
				
				slideshowControls($lightbox);
			} else if(options.type == 'html') {
				$.extend(state, {
					type: 'html'
				});
				$lightbox.data('state', state);
				
				if(options.width > 0 && options.width < 1) {
					$lightbox.find('.lightboxContent').width(options.width * $(window).width());
				} else {
					$lightbox.find('.lightboxContent').width(options.width);
				}
				$lightbox.find('.lightboxContent').append(content);
				lightbox.show();
			}
			
			if(!options.modular) {
				$lightbox.find('.lightboxContent').append('<div class="lightboxClose"></div>');
				$lightbox.one('click', function(e) {
					e.preventDefault();
					lightbox.close();
				}).find('.lightboxContent').on('click', function(e) {
					e.stopPropagation();
				}).find('.lightboxClose').one('click', function(e) {
					e.preventDefault();
					lightbox.close();
				});
			}
		},
		close: function(force) {
			if(typeof force == undefined) {
				force = false;
			}
			
			var $lightbox = $('.lightbox').last();
			var state = $lightbox.data('state');
			
			if(state != null) {
				if(force) {
					$lightbox.remove();
				} else {
					lightbox.hide(true);
				}
			}
		},
		show: function() {
			var $lightbox = $('.lightbox').last();
			var state = $lightbox.data('state');
			
			if(state != null) {
				$lightbox.find('.lightboxLoading').hide();
				$lightbox.find('.lightboxContent').stop().fadeIn(state.options.fadeInDuration);
			}
		},
		hide: function(remove) {
			if(typeof remove == undefined) {
				remove = false;
			}
			
			var $lightbox = $('.lightbox').last();
			var state = $lightbox.data('state');
			
			if(state != null) {
				if(remove) {
					$lightbox.stop().fadeOut(state.options.fadeOutDuration, function() {
						$lightbox.remove();
					});
				} else {
					$lightbox.find('.lightboxLoading').show();
					$lightbox.find('.lightboxContent').stop().fadeOut(state.options.fadeOutDuration);
				}
			}
		},
		changeTo: function($slide) {
			var $lightbox = $('.lightbox').last();
			var state = $lightbox.data('state');
			
			if(state != null && state.type == 'slideshow') {
				$lightbox.find('.lightboxLoading').show();
				state.current = $slide;
				$lightbox.find('.lightboxContent > img').one('load', function() {
					slideshowControls($lightbox);
					resizeImage($lightbox);
					$lightbox.find('.lightboxLoading').hide();
				}).attr('src', $slide.attr('href'));
			}
			
			$lightbox.data('state', state);
		}
	});
	
	$.fn.lightbox = function(options) {
		this.click(function(e) {
			e.preventDefault();
			
			lightbox.open(this, options);
		});
		
		return this;
	}
})(jQuery);