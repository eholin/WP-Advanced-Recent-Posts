<?php
/*
Plugin Name: Advanced Recent Posts
Plugin URI: http://lp-tricks.com/
Description: Plugin that shows the recent posts with thumbnails in the widget and in other parts of the your blog or theme with shortcodes.
Tags: widget, posts, plugin, recent, recent posts, latest, latest posts, shortcode, thumbnail, thumbnails, categories, content, featured image, Taxonomy, custom post type, custom
Version: 0.6.2
Author: Eugene Holin
Author URI: http://lp-tricks.com/
License: GPLv2 or later
Text Domain: lptw_recent_posts_domain
*/

/* load js and css styles */
function lptw_recent_posts_register_scripts() {
	wp_register_style( 'lptw-style', plugins_url( 'lptw-recent-posts.css', __FILE__ ) );
	wp_enqueue_style( 'lptw-style' );

	wp_enqueue_script( 'jquery-masonry' );

    wp_enqueue_script( 'lptw-recent-posts-script', plugins_url( 'lptw-recent-posts.js', __FILE__ ), array(), '0.5', true );
}

add_action( 'wp_enqueue_scripts', 'lptw_recent_posts_register_scripts' );

/* register custom image size for Grid Layout */
function lptw_recent_posts_activate () {
    add_image_size( 'lptw-grid-large', 610, 400, true );
}

/* trim excerpt to custom size */
function custom_excerpt ($limit) {
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

    $value = get_post_meta( $post->ID, 'featured_post', true );

    if ($value == 'on') {$checked = 'checked="checked"';}
    else {$checked = '';}
    echo '<p><label class="lptw-checkbox-label" for="featured_post"><input class="checkbox" type="checkbox" '.$checked.' id="featured_post" name="featured_post" />&nbsp;'.__( 'Featured post', 'lptw_recent_posts_domain' ).'</label></p>';
    echo '<p class="description">'.__( 'Featured post displays larger than the other posts in Grid Layout', 'lptw_recent_posts_domain' ).'</p>';
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
	$my_data = sanitize_text_field( $_POST['featured_post'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, 'featured_post', $my_data );
}
add_action( 'save_post', 'lptw_recent_posts_options_save_meta_box_data' );


/**
-------------------------------------- Fluid Images Widget --------------------------------------
**/

// Creating the widget with fluid images
class lptw_recent_posts_fluid_images_widget extends WP_Widget {

    function __construct() {

		$widget_ops = array('classname' => 'lptw_recent_posts_fluid_images_widget', 'description' => __( "Your site&#8217;s most recent Posts. Displays big fluid images, post date ant title.", 'lptw_recent_posts_domain') );
		parent::__construct('lptw-fluid-images-recent-posts', __('Recent Posts Widget (Fluid Images)', 'lptw_recent_posts_domain'), $widget_ops);
		$this->alt_option_name = 'lptw_widget_fluid_images_recent_entries';

		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );

    }

    // Creating widget front-end
    // This is where the action happens
	public function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'lptw_recent_posts_fluid_images_widget', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();

		$show_widget_title = isset( $instance['show_widget_title'] ) ? $instance['show_widget_title'] : true;
		$exclude_current_post = isset( $instance['exclude_current_post'] ) ? $instance['exclude_current_post'] : true;

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts', 'lptw_recent_posts_domain' );

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) {$number = 5;}

		$reverse_post_order = isset( $instance['reverse_post_order'] ) ? $instance['reverse_post_order'] : false;

		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : true;

		$date_format = isset( $instance['date_format'] ) ? $instance['date_format'] : 'm/d/Y';

		$time_format = isset( $instance['time_format'] ) ? $instance['time_format'] : 'g:i a';

		$show_time = isset( $instance['show_time'] ) ? $instance['show_time'] : true;

		$show_time_before = isset( $instance['show_time_before'] ) ? $instance['show_time_before'] : true;

		$show_post_title = isset( $instance['show_post_title'] ) ? $instance['show_post_title'] : true;

		$show_title_before = isset( $instance['show_title_before'] ) ? $instance['show_title_before'] : true;

		$color_scheme = isset( $instance['color_scheme'] ) ? $instance['color_scheme'] : 'light';

		$post_category = isset( $instance['post_category'] ) ? $instance['post_category'] : array();

		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

        /* don't show post in recent if it shows in page */
        global $post;
        if (!empty($post) && $exclude_current_post == true) { $exclude_post = array( $post->ID ); }

		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'post_type'             => $post_type,
			'posts_per_page'        => $number,
			'no_found_rows'         => true,
			'post_status'           => 'publish',
			'ignore_sticky_posts'   => true,
            'post__not_in'          => $exclude_post,
            'category__in'          => $post_category,
            'order'                 => 'DESC',
            'orderby'               => 'date'
		) ) );

		if ($r->have_posts()) :
            if ($reverse_post_order == 'true') { $r->posts = array_reverse($r->posts); }

