<?php

/* backend ajax */
add_action('admin_footer', 'my_action_javascript'); // Write our JS below here
function my_action_javascript() {
	?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {

        $('#post_type').change(function() {
            var selected_post_type = $(this).val();
    		var data = {
    			'action': 'get_terms_list',
    			'selected_post_type': selected_post_type
    		};
    		$.post(ajaxurl, data, function(response) {
    			$('.lptw-list-categories').html(response);
    		});
        });
	});
	</script>
<?php
}

add_action('wp_ajax_get_terms_list', 'get_terms_list_callback');
function get_terms_list_callback() {
    global $wpdb;

    $selected_post_type = $_POST['selected_post_type'];

    if ($_POST['widget_name']) {
        $widget_option = get_option('widget_'.$_POST['widget_name']);
        $post_category = $widget_option[$_POST['instance_id']]['post_category'];
    }

 	$taxonomies = get_object_taxonomies($selected_post_type);
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
     		    if (!empty($post_category)) {
                    if (is_array($post_category) && in_array($category->term_id, $post_category)) { $checked = 'checked="checked"'; }
                    else { $checked = ''; }
                } else { $checked = ''; }
     		    $categories_content .= '<li id="category-' . $category->term_id . '"><label class="selectit"><input type="checkbox" id="in-category-' . $category->term_id . '" name="post_category[]" value="' . $category->term_id . '" '.$checked.'> ' . $category->name . '</label></li>' . "\n";
     		}
     	}
    } else { $categories_content = 'No taxonomies for selected post type'; }

    echo $categories_content;
 	wp_die();
}

