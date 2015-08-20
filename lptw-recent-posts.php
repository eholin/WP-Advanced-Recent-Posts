<?php
/*
Plugin Name: Advanced Recent Posts
Plugin URI: http://lp-tricks.com/
Description: Plugin that shows the recent posts with thumbnails in the widget and in other parts of the your blog or theme with shortcodes.
Tags: widget, posts, plugin, recent, recent posts, video, latest, latest posts, shortcode, thumbnail, thumbnails, categories, content, featured image, Taxonomy, custom post type, custom
Version: 0.6.14
Author: Eugene Holin
Author URI: http://lp-tricks.com/
License: GPLv2 or later
Text Domain: lptw_recent_posts_domain
*/

/* load js and css styles */
function lptw_recent_posts_register_scripts() {
	wp_register_style( 'lptw-style', plugins_url( 'lptw-recent-posts.css', __FILE__ ) );
	wp_enqueue_style( 'lptw-style' );

    wp_enqueue_script( 'lptw-recent-posts-script', plugins_url( 'lptw-recent-posts.js', __FILE__ ), array('jquery', 'jquery-masonry'), false, true );
}
add_action( 'wp_enqueue_scripts', 'lptw_recent_posts_register_scripts' );

/* register custom image size for Grid Layout */
function lptw_recent_posts_activate () {
    add_image_size( 'lptw-grid-large', 610, 400, true );
}

/* trim excerpt to custom size */
function lptw_custom_excerpt ($limit, $ignore_more_tag) {
    global $more;
    if ($ignore_more_tag == 'true') { $more = 1; }
    else { $more = 0; }
    $excerpt = explode(' ', get_the_excerpt(), $limit);
    if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).'...';
    } else {
        $excerpt = implode(" ",$excerpt);
    }
    $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
    return $excerpt;
}

register_activation_hook( __FILE__, 'lptw_recent_posts_activate' );

/* add price table editor metabox */
add_action( 'add_meta_boxes', 'lptw_recent_posts_options' );
function lptw_recent_posts_options () {
    add_meta_box(
        'lptw_recent_posts_options',
        __( 'Advanced Recent Posts Options', 'lptw_recent_posts_domain' ),
        'lptw_recent_posts_options_box_content',
        'post',
        'normal',
        'default'
    );
}

function lptw_recent_posts_options_box_content ( $post ) {

    // Add a nonce field so we can check for it later.
	wp_nonce_field( 'lptw_recent_posts_options_box', 'lptw_recent_posts_meta_box_nonce' );

    $post_subtitle = get_post_meta( $post->ID, 'lptw_post_subtitle', true );
    $featured_post = get_post_meta( $post->ID, 'featured_post', true );
    $embedded_video = get_post_meta( $post->ID, 'embedded_video', true );
    $hide_youtube_controls = get_post_meta( $post->ID, 'hide_youtube_controls', true );

    echo '<p><label class="lptw-checkbox-label" for="post_subtitle"><strong>'.__( 'Post Subtitle:', 'lptw_recent_posts_domain' ).'</strong></label></p>';
    echo '<p><input class="text" type="text" id="post_subtitle" name="post_subtitle" value="'.esc_attr($post_subtitle).'" /></p>';
    echo '<p><label class="lptw-checkbox-label" for="featured_post"><input class="checkbox" type="checkbox" '.checked( $featured_post, 'on', false ).' id="featured_post" name="featured_post" />&nbsp;'.__( 'Featured post', 'lptw_recent_posts_domain' ).'</label></p>';
    echo '<p class="description">'.__( 'Featured post displays larger than the other posts in Responsive Grid Layout', 'lptw_recent_posts_domain' ).'</p>';
    echo '<div id="lptw-embedded-video-settings">';
    echo '<p><label class="lptw-checkbox-label" for="embedded_video"><input class="checkbox" type="checkbox" '.checked( $embedded_video, 'on', false ).' id="embedded_video" name="embedded_video" />&nbsp;'.__( 'Use embedded video (Experimental feature!!! It may not work properly.)', 'lptw_recent_posts_domain' ).'</label></p>';
    echo '<p><label class="lptw-checkbox-label" for="hide_youtube_controls"><input class="checkbox" type="checkbox" '.checked( $hide_youtube_controls, 'on', false ).' id="hide_youtube_controls" name="hide_youtube_controls" />&nbsp;'.__( 'Hide Youtube player controls', 'lptw_recent_posts_domain' ).'</label></p>';
    echo '<p class="description">'.__( 'Use embedded video (first movie) instead of the Post Featured Image in Responsive Grid Layout. If you have any ideas about this feature or it work not properly, please write me in special topic on <a href="https://wordpress.org/support/topic/new-feature-in-0613-the-embedded-video-instead-of-the-post-featured-image" target="_blank">Support Forum</a>', 'lptw_recent_posts_domain' ).'</p>';
    echo '</div>';
}

