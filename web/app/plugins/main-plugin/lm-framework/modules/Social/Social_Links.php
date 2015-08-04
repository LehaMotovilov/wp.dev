<?php
namespace LM\Modules\Social;

use LM\Core\Module;

/**
 * Display settings page for Social Links
 *
 * Example Config:
 * 'social' => [
 *		'class' => 'LM\Modules\Social\Social_Links',
 *		'social_networks' => [ 'facebook', 'google_plus', 'vk' ]
 * ]
 *
 * Example Using:
 * $social_links = get_option('lm_social_links');
 *
 * @author LehaMotovilov <lehaqs@gmail.com>
 * @version 1.0
 */
class Social_Links extends Module {

	/**
	 * Holds the values to be used in the fields callbacks.
	 */
	public $options;

	/**
	 * Holds all social networks from config array.
	 */
	public $social_networks;

	/**
	 * Hold default social networks.
	 */
	public $social_default_array = [
		'facebook', 'youtube'
	];

	/**
	 * Init method.
	 * @param array $config
	 */
	public function __construct( $config ) {
		// Setup Class properties.
		parent::init( $config );

		// Init admin settings page.
		add_action( 'admin_menu', [ $this, 'add_social_page' ] );
		add_action( 'admin_init', [ $this, 'page_init' ] );
	}

	/**
	 * Add setings page.
	 */
	public function add_social_page() {
		// This page will be under "Settings"
		add_options_page(
			__( 'Social Links', 'lm-framework' ), // Page title
			__( 'Social Links', 'lm-framework' ), // Menu title
			'manage_options', // Capability
			'social-links', // Menu slug
			[ $this, 'render_admin_page' ] // Callback function
		);
	}

	/**
	 * Options page callback
	 */
	public function render_admin_page() {
		// Set class property
		$this->options = get_option( 'lm_social_links' );
		?>
		<div class="wrap">
			<h2><?php _e( 'Social Links', 'lm-framework' ) ?></h2>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'social_links_option_group' );
				do_settings_sections( 'social-links' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'social_links_option_group', // Option group
			'lm_social_links', // Option name
			[ $this, 'sanitize' ] // Sanitize
		);

		add_settings_section(
			'setting_section_social', // ID
			'', // Title
			'', // Callback
			'social-links' // Page
		);

		// Let's register field with callback func foreach social network
		foreach ( $this->social_networks as $social_link ) {
			// Setup args for display callback function.
			$args = [
				'id' => $social_link,
				'label' => ucfirst( $social_link )
			];

			add_settings_field(
				$social_link, // Id
				ucfirst( str_replace( '_', ' ', $social_link ) ), // Title
				[ $this, 'display_callback' ], // Callback function
				'social-links', // Page on admin area
				'setting_section_social', // Setting section
				$args // Args for display function
			);
		}
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		// Let's do some sanitizing of text fields.
		foreach ( $input as $key => $value ) {
			$new_input[$key] = esc_url_raw( sanitize_text_field( $value ) );
		}

		return $new_input;
	}

	/**
	 * Get the settings option array and print one of its values
	 *
	 * @param array $args Contains all fields as array of ids and labes
	 */
	public function display_callback( $args ) {
		// Get value from all fields
		$value = isset( $this->options[$args['id']] ) ? esc_attr( $this->options[$args['id']]) : '';

		// Formating example link
		if ( !empty( $value ) ) {
			$example_link = sprintf(
				'<p class="description"><a href="%s" title="%s" target="_blank">%s</a></p>',
				$value, // 1 - link
				$args['label'], // 2 - title
				__( 'Check link', 'lm-framework' ) // 3 - text
			);
		} else {
			$example_link = '';
		}

		// Print field html
		printf(
			'<input type="url" id="%s" class="regular-text code" name="lm_social_links[%s]" value="%s" />%s',
			$args['id'], // 1 - id of imput
			$args['id'], // 2 - name of imput
			$value, // 3 - value of imput
			$example_link // 4 - value of imput
		);
	}
}
