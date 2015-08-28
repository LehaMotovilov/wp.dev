<?php

class WTF_Nag {

	/**
	 * Setup the class
	 */
	public function setup() {
		
		// catch nag hide
		$this->catch_hide_notice();
		
		// bind nag
		$this->bind();
		
	}

	/**
	 * Catch the hide nag request
	 */
	private function catch_hide_notice() {
		if ( isset( $_GET[ WhatTheFile::OPTION_ADMIN_NOTICE_KEY ] ) && current_user_can( 'install_plugins' ) ) {
			// Add user meta
			global $current_user;
			add_user_meta( $current_user->ID, WhatTheFile::OPTION_ADMIN_NOTICE_KEY, '1', true );

			// Build redirect URL
			$query_params = $this->get_admin_querystring_array();
			unset( $query_params[ WhatTheFile::OPTION_ADMIN_NOTICE_KEY ] );
			$query_string = http_build_query( $query_params );
			if ( $query_string != '' ) {
				$query_string = '?' . $query_string;
			}

			$redirect_url = 'http';
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
				$redirect_url .= 's';
			}
			$redirect_url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $query_string;

			// Redirect
			wp_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Bind nag message
	 */
	private function bind() {
		// Is admin notice hidden?
		$current_user = wp_get_current_user();
		$hide_notice  = get_user_meta( $current_user->ID, WhatTheFile::OPTION_ADMIN_NOTICE_KEY, true );

		// Check if we need to display the notice
		if ( current_user_can( 'install_plugins' ) && '' == $hide_notice ) {
			// Get installation date
			$datetime_install = $this->get_install_date();
			$datetime_past    = new DateTime( '-10 days' );

			if ( $datetime_past >= $datetime_install ) {
				// 10 or more days ago, show admin notice
				add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
			}
		}
	}

	/**
	 * Get the install data
	 *
	 * @return DateTime
	 */
	private function get_install_date() {
		$date_string = get_site_option( WhatTheFile::OPTION_INSTALL_DATE, '' );
		if ( $date_string == '' ) {
			// There is no install date, plugin was installed before version 1.2.0. Add it now.
			$date_string = self::insert_install_date();
		}

		return new DateTime( $date_string );
	}

	/**
	 * Parse the admin query string
	 *
	 * @return array
	 */
	private function get_admin_querystring_array() {
		parse_str( $_SERVER['QUERY_STRING'], $params );

		return $params;
	}

	/**
	 * Insert the install date
	 *
	 * @return string
	 */
	public static function insert_install_date() {
		$datetime_now = new DateTime();
		$date_string  = $datetime_now->format( 'Y-m-d' );
		add_site_option( WhatTheFile::OPTION_INSTALL_DATE, $date_string, '', 'no' );

		return $date_string;
	}

	/**
	 * Display the admin notice
	 */
	public function display_admin_notice() {

		$query_params = $this->get_admin_querystring_array();
		$query_string = '?' . http_build_query( array_merge( $query_params, array( WhatTheFile::OPTION_ADMIN_NOTICE_KEY => '1' ) ) );

		echo '<div class="updated"><p>';
		printf( __( "You've been using <b>What The File</b> for some time now, could you please give it a review at wordpress.org? <br /><br /> <a href='%s' target='_blank'>Yes, take me there!</a> - <a href='%s'>I've already done this!</a><br/><br/><small><a href='http://www.never5.com/' target='_blank'>Check out other Never5 plugins</a></small>" ), 'http://wordpress.org/support/view/plugin-reviews/what-the-file', $query_string );
		echo "</p></div>";

	}
}