function lptw_recent_posts_options_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['lptw_recent_posts_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['lptw_recent_posts_meta_box_nonce'], 'lptw_recent_posts_options_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'post' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	// Sanitize user input.
	$post_subtitle = sanitize_text_field( $_POST['post_subtitle'] );
	$featured_post = sanitize_text_field( $_POST['featured_post'] );
	$embedded_video = sanitize_text_field( $_POST['embedded_video'] );
	$hide_youtube_controls = sanitize_text_field( $_POST['hide_youtube_controls'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, 'lptw_post_subtitle', $post_subtitle );
	update_post_meta( $post_id, 'featured_post', $featured_post );
	update_post_meta( $post_id, 'embedded_video', $embedded_video );
	update_post_meta( $post_id, 'hide_youtube_controls', $hide_youtube_controls );
}
add_action( 'save_post', 'lptw_recent_posts_options_save_meta_box_data' );

/*
-------------------------------------- Include widgets classess --------------------------------------
*/

/* Fluid Images Widget */
include( plugin_dir_path( __FILE__ ) . 'includes/class.widget.fluid.images.php');
/* Small Thumbnails Widget */
include( plugin_dir_path( __FILE__ ) . 'includes/class.widget.small.thumbnails.php');

/* Register and load the widget */
function lptw_recent_posts_load_widget() {
	register_widget( 'lptw_recent_posts_fluid_images_widget' );
	register_widget( 'lptw_recent_posts_thumbnails_widget' );
}
add_action( 'widgets_init', 'lptw_recent_posts_load_widget' );

/**
-------------------------------------- Shortcode --------------------------------------
**/

/* Main Class for all Layouts Rendering */
include( plugin_dir_path( __FILE__ ) . 'includes/class.render.layout.php');

function lptw_display_recent_posts ( $atts ) {
    $default_posts_per_page =  get_option( 'posts_per_page', '10' );

    $a = shortcode_atts( array(
        'post_type'                 => 'post',
        'category_id'               => '',
        'authors_id'                => '',
        'post_parent'               => '0',
        'posts_per_page'            => $default_posts_per_page,
        'exclude_posts'             => '',
        'exclude_current_post'      => 'false',
        'thumbnail_size'            => 'thumbnail',
        'random_thumbnail'          => 'false',
        'layout'                    => 'basic',
        'color_scheme'              => 'no-overlay',
        'show_date'                 => 'true',
        'fluid_images'              => 'false',
        'columns'                   => '1',
        'height'                    => '',
        'featured_height'           => '400',
        'min_height'                => '400',
        'width'                     => '300',
        'date_format'               => 'd.m.Y',
        'time_format'               => 'H:i',
        'show_time'                 => 'true',
        'show_time_before'          => 'true',
        'show_date_before_title'    => 'true',
        'order'                     => 'DESC',
        'orderby'                   => 'date',
        'reverse_post_order'        => 'false',
        'background_color'          => '#4CAF50',
        'text_color'                => '#ffffff',
        'no_thumbnails'             => 'hide',
        'space_hor'                 => 10,
        'space_ver'                 => 10,
        'tags_id'                   => '',
        'tags_exclude'              => 'false',
        'override_colors'           => 'false',
        'excerpt_show'              => 'true',
        'excerpt_lenght'            => '35',
        'ignore_more_tag'           => 'false',
        'post_offset'               => 0,
        'read_more_show'            => 'false',
        'read_more_inline'          => 'false',
        'read_more_content'         => 'Read more &rarr;',
        'link_target'               => 'self',
        'show_subtitle'             => 'true'
    ), $atts );

    /* get the list of the post categories */
    if ($a['category_id'] == 'same_as_post') {
        $post_categories = get_the_category();
        if ( !empty($post_categories) ) {
            foreach ($post_categories as $category) {
                if ( $category->taxonomy == 'category' ) { $post_category[] = $category->term_id; }
            }
        }
    }

    /* ------------------------------------ WP_Query arguments filter start ------------------------------------ */
    if ($a['no_thumbnails'] == 'hide') { $meta_key = '_thumbnail_id'; }
    else { $meta_key = ''; }

    if (!empty($a['exclude_posts'])) {
        $exclude_post = explode(',', $a['exclude_posts']);
        }
    else { $exclude_post = ''; }
    if ($a['exclude_current_post'] == 'true') {
        $current_post = get_the_ID();
        $exclude_post[] = $current_post;
    }

    if ( strpos($a['authors_id'], ',') !== false ) {
        $authors_id = array_map('intval', explode(',', $a['authors_id']));
    } else { $authors_id = (integer) $a['authors_id']; }

    if ( strpos($a['category_id'], ',') !== false ) {
        $post_category = array_map('intval', explode(',', $a['category_id']));
    } else if ( $a['category_id'] != 'same_as_post' ) {
        $post_category = (integer) $a['category_id'];
    }

    $tax_query = '';

    if ( $a['post_type'] != 'post' && !empty($post_category) ) {
        $tax_query = array('relation' => 'AND');
        $taxonomies = get_object_taxonomies($a['post_type']);
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                $tax_array = array('taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $post_category, 'include_children' => false);
                array_push ($tax_query, $tax_array);
            }
        }
        $post_category = '';
    }

    if ( strpos($a['tags_id'], ',') !== false ) {
        $post_tags = array_map('intval', explode(',', $a['tags_id']));
    } else { $post_tags = (integer) $a['tags_id']; }

    if ( $a['post_type'] != 'post' ) { $post_tags = ''; }

    if ( $a['tags_exclude'] == 'true' ) { $tags_type = 'tag__not_in'; }
    else { $tags_type = 'tag__in'; }

    $lptw_shortcode_query_args = array(
        'post_type'             => $a['post_type'],
        'posts_per_page'        => $a['posts_per_page'],
		'no_found_rows'         => true,
		'post_status'           => 'publish',
		'ignore_sticky_posts'   => true,
        'post__not_in'          => $exclude_post,
        'author__in'            => $authors_id,
        'category__in'          => $post_category,
        $tags_type              => $post_tags,
        'tax_query'             => $tax_query,
        'order'                 => $a['order'],
        'orderby'               => $a['orderby'],
        'meta_key'              => $meta_key,
        'offset'                => $a['post_offset']
        );

    /* ------------------------------------ WP_Query arguments filter end ------------------------------------ */

    /* link target start */
    if ( $a['link_target'] == 'new' ) { $link_target = '_blank'; }
    else { $link_target = '_self'; }
    /* link target end */

    /* date, title and subtitle position start */
    if ( $a['show_date_before_title'] == 'true' ) {
        $date_pos = 1;
        $title_pos = 2;
    } else {
        $date_pos = 2;
        $title_pos = 1;
    }
    $subtitle_pos = 3;
    /* date, title and subtitle position end */


    $lptw_shortcode_query = new WP_Query( $lptw_shortcode_query_args );
    if( $lptw_shortcode_query->have_posts() ) {
        if ($a['reverse_post_order'] == 'true') { $lptw_shortcode_query->posts = array_reverse($lptw_shortcode_query->posts); }
        $i=1;
        $rand_grid = rand(11111, 99999);
        $content = lptw_display_layout_header ($a['layout'], $rand_grid);
        while( $lptw_shortcode_query->have_posts() ) {
            $lptw_shortcode_query->the_post();

            $post_id = get_the_ID();

            $post_date = get_the_date($a['date_format']);
            $post_time = get_the_date($a['time_format']);
            $post_date_time = render_post_date($a, $post_date, $post_time);
            $post_subtitle = get_post_meta( $post_id, 'lptw_post_subtitle', true );
            if ( $post_subtitle != '' && $a['show_subtitle'] == 'true') { $post_subtitle_show = true; }
            else { $post_subtitle_show = false; }

            $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), $a['thumbnail_size'] );
            $url = $thumb['0'];
            if (!$url && $a['random_thumbnail'] == 'true') {
                $thumb_posts = get_posts(array('orderby' => 'rand', 'category' => $a['category_id'], 'numberposts' => 1, 'meta_key' => '_thumbnail_id'));
                foreach( $thumb_posts as $rand_post ) {
                    $rand_post_id = $rand_post->ID;
                    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($rand_post_id), $a['thumbnail_size'] );
                    $url = $thumb['0'];
                }
            }

            /* ------------------------------------ main container columns ------------------------------------ */
            if ($a['fluid_images'] == 'true') { $column_class = 'lptw-columns-'.$a['columns']; }
            else { $column_class = 'lptw-columns-fixed'; }

            /* ------------------------------------ main container styles start ------------------------------------ */
            $element_style_args = Array();
            if ($a['width'] != '' && $a['fluid_images'] != 'true')  {$element_style_args['width'] = $a['width'].'px';}
            if ($a['height'] != '' && $a['fluid_images'] != 'true') {$element_style_args['height'] = $a['height'].'px';}

            if ( $a['columns'] > 1 && $a['layout'] != 'grid-medium' ) {
                if (($i % $a['columns']) == 0) {
                    $element_style_args['padding-bottom'] = $a['space_ver'].'px';
                }
                elseif (($i % $a['columns']) == 1 && $a['fluid_images'] != 'true') {
                    $element_style_args['padding-right'] = $a['space_hor'].'px';
                    $element_style_args['padding-bottom'] = $a['space_ver'].'px';
                    $element_style_args['clear'] = 'left';
                }
                else {
                    $element_style_args['padding-right'] = $a['space_hor'].'px';
                    $element_style_args['padding-bottom'] = $a['space_ver'].'px';
                }
            } else if ( $a['columns'] == 1 && $a['layout'] != 'grid-medium' ) { $element_style_args['padding-bottom'] = $a['space_ver'].'px'; }
            else { $element_style_args['margin-bottom'] = $a['space_ver'].'px'; }
            /* ------------------------------------ main container styles end ------------------------------------ */

            /* ------------------------------------ start layouts output ------------------------------------ */

            /* ---------- basic layout (fluid images) - fixed or adaptive width, multiple columns ---------- */
            if ($a['layout'] == 'basic' ) {
                if ($url != '') {
                    $overlay_class = Array ('overlay', 'overlay-'.$a['color_scheme'], 'lptw-post-thumbnail-link');
                    $overlay_style = '';
                    $img_class = 'fluid-image-wrapper';
                    $img_content = '<img src="'.$url.'" alt="'.get_the_title().'" class="fluid" />';
                    $layout_class = 'layout-'.$a['color_scheme'];
                    }
                else {
                    $overlay_class = Array ('user-overlay', 'lptw-thumbnail-noimglink');
                    $overlay_style = Array( 'background-color' => $a['background_color'] );
                    $img_content = '';
                    $img_class = '';
                    $a['color_scheme'] = 'user';
                    }

                if ( $a['override_colors'] == 'true' ) { $user_text_color = 'style="color: '.$a['text_color'].';"'; }
                else { $user_text_color = ''; }

                $layout_classes = Array (
                    0 => 'basic-layout',
                    1 => $column_class,
                    2 => $layout_class
                );

                /* array with layout settings */
                $layout_sections = Array (
                    0 => Array (
                        'type' => 'header',
                        'display' => true,
                        'id' => '',
                        'class' => ''
                    )
                );

                /* array with layout containers */
                $layout_containers = Array (
                    0 => Array (
                        'place' => 'header',
                        'name' => 'image',
                        'display' => true,
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => $overlay_class,
                        'style' => $overlay_style,
                        'id' => ''
                    ),
                    1 => Array (
                        'place' => 'header',
                        'name' => 'title',
                        'display' => true,
                        'class' => 'lptw-post-header',
                        'style' => '',
                        'id' => ''
                    )
                );

                /* array with layout objects */
                $layout_objects = Array (
                    0 => Array (
                        'container' => 'image',
                        'display' => true,
                        'tag' => 'span',
                        'class' => $img_class,
                        'style' => '',
                        'id' => '',
                        'content' => $img_content
                    ),
                    $date_pos => Array (
                        'container' => 'title',
                        'display' => $a['show_date'],
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => Array ('lptw-post-date', 'date-'.$a['color_scheme'] ),
                        'style' => $user_text_color,
                        'id' => '',
                        'content' => $post_date_time
                    ),
                    $title_pos => Array (
                        'container' => 'title',
                        'display' => true,
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => Array( 'lptw-post-title', 'title-'.$a['color_scheme'] ),
                        'style' => $user_text_color,
                        'id' => '',
                        'content' => get_the_title()
                    ),
                    $subtitle_pos => Array (
                        'container' => 'title',
                        'display' => $post_subtitle_show,
                        'tag' => 'span',
                        'class' => Array( 'lptw-post-subtitle', 'subtitle-'.$a['color_scheme'] ),
                        'style' => $user_text_color,
                        'id' => '',
                        'content' => $post_subtitle
                    )
                );

                $content .= render_article ($layout_classes, $element_style_args, $layout_sections, $layout_containers, $layout_objects);

            /* ---------- small thumbnails ---------- */
            } elseif ($a['layout'] == 'thumbnail' ) {
                /* get thumbnail url with size 100x100px */
                $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), array ( 100,100 ) );
                $title = get_the_title();

                if (empty($thumbnail) || $thumbnail == false) {
                    $img_class = 'lptw-thumbnail-noimglink';
                    $img_style = Array (
                        'background-color' => $a['background_color'],
                        'color' => $a['text_color']
                    );
                    $link_content = substr($title, 0, 1);
                } else {
                    $img_class = 'lptw-thumbnail-link';
                    $link_content = '<img src="'.$thumbnail['0'].'" width="'.$thumbnail[1].'" height="'.$thumbnail[2].'" alt="'.$title.'" />';
                }

                $layout_classes = Array (
                    0 => 'thumbnail-layout',
                    1 => $column_class
                );

                /* array with layout settings */
                $layout_sections = Array (
                    0 => Array (
                        'type' => 'header',
                        'display' => true,
                        'id' => '',
                        'class' => 'lptw-post-header'
                    )
                );

                /* array with layout containers */
                $layout_containers = Array (
                    0 => Array (
                        'place' => 'header',
                        'name' => 'image',
                        'display' => true,
                        'class' => 'lptw-thumbnail-image',
                        'style' => '',
                        'id' => ''
                    ),
                    1 => Array (
                        'place' => 'header',
                        'name' => 'title',
                        'display' => true,
                        'class' => 'lptw-thumbnail-title',
                        'style' => '',
                        'id' => ''
                    )
                );

                /* array with layout objects */
                $layout_objects = Array (
                    0 => Array (
                        'container' => 'image',
                        'display' => true,
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => $img_class,
                        'style' => $img_style,
                        'id' => '',
                        'content' => $link_content
                    ),
                    $date_pos => Array (
                        'container' => 'title',
                        'display' => $a['show_date'],
                        'tag' => 'span',
                        'class' => 'lptw-post-date',
                        'style' => '',
                        'id' => '',
                        'content' => $post_date_time
                    ),
                    $title_pos => Array (
                        'container' => 'title',
                        'display' => true,
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => 'lptw-post-title',
                        'style' => '',
                        'id' => '',
                        'content' => $title
                    ),
                    $subtitle_pos => Array (
                        'container' => 'title',
                        'display' => $post_subtitle_show,
                        'tag' => 'span',
                        'class' => Array( 'lptw-post-subtitle' ),
                        'style' => '',
                        'id' => '',
                        'content' => $post_subtitle
                    )
                );

                $content .= render_article ($layout_classes, $element_style_args, $layout_sections, $layout_containers, $layout_objects);

            /* ---------- recent posts without thumbnails, with date as drop cap ---------- */
            } elseif ( $a['layout'] == 'dropcap' ) {
                $post_date = get_the_date('M.Y');
                $post_day = get_the_date('d');

                $layout_classes = Array (
                    0 => 'dropcap-layout',
                    1 => $column_class
                );

                /* array with layout settings */
                $layout_sections = Array (
                    0 => Array (
                        'type' => 'header',
                        'display' => true,
                        'id' => ''
                    )
                );

                /* array with layout containers */
                $layout_containers = Array (
                    0 => Array (
                        'place' => 'header',
                        'name' => 'date',
                        'display' => true,
                        'class' => 'lptw-dropcap-date',
                        'style' => Array ( 'background-color' => $a['background_color'] ),
                        'id' => ''
                    ),
                    1 => Array (
                        'place' => 'header',
                        'name' => 'title',
                        'display' => true,
                        'class' => 'lptw-dropcap-title',
                        'style' => '',
                        'id' => ''
                    )
                );

                /* array with layout objects */
                $layout_objects = Array (
                    0 => Array (
                        'container' => 'date',
                        'display' => true,
                        'tag' => 'span',
                        'class' => 'lptw-dropcap-day',
                        'style' => Array ( 'color' => $a['text_color'] ),
                        'id' => '',
                        'content' => $post_day
                    ),
                    1 => Array (
                        'container' => 'date',
                        'display' => true,
                        'tag' => 'span',
                        'class' => 'lptw-dropcap-month',
                        'style' => Array ( 'color' => $a['text_color'] ),
                        'id' => '',
                        'content' => $post_date
                    ),
                    2 => Array (
                        'container' => 'title',
                        'display' => true,
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => 'lptw-dropcap-date-link',
                        'style' => '',
                        'id' => '',
                        'content' => get_the_title()
                    ),
                    3 => Array (
                        'container' => 'title',
                        'display' => $post_subtitle_show,
                        'tag' => 'span',
                        'class' => Array( 'lptw-post-subtitle' ),
                        'style' => '',
                        'id' => '',
                        'content' => $post_subtitle
                    )
                );

                /**
                 Main render function
                 **/
                $content .= render_article ($layout_classes, $element_style_args, $layout_sections, $layout_containers, $layout_objects);

            /* --------- Responsive Grid - recent posts with thumbnail and featured posts --------- */
            } elseif ($a['layout'] == 'grid-medium' ) {
                /* get meta values */
                $featured = get_post_meta ($post_id, 'featured_post', true);
                $embedded_video = get_post_meta ($post_id, 'embedded_video', true);
                $hide_youtube_controls = get_post_meta ($post_id, 'hide_youtube_controls', true);

                /* get embedded video frame code */
                $embedded_video_frame = lptw_get_first_embed_media($post_id);

                /* ------------ start calculate featured and base height and width ------------ */
                $featured_height = $a['featured_height'] . 'px';
                if ($a['fluid_images'] == 'true') {
                    $base_width = (100 / $a['columns']) - 1;
                    $normal_width = number_format($base_width, 2, '.', '') . '%';
                    $featured_width = number_format(($base_width * 2) + 1, 2, '.', '') . '%';
                } else {
                    $normal_width = $a['width'] . 'px';
                    $featured_width = ($a['width'] * 2) + $a['space_hor'] . 'px';
                }
                /* ------------ finish calculate featured and base height and width ------------ */

                /* ------------ start create styles ------------ */
                if ( $a['height'] > 0 ) { $element_style_args['height'] = $a['height'].'px'; }
                if ( $a['excerpt_show'] == 'false' ) { $element_style_args['padding-bottom'] = '0.5rem'; }
                if ( $a['min_height'] > 0 ) { $element_style_args['min-height'] = $a['min_height'].'px'; }

                if ( $a['override_colors'] == 'true' ) {
                    $user_text_color = 'style="color: '.$a['text_color'].';"';
                    $element_style_args['background-color'] = $a['background_color'];
                } else { $user_text_color = ''; }

                if ($featured == 'on') {
                    $element_style_args['width'] = $featured_width;
                    $element_style_args['min-height'] = $featured_height;
                } else {
                    $element_style_args['width'] = $normal_width;
                }
                /* ------------ finish create styles ------------ */

                /* set the layout classes */
                $layout_classes = Array (
                    'grid-layout',
                    'lptw-grid-element'
                );

                if ($featured == 'on') {
                    array_push($layout_classes, 'lptw-featured');
                    $thumb_grid = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'lptw-grid-large' );

                    if ( $embedded_video != 'on' ) {
                        $display_post_header = 'true';
                        $element_style_args['background'] = 'url('.$thumb_grid['0'].') center center no-repeat';
                        $element_style_args['background-size'] = 'cover';
                        $title_show = true;

                        /* image start */
                        $img_tag = 'a';
                        $img_href = get_the_permalink();
                        $img_class = 'lptw-post-grid-link';
                        $img_content = '<div class="overlay overlay-'.$a['color_scheme'].'"></div>';
                        /* image end */

                    } else {
                        $display_post_header = 'false';
                        $title_show = false;
                        $img_tag = 'div';
                        $img_href = '';
                        $img_class = '';
                        $img_content = $embedded_video_frame;
                        array_push($layout_classes, 'lptw-video-container-featured');
                        if ( $hide_youtube_controls != 'on' ) { array_push($layout_classes,  ' lptw-video-container-controls'); }
                    }

                } else {
                    $display_post_header = 'true';
                    array_push($layout_classes, 'grid-element-'.$a['color_scheme']);

                    /* image start */
                    if ($embedded_video == 'on' && $embedded_video_frame !== false) {
                        $img_tag = 'div';
                        $img_href = '';
                        if ( $hide_youtube_controls != 'on' ) { $controls_class = 'lptw-video-container-controls'; }
                        else { $controls_class = ''; }
                        $img_class = Array( 'lptw-video-container', $controls_class );
                        $img_content = $embedded_video_frame;
                    } else {
                        $thumb_grid = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'large' );
                        $img_tag = 'a';
                        $img_href = get_the_permalink();
                        $img_class = '';
                        $img_content = '<img src="'.$thumb_grid['0'].'" alt="'.get_the_title().'" />';
                    }
                    /* image end */

                } /* end of post type switcher */

                /* ------------ the post excerpt start ------------ */
                if ($a['excerpt_show'] == 'true' && $featured != 'on') {
                    $excerpt_display = 'true';
                    $manual_excerpt = $lptw_shortcode_query->post->post_excerpt;
                    if ( !empty($manual_excerpt) ) {
                        $post_excerpt = $manual_excerpt;
                    } else {
                        $post_excerpt = lptw_custom_excerpt($a['excerpt_lenght'], $a['ignore_more_tag']);
                    }
                } else { $excerpt_display = false; }
                if ($a['read_more_show'] == 'true' && $a['read_more_inline'] == 'true' ) {
                    $excerpt_style = Array ( 'display' => 'inline' );
                    $read_more_style = Array ( 'margin-left' => '5px', 'display' => 'inline' );
                    }
                /* ------------ the post excerpt end ------------ */

                /* array with layout settings */
                $layout_sections = Array (
                    0 => Array (
                        'type' => 'header',
                        'display' => 'true',
                        'id' => ''
                    ),
                    1 => Array (
                        'type' => 'section',
                        'display' => $excerpt_display,
                        'id' => ''
                    ),
                );

                /* array with layout containers
                * 1 - image or video
                * 2 - post header (title and date)
                * 3 - post excerpt
                */
                $layout_containers = Array (
                    0 => Array (
                        'place' => 'header',
                        'name' => 'image',
                        'display' => 'true',
                        'class' => 'lptw-post-grid-link',
                        'style' => '',
                        'id' => ''
                    ),
                    1 => Array (
                        'place' => 'header',
                        'name' => 'title',
                        'display' => $display_post_header,
                        'class' => 'lptw-post-header',
                        'style' => '',
                        'id' => ''
                    ),
                    2 => Array (
                        'place' => 'section',
                        'name' => 'excerpt',
                        'display' => $excerpt_display,
                        'class' => Array( 'post-excerpt', 'content-'.$a['color_scheme'], ),
                        'style' => $user_text_color,
                        'id' => ''
                    )
                );

                /* array with layout objects
                * 1 - image or video
                * 2 - post title
                * 3 - post date
                * 4 - post subtitle
                * 5 - post excerpt
                * 6 - read more link
                */

                $layout_objects = Array (
                    0 => Array (
                        'container' => 'image',
                        'display' => 'true',
                        'tag' => $img_tag,
                        'href' => $img_href,
                        'class' => $img_class,
                        'style' => '',
                        'id' => '',
                        'content' => $img_content
                    ),
                    $date_pos => Array (
                        'container' => 'title',
                        'display' => $a['show_date'],
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => Array ( 'lptw-post-date', 'date-'.$a['color_scheme'] ),
                        'style' => $user_text_color,
                        'id' => '',
                        'content' => $post_date_time
                    ),
                    $title_pos => Array (
                        'container' => 'title',
                        'display' => 'true',
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => Array ( 'lptw-post-title', 'title-'.$a['color_scheme'] ),
                        'style' => $user_text_color,
                        'id' => '',
                        'content' => get_the_title()
                    ),
                    $subtitle_pos => Array (
                        'container' => 'title',
                        'display' => $post_subtitle_show,
                        'tag' => 'span',
                        'class' => Array( 'lptw-post-subtitle', 'subtitle-'.$a['color_scheme'] ),
                        'style' => $user_text_color,
                        'id' => '',
                        'content' => $post_subtitle
                    ),
                    4 => Array (
                        'container' => 'excerpt',
                        'display' => $excerpt_display,
                        'tag' => 'div',
                        'class' => '',
                        'style' => $excerpt_style,
                        'id' => '',
                        'content' => $post_excerpt
                    ),
                    5 => Array (
                        'container' => 'excerpt',
                        'display' => $a['read_more_show'],
                        'tag' => 'a',
                        'href' => get_the_permalink(),
                        'target' => $link_target,
                        'class' => Array ( 'read-more-link', 'link-'.$a['color_scheme'] ),
                        'style' => $read_more_style,
                        'id' => '',
                        'content' => $a['read_more_content']
                    )
                );


                $content .= render_article ($layout_classes, $element_style_args, $layout_sections, $layout_containers, $layout_objects);
            } /* end of the layout switcher */

            $i++;
        } // end while( $lptw_shortcode_query->have_posts() )
        $content .= '</div>';
        if ($a['layout'] == 'grid-medium') {
            $content .= '<script>
                            jQuery(window).on("load", function() {
                              var $ = jQuery;
                              $(".overlay").css("display", "block");
                              var $container = $("#lptw-grid-'.$rand_grid.'");
                              var fluid_images = '.$a['fluid_images'].';
                              var countedColumnWidth;

                              // initialize
                              $container.masonry({
                                  itemSelector: ".lptw-grid-element",';
                if ($a['fluid_images'] != 'true') {$content .= 'gutter: ' . $a['space_hor'].',';}
                $content .= '     columnWidth: function(containerWidth) {
                                        if (containerWidth < 641) {
                                            $(".lptw-grid-element").css("width", "100%");
                                            countedColumnWidth = containerWidth - 1;
                                        } else if (containerWidth > 640) {
                                            $(".lptw-grid-element").css("width", "'.$normal_width.'");
                                            $(".lptw-featured").css("width", "'.$featured_width.'");
                                            if (fluid_images === true) {
                                        	    countedColumnWidth = (containerWidth / ' . $a['columns'] . ') - 1
                                            } else {
                                        	    countedColumnWidth = ' . $a['width'] . ' - 1
                                            }
                                        }
                                        return countedColumnWidth;
                                  }';
            $content .= '     });

                                $(window).resize(function() {
                                	var $container = $("#grid-container");
                                	var viewport = $(window).width();
                                    var fluid_images = '.$a['fluid_images'].';

                                	if (viewport < 641) {
                                        $(".lptw-grid-element").css("width", "100%");
                                        $(".lptw-grid-element").css("height", "auto");
                                		$container.masonry("option", {
                                			columnWidth: viewport - 1
                                		});
                                	} else if (viewport > 640) {
                                        var containerWidth = $container.width();
                                        $(".lptw-grid-element").css("width", "'.$normal_width.'");
                                        $(".lptw-featured").css("width", "'.$featured_width.'");

                                        if (fluid_images === true) {
                                    		$container.masonry("option", {
                                    			columnWidth: (containerWidth / ' . $a['columns'] . ') - 1
                                    		});
                                        } else {
                                            $(".lptw-featured").css("height", "'.$a['height'].'");
                                    		$container.masonry("option", {
                                    			columnWidth: ' . $a['width'] . ' - 1
                                    		});
                                        }
                                    }
                                });

                            });

                        </script>';
            }
    } else {
        $content = __( 'No recent posts', 'lptw_recent_posts_domain' );
    }
    wp_reset_postdata();
    return $content;
}

