<?php 

final class Lanlist_shortcode {
	/* singleton */
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->wp_hooks();
	}

	private function wp_hooks() {
		if (!shortcode_exists('lan')) add_shortcode('lan', array($this, 'add_shortcode'));
		else add_shortcode('emlanlist', array($this, 'add_shortcode'));

		if (!shortcode_exists('lan-bilde')) add_shortcode('lan-bilde', array($this, 'add_shortcode_bilde'));
		else add_shortcode('emlanlist-bilde', array($this, 'add_shortcode_bilde'));

		if (!shortcode_exists('lan-bestill')) add_shortcode('lan-bestill', array($this, 'add_shortcode_bestill'));
		else add_shortcode('emlanlist-bestill', array($this, 'add_shortcode_bestill'));
	
	}


	public function add_shortcode($atts, $content = null) {

		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		if (!is_array($atts)) $atts = [];

		$args = [
			'post_type' 		=> 'emlanlistse',
			'posts_per_page' 	=> -1,
			'orderby'			=> [
										'meta_value_num' => 'DESC',
										'title' => 'ASC'
								   ],
			'meta_key'			=> 'emlanlistse_sort'
		];

		$names = false;
		if (isset($atts['name'])) $names = explode(',', preg_replace('/ /', '', $atts['name']));
		if ($names) $args['post_name__in'] = $names;
		
		$exclude = get_option('emlanlistse_exclude');

		if (is_array($exclude) && !empty($exclude)) $args['post__not_in'] = $exclude;

		$posts = get_posts($args);

		$html = $this->get_html($posts);

		return $html;
	}



	public function add_shortcode_bilde($atts, $content = null) {
		if (!isset($atts['name'])) return;

		$args = [
			'post_type' => 'emlanlistse',
			'posts_per_page'	=> 1,
			'name' => sanitize_text_field($atts['name'])
		];

		$post = get_posts($args);

		if (!is_array($post)) return;

		if (!get_the_post_thumbnail_url($post[0])) return;
		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		return '<div class="emlanlist-logo-ls"><img style="width: 100%; height: auto;" src="'.esc_url(get_the_post_thumbnail_url($post[0], 'full')).'"></div>';
	}

	public function add_shortcode_bestill($atts, $content = null) {
		if (!isset($atts['name'])) return;

		$args = [
			'post_type' => 'emlanlistse',
			'posts_per_page'	=> 1,
			'name' => sanitize_text_field($atts['name'])
		];

		$post = get_posts($args);

		if (!is_array($post)) return;

		$meta = get_post_meta($post[0]->ID, 'emlanlistse_data');

		if (!is_array($meta)) return;

		$meta = $meta[0];

		if (!$meta['bestill']) return;

		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		return '<div class="emlanlist-bestill"><a target="_blank" rel="noopener" class="emlanlist-link" href="'.esc_url($meta['bestill']).'"><svg class="emlanlist-svg" version="1.1" x="0px" y="0px" width="26px" height="20px" viewBox="0 0 26 20" enable-background="new 0 0 24 24" xml:space="preserve"><path fill="none" d="M0,0h24v24H0V0z"/><path class="emlanlist-thumb" d="M1,21h4V9H1V21z M23,10c0-1.1-0.9-2-2-2h-6.31l0.95-4.57l0.03-0.32c0-0.41-0.17-0.79-0.44-1.06L14.17,1L7.59,7.59C7.22,7.95,7,8.45,7,9v10c0,1.1,0.9,2,2,2h9c0.83,0,1.54-0.5,1.84-1.22l3.02-7.05C22.95,12.5,23,12.26,23,12V10z"/></svg> Ansök här!</a></div>';
	}



	public function add_css() {
        wp_enqueue_style('emlanlistse-style', LANLIST_SE_PLUGIN_URL.'/assets/css/pub/em-lanlist-se.css', array(), '0.0.1', '(min-width: 1280px)');
        wp_enqueue_style('emlanlistse-mobile', LANLIST_SE_PLUGIN_URL.'/assets/css/pub/em-lanlist-se-mobile.css', array(), '0.0.1', '(max-width: 1279px)');
	}

	private function get_html($posts) {

		$html = '<ul class="emlanlist-ul">';

		foreach ($posts as $p) {
			// wp_die('<xmp>'.print_r($p, true).'</xmp>');
			
			$meta = get_post_meta($p->ID, 'emlanlistse_data');

			// wp_die('<xmp>'.print_r(get_post_meta($p->ID, 'emlanlistse_sort'), true).'</xmp>');
			

			if (isset($meta[0])) $meta = $meta[0];
			else continue;

			$meta = $this->esc_kses($meta);

			$html .= '<li class="emlanlist-container">';

			// title
			$html .= '<div class="emlanlist-title-container"><a class="emlanlist-title" href="'.$meta['readmore'].'">'.wp_kses_post($p->post_title).'</a></div>';

			// thumbnail
			$html .= '<div class="emlanlist-logo-container"><a target="_blank" rel="noopener" href="'.$meta['bestill'].'"><img class="emlanlist-logo" src="'.wp_kses_post(get_the_post_thumbnail_url($p,'post-thumbnail')).'"></a></div>';

			$html .= '<div class="emlanlist-info-container">';

			// info 1
			$html .= '<div class="emlanlist-info emlanlist-info-en">'.$meta['info01'].'</div>';

			// info 2
			$html .= '<div class="emlanlist-info emlanlist-info-to">'.$meta['info02'].'</div>';

			// info 3
			$html .= '<div class="emlanlist-info emlanlist-info-tre">'.$meta['info03'].'</div>';

			// info 4
			$html .= '<div class="emlanlist-info emlanlist-info-fire">'.$meta['info04'].'</div>';

			$html .= '</div>';

			$html .= '<div class="emlanlist-list-container">';

			// info 5
			$html .= '<div class="emlanlist-info emlanlist-info-fem">'.$meta['info05'].'</div>';

			// info 6
			$html .= '<div class="emlanlist-info emlanlist-info-seks">'.$meta['info06'].'</div>';

			// info 7
			$html .= '<div class="emlanlist-info emlanlist-info-syv">'.$meta['info07'].'</div>';

			// info 8
			$html .= '<div class="emlanlist-info emlanlist-info-atte">'.$meta['info08'].'</div>';

			$html .= '</div>';

			// terning

			if ($meta['terning'] != 'ingen') {
				$html .= '<svg class="emlanlist-terning">
							<defs>
							    <linearGradient id="emlanlist-grad" x1="0%" y1="100%" x2="100%" y2="100%">
							      <stop offset="0%" style="stop-color:rgb(200,0,0);stop-opacity:1" />
							      <stop offset="100%" style="stop-color:rgb(255,0,0);stop-opacity:1" />
							    </linearGradient>
							  </defs>
							<rect class="rect-svg" rx="7" ry="7" fill="url(#emlanlist-grad)"/>';

				switch ($meta['terning']) {

					case 'seks':
					$html .= '<circle class="circle-svg" cx="11" cy="25" r="5"/>';
					$html .= '<circle class="circle-svg" cx="39" cy="25" r="5"/>';

					case 'fire':
					$html .= '<circle class="circle-svg" cx="11" cy="10" r="5"/>';
					$html .= '<circle class="circle-svg" cx="39" cy="40" r="5"/>';
					// break;

					case 'to':
					$html .= '<circle class="circle-svg" cx="11" cy="40" r="5"/>';
					$html .= '<circle class="circle-svg" cx="39" cy="10" r="5"/>';
					break;

					case 'fem':
					$html .= '<circle class="circle-svg" cx="10" cy="10" r="5"/>';
					$html .= '<circle class="circle-svg" cx="40" cy="40" r="5"/>';

					case 'tre':
					$html .= '<circle class="circle-svg" cx="10" cy="40" r="5"/>';
					$html .= '<circle class="circle-svg" cx="40" cy="10" r="5"/>';

					case 'en':
					$html .= '<circle class="circle-svg" cx="25" cy="25" r="5"/>';
					break;

				}

				$html .= '</svg>';
			}

			// bestill 
			$html .= '<div class="emlanlist-bestill-container">';
			// $html .= '<div class="emlanlist-bestill"><a target="_blank" rel="noopener" class="emlanlist-link" href="'.$meta['bestill'].'">Ansök här <svg class="emlanlist-svg" version="1.1" x="0px" y="0px" width="26px" height="20px" viewBox="0 0 26 22" enable-background="new 0 0 24 24" xml:space="preserve"><path fill="none" d="M0,0h24v24H0V0z"/><path opacity="0.87" fill="none" d="M0,0h24v24H0V0z"/><path fill="#fff" d="M18,8h-1V6c0-2.76-2.24-5-5-5S7,3.24,7,6v2H6c-1.1,0-2,0.9-2,2v10c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V10C20,8.9,19.1,8,18,8z M9,6c0-1.66,1.34-3,3-3s3,1.34,3,3v2H9V6z M18,20H6V10h12V20z M12,17c1.1,0,2-0.9,2-2c0-1.1-0.9-2-2-2c-1.1,0-2,0.9-2,2C10,16.1,10.9,17,12,17z"/></svg></a></div>';
			$html .= '<div class="emlanlist-bestill"><a target="_blank" rel="noopener" class="emlanlist-link" href="'.$meta['bestill'].'"><svg class="emlanlist-svg" version="1.1" x="0px" y="0px" width="26px" height="20px" viewBox="0 0 26 20" enable-background="new 0 0 24 24" xml:space="preserve"><path fill="none" d="M0,0h24v24H0V0z"/><path class="emlanlist-thumb" d="M1,21h4V9H1V21z M23,10c0-1.1-0.9-2-2-2h-6.31l0.95-4.57l0.03-0.32c0-0.41-0.17-0.79-0.44-1.06L14.17,1L7.59,7.59C7.22,7.95,7,8.45,7,9v10c0,1.1,0.9,2,2,2h9c0.83,0,1.54-0.5,1.84-1.22l3.02-7.05C22.95,12.5,23,12.26,23,12V10z"/></svg> Ansök här!</a></div>';
			$html .= '<div class="emlanlist-bestilltext">'.$meta['bestill_text'].'</div>';
			$html .= '</div>';

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}


	/**
	 * kisses the data
	 * @param  Mixed $data Strings or Arrays
	 * @return Mixed       Kissed data
	 */
	private function esc_kses($data) {
		if (!is_array($data)) return wp_kses_post($data);

		$d = [];
		foreach($data as $key => $value)
			$d[$key] = $this->esc_kses($value);

		return $d;
	}
}