?>
		<?php echo $args['before_widget']; ?>
		<?php if ( $title && $show_widget_title == true) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
		<ul class="lptw-recent-posts-fluid-images-widget">
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
        <?php
            $post_date = get_the_date($date_format);
            $post_time = get_the_date($time_format);
            if ($show_time == true) {
                if ($show_time_before == true) { $post_date_time = $post_time . ' ' . $post_date; }
                else { $post_date_time = $post_date . ' ' . $post_time; }
            }
            else { $post_date_time = $post_date; }
        ?>


			<li>
                <?php if ( has_post_thumbnail() ) :
                    $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($r->post_ID), 'large' );
                    $url = $thumb['0'];
                ?>
				<div class="lptw-post-thumbnail">
                    <a href="<?php the_permalink(); ?>" class="lptw-post-thumbnail-link"><div class="overlay overlay-<?php echo $color_scheme; ?>"><img src="<?php echo $url; ?>" alt="<?php get_the_title() ? the_title() : the_ID(); ?>" /></div>
                    <div class="lptw-post-header">
        		    	<?php if ( $show_title_before == true ) : ?>
            		    	<?php if ( $show_post_title ) : ?>
        		    		<span class="lptw-post-title title-<?php echo $color_scheme; ?>"><?php get_the_title() ? the_title() : the_ID(); ?></span>
            			    <?php endif; ?>
            		    	<?php if ( $show_date == true ) : ?>
        	    			<span class="lptw-post-date date-<?php echo $color_scheme; ?>"><?php echo $post_date_time; ?></span>
            			    <?php endif; ?>
                        <?php else : ?>
            		    	<?php if ( $show_date == true ) : ?>
        	    			<span class="lptw-post-date date-<?php echo $color_scheme; ?>"><?php echo $post_date_time; ?></span>
            			    <?php endif; ?>
            		    	<?php if ( $show_post_title ) : ?>
        		    		<span class="lptw-post-title title-<?php echo $color_scheme; ?>"><?php get_the_title() ? the_title() : the_ID(); ?></span>
            			    <?php endif; ?>
            			<?php endif; ?>
                    </div>
                    </a>
                </div>
                <?php else : ?>
    			<?php if ( $show_date == true ) : ?>
    				<span class="lptw-post-date"><?php echo $post_date; ?></span>
    			<?php endif; ?>
				<a href="<?php the_permalink(); ?>" class="lptw-post-title-link"><?php get_the_title() ? the_title() : the_ID(); ?></a>
                <?php endif; ?>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $args['after_widget']; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'lptw_recent_posts_fluid_images_widget', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

    /* --------------------------------- Widget Backend --------------------------------- */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) { $title = esc_attr( $instance[ 'title' ]) ; }
        else { $title = __( 'Recent posts', 'lptw_recent_posts_domain' ); }

        if ( isset( $instance[ 'show_widget_title' ] ) ) { $show_widget_title = (bool) $instance[ 'show_widget_title' ]; }
        else { $show_widget_title = true; }

        if ( isset( $instance[ 'exclude_current_post' ] ) ) { $exclude_current_post = (bool) $instance[ 'exclude_current_post' ]; }
        else { $exclude_current_post = true; }

        if ( isset( $instance[ 'number' ] ) ) { $number = absint( $instance[ 'number' ] ); }
        else { $number = 5; }

        if ( isset( $instance[ 'reverse_post_order' ] ) ) { $reverse_post_order = (bool) $instance[ 'reverse_post_order' ]; }
        else { $reverse_post_order = false; }

        if ( isset( $instance[ 'show_post_title' ] ) ) { $show_post_title = (bool) $instance[ 'show_post_title' ]; }
        else { $show_post_title = true; }

        if ( isset( $instance[ 'show_title_before' ] ) ) { $show_title_before = (bool) $instance[ 'show_title_before' ]; }
        else { $show_title_before = false; }

        if ( isset( $instance[ 'show_date' ] ) ) { $show_date = (bool) $instance[ 'show_date' ]; }
        else { $show_date = false; }

        if ( isset( $instance[ 'date_format' ] ) ) { $date_format = $instance[ 'date_format' ]; }
        else { $date_format = 'm/d/Y'; }

        if ( isset( $instance[ 'time_format' ] ) ) { $time_format = $instance[ 'time_format' ]; }
        else { $time_format = 'g:i a'; }

        if ( isset( $instance[ 'show_time' ] ) ) { $show_time = (bool) $instance[ 'show_time' ]; }
        else { $show_time = false; }

        if ( isset( $instance[ 'show_time_before' ] ) ) { $show_time_before = (bool) $instance[ 'show_time_before' ]; }
        else { $show_time_before = false; }

        if ( isset( $instance[ 'color_scheme' ] ) ) { $color_scheme = $instance[ 'color_scheme' ] ; }
        else { $color_scheme = 'light'; }

        if ( isset( $instance[ 'post_category' ] ) ) { $post_category = $instance[ 'post_category' ]; }

        if ( isset( $instance[ 'post_type' ] ) ) { $post_type = $instance[ 'post_type' ]; }
        else { $post_type = 'post_type'; }

        // Widget admin form
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'lptw_recent_posts_domain' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />

		<p><input class="checkbox" type="checkbox" <?php checked( $show_widget_title ); ?> id="<?php echo $this->get_field_id( 'show_widget_title' ); ?>" name="<?php echo $this->get_field_name( 'show_widget_title' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_widget_title' ); ?>"><?php _e( 'Display widget title?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e( 'Post type:', 'lptw_recent_posts_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'post_type' ); ?>" id="<?php echo $this->get_field_id('post_type'); ?>" class="widefat">
                <?php
                    $post_types = get_post_types( '', 'names' );
                    foreach ( $post_types as $registered_post_type ) {
                        echo '<option value="' . $registered_post_type . '"'.selected( $post_type, $registered_post_type ).'>' . $registered_post_type . '</option>';
                    }
                ?>
			</select>
		</p>

        <div class="lptw-categories-dropdown"><a class="lptw-categories-dropdown-link" href="#">List of categories <span id="lptw-categories-action" class="lptw-categories-action-down"></span></a></div>
        <div id="lptw-categories-wrapper">
            <fieldset id="categories_list">
                <ul class="lptw-categories-list">
                    <?php wp_category_checklist(0, 0, $post_category); ?>
                </ul>
            </fieldset>
            <p class="description">If none of the categories is selected - will be displayed posts from all categories.</p>
        </div>

		<p><input class="checkbox" type="checkbox" <?php checked( $exclude_current_post ); ?> id="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>" name="<?php echo $this->get_field_name( 'exclude_current_post' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>"><?php _e( 'Exclude current post from list?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'lptw_recent_posts_domain' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $reverse_post_order ); ?> id="<?php echo $this->get_field_id( 'reverse_post_order' ); ?>" name="<?php echo $this->get_field_name( 'reverse_post_order' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'reverse_post_order' ); ?>"><?php _e( 'Reverse post order: display the latest post last in the list?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('date_format'); ?>"><?php _e( 'Date format:', 'lptw_recent_posts_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'date_format' ); ?>" id="<?php echo $this->get_field_id('date_format'); ?>" class="widefat">
				<option value="d.m.Y"<?php selected( $date_format, 'd.m.Y' ); ?>><?php echo date('d.m.Y') ?></option>
				<option value="m/d/Y"<?php selected( $date_format, 'm/d/Y' ); ?>><?php echo date('m/d/Y'); ?></option>
				<option value="d/m/Y"<?php selected( $date_format, 'd/m/Y' ); ?>><?php echo date('d/m/Y'); ?></option>
				<option value="F j, Y"<?php selected( $date_format, 'F j, Y' ); ?>><?php echo date('F j, Y'); ?></option>
				<option value="M j, Y"<?php selected( $date_format, 'M j, Y' ); ?>><?php echo date('M j, Y'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('time_format'); ?>"><?php _e( 'Time format:', 'lptw_recent_posts_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'time_format' ); ?>" id="<?php echo $this->get_field_id('time_format'); ?>" class="widefat">
				<option value="H:i"<?php selected( $time_format, 'H:i' ); ?>><?php echo date('H:i') ?></option>
				<option value="H:i:s"<?php selected( $time_format, 'H:i:s' ); ?>><?php echo date('H:i:s'); ?></option>
				<option value="g:i a"<?php selected( $time_format, 'g:i a' ); ?>><?php echo date('g:i a'); ?></option>
				<option value="g:i:s a"<?php selected( $time_format, 'g:i:s a' ); ?>><?php echo date('g:i:s a'); ?></option>
			</select>
		</p>
		<p><input class="checkbox" type="checkbox" <?php checked( $show_time ); ?> id="<?php echo $this->get_field_id( 'show_time' ); ?>" name="<?php echo $this->get_field_name( 'show_time' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_time' ); ?>"><?php _e( 'Display post time?', 'lptw_recent_posts_domain' ); ?></label></p>
		<p><input class="checkbox" type="checkbox" <?php checked( $show_time_before ); ?> id="<?php echo $this->get_field_id( 'show_time_before' ); ?>" name="<?php echo $this->get_field_name( 'show_time_before' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_time_before' ); ?>" title="<?php _e( 'By default post time displays after post date.', 'lptw_recent_posts_domain' );?>"><?php _e( 'Display post time before post date?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_post_title ); ?> id="<?php echo $this->get_field_id( 'show_post_title' ); ?>" name="<?php echo $this->get_field_name( 'show_post_title' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_post_title' ); ?>"><?php _e( 'Display post title?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_title_before ); ?> id="<?php echo $this->get_field_id( 'show_title_before' ); ?>" name="<?php echo $this->get_field_name( 'show_title_before' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_title_before' ); ?>"><?php _e( 'Display post title before post date and time?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('color_scheme'); ?>"><?php _e( 'Color scheme:', 'lptw_recent_posts_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'color_scheme' ); ?>" id="<?php echo $this->get_field_id('color_scheme'); ?>" class="widefat">
				<option value="light"<?php selected( $color_scheme, 'light' ); ?>><?php _e('Light', 'lptw_recent_posts_domain'); ?></option>
				<option value="dark"<?php selected( $color_scheme, 'dark' ); ?>><?php _e('Dark', 'lptw_recent_posts_domain'); ?></option>
			</select>
		</p>

        </p>
        <?php
    }

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['show_widget_title'] = isset( $new_instance['show_widget_title'] ) ? (bool) $new_instance['show_widget_title'] : false;
		$instance['exclude_current_post'] = isset( $new_instance['exclude_current_post'] ) ? (bool) $new_instance['exclude_current_post'] : false;
		$instance['reverse_post_order'] = isset( $new_instance['reverse_post_order'] ) ? (bool) $new_instance['reverse_post_order'] : false;
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_post_title'] = isset( $new_instance['show_post_title'] ) ? (bool) $new_instance['show_post_title'] : false;
		$instance['show_title_before'] = isset( $new_instance['show_title_before'] ) ? (bool) $new_instance['show_title_before'] : false;
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['date_format'] = strip_tags($new_instance['date_format']);
		$instance['time_format'] = strip_tags($new_instance['time_format']);
		$instance['show_time'] = isset( $new_instance['show_time'] ) ? (bool) $new_instance['show_time'] : false;
		$instance['show_time_before'] = isset( $new_instance['show_time_before'] ) ? (bool) $new_instance['show_time_before'] : false;
		$instance['color_scheme'] = strip_tags($new_instance['color_scheme']);

		if( isset( $_POST['post_category'] ) ) {
		    $posted_terms = $_POST['post_category'];
			// Once we actually have the $_POSTed terms, validate and and save them
			foreach ( $posted_terms as $term ) {
			    if( term_exists( absint( $term ), $taxonomy ) ) {
				    $terms[] = absint( $term );
				}
			}
            $instance['post_category'] = $terms;
		} else { $instance['post_category'] = ''; }

		$instance['post_type'] = strip_tags($new_instance['post_type']);

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['lptw_widget_fluid_images_recent_entries']) )
			delete_option('lptw_widget_fluid_images_recent_entries');

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete('lptw_recent_posts_fluid_images_widget', 'widget');
	}

} // Class wpb_widget ends here

