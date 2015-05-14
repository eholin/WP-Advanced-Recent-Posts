jQuery(document).ready(function($) {
    'use strict';
    /* widget scripts */

    $('body').on('click', '.lptw-categories-dropdown-link', function(e) {
        e.preventDefault();
        var $parent = $(this).closest('div[id]');
        $parent.find('#lptw-categories-wrapper').slideToggle('fast');
        $parent.find('#lptw-categories-action').toggleClass('lptw-categories-action-down');
        $parent.find('#lptw-categories-action').toggleClass('lptw-categories-action-up');
    });

    /* shortcode builder scripts */

    $("#lptw_generate_shortcode").click(function(e) {
        var sb_layout = $('input[name="sb_layout"]:checked', '#layout-types').val();

        var sb_post_type = $("#post_type").val();

        var post_category_selected = [];
        $('#categories_list input:checked').each(function() {
            post_category_selected.push($(this).attr('value'));
        });
        var sb_category_id = post_category_selected.toString();

        if ($("#sb_fluid_images").is(":checked") == true) {
            var sb_fluid_images = "true";
            var sb_width = "";
            var sb_columns = "";
        } else {
            var sb_fluid_images = "false";
            var sb_width = $("#sb_width").val();
            var sb_columns = $('input[name="sb_columns"]:checked', '#columns_count').val();
        }

        var sb_posts_per_page = $("#posts_per_page").val();

        if ($("#reverse_post_order").is(":checked") == true) {
            var sb_reverse_post_order = "true";
        } else {
            var sb_reverse_post_order = "false";
        }

        var sb_thumbnail_size = $("#thumbnail_size").val();
        var sb_color_scheme = $("#color_scheme").val();

        if ($("#show_date_before_title").is(":checked") == true) {
            var sb_show_date_before_title = "true";
        } else {
            var sb_show_date_before_title = "false";
        }

        if ($("#show_date").is(":checked") == true) {
            var sb_show_date = "true";
        } else {
            var sb_show_date = "false";
        }

        if ($("#show_time").is(":checked") == true) {
            var sb_show_time = "true";
        } else {
            var sb_show_time = "false";
        }

        if ($("#show_time_before").is(":checked") == true) {
            var sb_show_time_before = "true";
        } else {
            var sb_show_time_before = "false";
        }

        var sb_date_format = $('input[name="sb_date_format"]:checked', '#date_formats').val();
        var sb_time_format = $('input[name="sb_time_format"]:checked', '#time_formats').val();

        /* clear unused options depending on layout */

        /* basic layout */

        /* thumbnail layout */
        if (sb_layout == 'thumbnail') {
            sb_fluid_images = '';
            sb_thumbnail_size = '';
            sb_color_scheme = '';
        }

        /* dropcap layout */
        if (sb_layout == 'dropcap') {
            sb_show_date = '';
            sb_fluid_images = '';
            sb_thumbnail_size = '';
            sb_color_scheme = '';
            sb_date_format = '';
            sb_time_format = '';
            sb_show_time = '';
            sb_show_time_before = '';
            sb_show_date_before_title = '';
        }

        /* responsive grid layout */
        if (sb_layout == 'grid-medium') {
            sb_fluid_images = '';
            sb_thumbnail_size = '';
            sb_width = '';
            sb_columns = '';
        }


        var shortcode = '[lptw_recentposts';
        if (sb_layout != '') {
            shortcode += ' layout="' + sb_layout + '"';
        }
        if (sb_post_type != '') {
            shortcode += ' post_type="' + sb_post_type + '"';
        }
        if (sb_category_id != '') {
            shortcode += ' category_id="' + sb_category_id + '"';
        }
        if (sb_fluid_images != "") {
            shortcode += ' fluid_images="' + sb_fluid_images + '"';
        }
        if (sb_width != '') {
            shortcode += ' width="' + sb_width + '"';
        }
        if (sb_columns != '') {
            shortcode += ' columns="' + sb_columns + '"';
        }
        if (sb_posts_per_page != '') {
            shortcode += ' posts_per_page="' + sb_posts_per_page + '"';
        }
        if (sb_reverse_post_order != '') {
            shortcode += ' reverse_post_order="' + sb_reverse_post_order + '"';
        }
        if (sb_thumbnail_size != '') {
            shortcode += ' thumbnail_size="' + sb_thumbnail_size + '"';
        }
        if (sb_color_scheme != '') {
            shortcode += ' color_scheme="' + sb_color_scheme + '"';
        }
        if (sb_show_date_before_title != '') {
            shortcode += ' show_date_before_title="' + sb_show_date_before_title + '"';
        }
        if (sb_show_date != '') {
            shortcode += ' show_date="' + sb_show_date + '"';
        }
        if (sb_show_time != '') {
            shortcode += ' show_time="' + sb_show_time + '"';
        }
        if (sb_show_time_before != '') {
            shortcode += ' show_time_before="' + sb_show_time_before + '"';
        }
        if (sb_date_format != '') {
            shortcode += ' date_format="' + sb_date_format + '"';
        }
        if (sb_time_format != '') {
            shortcode += ' time_format="' + sb_time_format + '"';
        }
        shortcode += ']';

        $('#lptw_generate_shortcode_result').val(shortcode).addClass('ready');
        e.preventDefault();
    });

    /* disable/enable inputs */

    $('#sb_fluid_images').change(function() {
        $('#sb_width').prop('disabled', function(i, v) {
            return !v;
        });
        $('#sb_columns_1').prop('disabled', function(i, v) {
            return !v;
        });
        $('#sb_columns_2').prop('disabled', function(i, v) {
            return !v;
        });
    });

    $(".layout-radio").change(function() {

        if ($("#layout-basic").is(":checked") == true) {
            /* disable all inputs with class layout-basic-hide */
            $('.layout-basic-hide').prop('disabled', true);

            /* enable all inputs with class layout-basic-show */
            $('.layout-basic-show').prop('disabled', false);

            $('#sb_fluid_images').prop('checked', true);
        }

        if ($("#layout-thumbnail").is(":checked") == true) {
            /* disable all inputs with class layout-thumbnail-hide */
            $('.layout-thumbnail-hide').prop('disabled', true);

            /* enable all inputs with class layout-thumbnail-show */
            $('.layout-thumbnail-show').prop('disabled', false);

            $('#sb_fluid_images').prop('checked', false);
        }

        if ($("#layout-dropcap").is(":checked") == true) {
            /* disable all inputs with class layout-dropcap-hide */
            $('.layout-dropcap-hide').prop('disabled', true);

            /* enable all inputs with class layout-dropcap-show */
            $('.layout-dropcap-show').prop('disabled', false);

            $('#sb_fluid_images').prop('checked', false);
        }

        if ($("#layout-grid-medium").is(":checked") == true) {
            /* disable all inputs with class layout-basic-hide */
            $('.layout-grid-hide').prop('disabled', true);

            /* enable all inputs with class layout-basic-show */
            $('.layout-grid-show').prop('disabled', false);

            $('#sb_fluid_images').prop('checked', false);
        }

    });


});
