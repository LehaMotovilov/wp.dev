<?php

/**
 * Information about API
 */
class Info {

	/**
	 * Simple return api version.
	 */
	public function get_index() {
		return [
			'version' => LM_API_VERSION
		];
	}

}
