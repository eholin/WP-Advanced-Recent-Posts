<?php
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
		$no_thumbnails = isset( $instance['no_thumbnails'] ) ? $instance['no_thumbnails'] : false;

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
        if (!empty($post_category)) { $post_category_str = implode (',', $post_category); }

		$authors = isset( $instance['authors'] ) ? $instance['authors'] : array();

		$post_type = isset( $instance['post_type'] ) ? $instance['post_type'] : 'post';

        /* don't show post in recent if it shows in page */
        global $post;
        if (!empty($post) && $exclude_current_post == true) { $exclude_post = array( $post->ID ); }

        if ( $post_type != 'post' ) {
            if (!empty($post_category)) {
                $tax_query = array('relation' => 'AND');
             	$taxonomies = get_object_taxonomies($post_type);
                if (!empty($taxonomies)) {
                 	foreach ($taxonomies as $taxonomy) {
                        $tax_array = array('taxonomy' => $taxonomy, 'field' => 'term_id', 'include_children' => false, 'terms' => $post_category);

                        array_push ($tax_query, $tax_array);
                 	}
                }
            } else { $tax_query = ''; }
            $post_category = '';
        }

        if ($no_thumbnails == 'on') { $meta_key = '_thumbnail_id'; }
        else { $meta_key = ''; }

		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'post_type'             => $post_type,
			'posts_per_page'        => $number,
			'no_found_rows'         => true,
			'post_status'           => 'publish',
			'ignore_sticky_posts'   => true,
            'post__not_in'          => $exclude_post,
            'author__in'            => $authors,
            'category__in'          => $post_category,
            'tax_query'             => $tax_query,
            'order'                 => 'DESC',
            'orderby'               => 'date',
            'meta_key'              => $meta_key
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

        if ( isset( $instance[ 'no_thumbnails' ] ) ) { $no_thumbnails = (bool) $instance[ 'no_thumbnails' ]; }
        else { $no_thumbnails = false; }

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

        if ( isset( $instance[ 'authors' ] ) ) { $authors = $instance[ 'authors' ]; }

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
			<select name="<?php echo $this->get_field_name( 'post_type' ); ?>" id="<?php echo $this->get_field_id('post_type'); ?>" class="widefat registered-post-types">
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
                    <?php
                     	$taxonomies = get_object_taxonomies($post_type);
                        if (!empty($taxonomies)) {
                            $categories_content = '';
                         	foreach ($taxonomies as $taxonomy) {
                         	    $args = array(
                                    'taxonomy' => $taxonomy,
                                    'orderby' => 'name',
                                    'show_count' => 0,
                                    'pad_counts' => 0,
                                    'hierarchical' => 1,
                                    'hide_empty' => 0
                                );
                         		$categories = get_categories($args);
                         		foreach ($categories as $category) {
                         		    if (is_array($post_category) && in_array($category->term_id, $post_category)) { $checked = 'checked="checked"'; }
                                    else { $checked = ''; }
                         		    $categories_content .= '<li id="category-' . $category->term_id . '"><label class="selectit"><input type="checkbox" id="in-category-' . $category->term_id . '" name="post_category[]" value="' . $category->term_id . '" '.$checked.'> ' . $category->name . '</label></li>' . "\n";
                         		}
                         	}
                        } else { $categories_content = 'No taxonomies for selected post type'; }

                        echo $categories_content;
                    ?>
                </ul>
            </fieldset>
            <p class="description">If none of the categories is selected - will be displayed posts from all categories.</p>
        </div>

        <div class="chosen-container"><label for="<?php echo $this->get_field_id( 'authors' ); ?>"><?php _e( 'Select one or more authors:', 'lptw_recent_posts_domain' ); ?></label>
            <?php
                $authors_args = array(
                    'who'          => 'authors'
                );
                $blog_authors = get_users( $authors_args );
            ?>
            <select id="<?php echo $this->get_field_id( 'authors' ); ?>" name="<?php echo $this->get_field_name( 'authors' ); ?>[]" multiple class="widefat chosen-select chosen-select-widget" data-placeholder="<?php _e( 'Authors', 'lptw_recent_posts_domain' ); ?>">
            <?php
                foreach ($blog_authors as $blog_author) {
                    if (is_array($authors) && in_array($blog_author->id, $authors)) { $selected = 'selected="selected"'; }
                    else { $selected = ''; }
                    if ( $blog_author->first_name && $blog_author->last_name ) { $author_name = ' ('.$blog_author->first_name.' '.$blog_author->last_name.')'; }
                    else { $author_name = ''; }
                    echo '<option value="' . $blog_author->id . '" '.$selected.'>' . $blog_author->user_nicename . $author_name . '</option>';
                }
            ?>
            </select>
        </div>

		<p><input class="checkbox" type="checkbox" <?php checked( $no_thumbnails ); ?> id="<?php echo $this->get_field_id( 'no_thumbnails' ); ?>" name="<?php echo $this->get_field_name( 'no_thumbnails' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'no_thumbnails' ); ?>"><?php _e( 'Do not display Posts without Featured Image', 'lptw_recent_posts_domain' ); ?></label></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $exclude_current_post ); ?> id="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>" name="<?php echo $this->get_field_name( 'exclude_current_post' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'exclude_current_post' ); ?>"><?php _e( 'Exclude the current Post from list', 'lptw_recent_posts_domain' ); ?></label></p>

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
				<option value="no-overlay"<?php selected( $color_scheme, 'no-overlay' ); ?>><?php _e('Without overlay', 'lptw_recent_posts_domain'); ?></option>
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
		$instance['no_thumbnails'] = isset( $new_instance['no_thumbnails'] ) ? (bool) $new_instance['no_thumbnails'] : false;
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

        // need to replace $_POST by $new_instance as authors
		if( isset( $_POST['post_category'] ) ) {
		    $posted_terms = $_POST['post_category'];
			foreach ( $posted_terms as $term ) {
			    if( term_exists( absint( $term ), $taxonomy ) ) {
				    $terms[] = absint( $term );
				}
			}
            $instance['post_category'] = $terms;
		} else { $instance['post_category'] = ''; }

		if( isset( $new_instance['authors'] ) ) {
		    $authors = $new_instance['authors'];
			foreach ( $authors as $author ) {
			    $authors_id[] = absint( $author );
			}
            $instance['authors'] = $authors_id;
		} else { $instance['authors'] = ''; }

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

?>