document.addEventListener('DOMContentLoaded', function() {
	var splide = new Splide('.splide', {
		type: 'loop',
		autoplay: true,
		classes: {
			arrow: 'splide__arrow splide__arrow_custom',
			pagination: 'splide__pagination splide__pagination_custom',
			page: 'splide__pagination__page splide__pagination__page_custom',
			spinner: 'splide__spinner splide__spinner_custom'
		},
		lazyLoad: 'nearby'
	});
	splide.mount();
});