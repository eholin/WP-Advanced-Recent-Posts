jQuery(document).ready(function($){
	'use strict';

	$('.lptw-container').each(function(i, obj){
		var $container = $(this),
			featured_width = $container.data('featured_width'),
			normal_width = $container.data('normal_width'),
			space_hor = $container.data('space_hor'),
			width = $container.data('width'),
			height = $container.data('height'),
			columns = $container.data('columns'),
			fluid_images = $container.data('fluid_images'),
			countedColumnWidth;

		if (space_hor === undefined) {
			space_hor = 0;
		}

		//console.log(height);
		$container.masonry({
			itemSelector: '.lptw-grid-element',
			gutter: space_hor,
			columnWidth: function(containerWidth){
				if (containerWidth < 641) {
					$container.find('.lptw-grid-element').css('width', '100%');
					countedColumnWidth = containerWidth - 1;
				} else if (containerWidth > 640) {
					$container.find('.lptw-grid-element').css('width', normal_width);
					$container.find('.lptw-featured').css('width', featured_width);
					if (fluid_images == 'true') {
						countedColumnWidth = (containerWidth / columns) - 1
					} else {
						countedColumnWidth = width - 1;
					}
				}
				return countedColumnWidth;
			}
		});

	});

	/* где-то тут баг с высотой и его нужно поправить! */
	$(window).resize(function(){
		var viewport = $(window).width();

		$('.lptw-container').each(function(i, obj){
			var $container = $(this),
				featured_width = $container.data('featured_width'),
				normal_width = $container.data('normal_width'),
				space_hor = $container.data('space_hor'),
				width = $container.data('width'),
				height = $container.data('height'),
				columns = $container.data('columns'),
				fluid_images = $container.data('fluid_images');

			if (viewport < 641) {
				$container.find('.lptw-grid-element').css('width', '100%');
				$container.find('.lptw-grid-element').css('height', 'auto');
				$container.masonry('option', {
					columnWidth: viewport - 1
				});
			} else if (viewport > 640) {
				var containerWidth = $container.width();
				$container.find('.lptw-grid-element').css('width', normal_width);
				$container.find('.lptw-featured').css('width', featured_width);

				if (fluid_images == 'true') {
					$container.masonry('option', {
						columnWidth: (containerWidth / columns) - 1
					});
				} else {
					$container.find('.lptw-featured').css('height', height);
					$container.find('.lptw-grid-element').css('height', height);
					$container.masonry('option', {
						columnWidth: width - 1
					});
				}
			}
		});
	});

});
