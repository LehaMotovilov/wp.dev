<?php
/*
	Plugin Name: What The File
	Plugin URI: http://www.barrykooij.com/what-the-file/
	Description: What The File adds an option to your toolbar showing what file and template parts are used to display the page you’re currently viewing. You can click the file name to directly edit it through the theme editor. Supports BuddyPress and Roots Theme. More information can be found at the <a href='http://wordpress.org/extend/plugins/what-the-file/'>WordPress plugin page</a>.
	Version: 1.5.3
	Author: Never5
	Author URI: http://www.never5.com/
	License: GPL v3

	What The File Plugin
	Copyright (C) 2012-2016, Never5 - www.never5.com

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class WhatTheFile {

	const OPTION_INSTALL_DATE = 'whatthefile-install-date';
	const OPTION_ADMIN_NOTICE_KEY = 'whatthefile-hide-notice';

	/** @var string $template_name */
	private $template_name = '';

	/** @var array $template_parts */
	private $template_parts = array();

	/**
	 * Method run on plugin activation
	 */
	public static function plugin_activation() {
		// include nag class
		require_once( plugin_dir_path( __FILE__ ) . '/classes/class-nag.php' );

		// insert install date
		WTF_Nag::insert_install_date();
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'frontend_hooks' ) );
		add_action( 'admin_init', array( $this, 'admin_hooks' ) );
	}

	/**
	 * Setup the admin hooks
	 *
	 * @return void
	 */
	public function admin_hooks() {

		// Check if user is an administrator
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// include nag class
		require_once( plugin_dir_path( __FILE__ ) . '/classes/class-nag.php' );

		// setup nag
		$nag = new WTF_Nag();
		$nag->setup();

		// include plugin links class
		require_once( plugin_dir_path( __FILE__ ) . '/classes/class-plugin-links.php' );

		// setup plugin links
		$plugin_links = new WTF_Plugin_Links();
		$plugin_links->setup();
	}

	/**
	 * Setup the frontend hooks
	 *
	 * @return void
	 */
	public function frontend_hooks() {
		// Don't run in admin or if the admin bar isn't showing
		if ( is_admin() || ! is_admin_bar_showing() ) {
			return;
		}

		// WTF actions and filers
		add_action( 'wp_head', array( $this, 'print_css' ) );
		add_filter( 'template_include', array( $this, 'save_current_page' ), 1000 );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1000 );

		// BuddyPress support
		if ( class_exists( 'BuddyPress' ) ) {
			add_action( 'bp_core_pre_load_template', array( $this, 'save_buddy_press_template' ) );
		}

		// Template part hooks
		add_action( 'all', array( $this, 'save_template_parts' ), 1, 3 );
	}


	/**
	 * Get the current page
	 *
	 * @return string
	 */
	private function get_current_page() {
		return $this->template_name;
	}

	/**
	 * Check if file exists in child theme
	 *
	 * @param $file
	 *
	 * @return bool
	 */
	private function file_exists_in_child_theme( $file ) {
		return file_exists( STYLESHEETPATH . '/' . $file );
	}

	/**
	 * Returns if direct file editing through WordPress is allowed
	 *
	 * @return bool
	 */
	private function is_file_editing_allowed() {
		$allowed = true;
		if ( ( defined( 'DISALLOW_FILE_EDIT' ) && true == DISALLOW_FILE_EDIT ) || ( defined( 'DISALLOW_FILE_MODS' ) && true == DISALLOW_FILE_MODS ) ) {
			$allowed = false;
		}

		return $allowed;
	}

	/**
	 * Save the template parts in our array
	 *
	 * @param $tag
	 * @param null $slug
	 * @param null $name
	 */
	public function save_template_parts( $tag, $slug = null, $name = null ) {
		if ( 0 !== strpos( $tag, 'get_template_part_' ) ) {
			return;
		}

		// Check if slug is set
		if ( $slug != null ) {

			// Templates array
			$templates = array();

			// Add possible template part to array
			if ( $name != null ) {
				$templates[] = "{$slug}-{$name}.php";
			}

			// Add possible template part to array
			$templates[] = "{$slug}.php";

			// Get the correct template part
			$template_part = str_replace( get_template_directory() . '/', '', locate_template( $templates ) );
			$template_part = str_replace( get_stylesheet_directory() . '/', '', $template_part );

			// Add template part if found
			if ( $template_part != '' ) {
				$this->template_parts[] = $template_part;
			}
		}

	}

	/**
	 * Save the BuddyPress template
	 *
	 * @param $template
	 */
	public function save_buddy_press_template( $template ) {

		if ( '' == $this->template_name ) {
			$template_name       = $template;
			$template_name       = str_ireplace( get_template_directory() . '/', '', $template_name );
			$template_name       = str_ireplace( get_stylesheet_directory() . '/', '', $template_name );
			$this->template_name = $template_name;
		}

	}

	/**
	 * Save the current page in our local var
	 *
	 * @param $template_name
	 *
	 * @return mixed
	 */
	public function save_current_page( $template_name ) {
		$this->template_name = basename( $template_name );

		// Do Roots Theme check
		if ( function_exists( 'roots_template_path' ) ) {
			$this->template_name = basename( roots_template_path() );
		} else if( function_exists( 'Roots\Sage\Wrapper\template_path' ) ) {
			$this->template_name = basename( Roots\Sage\Wrapper\template_path() );
		}

		return $template_name;
	}

	/**
	 * Add the admin bar menu
	 */
	public function admin_bar_menu() {
		global $wp_admin_bar;

		// Check if direct file editing is allowed
		$edit_allowed = $this->is_file_editing_allowed();

		// Add top menu
		$wp_admin_bar->add_menu( array(
			'id'     => 'wtf-bar',
			'parent' => 'top-secondary',
			'title'  => __( 'What The File', 'what-the-file' ),
			'href'   => false
		) );

		// Check if template file exists in child theme
		$theme = get_stylesheet();
		if ( ! $this->file_exists_in_child_theme( $this->get_current_page() ) ) {
			$theme = get_template();
		}

		// Add current page
		$wp_admin_bar->add_menu( array(
			'id'     => 'wtf-bar-template-file',
			'parent' => 'wtf-bar',
			'title'  => $this->get_current_page(),
			'href'   => ( ( $edit_allowed ) ? get_admin_url() . 'theme-editor.php?file=' . $this->get_current_page() . '&theme=' . $theme : false )
		) );

		// Check if theme uses template parts
		if ( count( $this->template_parts ) > 0 ) {

			// Add template parts menu item
			$wp_admin_bar->add_menu( array(
				'id'     => 'wtf-bar-template-parts',
				'parent' => 'wtf-bar',
				'title'  => 'Template Parts',
				'href'   => false
			) );

			// Loop through template parts
			foreach ( $this->template_parts as $template_part ) {

				// Check if template part exists in child theme
				$theme = get_stylesheet();
				if ( ! $this->file_exists_in_child_theme( $template_part ) ) {
					$theme = get_template();
				}

				// Add template part to sub menu item
				$wp_admin_bar->add_menu( array(
					'id'     => 'wtf-bar-template-part-' . $template_part,
					'parent' => 'wtf-bar-template-parts',
					'title'  => $template_part,
					'href'   => ( ( $edit_allowed ) ? get_admin_url() . 'theme-editor.php?file=' . $template_part . '&theme=' . $theme : false )
				) );
			}

		}

		// Add powered by
		$wp_admin_bar->add_menu( array(
			'id'     => 'wtf-bar-powered-by',
			'parent' => 'wtf-bar',
			'title'  => 'Powered by Never5',
			'href'   => 'http://www.never5.com?utm_source=plugin&utm_medium=wtf-bar&utm_campaign=what-the-file'
		) );

	}

	/**
	 * Print the custom CSS
	 */
	public function print_css() {
		echo "<style type=\"text/css\" media=\"screen\">#wp-admin-bar-wtf-bar > .ab-item{padding-right:26px !important;background: url('" . plugins_url( 'assets/images/never5-logo.png', __FILE__ ) . "')center right no-repeat !important;} #wp-admin-bar-wtf-bar.hover > .ab-item {background-color: #32373c !important; } #wp-admin-bar-wtf-bar #wp-admin-bar-wtf-bar-template-file .ab-item, #wp-admin-bar-wtf-bar #wp-admin-bar-wtf-bar-template-parts {text-align:right;} #wp-admin-bar-wtf-bar-template-parts.menupop > .ab-item:before{ right:auto !important; }#wp-admin-bar-wtf-bar-powered-by{text-align: right;}#wp-admin-bar-wtf-bar-powered-by a{color:#ffa100 !important;}</style>\n";
	}

}

/**
 * What The File main function
 */
function __what_the_file_main() {
	new WhatTheFile();
}

// Init plugin
add_action( 'plugins_loaded', '__what_the_file_main' );

// Register hook
register_activation_hook( __FILE__, array( 'WhatTheFile', 'plugin_activation' ) );
