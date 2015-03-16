<?php
/*
Plugin Name: Recent Posts Widget
Plugin URI: http://lp-tricks.com/
Description: High customizable widget with recent posts
Version: 0.1b
Author: Eugene Holin
Author URI: http://lp-tricks.com/
License: GPLv2 or later
Text Domain: lptw_recent_posts_domain
*/
/* Start Adding Functions Below this Line */

// Creating the widget
class lptw_recent_posts_widget extends WP_Widget {

    function __construct() {

		$widget_ops = array('classname' => 'lptw_recent_posts_widget', 'description' => __( "Your site&#8217;s most recent Posts.", 'lptw_recent_posts_domain') );
		parent::__construct('lptw-recent-posts', __('Recent Posts Widget', 'lptw_recent_posts_domain'), $widget_ops);
		$this->alt_option_name = 'lptw_widget_recent_entries';

		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );

    }

    // Creating widget front-end
    // This is where the action happens
	public function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'lptw_recent_posts_widget', 'widget' );
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

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts', 'lptw_recent_posts_domain' );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) {$number = 5;}

		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		$color_scheme = isset( $instance['color_scheme'] ) ? $instance['color_scheme'] : 'light';

		/**
		 * Filter the arguments for the Recent Posts widget.
		 *
		 * @since 3.4.0
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args An array of arguments used to retrieve the recent posts.
		 */

        global $post;
        if (!empty($post)) { $exclude_post = array( $post->ID ); }

		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
            'post__not_in'        => $exclude_post
		) ) );

		if ($r->have_posts()) :
?>
		<?php echo $args['before_widget']; ?>
		<?php if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
		<ul class="lptw-recent-posts-widget">
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			<li>
                <?php if ( has_post_thumbnail() ) : ?>
				<div class="lptw-post-thumbnail">
                    <a href="<?php the_permalink(); ?>" class="lptw-post-thumbnail-link"><div class="overlay overlay-<?php echo $color_scheme; ?>"><?php the_post_thumbnail( array(300, 300) ); ?></div>
                    <div class="lptw-post-header">
        		    	<?php if ( $show_date ) : ?>
    	    			<span class="lptw-post-date date-<?php echo $color_scheme; ?>"><?php echo get_the_date(); ?></span>
        			    <?php endif; ?>
    		    		<span class="lptw-post-title title-<?php echo $color_scheme; ?>"><?php get_the_title() ? the_title() : the_ID(); ?></span>
                    </div>
                    </a>
                </div>
                <?php else : ?>
    			<?php if ( $show_date ) : ?>
    				<span class="lptw-post-date"><?php echo get_the_date(); ?></span>
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
			wp_cache_set( 'lptw_recent_posts_widget', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

    // Widget Backend
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) { $title = esc_attr( $instance[ 'title' ]) ; }
        else { $title = __( 'New title', 'lptw_recent_posts_domain' ); }

        if ( isset( $instance[ 'number' ] ) ) { $number = absint( $instance[ 'number' ] ); }
        else { $number = 5; }

        if ( isset( $instance[ 'show_date' ] ) ) { $show_date = (bool) $instance[ 'show_date' ]; }
        else { $show_date = false; }

        if ( isset( $instance[ 'color_scheme' ] ) ) { $color_scheme = $instance[ 'color_scheme' ] ; }
        else { $color_scheme = 'light'; }

        // Widget admin form
        ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'lptw_recent_posts_domain' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'lptw_recent_posts_domain' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?', 'lptw_recent_posts_domain' ); ?></label></p>

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
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['color_scheme'] = strip_tags($new_instance['color_scheme']);
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['lptw_widget_recent_entries']) )
			delete_option('lptw_widget_recent_entries');

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete('lptw_recent_posts_widget', 'widget');
	}

} // Class wpb_widget ends here

// Register and load the widget
function lptw_recent_posts_load_widget() {
	register_widget( 'lptw_recent_posts_widget' );
}
add_action( 'widgets_init', 'lptw_recent_posts_load_widget' );

function lptw_recent_posts_register_scripts() {
	wp_register_style( 'lptw-style', plugins_url( 'lptw-recent-posts/lptw-recent-posts.css' ) );
	wp_enqueue_style( 'lptw-style' );
}

add_action( 'wp_enqueue_scripts', 'lptw_recent_posts_register_scripts' );


/* Stop Adding Functions Below this Line */
?>