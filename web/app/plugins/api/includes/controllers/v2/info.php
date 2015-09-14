<?php

/**
 * Information about API
 */
class Info {

	/**
	 * Simple return api version.
	 * @return array
	 */
	public function get_index( $_request ) {
		return [
			'version' => LM_API_VERSION
		];
	}

}
