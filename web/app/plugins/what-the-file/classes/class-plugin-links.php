<?php

class WTF_Plugin_Links {

	/**
	 * Setup class
	 */
	public function setup() {
		add_filter( 'plugin_action_links_what-the-file/what-the-file.php', array( $this, 'add_links' ) );
	}

	/**
	 * Add to links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function add_links( $links ) {
		array_unshift( $links, '<a href="http://www.never5.com/?utm_source=plugin&utm_medium=link&utm_campaign=what-the-file" target="_blank" style="color:#ffa100;font-weight:bold;">' . __( 'More Never5 Plugins', 'what-the-file' ) . '</a>' );
		return $links;
	}

}
