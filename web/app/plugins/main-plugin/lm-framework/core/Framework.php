<?php
namespace LM\Core;

use LM\Site;

/**
 * Main Core Class
 */
class Framework {

	// Contains all active modules.
	protected $_modules;

	// Contains main application config.
	private $_config;

	/**
	 * Let's the party started!
	 */
	function run( array $config ) {
		$this->_config = $config;

		// Define framework constants.
		$this->define();

		// Autoload defined modules.
		$this->bootstrap();
	}

	public function __get( $name ) {
		if ( isset( $this->_modules[$name] ) && is_object( $this->_modules[$name] ) ) {
			return $this->_modules[$name];
		}

		if ( ! isset( $this->_config['modules'][$name] ) ) {
			throw new \Exception( 'Uknown module ' . $name );
		}

		$config = $this->_config['modules'][$name];

		$this->_modules[$name] = $this->_createModule( $name, $config );

		return $this->_modules[$name];
	}

	/**
	 * Define framework constants.
	 */
	private function define() {
		define( 'LM_BASE_DIR', str_replace( '/core', '', __DIR__ ) );
		define( 'LM_BASE_URL', plugins_url( 'main-plugin' ) );
		define( 'LM_MODULES_BASE_DIR', LM_BASE_DIR . '/lm-framework/modules/' );
		define( 'LM_MODULES_BASE_URL', LM_BASE_URL . '/lm-framework/modules/' );
	}

	/**
	 * Activate all modules from special part of config.
	 */
	public function bootstrap() {
		foreach ( $this->_config['bootstrap'] as $module_name ) {
			$this->$module_name;
		}
	}

	private function _createModule( $name, $config ) {
		if ( empty( $config['class'] ) ) {
			throw new \Exception( 'Missing class name for module ' . $name );
		}

		$className = $config['class'];

		unset( $config['class'] );

		return new $className( $config );
	}

}