add_shortcode( 'lptw_recentposts', 'lptw_display_recent_posts' );

/**
 Get the type of the layout
 Return header of the layout container
 **/
function lptw_display_layout_header ($layout, $rand_grid) {
    switch ($layout) {
        case 'basic':
            $content = '<div id="basic-container">';
        break;
        case 'thumbnail':
            $content = '<div id="thumbnail-container">';
        break;
        case 'dropcap':
            $content = '<div id="dropcap-container">';
        break;
        case 'grid-medium':
            $content = '<div class="lptw-container" id="lptw-grid-'.$rand_grid.'">';
        break;
    }
    return $content;
}

/**
 Find embedded video and use standard oembed to display it
 **/
/* --------------------------------------------- second function --------------------------------------------- */
function lptw_get_first_embed_media($post_id) {

    $post = get_post($post_id);
    $reg = preg_match('|^\s*(https?://[^\s"]+)\s*$|im', get_the_content(), $embeds);

    $embed_args = Array ( 'width' => 400, 'height' => 200 );

    if( !empty($embeds) ) {
        //return first embed
        $embed_code = wp_oembed_get( trim($embeds[0]), $embed_args );
        return $embed_code;

    } else {
        //No embeds found
        return false;
    }

}

/* --------------------------------------------- Filter video output --------------------------------------------- */