function lptw_recent_posts_manage_shortcodes() {
    $default_posts_per_page = intval(get_option('posts_per_page', '10'));
?>
    <div class="wrap">
    <h2>Advanced Recent Posts Shortcode Builder</h2>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">Layouts:</th>
                <td id="layouts">
                    <fieldset id="layout-types" class="layout-list">
                        <ul>
                            <li>
                                <label for="layout-basic"><input type="radio" class="layout-radio" name="sb_layout" id="layout-basic" value="basic" checked="checked" />&nbsp;Basic</label>&nbsp;&nbsp;
                                <a class="demo-link" href="http://demo.lp-tricks.com/recent-posts/basic-layout/" target="_blank"><span class="dashicons dashicons-admin-links"></span>&nbsp;<span class="demo">View demo (external link)</span></a>
                            </li>
                            <li>
                                <label for="layout-thumbnail"><input type="radio" class="layout-radio" name="sb_layout" id="layout-thumbnail" value="thumbnail" />&nbsp;Thumbnail</label>&nbsp;&nbsp;
                                <a class="demo-link" href="http://demo.lp-tricks.com/recent-posts/thumbnail-layout/" target="_blank"><span class="dashicons dashicons-admin-links"></span>&nbsp;<span class="demo">View demo (external link)</span></a>
                            </li>
                            <li>
                                <label for="layout-dropcap"><input type="radio" class="layout-radio" name="sb_layout" id="layout-dropcap" value="dropcap" />&nbsp;Drop Cap</label>&nbsp;&nbsp;
                                <a class="demo-link" href="http://demo.lp-tricks.com/recent-posts/drop-cap-layout/" target="_blank"><span class="dashicons dashicons-admin-links"></span>&nbsp;<span class="demo">View demo (external link)</span></a>
                            </li>
                            <li>
                                <label for="layout-grid-medium"><input type="radio" class="layout-radio" name="sb_layout" id="layout-grid-medium" value="grid-medium" />&nbsp;Responsive Grid</label>&nbsp;&nbsp;
                                <a class="demo-link" href="http://demo.lp-tricks.com/recent-posts/responsive-grid-dark/" target="_blank"><span class="dashicons dashicons-admin-links"></span>&nbsp;<span class="demo">View demo (external link)</span></a>
                            </li>
                        </ul>
                    </fieldset>
                    <label for="no_thumbnails"><input type="checkbox" value="hide" id="no_thumbnails" name="no_thumbnails">
                    Do not display Posts without Featured Image.</label>
                </td>
            </tr>
            <tr id="columns_and_width">
                <th scope="row">Columns and width:</th>
                <td>
                    <div class="lptw-sb-row">
                        <legend class="screen-reader-text"><span>Adaptive layout </span></legend>
                        <label for="sb_columns"><input type="number" class="small-text layout-basic-show layout-grid-show layout-thumbnail-show layout-dropcap-show" value="2" id="sb_columns" min="1" step="1" max="12" name="sb_columns">
                        Number of columns.</label>
                    </div>
                    <div class="lptw-sb-row">
                        Space beetween columns:&nbsp;&nbsp;&nbsp;
                        <label for="sb_space_hor"><input type="number" class="small-text layout-basic-show layout-grid-show layout-thumbnail-show layout-dropcap-show" value="10" id="sb_space_hor" min="1" step="1" name="sb_space_hor">
                        Horizontal.</label>
                        <label for="sb_space_ver"><input type="number" class="small-text layout-basic-show layout-grid-show layout-thumbnail-show layout-dropcap-show" value="10" id="sb_space_ver" min="1" step="1" name="sb_space_ver">
                        Vertical.</label>
                    </div>
                    <div class="lptw-sb-row">
                        <label for="sb_fluid_images"><input type="checkbox" class="layout-basic-show layout-grid-show layout-thumbnail-hide layout-dropcap-hide" checked="checked" value="0" id="sb_fluid_images" name="sb_fluid_images">
                        The width of the image adapts to the width of the container.</label>
                    </div>
                    <div class="lptw-sb-row">
                        <label for="sb_width"><input type="number" class="small-text layout-basic-hide layout-grid-hide layout-thumbnail-show layout-dropcap-show" value="300" id="sb_width" min="1" step="1" name="sb_width" disabled="disabled">
                        The width of the column in pixels, if not already selected adaptive layout.</label>
                    </div>
                    <div class="lptw-sb-row">
                        <label for="sb_height"><input type="number" class="small-text layout-basic-hide layout-grid-show layout-thumbnail-hide layout-dropcap-hide" value="400" id="sb_height" min="1" step="1" name="sb_height" disabled="disabled">
                        The fixed height of the cell in pixels, only for Responsive Grid. If not set - all cells have auto height.</label>
                    </div>
                </td>
            </tr>
            <tr id="post_types">
                <th scope="row"><label for="post_type">Post type:</label></th>
                <td>
                    <select id="post_type" name="post_type">
                    <?php
                        $post_types = get_post_types('', 'names');
                    	foreach ($post_types as $post_type) {
                    	    echo '<option value="' . $post_type . '">' . $post_type . '</option>';
                    	}
                    ?>
                    </select>
                </td>
            </tr>
            <tr id="post_authors">
                <th scope="row"><label for="authors">Authors:</label></th>
                <td>
                    <?php
                        $authors_args = array(
                            'who'          => 'authors'
                        );
                        $blog_authors = get_users( $authors_args );
                    ?>
                    <select id="authors" name="authors" multiple class="chosen-select" data-placeholder="Select one or more authors">
                    <?php

                    	foreach ($blog_authors as $blog_author) {
                    	    if ( $blog_author->first_name && $blog_author->last_name ) { $author_name = ' ('.$blog_author->first_name.' '.$blog_author->last_name.')'; }
                            else { $author_name = ''; }
                    	    echo '<option value="' . $blog_author->id . '">' . $blog_author->user_nicename . $author_name . '</option>';
                    	}
                    ?>
                    </select>
                    <p class="description">If none of authors is selected - will be displayed posts of all authors.</p>
                </td>
            </tr>
            <tr id="post_tags">
                <th scope="row"><label for="tags">Tags:</label><p class="description">Now only work with posts.</p></th>
                <td>
                    <?php
                        $posts_tags = get_tags();
                    ?>
                    <select id="tags" name="tags" multiple class="chosen-select" data-placeholder="Select one or more post tag">
                    <?php

                    	foreach ($posts_tags as $post_tag) {
                    	    echo '<option value="' . $post_tag->term_id . '">' . $post_tag->name . '</option>';
                    	}
                    ?>
                    </select>
                    <p class="description">If none of tags is selected - will be displayed posts with tags and without tags.</p>
                    <p>
                        <label for="tags_exclude"><input type="checkbox" value="0" id="tags_exclude" name="tags_exclude">
                        Exclude posts with this tags from the posts list.</label>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="category_id">Category:</label></th>
                <td>
                    <fieldset id="categories_list">
                        <ul class="lptw-list-categories">
                        <?php wp_category_checklist(); ?>
                        </ul>
                    </fieldset>
                    <p class="description">If none of the categories is selected - will be displayed the posts from all the categories.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="sorting">Sort & order posts:</label></th>
                <td>
                    <fieldset id="sorting">
                        <label for="orderby">Sort posts by:&nbsp;
                        <select class="layout-basic-show layout-dropcap-show layout-grid-show layout-thumbnail-show" id="orderby" name="orderby">
                            <option value="none">None</option>
                            <option value="title">Title</option>
                        	<option value="name">Name (post slug)</option>
                        	<option value="date" selected="selected">Date created</option>
                        	<option value="modified">Date modified</option>
                        	<option value="rand">Random</option>
                        	<option value="comment_count">Number of comments</option>
                        </select>
                        </label>&nbsp; &nbsp;
                        <label for="orderby">Order:&nbsp;
                        <select class="layout-basic-show layout-dropcap-show layout-grid-show layout-thumbnail-show" id="order" name="order">
                        	<option value="ASC">Ascending order from lowest to highest values</option>
                        	<option value="DESC" selected="selected">Descending order from highest to lowest values</option>
                        </select>
                        </label>
                        <p>
                            <label for="posts_per_page"><input type="number" class="small-text" value="<?php echo $default_posts_per_page;?>" id="posts_per_page" min="1" step="1" name="posts_per_page">
                            Posts per page.</label>
                        </p>
                        <p class="description">Only for shortcode, not global!</p>
                        <p>
                            <label for="reverse_post_order"><input type="checkbox" value="0" id="reverse_post_order" name="reverse_post_order">
                            Reverse post order: display the latest post last in the list. By default the latest post displays first.</label>
                        </p>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="thumbnail_size">Image size:</label></th>
                <td>
                    <select class="layout-basic-show layout-dropcap-hide layout-grid-hide layout-thumbnail-hide" id="thumbnail_size" name="thumbnail_size">
                        <option value="thumbnail">Thumbnail</option>
                    	<option value="medium" selected="selected">Medium</option>
                    	<option value="large">Large</option>
                    	<option value="full">Full</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="color_scheme">Color scheme:</label></th>
                <td>
                    <select class="layout-basic-show layout-grid-show layout-thumbnail-hide layout-dropcap-hide" id="color_scheme" name="color_scheme">
                        <option value="no-overlay">Without overlay</option>
                        <option value="light">Light</option>
                    	<option value="dark" selected="selected">Dark</option>
                    </select>
                    <p class="description">Only for Basic layout.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="color_scheme">Colors:</label></th>
                <td>
                    <div class="color-picker-wrapper">
                        <label for="dropcap-background-color">Background color</label><br>
                        <input type="text" value="#4CAF50" data-default-color="#4CAF50" class="color-field" name="dropcap-background-color" id="dropcap-background-color" />
                    </div>
                    <div class="color-picker-wrapper">
                        <label for="dropcap-text-color">Text color</label><br>
                        <input type="text" value="#ffffff" data-default-color="#ffffff" class="color-field" name="dropcap-text-color" id="dropcap-text-color" />
                    </div>
                    <p class="description">For Drop Cap layout. Also used in other Layouts if the Posts have no Featured Image.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Show date and time:</th>
                <td>
                    <fieldset id="display_date_time" class="layout-basic-show layout-grid-show layout-thumbnail-show layout-dropcap-hide">
                        <legend class="screen-reader-text"><span>Show date and time </span></legend>
                        <p>
                        	<label for="show_date_before_title"><input type="checkbox" checked="checked" value="0" id="show_date_before_title" name="show_date_before_title">
                        	Display date and time before post title.</label>
                        </p>
                        <p>
                        	<label for="show_date"><input type="checkbox" checked="checked" value="0" id="show_date" name="show_date">
                        	Display date in recent posts list</label>
                        </p>
                        <p>
                        	<label for="show_time"><input type="checkbox" checked="checked" value="0" id="show_time" name="show_time">
                        	Display time in recent posts list</label>
                        </p>
                        <p>
                        	<label for="show_time_before"><input type="checkbox" checked="checked" value="0" id="show_time_before" name="show_time_before">
                        	Display time <strong><u>before date</u></strong> in recent posts list. By default - after date.</label>
                        </p>
                    	<p class="description">Only for Basic and Thumbnail layouts.</p>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">Date Format</th>
                <td>
                	<fieldset id="date_formats" class="layout-basic-show layout-grid-show layout-thumbnail-show layout-dropcap-hide">
                        <legend class="screen-reader-text"><span>Date Format</span></legend>
                    	<label title="d.m.Y"><input type="radio" checked="checked" value="d.m.Y" name="sb_date_format"> <span><?php echo date('d.m.Y');?></span></label><br>
                    	<label title="m/d/Y"><input type="radio" value="m/d/Y" name="sb_date_format"> <span><?php echo date('m/d/Y');?></span></label><br>
                    	<label title="d/m/Y"><input type="radio" value="d/m/Y" name="sb_date_format"> <span><?php echo date('d/m/Y');?></span></label><br>
                    	<label title="F j, Y"><input type="radio" value="F j, Y" name="sb_date_format"> <span><?php echo date('F j, Y');?></span></label><br>
                    	<label title="M j, Y"><input type="radio" value="M j, Y" name="sb_date_format"> <span><?php echo date('M j, Y');?></span></label><br>
                	</fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">Time Format</th>
                <td>
                	<fieldset id="time_formats" class="layout-basic-show layout-grid-show layout-thumbnail-show layout-dropcap-hide">
                        <legend class="screen-reader-text"><span>Time Format</span></legend>
                    	<label title="H:i"><input type="radio" checked="checked" value="H:i" name="sb_time_format"> <span><?php echo date('H:i');?></span></label><br>
                    	<label title="H:i:s"><input type="radio" value="H:i:s" name="sb_time_format"> <span><?php echo date('H:i:s');?></span></label><br>
                    	<label title="g:i a"><input type="radio" value="g:i a" name="sb_time_format"> <span><?php echo date('g:i a');?></span></label><br>
                    	<label title="g:i:s a"><input type="radio" value="g:i:s a" name="sb_time_format"> <span><?php echo date('g:i:s a');?></span></label><br>
                	</fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">Result:</th>
                <td id="result">
                    <a href="#" class="button button-default button-large" id="lptw_generate_shortcode">Generate Shortcode</a>
                    <div class="lptw-sb-row">
                        <textarea name="lptw_generate_shortcode_result" id="lptw_generate_shortcode_result" class="lptw-sb-result"></textarea>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
<?php
}
?>