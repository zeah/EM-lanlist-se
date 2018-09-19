<?php 

/**
 * WP Shortcodes
 */
final class Lanlist_shortcode {
	/* singleton */
	private static $instance = null;

	public $pixels = [];

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->wp_hooks();
	}


	/**
	 * hooks for wp
	 */
	private function wp_hooks() {

		// loan list
		if (!shortcode_exists('lan')) add_shortcode('lan', array($this, 'add_shortcode'));
		else add_shortcode('emlanlist', array($this, 'add_shortcode'));

		// loan thumbnail
		if (!shortcode_exists('lan-bilde')) add_shortcode('lan-bilde', array($this, 'add_shortcode_bilde'));
		else add_shortcode('emlanlist-bilde', array($this, 'add_shortcode_bilde'));

		// loan button
		if (!shortcode_exists('lan-bestill')) add_shortcode('lan-bestill', array($this, 'add_shortcode_bestill'));
		else add_shortcode('emlanlist-bestill', array($this, 'add_shortcode_bestill'));


		add_filter('search_first', array($this, 'add_serp'));
	}


	/**
	 * returns a list of loans
	 */
	public function add_shortcode($atts, $content = null) {

		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		if (!is_array($atts)) $atts = [];

		$args = [
			'post_type' 		=> 'emlanlistse',
			'posts_per_page' 	=> -1,
			'orderby'			=> [
										'meta_value_num' => 'ASC',
										'title' => 'ASC'
								   ],
			'meta_key'			=> 'emlanlistse_sort'.($atts['lan'] ? '_'.sanitize_text_field($atts['lan']) : '')
		];


		$type = false;
		if (isset($atts['lan'])) $type = $atts['lan'];
		if ($type)
			$args['tax_query'] = array(
					array(
						'taxonomy' => 'emlanlistsetype',
						'field' => 'slug',
						'terms' => sanitize_text_field($type)
					)
				);


		$names = false;
		if (isset($atts['name'])) $names = explode(',', preg_replace('/ /', '', $atts['name']));
		if ($names) $args['post_name__in'] = $names;
		
		$exclude = get_option('emlanlistse_exclude');

		if (is_array($exclude) && !empty($exclude)) $args['post__not_in'] = $exclude;

		$posts = get_posts($args);	

		$sorted_posts = [];
		if ($names) {
			foreach(explode(',', preg_replace('/ /', '', $atts['name'])) as $n)
				foreach($posts as $p) 
					if ($n === $p->post_name) array_push($sorted_posts, $p);
		
			$posts = $sorted_posts;
		}
				

		$html = $this->get_html($posts, $atts);

		return $html;
	}


	/**
	 * returns only thumbnail from loan
	 */
	public function add_shortcode_bilde($atts, $content = null) {
		if (!isset($atts['name']) || $atts['name'] == '') return;

		$args = [
			'post_type' 		=> 'emlanlistse',
			'posts_per_page'	=> 1,
			'name' 				=> sanitize_text_field($atts['name'])
		];

		$post = get_posts($args);

		if (!is_array($post)) return;

		if (!get_the_post_thumbnail_url($post[0])) return;

		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		$meta = get_post_meta($post[0]->ID, 'emlanlistse_data');
		if (isset($meta[0])) $meta = $meta[0];

		$float = false;
		if ($atts['float']) 
			switch ($atts['float']) {
				case 'left': $float = ' style="float: left; margin-right: 3rem;"'; break;
				case 'right': $float = ' style="float: right; margin-left: 3rem;"'; break;
			}

		$html = '';

		// if ($meta['pixel']) {
		// 	if ($atts['source']) $meta['pixel'] = $this->add_source($meta['pixel'], $atts['source']); 
		// 	$html .= $this->add_pixel($meta['pixel']);
		// }
		// if ($atts['source']) $meta['bestill'] = $this->add_source($meta['bestill'], $atts['source']);
		// if  ($atts['source']) {
		// 	if (preg_match('/(?:(?!\?|&))(?:source=.*?)(?:(&|$))/', $meta['bestill'], $matches))
		// 		$meta['bestill'] = str_replace($matches[0], '', $meta['bestill']); 

		// 	$meta['bestill'] = preg_replace('/\?$/', '', $meta['bestill']);
 
		// 	if (strpos($meta['bestill'], '?') !== false) $meta['bestill'] .= '&source=' . $atts['source'];
		// 	else $meta['bestill'] .= '?source=' . $atts['source'];
		// }

		// returns with anchor
		if ($meta['bestill']) {
			// adding tracking pixel
			if ($meta['pixel']) {
				if ($meta['qstring']) $html .= $this->add_pixel($this->add_query_string($meta['pixel'], $atts['source'], $atts['page']));
				else $html .= $this->add_pixel($meta['pixel']);
			}

			// fixing query string
			if ($meta['qstring']) $meta['bestill'] = $this->add_query_string($meta['bestill'], $atts['source'], $atts['page']);

			// image with anchor
			// $meta['bestill'] = $this->add_query_string($meta['bestill'], $atts['source'], $atts['page']);
			// if ($meta['pixel']) $html .= $this->add_pixel($this->add_query_string($meta['pixel'], $atts['source'], $atts['page']));


			$html .= '<div class="emlanlist-logo-ls"'.($float ? $float : '').'><a target="_blank" rel=noopener href="'.esc_url($meta['bestill']).'"><img alt="'.esc_attr($post[0]->post_title).'" src="'.esc_url(get_the_post_thumbnail_url($post[0], 'full')).'"></a></div>';
			return $html;
		}
		// anchor-less image
		return'<div class="emlanlist-logo-ls"'.($float ? $float : '').'><img alt="'.esc_attr($post[0]->post_title).'" src="'.esc_url(get_the_post_thumbnail_url($post[0], 'full')).'"></div>';
	}


	/**
	 * returns bestill button only from loan
	 */
	public function add_shortcode_bestill($atts, $content = null) {
		if (!isset($atts['name']) || $atts['name'] == '') return;

		$args = [
			'post_type' 		=> 'emlanlistse',
			'posts_per_page'	=> 1,
			'name' 				=> sanitize_text_field($atts['name'])
		];

		$post = get_posts($args);
		if (!is_array($post)) return;

		$meta = get_post_meta($post[0]->ID, 'emlanlistse_data');

		if (!is_array($meta)) return;

		$meta = $meta[0];

		if (!$meta['bestill']) return;

		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		$float = false;
		if ($atts['float']) 
			switch ($atts['float']) {
				case 'left': $float = ' style="float: left; margin-right: 3rem;"'; break;
				case 'right': $float = ' style="float: right; margin-left: 3rem;"'; break;
			}

		$html = '';

		// fixing query string
		if ($meta['qstring']) $meta['bestill'] = $this->add_query_string($meta['bestill'], $atts['source'], $atts['page']);

		// adding tracking pixel
		if ($meta['pixel']) {
			if ($meta['qstring']) $html .= $this->add_pixel($this->add_query_string($meta['pixel'], $atts['source'], $atts['page']));
			else $html .= $this->add_pixel($meta['pixel']);
		}

		$html .= '<div class="emlanlist-bestill emlanlist-bestill-mobile"'.($float ? $float : '').'><a target="_blank" rel="noopener" class="emlanlist-link" href="'.esc_url($meta['bestill']).'"><svg class="emlanlist-svg" version="1.1" x="0px" y="0px" width="26px" height="20px" viewBox="0 0 26 20" enable-background="new 0 0 24 24" xml:space="preserve"><path fill="none" d="M0,0h24v24H0V0z"/><path class="emlanlist-thumb" d="M1,21h4V9H1V21z M23,10c0-1.1-0.9-2-2-2h-6.31l0.95-4.57l0.03-0.32c0-0.41-0.17-0.79-0.44-1.06L14.17,1L7.59,7.59C7.22,7.95,7,8.45,7,9v10c0,1.1,0.9,2,2,2h9c0.83,0,1.54-0.5,1.84-1.22l3.02-7.05C22.95,12.5,23,12.26,23,12V10z"/></svg> Ansök här!</a></div>';
		return $html;
	}


	/**
	 * adding sands to head
	 */
	public function add_css() {
        wp_enqueue_style('emlanlistse-style', LANLIST_SE_PLUGIN_URL.'assets/css/pub/em-lanlist-se.css', array(), '1.0.1', '(min-width: 801px)');
        wp_enqueue_style('emlanlistse-mobile', LANLIST_SE_PLUGIN_URL.'assets/css/pub/em-lanlist-se-mobile.css', array(), '1.0.1', '(max-width: 800px)');
	}


	/**
	 * returns the html of a list of loans
	 * @param  WP_Post $posts a wp post object
	 * @return [html]        html list of loans
	 */
	private function get_html($posts, $atts) {
		$html = '<ul class="emlanlist-ul">';

		foreach ($posts as $p) {
			
			$meta = get_post_meta($p->ID, 'emlanlistse_data');

			// skip if no meta found
			if (isset($meta[0])) $meta = $meta[0];
			else continue;

			// if ($meta['pixel']) {
			// 	if ($source) $meta['pixel'] = $this->add_source($meta['pixel'], $source);
			// 	$html .= $this->add_pixel($meta['pixel']);
			// }

			
			// if ($source && $meta['bestill']) $meta['bestill'] = $this->add_source($meta['bestill'], $source);

			// grid container
			$html .= '<li class="emlanlist-container">';

			if ($meta['qstring']) $meta['bestill'] = $this->add_query_string($meta['bestill'], $atts['source'], $atts['page']);

			if ($meta['pixel']) {
				if ($meta['qstring']) $html .= $this->add_pixel($this->add_query_string($meta['pixel'], $atts['source'], $atts['page']));
				else $html .= $this->add_pixel($meta['pixel']);
			}

			// sanitize meta
			$meta = $this->esc_kses($meta);

			// title
			$html .= '<div class="emlanlist-title-container"><a class="emlanlist-title" href="'.$meta['readmore'].'">'.wp_kses_post($p->post_title).'</a></div>';

			// thumbnail
			$html .= '<div class="emlanlist-logo-container"><a target="_blank" rel="noopener" href="'.$meta['bestill'].'"><img class="emlanlist-logo" src="'.wp_kses_post(get_the_post_thumbnail_url($p,'post-thumbnail')).'"></a></div>';

			// info container
			$html .= '<div class="emlanlist-info-container">';

			// info 1
			if ($meta['info01']) $html .= '<div class="emlanlist-info emlanlist-info-en">'.$meta['info01'].'</div>';

			// info 2
			if ($meta['info02']) $html .= '<div class="emlanlist-info emlanlist-info-to">'.$meta['info02'].'</div>';

			// info 3
			if ($meta['info03']) $html .= '<div class="emlanlist-info emlanlist-info-tre">'.$meta['info03'].'</div>';

			// info 4
			if ($meta['info04']) $html .= '<div class="emlanlist-info emlanlist-info-fire">'.$meta['info04'].'</div>';

			$html .= '</div>';

			// info list container 
			$html .= '<div class="emlanlist-list-container">';

			// info 5
			if ($meta['info05']) $html .= '<div class="emlanlist-info emlanlist-info-fem">'.$meta['info05'].'</div>';

			// info 6
			if ($meta['info06']) $html .= '<div class="emlanlist-info emlanlist-info-seks">'.$meta['info06'].'</div>';

			// info 7
			if ($meta['info07']) $html .= '<div class="emlanlist-info emlanlist-info-syv">'.$meta['info07'].'</div>';

			// info 8
			if ($meta['info08']) $html .= '<div class="emlanlist-info emlanlist-info-atte">'.$meta['info08'].'</div>';

			$html .= '</div>';

			$html .= '<div class="emlanlist-end-container">';
			// terning
			if ($meta['terning'] != 'ingen') {
				$html .= '<svg class="emlanlist-terning">
							<defs>
							    <linearGradient id="emlanlist-grad" x1="0%" y1="100%" x2="100%" y2="100%">
							      <stop offset="0%" style="stop-color:rgb(200,0,0);stop-opacity:1" />
							      <stop offset="100%" style="stop-color:rgb(255,0,0);stop-opacity:1" />
							    </linearGradient>
							  </defs>
							<rect class="emlanlist-rect-svg" rx="7" ry="7" fill="url(#emlanlist-grad)"/>';

				switch ($meta['terning']) {

					case 'seks':
					$html .= '<circle class="emlanlist-circle-svg" cx="11" cy="25" r="5"/>';
					$html .= '<circle class="emlanlist-circle-svg" cx="39" cy="25" r="5"/>';

					case 'fire':
					$html .= '<circle class="emlanlist-circle-svg" cx="11" cy="10" r="5"/>';
					$html .= '<circle class="emlanlist-circle-svg" cx="39" cy="40" r="5"/>';

					case 'to':
					$html .= '<circle class="emlanlist-circle-svg" cx="11" cy="40" r="5"/>';
					$html .= '<circle class="emlanlist-circle-svg" cx="39" cy="10" r="5"/>';
					break;

					case 'fem':
					$html .= '<circle class="emlanlist-circle-svg" cx="10" cy="10" r="5"/>';
					$html .= '<circle class="emlanlist-circle-svg" cx="40" cy="40" r="5"/>';

					case 'tre':
					$html .= '<circle class="emlanlist-circle-svg" cx="10" cy="40" r="5"/>';
					$html .= '<circle class="emlanlist-circle-svg" cx="40" cy="10" r="5"/>';

					case 'en':
					$html .= '<circle class="emlanlist-circle-svg" cx="25" cy="25" r="5"/>';
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

			$html .= '</div>'; // end-container

			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}









	private function add_pixel($pixel) {
		if ($this->pixels[$pixel]) return '';

		$this->pixels[$pixel] = true;

		return '<img width=0 height=0 src="'.esc_url($pixel).'" style="position:absolute">';
	}










	private function add_query_string($link = null, $source = null, $page = null) {
		
		$axo = [
			'page' => 'aff_sub2',
			'source' => 'source',
			'click_id' => 'aff_click_id',
			'type' => 'aff_sub'
		];

		$adservice = [
			'page' => 'sub'
		];

		if (strpos($link, 'axo') !== false) $def = $axo;
		elseif (strpos($link, 'adservice') !== false) $def = $adservice;
		else $def = $axo; // axo is currently default

		// parsing given url for query string
		parse_str(parse_url($link)['query'], $url_query);

		// parsing current url for query string
		parse_str($_SERVER['QUERY_STRING'], $result);
		// wp_die('<xmp>'.print_r($url_query, true).'</xmp>');
		
		// source
		if ($def['source']) {
			if ($source) $result[$def['source']] = $source;
			// elseif ($url_query[$def['source']]) $result[$def['source']] = $url_query[$def['source']];
			elseif (!$url_query[$def['source']]) $result[$def['source']] = $_SERVER['SERVER_NAME'];
		}


		// page (aff_sub2)
		if ($def['page']) {
			global $post;
			// global $pagename;
			if ($page) $result[$def['page']] = $page;
			// elseif ($url_query[$def['page']]) $result[$def['page']] = $url_query[$def['page']]; 
			elseif (!$url_query[$def['page']]) $result[$def['page']] = $post->post_name;
		}
			// wp_die('<xmp>'.print_r($result, true).'</xmp>');
		// aff_sub 
		// aff_click_id
		// from google ad, bing ad or organic
		if ($def['type']) {
		$result[$def['type']] = 'organic';
		foreach($result as $key => $value)
			switch ($key) {
				case 'gclid': 
					$result[$def['type']] = 'google'; 
					$result[$def['click_id']] = $value;
					break;
				case 'msclkid': 
					$result[$def['type']] = 'bing'; 
					$result[$def['click_id']] = $value;
					break;
			}
		// removing google ad and bing ad parameter
		}
			unset($result['gclid']);
			unset($result['msclkid']);

		return add_query_arg($result, $link);
	}












	/**
	 * wp filter for adding to internal serp
	 * array_push to $data
	 * $data['html'] to be printed
	 * 
	 * @param [Array] $data [filter]
	 */
	public function add_serp($data) {
		global $post;

		if ($post->post_type != 'emlanlistse') return $data;

		$exclude = get_option('emlanlistse_exclude');
		if (!is_array($exclude)) $exclude = [];
		if (in_array($post->ID, $exclude)) return $data;

		$exclude_serp = get_option('emlanlistse_exclude_serp');
		if (!is_array($exclude_serp)) $exclude_serp = [];
		if (in_array($post->ID, $exclude_serp)) return $data;

		$html['html'] = $this->get_html([$post]);

		array_push($data, $html);
		add_action('wp_enqueue_scripts', array($this, 'add_css'));

		return $data;
	}

	// private function add_source($meta, $source) {

	// 	// removing current source
	// 	if (preg_match('/(?:(?!\?|&))(?:source=.*?)(?:(&|$))/', $meta, $matches))
	// 		$meta = str_replace($matches[0], '', $meta); 
	// 	$meta = preg_replace('/\?$/', '', $meta);

	// 	// adding source
	// 	if (strpos($meta, '?') !== false) $meta .= '&source=' . $source;
	// 	else $meta .= '?source=' . $source;

	// 	return $meta;
	// }












	/**
	 * kisses the data
	 * recursive sanitizer
	 * 
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