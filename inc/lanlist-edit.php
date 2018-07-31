<?php 
defined('ABSPATH') or die('Blank Space');


/*
*/
final class Emlanlist_edit {
	/* singleton */
	private static $instance = null;


	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();
		return self::$instance;
	}



	private function __construct() {


		// add_action('manage_emkort_posts_columns', array($this, 'column_head'));
		// add_filter('manage_emkort_posts_custom_column', array($this, 'custom_column'));
		// add_filter('manage_edit-emkort_sortable_columns', array($this, 'sort_column'));
		
		/* metabox, javascript */
		add_action('add_meta_boxes_emlanlistse', array($this, 'create_meta'));
		/* hook for page saving/updating */
		add_action('save_post', array($this, 'save'));
	}


	// public function column_head($defaults) {
	// 	$defaults['emkort_sort'] = 'Sorting Order';
	// 	$defaults['make_list'] = 'Make List';
	// 	return $defaults;
	// }


	// public function custom_column($column_name) {
	// 	global $post;
	// 	if ($column_name == 'emkort_sort') {
	// 		$meta = get_post_meta($post->ID, 'emkort_sort');
	// 		if (isset($meta[0]))
	// 			echo $meta[0];
	// 	}
	// 	if ($column_name == 'make_list')
	// 		echo '<button type="button" class="emkort-button button" data="'.$post->post_name.'">Add</button>';
	// }


	// public function sort_column($columns) {
	// 	$columns['emkort_sort'] = 'emkort_sort';
	// 	return $columns;
	// }



	/*
		creates wordpress metabox
		adds javascript
	*/
	public function create_meta() {

		/* kredittkort info meta */
		add_meta_box(
			'emlanlistse_meta', // name
			'Lån Info', // title 
			array($this,'create_meta_box'), // callback
			'emlanlistse' // page
		);

		wp_enqueue_style('em-lanlist-se-admin-style', LANLIST_SE_PLUGIN_URL . '/assets/css/admin/em-lanlist-se.css', array(), false);
		wp_enqueue_script('em-lanlist-se-admin', LANLIST_SE_PLUGIN_URL . '/assets/js/admin/em-lanlist-se.js', array(), false, true);
	}
	/*
		creates content in metabox
	*/
	public function create_meta_box($post) {
		wp_nonce_field('em'.basename(__FILE__), 'emlanlistse_nonce');

		$meta = get_post_meta($post->ID, 'emlanlistse_data');
		$sort = get_post_meta($post->ID, 'emlanlistse_sort');
		// wp_die('<xmp>'.print_r($sort, true).'</xmp>');
		
		$json = [
			'meta' => isset($meta[0]) ? $this->sanitize($meta[0]) : '',
			'sort' => isset($sort[0]) ? floatval($sort[0]) : ''
		];

		wp_localize_script('em-lanlist-se-admin', 'emlanlistse_meta', json_decode(json_encode($json), true));
		echo '<div class="emlanlistse-meta-container"></div>';
	}
 


	public function save($post_id) {
		// post type is emlanlistse
		if (!get_post_type($post_id) == 'emlanlistse') return;

		// is on admin screen
		if (!is_admin()) return;

		// user is logged in and has permission
		if (!current_user_can('edit_posts')) return;

		// nonce is sent
		if (!isset($_POST['emlanlistse_nonce'])) return;

		// nonce is checked
		if (!wp_verify_nonce($_POST['emlanlistse_nonce'], 'em'.basename(__FILE__))) return;

		// data is sent, then sanitized and saved
		if (isset($_POST['emlanlistse_data'])) update_post_meta($post_id, 'emlanlistse_data', $this->sanitize($_POST['emlanlistse_data']));
		if (isset($_POST['emlanlistse_sort'])) update_post_meta($post_id, 'emlanlistse_sort', $this->sanitize($_POST['emlanlistse_sort']));
	}


	/*
		recursive sanitizer
	*/
	private function sanitize($data) {
		if (!is_array($data)) return wp_kses_post($data);

		$d = [];
		foreach($data as $key => $value)
			$d[$key] = $this->sanitize($value);

		return $d;
	}
}