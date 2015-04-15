jQuery(document).ready(function($) {
  'use strict';
  /* shortcode builder scripts */

  $("#lptw_generate_shortcode").click(function(e) {
    var sb_layout = $('input[name="sb_layout"]:checked', '#layout-types').val();

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
    var sb_thumbnail_size = $("#thumbnail_size").val();
    var sb_color_scheme = $("#color_scheme").val();

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
    }

    var shortcode = '[lptw_recentposts';
    if (sb_layout != '') {
      shortcode += ' layout="' + sb_layout + '"';
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
    if (sb_thumbnail_size != '') {
      shortcode += ' thumbnail_size="' + sb_thumbnail_size + '"';
    }
    if (sb_color_scheme != '') {
      shortcode += ' color_scheme="' + sb_color_scheme + '"';
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
      $('#sb_fluid_images').prop('disabled', false);
      $('#sb_fluid_images').prop('checked', true);
      $('#sb_width').prop('disabled', true);
      $('#sb_columns_1').prop('disabled', true);
      $('#sb_columns_2').prop('disabled', true);
      $('#thumbnail_size').prop('disabled', false);
      $('#date_formats').prop('disabled', false);
      $('#time_formats').prop('disabled', false);
      $('#display_date_time').prop('disabled', false);
      $('#color_scheme').prop('disabled', false);
    }

    if ($("#layout-thumbnail").is(":checked") == true) {
      $('#sb_fluid_images').prop('checked', false);
      $('#sb_fluid_images').prop('disabled', true);
      $('#sb_width').prop('disabled', false);
      $('#sb_columns_1').prop('disabled', false);
      $('#sb_columns_2').prop('disabled', false);
      $('#color_scheme').prop('disabled', true);
      $('#thumbnail_size').prop('disabled', true);
      $('#date_formats').prop('disabled', false);
      $('#time_formats').prop('disabled', false);
      $('#display_date_time').prop('disabled', false);
    }

    if ($("#layout-dropcap").is(":checked") == true) {
      $('#display_date_time').prop('disabled', true);
      $('#sb_fluid_images').prop('checked', false);
      $('#sb_fluid_images').prop('disabled', true);
      $('#color_scheme').prop('disabled', true);
      $('#date_formats').prop('disabled', true);
      $('#time_formats').prop('disabled', true);
    } else {
      $('#show_date').prop('disabled', false);
    }

  });


});
