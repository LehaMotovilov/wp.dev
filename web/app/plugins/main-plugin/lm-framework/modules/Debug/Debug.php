<?php
namespace LM\Modules\Debug;
use LM\Core\Module;

class Debug extends Module {

	// All tabs for Debug module.
	public $tabs;

	/**
	 * Init tabs and setup menu.
	 */
	public function __construct() {
		// Setup all tabs with debug info.
		$this->tabs = array(
			'wordpress' => __( 'WordPress', 'lm-framework' ),
			'php' => __( 'PHP', 'lm-framework' ),
			'images' => __( 'Images', 'lm-framework' ),
			'mysql' => __( 'MySQL', 'lm-framework' ),
			'emails' => __( 'Emails', 'lm-framework' )
		);

		// Add menu page
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

		// Add styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );

		// Add Ajax Handler
		add_action( 'wp_ajax_nopriv_lm_send_test_mail', array( $this, 'lm_send_test_mail_callback' ) );
		add_action( 'wp_ajax_lm_send_test_mail', array( $this, 'lm_send_test_mail_callback' ) );
	}

	/**
	 * Load some styles.
	 */
	public function enqueue_style( $hook ) {
		// Check page. Because we don't want to load css everywhere.
		if ( !empty( $hook ) && $hook == 'tools_page_debug_info' ) {
			// Register our styles
			wp_register_style(
				'lm-framework-module-debug', // Style id
				LM_MODULES_BASE_URL . '/Debug/assets/css/debug-info.css' // Style url
			);

			// Load styles
			wp_enqueue_style( 'lm-framework-module-debug' );
		}
	}

	/**
	 * Load some scripts.
	 */
	public function enqueue_script( $hook ) {
		// Check page. Because we don't want to load JS everywhere.
		if ( !empty( $hook ) && $hook == 'tools_page_debug_info' ) {
			// Register our script
			wp_register_script(
				'lm-framework-module-debug', // Script id
				LM_MODULES_BASE_URL . '/Debug/assets/js/debug-info.js', // Script url
				array( 'jquery' ), // Dependency
				'1.0' // Version
			);

			// Localize the script with ajax_object
			wp_localize_script(
				'lm-framework-module-debug',
				'ajax_object',
				array(
					'ajax_nonce' => wp_create_nonce( 'send_email_secure' ),
					'ajax_url' => admin_url( 'admin-ajax.php' )
				)
			);

			// Load script
    		wp_enqueue_script( 'lm-framework-module-debug' );
		}
	}

	/**
	 * Register Submenu for Tools.
	 */
	public function add_menu() {
		add_submenu_page(
			'tools.php', // Parent slug
			__( 'Debug Info', 'lm-framework' ), // Page title
			__( 'Debug Info', 'lm-framework' ), // Menu title
			'manage_options', // Capability
			'debug_info', // Menu slug
			array( $this, 'display_debug_page' ) // Callback function
		);
	}

	/**
	 * Display main page.
	 */
	public function display_debug_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Debug Info', 'lm-framework' ) ?></h2>

			<?php // Display page header with tabs links. ?>
			<?php $this->display_page_header(); ?>

			<?php // Display page content. ?>
			<?php $this->display_page_content(); ?>

		</div>
		<?php
	}

	/**
	 * Ajax Handler for send test mail.
	 */
	public function lm_send_test_mail_callback() {
		// Check security field.
		if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'send_email_secure' ) ) {
			wp_send_json_error( __( 'Internal Error', 'lm-framework' ) );
		}

		// Check email
		if ( !isset( $_POST['email'] ) || !is_email( $_POST['email'] ) ) {
			wp_send_json_error( __( 'Invalid Email', 'lm-framework' ) );
		}

		// All good, lets send test email!
		$email_to 		= $_POST['email'];
		$subject_wp 	= __( 'Test wp_mail', 'lm-framework' );
		$subject_php 	= __( 'Test php_mail', 'lm-framework' );
		$message 		= sprintf( __( 'Test message date: %s', 'lm-framework' ), date( 'Y-m-d' ) );
		$results 		= array();

		// Send test email via WP Api
		if ( wp_mail( $email_to, $subject_wp, $message ) ) {
			$results['wp_mail'] = __( 'wp_mail - Work\'s good.', 'lm-framework' );
		} else {
			$results['wp_mail'] = __( 'wp_mail - Don\'t Work.', 'lm-framework' );
		}

		// Send test email via simple PHP function
		if ( mail( $email_to, $subject_php, $message ) ) {
			$results['php_mail'] = __( 'php_mail - Work\'s good.', 'lm-framework' );
		} else {
			$results['php_mail'] = __( 'php_mail - Don\'t Work.', 'lm-framework' );
		}

		// Just text message for user.
		$results['message'] = sprintf( __( 'Check "%s" mail.', 'lm-framework' ), $email_to );

		wp_send_json_success( $results );
	}

	/**
	 * Returns current page based on simple $_GET param.
	 *
	 * @return string
	 */
	private function get_current_page() {
		if ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], array_keys( $this->tabs ) ) ) {
			$current_tab = $_GET['tab'];
		} else {
			$current_tab = 'wordpress';
		}

		return $current_tab;
	}

	/**
	 * Display page header with tabs links.
	 */
	private function display_page_header() {
		$current_tab = $this->get_current_page();
		?>
		<div class="wp-filter">
			<ul class="filter-links">
				<?php foreach ( $this->tabs as $tab => $name ): ?>
					<?php
						if ( $tab == $current_tab ) {
							$class = 'current';
						} else {
							$class = '';
						}
						$tab_link = esc_url( admin_url( "tools.php?page=debug_info&tab=$tab" ) );
					?>
					<li><a href="<?php echo $tab_link; ?>" class="<?php echo esc_attr( $class ); ?>"><?php echo $name; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Display content for each tab.
	 */
	private function display_page_content() {
		$page = $this->get_current_page();

		$page_path = LM_BASE_DIR . '/modules/Debug/pages/' . $page . '.php';

		if ( file_exists( $page_path ) ) {
			include( $page_path );
		} else {
			$not_found = LM_BASE_DIR . '/modules/Debug/pages/not-found.php';
			include( $not_found );
		}
	}

}
