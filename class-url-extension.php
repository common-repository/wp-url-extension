<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

if(!function_exists('prr')){function prr($str){echo "<pre>";print_r($str);echo "</pre>\r\n";}}

class WPUE_Extension {
	/**
	 *  wpue_extension class instance
	 *
	 *  @var protected static $instance
	 */
	public $instance;

	/**
	 * Array of selected post type for url change
	 *
	 * @var $selected_post_type
	 */
	private $url_end, $url_cat_end, $selected_post_type;
	/**
	 * __construct wpue_extension class constructor
	 */
	function __construct() {
		// add_action( 'init', array( $this, 'wpue_extension_page_permalink' ), -1 );
		// add_filter( 'user_trailingslashit', array( $this, 'wpue_extension_page_slash' ),66,2 );
		register_activation_hook( __FILE__, array( $this, 'wpue_extension_active' ) );
		register_deactivation_hook( __FILE__, array( $this, 'wpue_extension_deactive' ) );
		$this->url_end = get_option('wpue_permalinks')['url_end'];
		$this->url_cat_end = get_option('wpue_permalinks')['url_cat_end'];
		$this->selected_post_type = get_option('wpue_permalinks')['post_types'];
		if ( ! is_array( $this->selected_post_type ) )
			$this->selected_post_type = array();

		if ( isset($this->selected_post_type) && !empty($this->selected_post_type) ) {
			$post_type_array = $this->selected_post_type;
			$exclude = array('page'); // 'page'
			$post_types = array_diff( $post_type_array, $exclude );//prr($rules);
			$this->post_types = $post_types;
			// prr($this);
			add_filter('rewrite_rules_array', array( $this, 'wpue_extension_rewrite_rules' ) );

			add_filter('post_link', array($this, 'wpue_extension_cpt_url'), 10, 3); // 'append_query_string'
			add_filter('post_type_link',array( $this, 'wpue_extension_cpt_url' ), 10, 1 );
			add_filter('tag_link', array($this, 'wpue_extension_tag_url'), 10, 2);

			add_filter('category_rewrite_rules', array($this, 'wpue_extension_rewrite_category_rules'));
			add_filter('category_link', array($this, 'wpue_extension_category_url'),1);
		}
		// if ( isset($this->selected_post_type) && in_array('page',$this->selected_post_type) ) {
	    // add_filter('user_trailingslashit', array($this, 'wpue_no_page_slash'), 1, 2);
			add_filter('page_rewrite_rules', array($this, 'wpue_extension_rewrite_rules'), 2);
	    add_filter('page_link', array($this, 'wpue_page_link'));
		// }

		add_filter('redirect_canonical', '__return_false' );
		add_filter('user_trailingslashit', array($this,'wpue_extension_trailingslashit'),66,2);
		add_filter('register_post_type_args', array($this,'post_to_blog'), 10, 2);
	}
	// function wpue_no_page_slash($string, $type){
	//     global $wp_rewrite;
	//     if ($wp_rewrite->using_permalinks() && $wp_rewrite->use_trailing_slashes==true) {
	//         return untrailingslashit($string);
	//     } else {
	//         return $string;
	//     }
	// }


	/* Function to add .html at the end of file */
	function wpue_page_link($link){
			return $link . $this->url_end;
	}
	// function wpue_extension_page_permalink() {
	// 	global $wp_rewrite;
	// 	if ( in_array( 'page', $this->selected_post_type ) ) {
	// 		if ( ! strpos( $wp_rewrite->get_page_permastruct(), $this->url_end ) ) {
	// 			$wp_rewrite->page_structure = $wp_rewrite->page_structure . $this->url_end;
	// 		}
	// 	}
	// 	$wp_rewrite->flush_rules();
	// }
	/* Conditionally adds a trailing slash if the permalink structure has a trailing slash, strips the */
	// function wpue_extension_page_slash( $string, $type ) {
	// 	global $wp_rewrite;
	// 	if ( in_array( $type, $this->selected_post_type ) ) {
	// 		if ( $wp_rewrite->using_permalinks() && true === $wp_rewrite->use_trailing_slashes && 'page' === $type ) {
	// 			$string = untrailingslashit( $string );
	// 		}
	// 	}
	// 	return $string;
	// }
	/* Function to get call when Plugin get activated */
	function wpue_extension_active() {
		global $wp_rewrite;
		if ( in_array( 'page', $this->selected_post_type ) ) {
			if ( ! strpos( $wp_rewrite->get_page_permastruct(), $this->url_end ) ) {
				$wp_rewrite->page_structure = $wp_rewrite->page_structure . $this->url_end;
			}
		}
		$wp_rewrite->flush_rules();
	}
	/* Function to get call when Plug in get deactivated */
	function wpue_extension_deactive() {
		global $wp_rewrite;
		if ( in_array( 'page', $this->selected_post_type ) ) {
			$wp_rewrite->page_structure = str_replace( $this->url_end,'',$wp_rewrite->page_structure );
			$wp_rewrite->flush_rules();
		}
	}

