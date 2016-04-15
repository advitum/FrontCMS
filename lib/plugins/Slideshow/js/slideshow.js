function Slideshow(container) {
	var slideshow = this;
	
	slideshow.container = container;
	slideshow.slides = slideshow.container.querySelectorAll('.slide');
	
	slideshow.navigation = document.createElement('ul');
	slideshow.container.appendChild(slideshow.navigation);
	slideshow.navigation.classList.add('navigation');
	
	for(var index = 0; index < slideshow.slides.length; index++) {
		var button = document.createElement('li');
		slideshow.navigation.appendChild(button);
		
		(function(index) {
			button.addEventListener('click', function(event) {
				slideshow.changeSlide(index);
			});
		})(index);
	}
	
	slideshow.container.classList.add('js');
	
	slideshow.changeSlide(0);
}

Slideshow.prototype.changeSlide = function(activeIndex) {
	this.slides[activeIndex].classList.add('active');
	this.navigation.childNodes[activeIndex].classList.add('active');
	
	for(var index = 0; index < this.slides.length; index++) {
		if(index !== activeIndex) {
			this.slides[index].classList.remove('active');
			this.navigation.childNodes[index].classList.remove('active');
		}
	}
};

var slideshows = document.querySelectorAll('.slideshow');
for(var index = 0; index < slideshows.length; index++) {
	new Slideshow(slideshows[index]);
}