/**
-------------------------------------- Small Thumbnails Widget --------------------------------------
**/

// Creating the widget with fluid images
class lptw_recent_posts_thumbnails_widget extends WP_Widget {

    function __construct() {

		$widget_ops = array('classname' => 'lptw_recent_posts_thumbnails_widget', 'description' => __( "Your site&#8217;s most recent Posts. Displays small thumbnails, post date and title.", 'lptw_recent_posts_domain') );
		parent::__construct('lptw-thumbnails-recent-posts', __('Recent Posts Widget (Thumbnails)', 'lptw_recent_posts_domain'), $widget_ops);
		$this->alt_option_name = 'lptw_widget_thumbnails_recent_entries';

		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );

    }

    // Creating widget front-end
    // This is where the action happens
	public function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'lptw_recent_posts_thumbnails_widget', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();

		$show_widget_title = isset( $instance['show_widget_title'] ) ? $instance['show_widget_title'] : true;
		$exclude_current_post = isset( $instance['exclude_current_post'] ) ? $instance['exclude_current_post'] : true;

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts', 'lptw_recent_posts_domain' );

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) {$number = 5;}

        if ( isset( $instance[ 'reverse_post_order' ] ) ) { $reverse_post_order = (bool) $instance[ 'reverse_post_order' ]; }
        else { $reverse_post_order = false; }

		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : true;

		$date_format = isset( $instance['date_format'] ) ? $instance['date_format'] : 'm/d/Y';

		$time_format = isset( $instance['time_format'] ) ? $instance['time_format'] : 'g:i a';

		$show_time = isset( $instance['show_time'] ) ? $instance['show_time'] : true;

		$show_time_before = isset( $instance['show_time_before'] ) ? $instance['show_time_before'] : true;

		$show_post_title = isset( $instance['show_post_title'] ) ? $instance['show_post_title'] : true;

		$show_title_before = isset( $instance['show_title_before'] ) ? $instance['show_title_before'] : false;

		$post_category = isset( $instance['post_category'] ) ? $instance['post_category'] : array();

		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

        /* don't show post in recent if it shows in page */
        global $post;
        if (!empty($post) && $exclude_current_post == true) { $exclude_post = array( $post->ID ); }

		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'post_type'             => $post_type,
			'posts_per_page'        => $number,
			'no_found_rows'         => true,
			'post_status'           => 'publish',
			'ignore_sticky_posts'   => true,
            'post__not_in'          => $exclude_post,
            'category__in'          => $post_category,
            'order'                 => 'DESC',
            'orderby'               => 'date'
		) ) );

		if ($r->have_posts()) :
            if ($reverse_post_order == 'true') { $r->posts = array_reverse($r->posts); }
