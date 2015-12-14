<?php
namespace LM\Theme;

use Exception;

/**
 * Class deals with all assets.
 */
class ThemeAssets {

	/**
	 * If _assets_with_hash true js filename will be like this: assets/scripts/dist/main.59a5d211.js
	 * else: assets/scripts/compiled/main.js
	 */
	public $_assets_with_hash;
	public $css_files;
	public $js_files;

	public function __construct( $config ) {
		$this->setup_properties( $config );
		$this->setup_css_files();
		$this->setup_js_files();
		$this->load_css();
		$this->load_js();
		$this->localize_script();
		$this->remove_emoji(); // we don't need smiles
		$this->remove_wpembed(); // we don't need embeds
	}

	/**
	 * Setup class properties.
	 * Allow to redefine _assets_with_hash.
	 *
	 * @param array $config
	 */
	public function setup_properties( array $config ) {
		// If development environment we must load not hashed assets.
		if ( defined( 'WP_ENV' ) && WP_ENV == 'development' ) {
			$this->_assets_with_hash = true;
		} else {
			$this->_assets_with_hash = true;
		}

		$this->css_files = $config['css'];
		$this->js_files = $config['js'];

		/**
		 * You can redefine this variable.
		 * add_filter( 'theme_assets_with_hash', function ( $assets_with_hash ) {
		 *	return false;
		 * }, 10, 1 );
		 */
		$this->_assets_with_hash = apply_filters( 'theme_assets_with_hash', $this->_assets_with_hash );
	}

	/**
	 * Setup css files before load.
	 * File urls dependent on assets_with_hash.
	 *
	 * @throws Exception If file not found.
	 */
	public function setup_css_files() {
		$css_url = get_template_directory_uri() . '/assets/css/';
		if ( $this->_assets_with_hash ) {
			$css = ThemeHelper::get_dist_css( get_template_directory() . '/assets/css/dist/' );

			foreach ( $this->css_files as $id => $info ) {
				if ( $info['type'] !== 'file' ) {
					continue;
				}
				if ( isset( $css[$id] ) ) {
					$this->css_files[$id]['file'] = $css_url . 'dist/' . $css[$id];
				} else {
					throw new Exception( 'Can\'t find CSS file.' );
				}
			}
		} else {
			foreach ( $this->css_files as $id => $info ) {
				if ( $info['type'] !== 'file' ) {
					continue;
				}
				$this->css_files[$id]['file'] = $css_url . 'compiled/' . $id . '.css';
			}
		}
	}

	/**
	 * Setup js files before load.
	 * File urls dependent on assets_with_hash.
	 *
	 * @throws Exception If file not found.
	 */
	public function setup_js_files() {
		$js_url = get_template_directory_uri() . '/assets/js/';
		if ( $this->_assets_with_hash ) {
			$js = ThemeHelper::get_dist_js( get_template_directory() . '/assets/js/dist/' );

			foreach ( $this->js_files as $id => $info ) {
				if ( $info['type'] !== 'file' ) {
					continue;
				}
				if ( isset( $js[$id] ) ) {
					$this->js_files[$id]['file'] = $js_url . 'dist/' . $js[$id];
				} else {
					throw new Exception( 'Can\'t find JS file.' );
				}
			}
		} else {
			foreach ( $this->js_files as $id => $info ) {
				if ( $info['type'] !== 'file' ) {
					continue;
				}
				$this->js_files[$id]['file'] = $js_url . 'compiled/' . $id . '.js';
			}
		}
	}

	/**
	 * Load theme CSS files.
	 */
	public function load_css() {
		add_action( 'wp_enqueue_scripts', function() {
			foreach ( $this->css_files as $id => $info ) {
				wp_register_style(
						'theme-' . $id, // Name of the stylesheet.
						$info['file'], // Path to the stylesheet.
						$info['deps'], // An array of registered style handles this stylesheet depends on.
						$ver = null, // String specifying the stylesheet version number.
						$media = 'all' // The media for which this stylesheet has been defined.
				);

				if ( $info['condition'] == 'all' ) {
					wp_enqueue_style( 'theme-' . $id );
				} elseif ( $info['condition'] == 'single' && is_single() ) {
					wp_enqueue_style( 'theme-' . $id );
				} elseif ( $info['condition'] == 'category' && is_category() ) {
					wp_enqueue_style( 'theme-' . $id );
				}
			}
		} );
	}

	/**
	 * Load theme JS files.
	 */
	public function load_js() {
		add_action( 'wp_enqueue_scripts', function() {
			foreach ( $this->js_files as $id => $info ) {
				wp_register_script(
					'theme-' . $id, // Name of the script.
					$info['file'], // Path to the script.
					$info['deps'], // An array of registered script handles this script depends on.
					$ver = null, // String specifying script version number.
					$in_footer = true // Whether to enqueue the script before </head> or before </body>.
				);

				if ( $info['condition'] == 'all' ) {
					wp_enqueue_script( 'theme-' . $id );
				} elseif ( $info['condition'] == 'single' && is_single() ) {
					wp_enqueue_script( 'theme-' . $id );
				} elseif ( $info['condition'] == 'category' && is_category() ) {
					wp_enqueue_script( 'theme-' . $id );
				}
			}

			// Check jQuery.
			foreach ( $this->js_files as $js_file ) {
				if ( in_array( 'jquery', $js_file['deps'] ) ) {
					wp_deregister_script( 'jquery' );
					wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js', $deps = [], $ver = null, $in_footer = false );
				}
			}
		} );
	}

	/**
	 * Some themes don't support emoji (WP 4.2+)
	 */
	public function remove_emoji() {
		add_action( 'wp_enqueue_scripts', function() {
			// Remove smiles style and script.
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
		} );
	}

	/**
	 * Some themes don't support WP Embeds (WP 4.4)
	 */
	public function remove_wpembed() {
		// Remove oEmbed-specific script from the front-end and back-end.
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	}

	/**
	 * Localize scripts, we can pass php variables here.
	 */
	public function localize_script() {
		add_action( 'wp_enqueue_scripts', function() {
			wp_localize_script( 'theme-main', 'jsObject', [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			] );
		} );
	}

}
