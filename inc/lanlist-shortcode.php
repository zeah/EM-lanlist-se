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

	public function add_css() {
        wp_enqueue_style('emlanlistse-style', LANLIST_SE_PLUGIN_URL.'/assets/css/pub/em-lanlist-se.css', array(), '0.0.1', '(min-width: 1280px)');
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
					$html .= '<circle class="circle-svg" cx="10" cy="25" r="7"/>';
					$html .= '<circle class="circle-svg" cx="40" cy="25" r="7"/>';

					case 'fire':
					$html .= '<circle class="circle-svg" cx="10" cy="10" r="7"/>';
					$html .= '<circle class="circle-svg" cx="10" cy="40" r="7"/>';
					$html .= '<circle class="circle-svg" cx="40" cy="10" r="7"/>';
					$html .= '<circle class="circle-svg" cx="40" cy="40" r="7"/>';
					break;

					case 'fem':
					$html .= '<circle class="circle-svg" cx="10" cy="10" r="7"/>';
					$html .= '<circle class="circle-svg" cx="40" cy="40" r="7"/>';

					case 'tre':
					$html .= '<circle class="circle-svg" cx="10" cy="40" r="7"/>';
					$html .= '<circle class="circle-svg" cx="40" cy="10" r="7"/>';

					case 'en':
					$html .= '<circle class="circle-svg" cx="25" cy="25" r="7"/>';
					break;

					case 'to':
					$html .= '<circle class="circle-svg" cx="10" cy="40" r="7"/>';
					$html .= '<circle class="circle-svg" cx="40" cy="10" r="7"/>';
					break;

				}

				$html .= '</svg>';
			}

			// bestill 
			$html .= '<div class="emlanlist-bestill-container">';
			$html .= '<div class="emlanlist-bestill"><a target="_blank" rel="noopener" class="emlanlist-link" href="'.$meta['bestill'].'"><svg class="emlanlist-svg" version="1.1" x="0px" y="0px" width="26px" height="20px" viewBox="0 0 26 20" enable-background="new 0 0 24 24" xml:space="preserve"><path fill="none" d="M0,0h24v24H0V0z"/><path class="emlanlist-thumb" d="M1,21h4V9H1V21z M23,10c0-1.1-0.9-2-2-2h-6.31l0.95-4.57l0.03-0.32c0-0.41-0.17-0.79-0.44-1.06L14.17,1L7.59,7.59C7.22,7.95,7,8.45,7,9v10c0,1.1,0.9,2,2,2h9c0.83,0,1.54-0.5,1.84-1.22l3.02-7.05C22.95,12.5,23,12.26,23,12V10z"/></svg>Ansök här!</a></div>';
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