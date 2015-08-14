<?php
namespace LM\Theme;

use LM\Theme\ThemeHelper, Exception;

/**
 * Class deals with all assets.
 */
class ThemeAssets {

	/**
	 * If _assets_with_hash true js filename will be like this: assets/js/dist/main.59a5d211.js
	 * else: assets/js/compiled/main.js
	 */
	public $_assets_with_hash;

	public function __construct() {
		$this->setup_properties();
	}

	/**
	 * Setup class properties.
	 * Allow to redefine _assets_with_hash.
	 */
	public function setup_properties() {
		// If development environment we must load not hashed assets.
		if ( defined( 'WP_ENV' ) && WP_ENV == 'development' ) {
			$this->_assets_with_hash = true;
		} else {
			$this->_assets_with_hash = true;
		}

		/**
		 * You can redefine this variable.
		 * add_filter( 'theme_assets_with_hash', function ( $assets_with_hash ) {
		 *	return false;
		 * }, 10, 1 );
		 */
		$this->_assets_with_hash = apply_filters( 'theme_assets_with_hash', $this->_assets_with_hash );
	}

	/**
	 * Load theme CSS files.
	 * Filenames dependent on assets_with_hash.
	 */
	public function load_css() {
		add_action( 'wp_enqueue_scripts', function() {
			$css_url = get_template_directory_uri() . '/assets/css/';
			if ( $this->_assets_with_hash ) {
				$css = ThemeHelper::get_dist_css( get_template_directory() . '/assets/css/dist/' );

				if ( isset( $css['main'] ) ) {
					$css_url .= 'dist/' . $css['main'];
				} else {
					throw new Exception( 'Can\'t find CSS file.' );
				}
			} else {
				$css_url .= 'compiled/main.css';
			}

			wp_register_style(
				'theme-css', // Name of the stylesheet.
				$css_url, // Path to the stylesheet.
				$deps = [], // An array of registered style handles this stylesheet depends on.
				$ver = null, // String specifying the stylesheet version number.
				$media = 'all' // The media for which this stylesheet has been defined.
			);
			wp_enqueue_style( 'theme-css' );
		} );
	}

	/**
	 * Load theme JS files.
	 * Filenames dependent on assets_with_hash.
	 */
	public function load_js() {
		add_action( 'wp_enqueue_scripts', function() {
			$js_url = get_template_directory_uri() . '/assets/js/';
			if ( $this->_assets_with_hash ) {
				$js = ThemeHelper::get_dist_js( get_template_directory() . '/assets/js/dist/' );

				if ( isset( $js['main'] ) ) {
					$js_url .= 'dist/' . $js['main'];
				} else {
					throw new Exception( 'Can\'t find JS file.' );
				}
			} else {
				$js_url .= 'compiled/main.js';
			}

			wp_register_script(
				'theme-js', // Name of the script.
				$js_url, // Path to the script.
				$deps = [], // An array of registered script handles this script depends on.
				$ver = null, // String specifying script version number.
				$in_footer = true // Whether to enqueue the script before </head> or before </body>.
			);
			wp_enqueue_script( 'theme-js' );
		} );
	}

	/**
	 * Some themes don't support emoji (WP 4.2+)
	 */
	public function remove_emoji() {
		add_action( 'wp_enqueue_scripts', function() {
			// Remove emoji style and script.
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
		} );
	}

}
