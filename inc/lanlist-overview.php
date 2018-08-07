<?php 
defined('ABSPATH') or die('Blank Space');


final class Lanlist_overview {
	/* singleton */
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		add_action('admin_menu', array($this, 'add_menu'));
	}

	public function add_menu() {
		add_submenu_page('edit.php?post_type=emlanlistse', 'Overview', 'Overview', 'manage_options', 'emlanlistse-overview', array($this, 'add_page'));
	}

	public function add_page() {

		$args = [
			'post_type' 		=> array('page', 'post'),
			'posts_per_page'	=> -1
		];

		$posts = get_posts($args);

		$site = get_site_url();

		$html = '<table style="font-size: 16px;"><tr><td>Post</td><td>Shortcode</td></tr>';

		foreach ($posts as $post) {

			if (strpos($post->post_content, '[lan') !== false) {
				preg_match_all('/\[lan.*?\]/', $post->post_content, $matches);

				$m = '';

				foreach($matches[0] as $match)
					$m .= $match.' ';

				$html .= '<tr><td><a target="_blank" rel=noopener href="'.$site.'/wp-admin/post.php?post='.$post->ID.'&action=edit">'.$post->post_name.'</a></td><td>'.$m.'</td></tr>';
			}
		}

		$html .= '</table>';
		echo $html;
	}

}