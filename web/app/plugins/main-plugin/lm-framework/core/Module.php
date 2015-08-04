<?php
namespace LM\Core;

/**
 * Main Module Class
 */
class Module {

	public function init( $config ) {
		$this->set_properties( $config );
	}

	/**
	 * Set properties from config.
	 * Check if magic setter method exist -> use setParam( $value )
	 * @param array $config
	 */
	private function set_properties( $config ) {
		// If empty config?
		if ( empty( $config ) ) {
			return;
		}

		// Setup default properties.
		foreach ( $config as $param => $value ) {
			if ( property_exists( $this, $param ) ) {
				if ( method_exists( $this, 'set' . ucfirst( $param ) ) ) {
					call_user_func( array( $this, 'set' . ucfirst( $param ) ), $value );
				} else {
					$this->$param = $value;
				}
			}
		}
	}

}