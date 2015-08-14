<?php
namespace LM\Theme;

use LM\Theme\ThemeAssets;

/**
 * Main theme class.
 */
class Theme {

	/**
	 * Let's init main theme functionality like load js/css, load translations, register sidebars etc.
	 */
	public function init() {
		$this->init_assets();
		$this->init_translations();
		$this->init_sidebars();
	}

	/**
	 * Include all necessary assets.
	 */
	public function init_assets() {
		$theme = new ThemeAssets();
		$theme->load_css();
		$theme->load_js();
		$theme->remove_emoji(); // we don't need emoji
	}

	/**
	 * Make theme available for translation.
	 */
	public function init_translations() {
		add_action( 'after_setup_theme', function() {
			load_theme_textdomain( 'leham', get_template_directory() . '/languages' );
		} );
	}

	/**
	 * Register sidebars.
	 */
	public function init_sidebars() {
		add_action( 'widgets_init', function() {
			register_sidebar( array(
				'name'          => esc_html__( 'Sidebar', 'leham' ),
				'id'            => 'sidebar',
				'description'   => '',
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			) );
		} );
	}

}