add_filter('oembed_result','lptw_oembed_result', 10, 3);
function lptw_oembed_result ($html, $url, $args) {
    global $post;

    // $args includes custom argument
    /* ---------------- only for youtube ---------------- */
    /* all arguments */
    //$args = array( 'rel' => '0', 'controls' => '0', 'showinfo' => '0' );

    $hide_youtube_controls = get_post_meta ($post->ID, 'hide_youtube_controls', true);
    if ($hide_youtube_controls == 'on') {
        /* only hide controls */
        $args = array( 'controls' => 0 );
    } else { $args = ''; }


    if ( strpos($html, 'youtu') !== false && !empty($args) ) {
    	$parameters = http_build_query( $args );

    	// Modify video parameters
	    $html = str_replace( '?feature=oembed', '?feature=oembed'.'&amp;'.$parameters, $html );
    }

    return $html;
}

/**
 Add Shortcode Builder
 **/
function lptw_register_recent_posts_menu_page(){
    add_menu_page( 'Advanced Recent Posts', 'Advanced Recent Posts', 'manage_options', 'recent_posts', 'lptw_recent_posts_manage_shortcodes', 'dashicons-editor-code' );
}
add_action( 'admin_menu', 'lptw_register_recent_posts_menu_page' );

/**
 Include Shortcode Builder scripts and styles
 **/
