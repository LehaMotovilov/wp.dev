<?php
namespace LM\Theme;

/**
 * Main theme class.
 */
class Theme {

	/**
	 * Contains all themes menu locations.
	 *
	 * @var array
	 */
	private $_menus;

	/**
	 * Contains all extra theme files.
	 *
	 * @var array
	 */
	private $_extra_files;

	/**
	 * Contains all JS/CSS files.
	 *
	 * @var array
	 */
	private $_assets_files;

	/**
	 * Theme constructor.
	 */
	public function __construct() {
		$this->_extra_files = require_once( get_template_directory() . '/includes/configs/extra-files.php' );
		$this->_assets_files = require_once( get_template_directory() . '/includes/configs/assets-config.php' );
		$this->_menus = require_once( get_template_directory() . '/includes/configs/menus.php' );
	}

	/**
	 * Let's init main theme functionality like load js/css,
	 * load translations, register sidebars etc.
	 */
	public function init() {
		$this->init_assets();
		$this->init_theme_supports();
		$this->include_theme_functions();
		$this->init_menus();
	}

	/**
	 * Include all necessary assets.
	 */
	public function init_assets() {
		new ThemeAssets( $this->_assets_files );
	}

	/**
	 * Add theme supports.
	 *
	 * @link: https://codex.wordpress.org/Function_Reference/add_theme_support
	 */
	public function init_theme_supports() {
		add_action( 'after_setup_theme', function() {
			add_theme_support( 'html5', [
				'comment-list',
				'comment-form',
				'search-form',
				'gallery',
				'caption'
			] );
			add_theme_support( 'menu' );
		} );
	}

	/**
	 * Include template extra actions, filters, template functions etc.
	 */
	public function include_theme_functions() {
		$template_path = get_template_directory() . '/includes/functions/';
		foreach ( $this->_extra_files as $file ) {
			$file = $template_path . $file;
			if ( file_exists( $file ) ) {
				require_once( $file );
			}
		}
	}

	/**
	 * Register all menu locations.
	 */
	public function init_menus() {
		add_action( 'after_setup_theme', function () {
			foreach ( $this->_menus as $location ) {
				register_nav_menu( $location, ucwords( str_replace( '-', ' ', $location ) ) );
			}
		} );
	}

}
