<?php 
defined('ABSPATH') or die('Blank Space');


/*
*/
final class Lanlist_edit {
	/* singleton */
	private static $instance = null;


	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();
		return self::$instance;
	}



	private function __construct() {


		add_action('manage_emlanlistse_posts_columns', array($this, 'column_head'));
		add_filter('manage_emlanlistse_posts_custom_column', array($this, 'custom_column'));
		add_filter('manage_edit-emlanlistse_sortable_columns', array($this, 'sort_column'));
		
		/* metabox, javascript */
		add_action('add_meta_boxes_emlanlistse', array($this, 'create_meta'));
		/* hook for page saving/updating */
		add_action('save_post', array($this, 'save'));


		add_filter('emtheme_doc', array($this, 'add_doc'), 99);

	}

	/**
	 * theme filter for populating documentation
	 * 	
	 * @param [array] $data [array passing through theme filter]
	 */
	public function add_doc($data) {
		$data['emlanlistse']['title'] = '<h1 id="emlanlistse">Lånlist Sverige (Plugin)</h1>';

		$data['emlanlistse']['index'] = '<li><h2><a href="#emlanlistse">Lånlist Sverige (Plugin)</a></h2>
											<ul>
												<li><a href="#emlanlistse-shortcode">Shortcode</a></li>
												<li><a href="#emlanlistse-aldri">Aldri vis</a></li>
												<li><a href="#emlanlistse-sort">Sorting order</a></li>
											</ul>
										</li>';
		$data['emlanlistse']['info'] = '<li id="emlanlistse-shortcode"><h2>Shortcodes</h2>
										<ul>
											<li><b>[lan]</b>
											<p>[lan] will show all.</p>
											</li>
											<li><b>[lan name="xx, yy"]</b>
											<p>Shows only the loans that is mentioned in the shortcode.
											<br>The name needs to be the slug-name of the loan.
											<br>Loans are sorted by the position they have in name=""
											<br>eks.: [lan name="lendo-privatlan"] will only show the loan with slug-name "lendo-privatlån.
											<br>[lan name="lendo-privatlan, axo-finans"] will show 2 loans: lendo and axo.</p>
											<li><b>[lan lan="xx"]</b>
											<p>lan must match the slug-name of the lan type.
											<br>The loans are sorted by the sort order given in load edit page for that type.
											<br>Eks: [lan lan="frontpage"] shows all loans with the category "frontpage" in the order of lowest number
											<br>of field "Sort frontpage" has in the load editor page.</p>
											</li>
											</li>
											<li><b>[lan-bilde name="xx"]</b>
											<p>Name is required. Will show the loan\'s thumbnail.</p></li>
											<li><b>[lan-bestill name="xx"]</b>
											<p>Name is required. Will show the loan\'s button.</p></li>
										</ul>
										</li>
										<li id="emlanlistse-aldri"><h2>Aldri vis</h2>
										<p>If tagged, then the loan will never appear on the front-end.</p>
										</li>
										</li>
										<li id="emlanlistse-sort"><h2>Sorting order</h2>
										<p>The loans will be shown with the highest "Sort"-value first.</p>
										</li>';

		return $data;
	}

	/**
	 * wp filter for adding columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function column_head($defaults) {
		$defaults['emlanlistse_sort'] = 'Sorting Order';
		return $defaults;
	}


	/**
	 * filter for populating columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function custom_column($column_name) {
		global $post;

		if ($column_name == 'emlanlistse_sort') {
			$meta = get_post_meta($post->ID, 'emlanlistse_sort');
			
			if (isset($meta[0])) echo $meta[0];
		}
	}


	/**
	 * filter for sorting by columns on ctp list page
	 * 
	 * @param  [array] $defaults [array going through wp filter]
	 * @return [array]           [array going through wp filter]
	 */
	public function sort_column($columns) {
		$columns['emlanlistse_sort'] = 'emlanlistse_sort';
		return $columns;
	}



	/*
		creates wordpress metabox
		adds javascript
	*/
	public function create_meta() {

		/* lan info meta */
		add_meta_box(
			'emlanlistse_meta', // name
			'Lån Info', // title 
			array($this,'create_meta_box'), // callback
			'emlanlistse' // page
		);

		/* to show or not on front-end */
		add_meta_box(
			'emalanlistse_exclude',
			'Aldri vis',
			array($this, 'exclude_meta_box'),
			'emlanlistse',
			'side'
		);
		
		/* adding admin css and js */
		wp_enqueue_style('em-lanlist-se-admin-style', LANLIST_SE_PLUGIN_URL . 'assets/css/admin/em-lanlist-se.css', array(), false);
		wp_enqueue_script('em-lanlist-se-admin', LANLIST_SE_PLUGIN_URL . 'assets/js/admin/em-lanlist-se.js', array(), false, true);
	}


	/*
		creates content in metabox
	*/
	public function create_meta_box($post) {
		wp_nonce_field('em'.basename(__FILE__), 'emlanlistse_nonce');

		$meta = get_post_meta($post->ID, 'emlanlistse_data');
		$sort = get_post_meta($post->ID, 'emlanlistse_sort');

		$tax = wp_get_post_terms($post->ID, 'emlanlistsetype');

		$taxes = [];
		if (is_array($tax))
			foreach($tax as $t)
				array_push($taxes, $t->slug);

		$json = [
			'meta' => isset($meta[0]) ? $this->sanitize($meta[0]) : '',
			'emlanlistse_sort' => isset($sort[0]) ? floatval($sort[0]) : '',
			'tax'  => $taxes
		];

		$ameta = get_post_meta($post->ID);
		foreach($ameta as $key => $value)
			if (strpos($key, 'emlanlistse_sort_') !== false && isset($value[0])) $json[$key] = esc_html($value[0]);


		wp_localize_script('em-lanlist-se-admin', 'emlanlistse_meta', json_decode(json_encode($json), true));
		echo '<div class="emlanlistse-meta-container"></div>';
	}
 

 	/**
 	 * [exclude_meta_box description]
 	 */
	public function exclude_meta_box() {
		$option = get_option('emlanlistse_exclude');
		global $post;

		if (!is_array($option)) $option = [];
		// echo 'hi'.print_r($option, true);


		echo '<input name="emlanlistse_exclude" id="emlanlistse_exc" type="checkbox"'.(array_search($post->ID, $option) !== false ? ' checked' : '').'><label for="emlanlistse_exc">Lån vil ikke vises på front-end når boksen er markert.</label>';
	}



	/**
	 * wp action when saving
	 */
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

		// saves to wp option instead of post meta
		// when adding
		if (isset($_POST['emlanlistse_exclude'])) {
			$option = get_option('emlanlistse_exclude');

			// to avoid php error
			if (!is_array($option)) $option = [];

			// if not already added
			if (array_search($post_id, $option) === false) {

				// if to add to collection
				if (is_array($option)) {
					array_push($option, intval($post_id));

					update_option('emlanlistse_exclude', $option);
				}
				
				// if to create collection (of one)
				else update_option('emlanlistse_exclude', [$post_id]);
			}
		}
		// when removing
		else {
			$option = get_option('emlanlistse_exclude');

			if (array_search($post_id, $option) !== false) {
				unset($option[array_search($post_id, $option)]);
				update_option('emlanlistse_exclude', $option);
			}
		}

		// data is sent, then sanitized and saved
		if (isset($_POST['emlanlistse_data'])) update_post_meta($post_id, 'emlanlistse_data', $this->sanitize($_POST['emlanlistse_data']));
		if (isset($_POST['emlanlistse_sort'])) update_post_meta($post_id, 'emlanlistse_sort', floatval($_POST['emlanlistse_sort']));

		foreach($_POST as $key => $po) {
			if (strpos($key, 'emlanlistse_sort_') !== false)
				update_post_meta($post_id, sanitize_text_field(str_replace(' ', '', $key)), floatval($po));
		}

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