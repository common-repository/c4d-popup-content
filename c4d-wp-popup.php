<?php
/*
Plugin Name: C4D WordPress Popups
Plugin URI: http://coffee4dev.com/
Description: Create carousel slider for product/category
Author: Coffee4dev.com
Author URI: http://coffee4dev.com/
Text Domain: c4d-wppu
Version: 2.0.0
*/

define('C4DWPPU_PLUGIN_URI', plugins_url('', __FILE__));
define('C4DWPPU', 'c4d-wp-popup');

add_action('wp_enqueue_scripts', 'c4d_wppu_safely_add_stylesheet_to_frontsite');
add_action('init', 'c4d_wppu_create_posttype' );
add_action('add_meta_boxes', 'c4d_wppu_meta_box', 10 , 3);
add_action('save_post', 'c4d_wppu_save_data');
add_action('wp_footer', 'c4d_wppu_auto_add_to_footer');
add_shortcode('c4d-wppu', 'c4d-wppu-shortcode');
add_shortcode('c4d-wppu-dont-show', 'c4d_wppu_shortcode_dont_show');
add_shortcode('c4d-wppu-no-thank', 'c4d_wppu_shortcode_not_thank');
add_filter( 'plugin_row_meta', 'c4d_wppu_plugin_row_meta', 10, 2 );

function c4d_wppu_plugin_row_meta( $links, $file ) {
    if ( strpos( $file, basename(__FILE__) ) !== false ) {
        $new_links = array(
            'visit' => '<a href="http://coffee4dev.com">Visit Plugin Site</<a>',
            'forum' => '<a href="http://coffee4dev.com/forums/">Forum</<a>',
            'premium' => '<a href="http://coffee4dev.com">Premium Support</<a>'
        );
        
        $links = array_merge( $links, $new_links );
    }
    
    return $links;
}

$c4d_wppu_meta_boxes = array (
	'id' => 'c4d-wppu__global-config', 
	'title' => esc_html__('Global Config', 'c4d-wppu'), 
	'callback' => 'c4d_wppu_html', 
	'page' => C4DWPPU, 
	'context' => 'side', 
	'priority' => 'default',
	'fields' => array(
		array(
            'title' => esc_html__('Shown On', 'c4d-wppu'),
            'desc' => esc_html__('Set url you want to show', 'c4d-wppu'),
            'id' => 'c4d_wppu_display',
            'type' => 'text',
            'default' => 'All'
        ),
        array(
            'title' => esc_html__('Shown On', 'c4d-wppu'),
            'desc' => esc_html__('Delay time before show popup in milisecond', 'c4d-wppu'),
            'id' => 'c4d_wppu_delay_time',
            'type' => 'text',
            'default' => '3000'
        )
	)
);

function c4d_wppu_shortcode_dont_show($params, $content) {
	return '<a class="c4d-wppu__dont-show" href="#"><span class="checkbox"></span>'.$content.'</a>';
}
function c4d_wppu_shortcode_not_thank($params, $content) {
	return '<a class="c4d-wppu__no-thank" href="#">'.$content.'</a>';
}
function c4d_wppu_auto_add_to_footer() {
	if (defined('DOING_AJAX') && DOING_AJAX) {
		return;
	}
	$args = array(
        'posts_per_page' 	=> 20,
        'paged'				=> 0,
        'post_type' 		=> C4DWPPU,
        'orderby'   		=> 'date',
    	'order'     		=> 'desc',
        'post_status'       => 'publish'
    );
    
	$q = new WP_Query( $args );

	if ($q->have_posts()) {
		while($q->have_posts()) {
			$p = $q->the_post();
			$time = get_post_meta(get_the_ID(), 'c4d_wppu_delay_time', true);
			$time = $time ? $time : 8000;
			$id = 'c4d-wppu-'.uniqid();
			echo '<div class="c4d-wppu__wrapper"><a class="c4d-wppu__open-link" href="#'.esc_attr($id).'"></a><div id="'.esc_attr($id).'" data-id="'.get_the_ID().'" data-delay-time="'.esc_attr($time).'" class="c4d-wppu__site"><div class="c4d-wppu__site_inner">'.do_shortcode(get_the_content()).'</div></div></div>';	
		}
	}
	woocommerce_reset_loop();
	wp_reset_postdata();
}

function c4d_wppu_safely_add_stylesheet_to_frontsite( $page ) {
	if(!defined('C4DPLUGINMANAGER')) {
		wp_enqueue_style( 'c4d-wppu-frontsite-style', C4DWPPU_PLUGIN_URI.'/assets/default.css' );
		wp_enqueue_script( 'c4d-wppu-frontsite-plugin-js', C4DWPPU_PLUGIN_URI.'/assets/default.js', array( 'jquery' ), false, true ); 
	}
	wp_enqueue_style( 'fancybox', C4DWPPU_PLUGIN_URI.'/libs/jquery.fancybox.min.css'); 
	wp_enqueue_script( 'fancybox', C4DWPPU_PLUGIN_URI.'/libs/jquery.fancybox.min.js', array( 'jquery' ), false, true ); 
	wp_localize_script( 'jquery', 'c4d_wp_pu',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

// Our custom post type function
function c4d_wppu_create_posttype() {
	register_post_type( C4DWPPU,
		array(
			'labels' => array(
				'name' => __( 'C4D WP Popup' ),
				'singular_name' => __( 'C4D WP Popup' )
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => C4DWPPU),
			'register_meta_box_cb' => 'c4d_wppu_meta_box'
		)
	);
}

function c4d_wppu_meta_box() {
	global $c4d_wppu_meta_boxes;
	add_meta_box($c4d_wppu_meta_boxes['id'], $c4d_wppu_meta_boxes['title'], $c4d_wppu_meta_boxes['callback'], $c4d_wppu_meta_boxes['page'], $c4d_wppu_meta_boxes['context'], $c4d_wppu_meta_boxes['priority']);	
}

function c4d_wppu_html() {
	global $c4d_wppu_meta_boxes, $post;
	
	echo '<input type="hidden" name="c4d_wppu_meta_box_nonce" value="', wp_create_nonce(plugin_basename(__FILE__)), '" />';
	echo '<div class="c4d-wppu__row"><div><label>'.esc_html__('Shortcode').':</label></div>';
	echo '<div class="desc">'.esc_html__('Copy this shortcode to page you want to use').'</div>';
	echo '<code>[c4d-wppu id="'.$post->ID.'"]</code>';
	echo '</div>';

	foreach ($c4d_wppu_meta_boxes['fields'] as $key => $value) {
		$current = get_post_meta($post->ID, $value['id'], true);
		$current = $current ? $current : $value['default'];
		echo '<div class="c4d-wppu__row">';
		echo '<div><label>'.$value['title'].'</label></div>';
		echo '<div class="desc">'.$value['desc'].'</div>';
		switch ($value['type']) {
            case 'text':
            	echo '<input id="'.esc_attr($value['id']).'" type="text" name="'.esc_attr($value['id']).'" value="'.esc_attr($current).'">';
            break;
        }
		echo '</div>';
	}
}

// Save data from meta box
function c4d_wppu_save_data($post_id) {
    global $c4d_wppu_meta_boxes;
    if (!isset($_POST['c4d_wppu_meta_box_nonce'])) return $post_id;
    // verify nonce
    if (!wp_verify_nonce($_POST['c4d_wppu_meta_box_nonce'], plugin_basename(__FILE__))) {
        return $post_id;
    }
    
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    foreach ($c4d_wppu_meta_boxes['fields'] as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = sanitize_text_field($_POST[$field['id']]);

        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    }
}

