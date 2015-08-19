<?php
/* reserved for class where render all layouts */

function render_post_date($a, $post_date, $post_time) {
    if ($a['show_time'] == 'true' && $a['show_time_before'] == 'true') { $post_date_time = $post_time . ' ' . $post_date; }
    else if ( $a['show_time'] == 'true' && $a['show_time_before'] != 'true' ) { $post_date_time = $post_date . ' ' . $post_time; }
    else { $post_date_time = $post_date; }
    return $post_date_time;
}

function render_element_styles ($style_args) {
    if ( is_array($style_args) ) {
        $element_style = 'style="';
        foreach ( $style_args as $argument_key => $argument_val ) {
            $element_style .= $argument_key . ': ' . $argument_val . '; ';
        }
        $element_style .= '"';
        return $element_style;
    } else {
        return;
    }
}

function render_element_class ($classes) {
    if ( is_array($classes) ) {
        $element_class = 'class="';
        foreach ( $classes as $argument ) {
            $element_class .= $argument . ' ';
        }
        $element_class = substr($element_class, 0, -1);
        $element_class .= '"';
        return $element_class;
    } else if (!empty($classes)){
        return 'class="' . $classes . '"';
    } else return;
}

function render_element_attributes ($object) {
    $content = '';

    /* create id if exsist */
    if (!empty($object['id']))          { $content .= 'id="'.$object['id'].'"'; }
    /* create class if exist */
    if (!empty($object['class']))       { $content .= ' '.render_element_class($object['class']); }
    /* create style if exist */
    if (!empty($object['style']))       { $content .= ' '.render_element_styles($object['style']); }
    /* create style if exist */
    if (!empty($object['href']))        { $content .= ' href="'.$object['href'].'"'; }

    return $content;
}

function render_article ($layout_classes, $layout_styles, $layout_sections, $layout_containers, $layout_objects) {
    $content = '<article '.render_element_class($layout_classes).' '.render_element_styles($layout_styles).'>'."\n";
    foreach ( $layout_sections as $section ) {
        if ($section['display'] === true) {
            switch ($section['type']) {
                case 'header':
                    $content .= '<header';
                    $content .= render_element_attributes ($section);
                    $content .= '>'."\n";
                    $content .= render_article_part ('header', $layout_containers, $layout_objects);
                    $content .= '</header>';
                break;
                case 'section':
                    $content .= '<section';
                    $content .= render_element_attributes ($section);
                    $content .= '>'."\n";
                    $content .= render_article_part ('section', $layout_containers, $layout_objects);
                    $content .= '</section>';
                break;
                case 'footer':
                    $content .= '<footer';
                    $content .= render_element_attributes ($section);
                    $content .= '>'."\n";
                    $content .= render_article_part ('footer', $layout_containers, $layout_objects);
                    $content .= '</footer>';
                break;
                default:
                    $content .= '<header';
                    $content .= render_element_attributes ($section);
                    $content .= '>'."\n";
                    $content .= render_article_part ('header', $layout_containers, $layout_objects);
                    $content .= '</header>';
                break;
            }
        }
    }
    $content .= '</article>';
    return $content."\n";
}

function render_article_container ($container, $layout_objects) {
    if (empty($container['tag'])) { $container_tag = 'div'; }
    else { $container_tag = $container['tag']; }
    $content = '<'.$container_tag;
    /* render attributes of the container */
    $content .= render_element_attributes ($container);
    /* display content of the container */
    $content .= '>'.render_article_objects($container['name'], $layout_objects).'</'.$container_tag.'>';
    return $content;
}

function render_article_objects($container_name, $layout_objects) {
    ksort($layout_objects);
    $content = '';
    foreach ( $layout_objects as $object ) {
        if ($object['display'] === true && $object['container'] == $container_name) {
            $content .= '<'.$object['tag'];
            $content .= render_element_attributes ($object);
            $content .= '>'.$object['content'].'</'.$object['tag'].'>';
        }
    }
    return $content."\n";
}

function render_article_part ($place, $layout_containers, $layout_objects) {
    $content = '';
    foreach ( $layout_containers as $container ) {
        if ($container['display'] === true && $container['place'] == $place) {
            $content .= render_article_container ($container, $layout_objects);
        }
    }
    return $content."\n";
}

?>