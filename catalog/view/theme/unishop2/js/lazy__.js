function uniLazy() {
	var images = $('.uni-lazy');

	const config = {
		rootMargin: '0px 0px 50px 0px'
	};

	let observer = new IntersectionObserver(function (entries, self) {
				
		$(entries).each(function() {
				
			entry = $(this)[0];
				
			if (entry.isIntersecting) {
	  
				var img = $(entry.target);
			
				console.log(img.data('src'));
	  
				img.attr('src', img.data('src')).removeAttr('data-src').addClass('uni-lazyloaded');
	  

				self.unobserve(entry.target);
			}
		});
	}, config);

	images.each(function(image) {
		observer.observe($(this)[0]);
	});
}