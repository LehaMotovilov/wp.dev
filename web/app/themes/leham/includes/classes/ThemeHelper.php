<?php
namespace LM\Theme;

/**
 * Helper for template functionality.
 */
class ThemeHelper {

	public static $_js_hash_regex = '/(^([a-z]+)\.\w+\.js)$/';
	public static $_css_hash_regex = '/(^([a-z]+)\.\w+\.css)$/';

	/**
	 * Simple wrap for scandir().
	 *
	 * @param string $directory
	 *
	 * @return array
	 */
	public static function read_dir( $directory ) {
		return array_diff( scandir( $directory ), ['.', '..', '.gitkeep'] );
	}

	/**
	 * Return array with dist files by regex.
	 *
	 * @param string $directory Path for folder with files.
	 *
	 * @param string $regex Regex for files.
	 *
	 * @return array
	 */
	public static function get_dist_files( $directory, $regex ) {
		$files = self::read_dir( $directory );
		$found = array();
		foreach ( $files as $file ) {
			preg_match( $regex, $file, $matches );

			if ( isset( $matches[0] ) && !empty( $matches[0] ) ) {
				$found[$matches[2]] = $matches[0];
			}
		}

		return $found;
	}

	/**
	 * Wrapper for easy access to get_dist_files.
	 *
	 * @param string $directory Path for folder with files.
	 *
	 * @return array Array of JS files.
	 */
	public static function get_dist_js( $directory ) {
		return self::get_dist_files( $directory, self::$_js_hash_regex );
	}

	/**
	 * Wrapper for easy access to get_dist_files.
	 *
	 * @param string $directory Path for folder with files.
	 *
	 * @return array Array of JS files.
	 */
	public static function get_dist_css( $directory ) {
		return self::get_dist_files( $directory, self::$_css_hash_regex );
	}

}