?>
		<?php echo $args['before_widget']; ?>
		<?php if ( $title && $show_widget_title == true) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
		<ul class="lptw-recent-posts-thumbnails-widget">
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
        <?php
            $post_date = get_the_date($date_format);
            $post_time = get_the_date($time_format);
            if ($show_time == true) {
                if ($show_time_before == true) { $post_date_time = $post_time . ' ' . $post_date; }
                else { $post_date_time = $post_date . ' ' . $post_time; }
            }
            else { $post_date_time = $post_date; }
        ?>

			<li>
                <div class="lptw-post-small-thumbnail">
                    <a href="<?php the_permalink(); ?>" class="lptw-thumbnail-link"><?php if ( has_post_thumbnail() ) {the_post_thumbnail( array(100, 100) );} ?></a>
                    <div class="lptw-post-header">
                        <?php if ( $show_title_before == true ) : ?>
            		    	<?php if ( $show_post_title ) : ?>
            		    	<a href="<?php the_permalink(); ?>" class="lptw-header-link"><?php get_the_title() ? the_title() : the_ID(); ?></a>
            			    <?php endif; ?>
            		    	<?php if ( $show_date == true ) : ?>
            	    		<span class="lptw-post-date"><?php echo $post_date_time; ?></span>
            			    <?php endif; ?>
                        <?php else : ?>
            		    	<?php if ( $show_date == true ) : ?>
            	    		<span class="lptw-post-date"><?php echo $post_date_time; ?></span>
            			    <?php endif; ?>
            		    	<?php if ( $show_post_title ) : ?>
            		    	<a href="<?php the_permalink(); ?>" class="lptw-header-link"><?php get_the_title() ? the_title() : the_ID(); ?></a>
            			    <?php endif; ?>
            			<?php endif; ?>
                    </div>
                </div>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $args['after_widget']; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'lptw_recent_posts_thumbnails_widget', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

    /* --------------------------------- Widget Backend --------------------------------- */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) { $title = esc_attr( $instance[ 'title' ]) ; }
        else { $title = __( 'Recent posts', 'lptw_recent_posts_domain' ); }

        if ( isset( $instance[ 'show_widget_title' ] ) ) { $show_widget_title = (bool) $instance[ 'show_widget_title' ]; }
        else { $show_widget_title = true; }

        if ( isset( $instance[ 'exclude_current_post' ] ) ) { $exclude_current_post = (bool) $instance[ 'exclude_current_post' ]; }
        else { $exclude_current_post = true; }

        if ( isset( $instance[ 'number' ] ) ) { $number = absint( $instance[ 'number' ] ); }
        else { $number = 5; }

        if ( isset( $instance[ 'reverse_post_order' ] ) ) { $reverse_post_order = (bool) $instance[ 'reverse_post_order' ]; }
        else { $reverse_post_order = false; }

        if ( isset( $instance[ 'show_post_title' ] ) ) { $show_post_title = (bool) $instance[ 'show_post_title' ]; }
        else { $show_post_title = true; }

        if ( isset( $instance[ 'show_title_before' ] ) ) { $show_title_before = (bool) $instance[ 'show_title_before' ]; }
        else { $show_title_before = false; }

        if ( isset( $instance[ 'show_date' ] ) ) { $show_date = (bool) $instance[ 'show_date' ]; }
        else { $show_date = false; }

        if ( isset( $instance[ 'date_format' ] ) ) { $date_format = $instance[ 'date_format' ]; }
        else { $date_format = 'd.m.Y'; }

        if ( isset( $instance[ 'time_format' ] ) ) { $time_format = $instance[ 'time_format' ]; }
        else { $time_format = 'H:i'; }

        if ( isset( $instance[ 'show_time' ] ) ) { $show_time = (bool) $instance[ 'show_time' ]; }
        else { $show_time = false; }

        if ( isset( $instance[ 'show_time_before' ] ) ) { $show_time_before = (bool) $instance[ 'show_time_before' ]; }
        else { $show_time_before = false; }

        if ( isset( $instance[ 'post_category' ] ) ) { $post_category = $instance[ 'post_category' ]; }

        if ( isset( $instance[ 'post_type' ] ) ) { $post_type = $instance[ 'post_type' ]; }
        else { $post_type = 'post_type'; }

        // Widget admin form
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'lptw_recent_posts_domain' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />

		<p><input class="checkbox" type="checkbox" <?php checked( $show_widget_title ); ?> id="<?php echo $this->get_field_id( 'show_widget_title' ); ?>" name="<?php echo $this->get_field_name( 'show_widget_title' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_widget_title' ); ?>"><?php _e( 'Display widget title?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e( 'Post type:', 'lptw_recent_posts_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'post_type' ); ?>" id="<?php echo $this->get_field_id('post_type'); ?>" class="widefat">
                <?php
                    $post_types = get_post_types( '', 'names' );
                    foreach ( $post_types as $registered_post_type ) {
                        echo '<option value="' . $registered_post_type . '"'.selected( $post_type, $registered_post_type ).'>' . $registered_post_type . '</option>';
                    }
                ?>
			</select>
		</p>

        <div class="lptw-categories-dropdown"><a class="lptw-categories-dropdown-link" href="#">List of categories <span id="lptw-categories-action" class="lptw-categories-action-down"></span></a></div>
        <div id="lptw-categories-wrapper">
            <fieldset id="categories_list">
                <ul class="lptw-categories-list">
                    <?php wp_category_checklist(0, 0, $post_category); ?>
                </ul>
            </fieldset>
            <p class="description">If none of the categories is selected - will be displayed posts from all categories.</p>
        </div>

		<p><input class="checkbox" type="checkbox" <?php checked( $exclude_current_post ); ?> id="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>" name="<?php echo $this->get_field_name( 'exclude_current_post' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>"><?php _e( 'Exclude current post from list?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'lptw_recent_posts_domain' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $reverse_post_order ); ?> id="<?php echo $this->get_field_id( 'reverse_post_order' ); ?>" name="<?php echo $this->get_field_name( 'reverse_post_order' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'reverse_post_order' ); ?>"><?php _e( 'Reverse post order: display the latest post last in the list?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('date_format'); ?>"><?php _e( 'Date format:', 'lptw_recent_posts_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'date_format' ); ?>" id="<?php echo $this->get_field_id('date_format'); ?>" class="widefat">
				<option value="d.m.Y"<?php selected( $date_format, 'd.m.Y' ); ?>><?php echo date('d.m.Y') ?></option>
				<option value="m/d/Y"<?php selected( $date_format, 'm/d/Y' ); ?>><?php echo date('m/d/Y'); ?></option>
				<option value="d/m/Y"<?php selected( $date_format, 'd/m/Y' ); ?>><?php echo date('d/m/Y'); ?></option>
				<option value="F j, Y"<?php selected( $date_format, 'F j, Y' ); ?>><?php echo date('F j, Y'); ?></option>
				<option value="M j, Y"<?php selected( $date_format, 'M j, Y' ); ?>><?php echo date('M j, Y'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('time_format'); ?>"><?php _e( 'Time format:', 'lptw_recent_posts_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'time_format' ); ?>" id="<?php echo $this->get_field_id('time_format'); ?>" class="widefat">
				<option value="H:i"<?php selected( $time_format, 'H:i' ); ?>><?php echo date('H:i') ?></option>
				<option value="H:i:s"<?php selected( $time_format, 'H:i:s' ); ?>><?php echo date('H:i:s'); ?></option>
				<option value="g:i a"<?php selected( $time_format, 'g:i a' ); ?>><?php echo date('g:i a'); ?></option>
				<option value="g:i:s a"<?php selected( $time_format, 'g:i:s a' ); ?>><?php echo date('g:i:s a'); ?></option>
			</select>
		</p>
		<p><input class="checkbox" type="checkbox" <?php checked( $show_time ); ?> id="<?php echo $this->get_field_id( 'show_time' ); ?>" name="<?php echo $this->get_field_name( 'show_time' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_time' ); ?>"><?php _e( 'Display post time?', 'lptw_recent_posts_domain' ); ?></label></p>
		<p><input class="checkbox" type="checkbox" <?php checked( $show_time_before ); ?> id="<?php echo $this->get_field_id( 'show_time_before' ); ?>" name="<?php echo $this->get_field_name( 'show_time_before' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_time_before' ); ?>" title="<?php _e( 'By default post time displays after post date.', 'lptw_recent_posts_domain' );?>"><?php _e( 'Display post time before post date?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_post_title ); ?> id="<?php echo $this->get_field_id( 'show_post_title' ); ?>" name="<?php echo $this->get_field_name( 'show_post_title' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_post_title' ); ?>"><?php _e( 'Display post title?', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_title_before ); ?> id="<?php echo $this->get_field_id( 'show_title_before' ); ?>" name="<?php echo $this->get_field_name( 'show_title_before' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_title_before' ); ?>"><?php _e( 'Display post title before post date and time??', 'lptw_recent_posts_domain' ); ?></label></p>

        </p>
        <?php
    }

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['show_widget_title'] = isset( $new_instance['show_widget_title'] ) ? (bool) $new_instance['show_widget_title'] : false;
		$instance['exclude_current_post'] = isset( $new_instance['exclude_current_post'] ) ? (bool) $new_instance['exclude_current_post'] : false;
		$instance['number'] = (int) $new_instance['number'];
		$instance['reverse_post_order'] = isset( $new_instance['reverse_post_order'] ) ? (bool) $new_instance['reverse_post_order'] : false;
		$instance['show_post_title'] = isset( $new_instance['show_post_title'] ) ? (bool) $new_instance['show_post_title'] : false;
		$instance['show_title_before'] = isset( $new_instance['show_title_before'] ) ? (bool) $new_instance['show_title_before'] : false;
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['date_format'] = strip_tags($new_instance['date_format']);
		$instance['time_format'] = strip_tags($new_instance['time_format']);
		$instance['show_time'] = isset( $new_instance['show_time'] ) ? (bool) $new_instance['show_time'] : false;
		$instance['show_time_before'] = isset( $new_instance['show_time_before'] ) ? (bool) $new_instance['show_time_before'] : false;

		if( isset( $_POST['post_category'] ) ) {
		    $posted_terms = $_POST['post_category'];
			// Once we actually have the $_POSTed terms, validate and and save them
			foreach ( $posted_terms as $term ) {
			    if( term_exists( absint( $term ), $taxonomy ) ) {
				    $terms[] = absint( $term );
				}
			}
            $instance['post_category'] = $terms;
		} else { $instance['post_category'] = ''; }

		$instance['post_type'] = strip_tags($new_instance['post_type']);

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['lptw_widget_thumbnails_recent_entries']) )
			delete_option('lptw_widget_thumbnails_recent_entries');

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete('lptw_recent_posts_thumbnails_widget', 'widget');
	}

} // Class wpb_widget ends here

