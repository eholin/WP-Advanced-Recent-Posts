jQuery(document).ready(function($) {
    var $container = $('#grid-container');

    // initialize
    $container.masonry({
        gutter: 10,
        itemSelector: '.lptw-grid-element'
    });
});
