<?php
namespace LM\Theme;

use LM\Theme\ThemeAssets;

/**
 * Main theme class.
 */
class Theme {

	// Contains all extra theme files.
	private $_extra_files = [
		'extra-actions.php',
		'extra-filters.php',
		'template-functions.php'
	];

	/**
	 * Let's init main theme functionality like load js/css, load translations, register sidebars etc.
	 */
	public function init() {
		$this->init_assets();
		$this->init_translations();
		$this->init_sidebars();
		$this->init_thumbnails();
		$this->init_theme_supports();
		$this->include_theme_functions();
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

	/**
	 * Enable support for Post Thumbnails on posts and pages.
	 * Setup default thumbnail size and add custom size.
	 */
	public function init_thumbnails() {
		add_action( 'after_setup_theme', function() {
			add_theme_support( 'post-thumbnails' );
			set_post_thumbnail_size( 300, 300 ); // setup default thumbnail size
			add_image_size( 'custom-size', $width = 400, $height = 400, $crop = true );
		} );
	}

	/**
	 * Add theme supports.
	 * Supported features see here: https://codex.wordpress.org/Function_Reference/add_theme_support
	 */
	public function init_theme_supports() {
		add_action( 'after_setup_theme', function() {
			add_theme_support( 'html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption'] );
			add_theme_support( 'title-tag' );
			add_theme_support( 'automatic-feed-links' );
		} );
	}

	/**
	 * Include template extra actions, filters, template functions etc.
	 */
	public function include_theme_functions() {
		$template_path = get_template_directory() . '/includes/functions/';
		foreach ( $this->_extra_files as $file ) {
			if ( file_exists( $template_path . $file ) ) {
				require_once( $template_path . $file );
			}
		}
	}

}