// Register and load the widget
function lptw_recent_posts_load_widget() {
	register_widget( 'lptw_recent_posts_fluid_images_widget' );
	register_widget( 'lptw_recent_posts_thumbnails_widget' );
}
add_action( 'widgets_init', 'lptw_recent_posts_load_widget' );


/**
-------------------------------------- Shortcode --------------------------------------
**/

// all-news-small-thumb

function lptw_display_recent_posts ( $atts ) {
    $default_posts_per_page =  get_option( 'posts_per_page', '10' );

    $a = shortcode_atts( array(
        'post_type'                 => 'post',
        'category_id'               => '',
        'post_parent'               => '0',
        'posts_per_page'            => $default_posts_per_page,
        'exclude_posts'             => '',
        'thumbnail_size'            => 'thumbnail',
        'random_thumbnail'          => 'false',
        'layout'                    => 'basic',
        'color_scheme'              => 'light',
        'show_date'                 => 'true',
        'fluid_images'              => 'false',
        'columns'                   => '1',
        'height'                    => '',
        'width'                     => '300',
        'date_format'               => 'd.m.Y',
        'time_format'               => 'H:i',
        'show_time'                 => 'true',
        'show_time_before'          => 'true',
        'show_date_before_title'    => 'true',
        'reverse_post_order'        => 'false'
    ), $atts );

    if ($a['width'] != '' || $a['height'] != '') {
        $dim_style = 'style="';
        if ($a['width'] != '')  {$dim_style .= 'width:'.$a['width'].'px;';}
        if ($a['height'] != '') {$dim_style .= 'height:'.$a['height'].'px;';}
        $dim_style .= '"';
    }

    if ($a['fluid_images'] == 'true') { $dim_style = 'style="width:100%;"'; }

    if ($a['columns'] < 1) {$a['columns'] = 1;}
    elseif ($a['columns'] > 2) {$a['columns'] = 2;}
    $column_style = 'columns-'.$a['columns'];

    if (!empty($a['exclude_posts'])) {
        $exclude_post = explode(',', $a['exclude_posts']);
        }
    else { $exclude_post = ''; }

    $args = array(
        'post_type'       => $a['post_type'],
        'cat'             => $a['category_id'],
        'post_parent'     => $a['post_parent'],
        'post__not_in'    => $exclude_post,
        'posts_per_page'  => $a['posts_per_page'],
        'order'           => 'DESC',
        'orderby'         => 'date'
        );

    $allnews = new WP_Query( $args );
    if( $allnews->have_posts() ) {
        if ($a['reverse_post_order'] == 'true') { $allnews->posts = array_reverse($allnews->posts); }
        $i=0;
        $content = '';
        if ($a['layout'] == 'grid-medium') {$content .= '<div id="grid-container">';}
        while( $allnews->have_posts() ) {
            $allnews->the_post();

            $post_id = get_the_ID();

            $post_date = get_the_date($a['date_format']);
            $post_time = get_the_date($a['time_format']);
            if ($a['show_time'] == 'true') {
                if ($a['show_time_before'] == 'true') { $post_date_time = $post_time . ' ' . $post_date; }
                else { $post_date_time = $post_date . ' ' . $post_time; }
            }
            else { $post_date_time = $post_date; }


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

            if ($a['columns'] > 1) {
                if (($i % 2) == 1) {$cell_style = 'last-cell';}
                else {$cell_style = 'inner-cell';}
            } else {$cell_style = 'last-cell';}

            /* start layouts output */
            /* basic layout - one or tho columns, fixed or adaptive width */
            if ($a['layout'] == 'basic' ) {
                $content .= '
                <article class="basic-layout '.$column_style.' '.$cell_style.'" '.$dim_style.'>
                    <header>
                        <a href="'.get_the_permalink().'" class="lptw-post-thumbnail-link"><div class="overlay overlay-'.$a['color_scheme'].'"><img src="'.$url.'" alt="'.get_the_title().'" class="fluid" /></div>
                        <div class="lptw-post-header">';
                if ( $a['show_date_before_title'] == 'true' ) {
                	if ( $a['show_date'] == 'true') {$content .= '<span class="lptw-post-date date-'.$a['color_scheme'].'">'.$post_date_time.'</span>';}
            		$content .= '<span class="lptw-post-title title-'.$a['color_scheme'].'">'.get_the_title().'</span>';
                } else {
            		$content .= '<span class="lptw-post-title title-'.$a['color_scheme'].'">'.get_the_title().'</span>';
                	if ( $a['show_date'] == 'true') {$content .= '<span class="lptw-post-date date-'.$a['color_scheme'].'">'.$post_date_time.'</span>';}
                }
                $content .= '</div>
                        </a>
                    </header>
                </article>';

            /* small thumbnails */
            } elseif ($a['layout'] == 'thumbnail' ) {
                $thumb_100 = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), array ( 100,100 ) );
                $url_100 = $thumb_100['0'];
                $content .= '<article class="thumbnail-layout '.$column_style.' '.$cell_style.'" '.$dim_style.'>
                    <a href="'.get_the_permalink().'" class="lptw-thumbnail-link"><img src="'.$url_100.'" width="100" height="100" alt="'.get_the_title().'" /></a>
                    <header class="lptw-post-header">';
                if ( $a['show_date_before_title'] == 'true' ) {
                    if ( $a['show_date'] == 'true') { $content .= '<span class="lptw-post-date">'.$post_date_time.'</span>'; }
            		$content .= '<a href="'.get_the_permalink().'" class="lptw-post-title">'.get_the_title().'</a>';
                } else {
            		$content .= '<a href="'.get_the_permalink().'" class="lptw-post-title">'.get_the_title().'</a>';
                    if ( $a['show_date'] == 'true') { $content .= '<span class="lptw-post-date">'.$post_date_time.'</span>'; }
                }
                $content .= '</header>
                </article>';

            /* recent posts without thumbnails, with date as drop cap */
            } elseif ($a['layout'] == 'dropcap' ) {
                $post_date = get_the_date('M.Y');
                $post_day = get_the_date('d');

                $content .= '<article class="dropcap-layout '.$column_style.' '.$cell_style.'" '.$dim_style.'>
                <header>
                    <div class="lptw-dropcap-date">
                        <span class="lptw-dropcap-day">'.$post_day.'</span>
                        <span class="lptw-dropcap-month">'.$post_date.'</span>
                    </div>
                    <a class="lptw-dropcap-date-link" href="'.get_the_permalink().'">'.get_the_title().'</a>
                </header>
            </article>';

            /* recent posts with thumbnail and featured posts */
            } elseif ($a['layout'] == 'grid-medium' ) {
                $featured = get_post_meta ($post_id, 'featured_post', true);
                if ($featured == 'on') {
                    $thumb_grid = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'lptw-grid-large' );
                    $url_grid = $thumb_grid['0'];

                    $content .= '
                    <article id="grid-'. $post_id .'" class="grid-layout lptw-grid-element w3">
                        <header>
                            <a href="'.get_the_permalink().'" class="lptw-post-grid-link"><div class="overlay overlay-'.$a['color_scheme'].'"><img src="'.$url_grid.'" alt="'.get_the_title().'" /></div>
                            <div class="lptw-post-header">';
                    if ( $a['show_date_before_title'] == 'true' ) {
                    	if ( $a['show_date'] == 'true') {$content .= '<span class="lptw-post-date date-'.$a['color_scheme'].'">'.$post_date_time.'</span>';}
                		$content .= '<span class="lptw-post-title title-'.$a['color_scheme'].'">'.get_the_title().'</span>';
                    } else {
                		$content .= '<span class="lptw-post-title title-'.$a['color_scheme'].'">'.get_the_title().'</span>';
                    	if ( $a['show_date'] == 'true') {$content .= '<span class="lptw-post-date date-'.$a['color_scheme'].'">'.$post_date_time.'</span>';}
                    }
                    $content .= '</div>
                            </a>
                        </header>';
                    $content .= '</article>';
                }
                else {
                    $thumb_grid = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'medium' );
                    $url_grid = $thumb_grid['0'];

                    $content .= '
                    <article id="grid-'. $post_id .'" class="grid-layout lptw-grid-element grid-element-'.$a['color_scheme'].'">
                        <header>
                            <a href="'.get_the_permalink().'" class="lptw-post-grid-img"><img src="'.$url_grid.'" alt="'.get_the_title().'" /></a>
                            <div class="lptw-post-header">';
                    if ( $a['show_date_before_title'] == 'true' ) {
                    	if ( $a['show_date'] == 'true') {$content .= '<span class="lptw-post-date date-'.$a['color_scheme'].'">'.$post_date_time.'</span>';}
                		$content .= '<a class="lptw-post-title title-'.$a['color_scheme'].'" href="'.get_the_permalink().'">'.get_the_title().'</a>';
                    } else {
                		$content .= '<a class="lptw-post-title title-'.$a['color_scheme'].'" href="'.get_the_permalink().'">'.get_the_title().'</a>';
                    	if ( $a['show_date'] == 'true') {$content .= '<span class="lptw-post-date date-'.$a['color_scheme'].'">'.$post_date_time.'</span>';}
                    }
                    $content .= '</div>
                        </header>';
                    if ( has_excerpt( $post_id ) ) {
                        $my_excerpt = get_the_excerpt();
                        $content .= '<content class="post-excerpt content-'.$a['color_scheme'].'">' . $my_excerpt . '</content>';
                    } else {
                        $my_excerpt = custom_excerpt(35);
                        $content .= '<content class="post-excerpt content-'.$a['color_scheme'].'">' . $my_excerpt . '</content>';
                    }
                    $content .= '</article>';
                }
            }

            $i++;
        } // end while( $allnews->have_posts() )
        if ($a['layout'] == 'grid-medium') {$content .= '</div>';}
        return $content;
    } else {
        return __( 'No recent posts', 'lptw_recent_posts_domain' );
    }
}

add_shortcode( 'lptw_recentposts', 'lptw_display_recent_posts' );

/*
 * Add Shortcode Builder
 */
function lptw_register_recent_posts_menu_page(){
    add_menu_page( 'Advanced Recent Posts', 'Advanced Recent Posts', 'manage_options', 'recent_posts', 'lptw_recent_posts_manage_shortcodes', 'dashicons-editor-code', 100 );
}
add_action( 'admin_menu', 'lptw_register_recent_posts_menu_page' );

function lptw_recent_posts_backend_scripts() {
	wp_register_style('lptw-recent-posts-backend-style', plugins_url( 'backend/lptw-recent-posts-backend.css', __FILE__ ) );
	wp_enqueue_style('lptw-recent-posts-backend-style' );

    wp_enqueue_script( 'lptw-shortcode-builder-script', plugins_url ( 'backend/lptw-recent-posts-shortcode-builder.js', __FILE__ ), array(), '0.3', true );
}
add_action( 'admin_enqueue_scripts', 'lptw_recent_posts_backend_scripts' );

/* include shortcode builder  */
include( plugin_dir_path( __FILE__ ) . 'backend/lptw-recent-posts-backend.php');

?>