	/* Add rewrite rules for all post, Custom Post Type */
	function wpue_extension_rewrite_category_rules( $rules ) {
		if ( in_array('category', $this->selected_post_type) ) {
			foreach ( $rules as $key => $value ) {
				// if($this->url_cat_end == '/')
				// 	$new_rules[ $value . '/([^/]+)'.$this->url_cat_end ] = 'index.php?category_name=' . $value . '&name=$matches[1]';
				// else
					$new_rules[str_replace('/?', $this->url_cat_end, $key)] = $value;
			}
			$new_rules = $new_rules + $rules;
			return $new_rules;
		}else
			return $rules;
	}
	function wpue_extension_category_url( $cat_link ) {
		if ( in_array('category', $this->selected_post_type) )
			$cat_link = $cat_link . $this->url_cat_end;
		return $cat_link;
	}


	function wpue_extension_rewrite_rules( $rules ){
		$new_rules = array();
		foreach ( $rules as $key => $value ){
			if( $this->url_end !== '/' ) $needle = '/?';
			if( $this->url_cat_end !== '/' ) $needle_cat = '/?';
			$new_rules[str_replace($needle, $this->url_end, $key)] = $value;
			if( mb_strpos($value, 'category_name=$matches[1]&') !== false ){}else
				$new_rules[str_replace($needle_cat, $this->url_cat_end, $key)] = $value;
		}
		return $new_rules;
	}
	function wpue_extension_rewrite_rules_test( $rules ){
		// prr($rules);
		$new_rules = array();
		// prr($this);
		// foreach ( $this->selected_post_type as $k => $post_type ){
		// 	if( $post_type == 'post') $post_type == 'blog';
		// 	if( $post_type == 'post_tag') $post_type == 'tag';
		// 	if( $post_type == 'category') $post_type == 'category_name';
			foreach ( $rules as $key => $value ){
				// if( strpos($value, $post_type) !== false ){
				if( $this->url_end !== '/' ) $needle = '/?';
				if( $this->url_cat_end !== '/' ) $needle_cat = '/?';
				$new_rules[str_replace($needle, $this->url_end, $key)] = $value;
				if( mb_strpos($value, 'category_name=$matches[1]&') !== false ){}else
					$new_rules[str_replace($needle_cat, $this->url_cat_end, $key)] = $value;

				// }else
				// 	$new_rules[$key] = $value;
			}
		// }
		// prr($new_rules);
		return $new_rules;
	}
	function post_to_blog($args, $post_type){
	    if ($post_type == 'post'){
	        $args['rewrite']['slug'] = 'blog';
	        $args['rewrite']['with_front'] = false;
	    }
	    return $args;
	}

	/* Add .html in custom post URL */
	function wpue_extension_cpt_url( $post_link ) {
		global $post,$wp_rewrite;
		if ( isset( $post->ID ) && ! empty( $post->ID ) ) {
			$type = get_post_type( $post->ID );
			if ( in_array( $type, $this->post_types ) ) {
				// if(substr($post_link, -1) == '/')
				//   $post_link = substr($post_link, 0, -1);
				$post_link = $post_link.$this->url_end;
			}
		}
		return $post_link;
	}
	function wpue_extension_tag_url( $tax_link ) {
		$type = 'post_tag';
		if ( in_array( $type, $this->post_types ) ) {
			// if(substr($tax_link, -1) == '/')
			// 	$tax_link = substr($tax_link, 0, -1);
			$tax_link = $tax_link . $this->url_end;
		}
		return $tax_link;
	}

	function wpue_extension_trailingslashit($string, $type){
		global $wp_rewrite;
		if ($wp_rewrite->using_permalinks() && $wp_rewrite->use_trailing_slashes==true){ // && $type == 'page'
			return untrailingslashit($string);
		}else{
			return $string;
		}
	}

}
