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

	$('body').on('change', '.registered-post-types', function(e) {
		e.preventDefault();
		var selected_post_type = $(this).val();
		var parent = $(this).closest('div[id]').attr('id');
		var id_values = parent.split('_');
		var instance_id = id_values[1].match(/\d+/)[0];
		var widget_name = id_values[1].replace(/[0-9]/g, '');
		widget_name = widget_name.substring(0, widget_name.length - 1);
		var data = {
			'action': 'get_terms_list',
			'widget_name': widget_name,
			'instance_id': instance_id,
			'selected_post_type': selected_post_type
		};
		$.post(ajaxurl, data, function(response) {
			$('#' + parent).find('.lptw-categories-list').html(response);
		});
	});

	/* Add chosen style to authors dropdown in widget */
	$(".chosen-select-widget").chosen({
		width: "100%"
	});

	/* re-init choosen after ajax complete */
	$(document).ajaxComplete(function() {
		$(".chosen-select-widget").chosen({
			width: "100%"
		});
		/*$(".chosen-select-widget").trigger("liszt:updated");*/
	});

	/* shortcode builder scripts */

	/* Add Color Picker to all inputs that have 'color-field' class */
	$('.color-field').wpColorPicker();

	/* Add chosen style to authors dropdown */
	$("#authors").chosen({
		width: '90%'
	});

	/* Add chosen style to tags dropdown in shortcode builder */
	$("#tags").chosen({
		width: '90%'
	});


	$("#lptw_generate_shortcode").click(function(e) {
		var sb_layout = $('input[name="sb_layout"]:checked', '#layout-types').val();

		if ($("#no_thumbnails").is(":checked") == true) {
			var sb_no_thumbnails = "hide";
		} else {
			var sb_no_thumbnails = "show";
		}

		var sb_post_type = $("#post_type").val();

		var post_category_selected = [];
		$('#categories_list input:checked').each(function() {
			post_category_selected.push($(this).attr('value'));
		});
		var sb_category_id = post_category_selected.toString();

		var sb_authors = $("#authors").chosen().val();
		if (sb_authors === null) {
			sb_authors = '';
		} else {
			sb_authors = sb_authors.toString();
		}

		var sb_tags = $("#tags").chosen().val();
		if (sb_tags === null) {
			sb_tags = '';
		} else {
			sb_tags = sb_tags.toString();
		}

		if ($("#tags_exclude").is(":checked") == true) {
			var sb_tags_exclude = "true";
		} else {
			var sb_tags_exclude = '';
		}

		if ($("#sb_fluid_images").is(":checked") == true) {
			var sb_fluid_images = "true";
			var sb_width = '';
		} else {
			var sb_fluid_images = "false";
			var sb_width = $("#sb_width").val();
		}

		var sb_space_hor = $("#sb_space_hor").val();

		var sb_space_ver = $("#sb_space_ver").val();

		var sb_height = $("#sb_height").val();

		var sb_columns = $('#sb_columns').val();

		var sb_order = $("#order").val();
        
        var sb_orderby = $("#orderby").val();
        
        var sb_posts_per_page = $("#posts_per_page").val();

		if ($("#reverse_post_order").is(":checked") == true) {
			var sb_reverse_post_order = "true";
		} else {
			var sb_reverse_post_order = "false";
		}

		var sb_thumbnail_size = $("#thumbnail_size").val();
		var sb_color_scheme = $("#color_scheme").val();
		var sb_dropcap_background_color = $("#dropcap-background-color").val();
		var sb_dropcap_text_color = $("#dropcap-text-color").val();

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
		if (sb_layout == 'basic') {
			sb_height = '';
		}

		/* thumbnail layout */
		if (sb_layout == 'thumbnail') {
			sb_fluid_images = '';
			sb_thumbnail_size = '';
			sb_color_scheme = '';
			sb_height = '';
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
			sb_height = '';
		}

		/* responsive grid layout */
		if (sb_layout == 'grid-medium') {
			sb_thumbnail_size = '';
			sb_dropcap_background_color = '';
			sb_dropcap_text_color = '';
		}


		var shortcode = '[lptw_recentposts';
		if (sb_layout != '') {
			shortcode += ' layout="' + sb_layout + '"';
		}
		if (sb_post_type != '') {
			shortcode += ' post_type="' + sb_post_type + '"';
		}
		if (sb_authors != '') {
			shortcode += ' authors_id="' + sb_authors + '"';
		}
		if (sb_tags != '') {
			shortcode += ' tags_id="' + sb_tags + '"';
		}
		if (sb_tags_exclude != '') {
			shortcode += ' tags_exclude="' + sb_tags_exclude + '"';
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
		if (sb_height != '') {
			shortcode += ' height="' + sb_height + '"';
		}
		if (sb_space_hor != '') {
			shortcode += ' space_hor="' + sb_space_hor + '"';
		}
		if (sb_space_ver != '') {
			shortcode += ' space_ver="' + sb_space_ver + '"';
		}
		if (sb_columns != '') {
			shortcode += ' columns="' + sb_columns + '"';
		}
		if (sb_order != '') {
			shortcode += ' order="' + sb_order + '"';
		}
		if (sb_orderby != '') {
			shortcode += ' orderby="' + sb_orderby + '"';
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
		if (sb_dropcap_background_color != '') {
			shortcode += ' background_color="' + sb_dropcap_background_color + '"';
		}
		if (sb_dropcap_text_color != '') {
			shortcode += ' text_color="' + sb_dropcap_text_color + '"';
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
		if (sb_no_thumbnails != '') {
			shortcode += ' no_thumbnails="' + sb_no_thumbnails + '"';
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
	});

	$(".layout-radio").change(function() {

		if ($("#layout-basic").is(":checked") == true) {
			/* disable all inputs with class layout-basic-hide */
			$('.layout-basic-hide').prop('disabled', true);

			/* enable all inputs with class layout-basic-show */
			$('.layout-basic-show').prop('disabled', false);

			//$('#sb_fluid_images').prop('checked', true);
		}

		if ($("#layout-thumbnail").is(":checked") == true) {
			/* disable all inputs with class layout-thumbnail-hide */
			$('.layout-thumbnail-hide').prop('disabled', true);

			/* enable all inputs with class layout-thumbnail-show */
			$('.layout-thumbnail-show').prop('disabled', false);

			//$('#sb_fluid_images').prop('checked', false);
		}

		if ($("#layout-dropcap").is(":checked") == true) {
			/* disable all inputs with class layout-dropcap-hide */
			$('.layout-dropcap-hide').prop('disabled', true);

			/* enable all inputs with class layout-dropcap-show */
			$('.layout-dropcap-show').prop('disabled', false);

			//$('#sb_fluid_images').prop('checked', false);
		}

		if ($("#layout-grid-medium").is(":checked") == true) {
			/* disable all inputs with class layout-basic-hide */
			$('.layout-grid-hide').prop('disabled', true);

			/* enable all inputs with class layout-basic-show */
			$('.layout-grid-show').prop('disabled', false);

			//$('#sb_fluid_images').prop('checked', false);
		}

	});


});