function lptw_recent_posts_backend_scripts() {
    $screen = get_current_screen();
    $post_type = $screen->id;
    if ( strpos($post_type, 'page_recent_posts') !== false || strpos($post_type, 'widgets') !== false ) {
    	wp_register_style('lptw-recent-posts-backend-style', plugins_url( 'backend/lptw-recent-posts-backend.css', __FILE__ ) );
    	wp_enqueue_style('lptw-recent-posts-backend-style' );

        // Add the color picker css styles
        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_script( 'lptw-shortcode-builder-script', plugins_url ( 'backend/lptw-recent-posts-shortcode-builder.js', __FILE__ ), array( 'wp-color-picker' ), false, true );

        /* chosen css & js files */
    	wp_register_style('chosen-style', plugins_url( 'backend/chosen/chosen.min.css', __FILE__ ) );
    	wp_enqueue_style('chosen-style' );

        wp_enqueue_script( 'chosen-script', plugins_url ( 'backend/chosen/chosen.jquery.min.js', __FILE__ ), array(), '1.4.2', true );
    } else if ( $post_type = 'post' ) {
    	wp_register_style('lptw-recent-posts-backend-style', plugins_url( 'backend/lptw-recent-posts-backend.css', __FILE__ ) );
    	wp_enqueue_style('lptw-recent-posts-backend-style' );
    }
}
add_action( 'admin_enqueue_scripts', 'lptw_recent_posts_backend_scripts' );

/**
 Include shortcode builder code
 **/
include( plugin_dir_path( __FILE__ ) . 'backend/lptw-recent-posts-backend.php');

?>