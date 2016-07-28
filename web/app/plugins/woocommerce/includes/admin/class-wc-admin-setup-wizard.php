<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their store.
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin
 * @version     2.6.0
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Setup_Wizard class.
 */
class WC_Admin_Setup_Wizard {

	/** @var string Currenct Step */
	private $step   = '';

	/** @var array Steps for the setup wizard */
	private $steps  = array();

	/** @var array Tweets user can optionally send after install */
	private $tweets = array(
		'Someone give me woo-t, I just set up a new store with #WordPress and @WooCommerce!',
		'Someone give me high five, I just set up a new store with #WordPress and @WooCommerce!'
	);

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		if ( apply_filters( 'woocommerce_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wc-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || 'wc-setup' !== $_GET['page'] ) {
			return;
		}
		$this->steps = array(
			'introduction' => array(
				'name'    =>  __( 'Introduction', 'woocommerce' ),
				'view'    => array( $this, 'wc_setup_introduction' ),
				'handler' => ''
			),
			'pages' => array(
				'name'    =>  __( 'Page Setup', 'woocommerce' ),
				'view'    => array( $this, 'wc_setup_pages' ),
				'handler' => array( $this, 'wc_setup_pages_save' )
			),
			'locale' => array(
				'name'    =>  __( 'Store Locale', 'woocommerce' ),
				'view'    => array( $this, 'wc_setup_locale' ),
				'handler' => array( $this, 'wc_setup_locale_save' )
			),
			'shipping_taxes' => array(
				'name'    =>  __( 'Shipping &amp; Tax', 'woocommerce' ),
				'view'    => array( $this, 'wc_setup_shipping_taxes' ),
				'handler' => array( $this, 'wc_setup_shipping_taxes_save' ),
			),
			'payments' => array(
				'name'    =>  __( 'Payments', 'woocommerce' ),
				'view'    => array( $this, 'wc_setup_payments' ),
				'handler' => array( $this, 'wc_setup_payments_save' ),
			),
			'next_steps' => array(
				'name'    =>  __( 'Ready!', 'woocommerce' ),
				'view'    => array( $this, 'wc_setup_ready' ),
				'handler' => ''
			)
		);
		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), '3.5.2' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION );
		wp_localize_script( 'wc-enhanced-select', 'wc_enhanced_select_params', array(
			'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
			'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
			'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
			'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
			'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
			'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'search_products_nonce'     => wp_create_nonce( 'search-products' ),
			'search_customers_nonce'    => wp_create_nonce( 'search-customers' )
		) );
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array( 'dashicons', 'install' ), WC_VERSION );

		wp_register_script( 'wc-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup.min.js', array( 'jquery', 'wc-enhanced-select', 'jquery-blockui' ), WC_VERSION );
		wp_localize_script( 'wc-setup', 'wc_setup_params', array(
			'locale_info' => json_encode( include( WC()->plugin_path() . '/i18n/locale-info.php' ) )
		) );

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'] );
		}

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	public function get_next_step_link() {
		$keys = array_keys( $this->steps );
		return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ] );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php _e( 'WooCommerce &rsaquo; Setup Wizard', 'woocommerce' ); ?></title>
			<?php wp_print_scripts( 'wc-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wc-setup wp-core-ui">
			<h1 id="wc-logo"><a href="https://woothemes.com/woocommerce"><img src="<?php echo WC()->plugin_url(); ?>/assets/images/woocommerce_logo.png" alt="WooCommerce" /></a></h1>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		?>
			<?php if ( 'next_steps' === $this->step ) : ?>
				<a class="wc-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'woocommerce' ); ?></a>
			<?php endif; ?>
			</body>
		</html>
		<?php
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$ouput_steps = $this->steps;
		array_shift( $ouput_steps );
		?>
		<ol class="wc-setup-steps">
			<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
				<li class="<?php
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
					}
				?>"><?php echo esc_html( $step['name'] ); ?></li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		echo '<div class="wc-setup-content">';
		call_user_func( $this->steps[ $this->step ]['view'] );
		echo '</div>';
	}

	/**
	 * Introduction step.
	 */
	public function wc_setup_introduction() {
		?>
		<h1><?php _e( 'Welcome to the world of WooCommerce!', 'woocommerce' ); ?></h1>
		<p><?php _e( 'Thank you for choosing WooCommerce to power your online store! This quick setup wizard will help you configure the basic settings. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong>', 'woocommerce' ); ?></p>
		<p><?php _e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', 'woocommerce' ); ?></p>
		<p class="wc-setup-actions step">
			<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'woocommerce' ); ?></a>
			<a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php _e( 'Not right now', 'woocommerce' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Page setup.
	 */
	public function wc_setup_pages() {
		?>
		<h1><?php _e( 'Page Setup', 'woocommerce' ); ?></h1>
		<form method="post">
			<p><?php printf( __( 'Your store needs a few essential %spages%s. The following will be created automatically (if they do not already exist):', 'woocommerce' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">', '</a>' ); ?></p>
			<table class="wc-setup-pages" cellspacing="0">
				<thead>
					<tr>
						<th class="page-name"><?php _e( 'Page Name', 'woocommerce' ); ?></th>
						<th class="page-description"><?php _e( 'Description', 'woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="page-name"><?php echo _x( 'Shop', 'Page title', 'woocommerce' ); ?></td>
						<td><?php _e( 'The shop page will display your products.', 'woocommerce' ); ?></td>
					</tr>
					<tr>
						<td class="page-name"><?php echo _x( 'Cart', 'Page title', 'woocommerce' ); ?></td>
						<td><?php _e( 'The cart page will be where the customers go to view their cart and begin checkout.', 'woocommerce' ); ?></td>
					</tr>
					<tr>
						<td class="page-name"><?php echo _x( 'Checkout', 'Page title', 'woocommerce' ); ?></td>
						<td>
							<?php _e( 'The checkout page will be where the customers go to pay for their items.', 'woocommerce' ); ?>
						</td>
					</tr>
					<tr>
						<td class="page-name"><?php echo _x( 'My Account', 'Page title', 'woocommerce' ); ?></td>
						<td>
							<?php _e( 'Registered customers will be able to manage their account details and view past orders on this page.', 'woocommerce' ); ?>
						</td>
					</tr>
				</tbody>
			</table>

			<p><?php printf( __( 'Once created, these pages can be managed from your admin dashboard on the %sPages screen%s. You can control which pages are shown on your website via %sAppearance > Menus%s.', 'woocommerce' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">', '</a>', '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">', '</a>' ); ?></p>

			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woocommerce' ); ?>" name="save_step" />
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'woocommerce' ); ?></a>
				<?php wp_nonce_field( 'wc-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Save Page Settings.
	 */
	public function wc_setup_pages_save() {
		check_admin_referer( 'wc-setup' );

		WC_Install::create_pages();
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Locale settings.
	 */
	public function wc_setup_locale() {
		$user_location  = WC_Geolocation::geolocate_ip();
		$country        = ! empty( $user_location['country'] ) ? $user_location['country'] : 'US';
		$state          = ! empty( $user_location['state'] ) ? $user_location['state'] : '*';
		$state          = 'US' === $country && '*' === $state ? 'AL' : $state;

		// Defaults
		$currency       = get_option( 'woocommerce_currency', 'GBP' );
		$currency_pos   = get_option( 'woocommerce_currency_pos', 'left' );
		$decimal_sep    = get_option( 'woocommerce_price_decimal_sep', '.' );
		$num_decimals   = get_option( 'woocommerce_price_num_decimals', '2' );
		$thousand_sep   = get_option( 'woocommerce_price_thousand_sep', ',' );
		$dimension_unit = get_option( 'woocommerce_dimension_unit', 'cm' );
		$weight_unit    = get_option( 'woocommerce_weight_unit', 'kg' );
		?>
		<h1><?php _e( 'Store Locale Setup', 'woocommerce' ); ?></h1>
		<form method="post">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="store_location"><?php _e( 'Where is your store based?', 'woocommerce' ); ?></label></th>
					<td>
					<select id="store_location" name="store_location" style="width:100%;" required data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'woocommerce' ); ?>" class="wc-enhanced-select">
							<?php WC()->countries->country_dropdown_options( $country, $state ); ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="currency_code"><?php _e( 'Which currency will your store use?', 'woocommerce' ); ?></label></th>
					<td>
						<select id="currency_code" name="currency_code" style="width:100%;" data-placeholder="<?php esc_attr_e( 'Choose a currency&hellip;', 'woocommerce' ); ?>" class="wc-enhanced-select">
							<option value=""><?php _e( 'Choose a currency&hellip;', 'woocommerce' ); ?></option>
							<?php
							foreach ( get_woocommerce_currencies() as $code => $name ) {
								echo '<option value="' . esc_attr( $code ) . '" ' . selected( $currency, $code, false ) . '>' . esc_html( $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')' ) . '</option>';
							}
							?>
						</select>
						<span class="description"><?php printf( __( 'If your currency is not listed you can %sadd it later%s.', 'woocommerce' ), '<a href="https://docs.woocommerce.com/document/add-a-custom-currency-symbol/" target="_blank">', '</a>' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="currency_pos"><?php _e( 'Currency Position', 'woocommerce' ); ?></label></th>
					<td>
						<select id="currency_pos" name="currency_pos" class="wc-enhanced-select">
							<option value="left" <?php selected( $currency_pos, 'left' ); ?>><?php echo __( 'Left', 'woocommerce' ); ?></option>
							<option value="right" <?php selected( $currency_pos, 'right' ); ?>><?php echo __( 'Right', 'woocommerce' ); ?></option>
							<option value="left_space" <?php selected( $currency_pos, 'left_space' ); ?>><?php echo __( 'Left with space', 'woocommerce' ); ?></option>
							<option value="right_space" <?php selected( $currency_pos, 'right_space' ); ?>><?php echo __( 'Right with space', 'woocommerce' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="thousand_sep"><?php _e( 'Thousand Separator', 'woocommerce' ); ?></label></th>
					<td>
						<input type="text" id="thousand_sep" name="thousand_sep" size="2" value="<?php echo esc_attr( $thousand_sep ) ; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="decimal_sep"><?php _e( 'Decimal Separator', 'woocommerce' ); ?></label></th>
					<td>
						<input type="text" id="decimal_sep" name="decimal_sep" size="2" value="<?php echo esc_attr( $decimal_sep ) ; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="num_decimals"><?php _e( 'Number of Decimals', 'woocommerce' ); ?></label></th>
					<td>
						<input type="text" id="num_decimals" name="num_decimals" size="2" value="<?php echo esc_attr( $num_decimals ) ; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="weight_unit"><?php _e( 'Which unit should be used for product weights?', 'woocommerce' ); ?></label></th>
					<td>
						<select id="weight_unit" name="weight_unit" class="wc-enhanced-select">
							<option value="kg" <?php selected( $weight_unit, 'kg' ); ?>><?php echo __( 'kg', 'woocommerce' ); ?></option>
							<option value="g" <?php selected( $weight_unit, 'g' ); ?>><?php echo __( 'g', 'woocommerce' ); ?></option>
							<option value="lbs" <?php selected( $weight_unit, 'lbs' ); ?>><?php echo __( 'lbs', 'woocommerce' ); ?></option>
							<option value="oz" <?php selected( $weight_unit, 'oz' ); ?>><?php echo __( 'oz', 'woocommerce' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dimension_unit"><?php _e( 'Which unit should be used for product dimensions?', 'woocommerce' ); ?></label></th>
					<td>
						<select id="dimension_unit" name="dimension_unit" class="wc-enhanced-select">
							<option value="m" <?php selected( $dimension_unit, 'm' ); ?>><?php echo __( 'm', 'woocommerce' ); ?></option>
							<option value="cm" <?php selected( $dimension_unit, 'cm' ); ?>><?php echo __( 'cm', 'woocommerce' ); ?></option>
							<option value="mm" <?php selected( $dimension_unit, 'mm' ); ?>><?php echo __( 'mm', 'woocommerce' ); ?></option>
							<option value="in" <?php selected( $dimension_unit, 'in' ); ?>><?php echo __( 'in', 'woocommerce' ); ?></option>
							<option value="yd" <?php selected( $dimension_unit, 'yd' ); ?>><?php echo __( 'yd', 'woocommerce' ); ?></option>
						</select>
					</td>
				</tr>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woocommerce' ); ?>" name="save_step" />
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'woocommerce' ); ?></a>
				<?php wp_nonce_field( 'wc-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Save Locale Settings.
	 */
	public function wc_setup_locale_save() {
		check_admin_referer( 'wc-setup' );

		$store_location = sanitize_text_field( $_POST['store_location'] );
		$currency_code  = sanitize_text_field( $_POST['currency_code'] );
		$currency_pos   = sanitize_text_field( $_POST['currency_pos'] );
		$decimal_sep    = sanitize_text_field( $_POST['decimal_sep'] );
		$num_decimals   = sanitize_text_field( $_POST['num_decimals'] );
		$thousand_sep   = sanitize_text_field( $_POST['thousand_sep'] );
		$weight_unit    = sanitize_text_field( $_POST['weight_unit'] );
		$dimension_unit = sanitize_text_field( $_POST['dimension_unit'] );

		update_option( 'woocommerce_default_country', $store_location );
		update_option( 'woocommerce_currency', $currency_code );
		update_option( 'woocommerce_currency_pos', $currency_pos );
		update_option( 'woocommerce_price_decimal_sep', $decimal_sep );
		update_option( 'woocommerce_price_num_decimals', $num_decimals );
		update_option( 'woocommerce_price_thousand_sep', $thousand_sep );
		update_option( 'woocommerce_weight_unit', $weight_unit );
		update_option( 'woocommerce_dimension_unit', $dimension_unit );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Shipping and taxes.
	 */
	public function wc_setup_shipping_taxes() {
		?>
		<h1><?php _e( 'Shipping &amp; Tax Setup', 'woocommerce' ); ?></h1>
		<form method="post">
			<p><?php _e( 'If you will be charging sales tax, or shipping physical goods to customers, you can enable these below. This is optional and can be changed later.', 'woocommerce' ); ?></p>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="woocommerce_calc_shipping"><?php _e( 'Will you be shipping products?', 'woocommerce' ); ?></label></th>
					<td>
						<input type="checkbox" id="woocommerce_calc_shipping" <?php checked( get_option( 'woocommerce_ship_to_countries', '' ) !== 'disabled', true ); ?> name="woocommerce_calc_shipping" class="input-checkbox" value="1" />
						<label for="woocommerce_calc_shipping"><?php _e( 'Yes, I will be shipping physical goods to customers', 'woocommerce' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="woocommerce_calc_taxes"><?php _e( 'Will you be charging sales tax?', 'woocommerce' ); ?></label></th>
					<td>
						<input type="checkbox" <?php checked( get_option( 'woocommerce_calc_taxes', 'no' ), 'yes' ); ?> id="woocommerce_calc_taxes" name="woocommerce_calc_taxes" class="input-checkbox" value="1" />
						<label for="woocommerce_calc_taxes"><?php _e( 'Yes, I will be charging sales tax', 'woocommerce' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="woocommerce_prices_include_tax"><?php _e( 'How will you enter product prices?', 'woocommerce' ); ?></label></th>
					<td>
						<label><input type="radio" <?php checked( get_option( 'woocommerce_prices_include_tax', 'no' ), 'yes' ); ?> id="woocommerce_prices_include_tax" name="woocommerce_prices_include_tax" class="input-radio" value="yes" /> <?php _e( 'I will enter prices inclusive of tax', 'woocommerce' ); ?></label><br/>
						<label><input type="radio" <?php checked( get_option( 'woocommerce_prices_include_tax', 'no' ), 'no' ); ?> id="woocommerce_prices_include_tax" name="woocommerce_prices_include_tax" class="input-radio" value="no" /> <?php _e( 'I will enter prices exclusive of tax', 'woocommerce' ); ?></label>
					</td>
				</tr>
				<?php
					$locale_info = include( WC()->plugin_path() . '/i18n/locale-info.php' );
					$tax_rates   = array();
					$country     = WC()->countries->get_base_country();
					$state       = WC()->countries->get_base_state();

					if ( isset( $locale_info[ $country ] ) ) {
						if ( isset( $locale_info[ $country ]['tax_rates'][ $state ] ) ) {
							$tax_rates = $locale_info[ $country ]['tax_rates'][ $state ];
						} elseif ( isset( $locale_info[ $country ]['tax_rates'][''] ) ) {
							$tax_rates = $locale_info[ $country ]['tax_rates'][''];
						}
						if ( isset( $locale_info[ $country ]['tax_rates']['*'] ) ) {
							$tax_rates = array_merge( $locale_info[ $country ]['tax_rates']['*'], $tax_rates );
						}
					}
					if ( $tax_rates ) {
						?>
						<tr class="tax-rates">
							<td colspan="2">
								<p><?php printf( __( 'The following tax rates will be imported automatically for you. You can read more about taxes in %1$sour documentation%2$s.', 'woocommerce' ), '<a href="https://docs.woocommerce.com/document/setting-up-taxes-in-woocommerce/" target="_blank">', '</a>' ); ?></p>
								<div class="importing-tax-rates">
									<table class="tax-rates">
										<thead>
											<tr>
												<th><?php _e( 'Country', 'woocommerce' ); ?></th>
												<th><?php _e( 'State', 'woocommerce' ); ?></th>
												<th><?php _e( 'Rate (%)', 'woocommerce' ); ?></th>
												<th><?php _e( 'Name', 'woocommerce' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach ( $tax_rates as $rate ) {
													?>
													<tr>
														<td class="readonly"><?php echo esc_attr( $rate['country'] ); ?></td>
														<td class="readonly"><?php echo esc_attr( $rate['state'] ? $rate['state'] : '*' ); ?></td>
														<td class="readonly"><?php echo esc_attr( $rate['rate'] ); ?></td>
														<td class="readonly"><?php echo esc_attr( $rate['name'] ); ?></td>
													</tr>
													<?php
												}
											?>
										</tbody>
									</table>
								</div>
								<p class="description"><?php printf( __( 'You may need to add/edit rates based on your products or business location which can be done from the %1$stax settings%2$s screen. If in doubt, speak to an accountant.', 'woocommerce' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=tax' ) . '" target="_blank">', '</a>' ); ?></p>
							</td>
						</tr>
						<?php
					}
				?>
			</table>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woocommerce' ); ?>" name="save_step" />
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'woocommerce' ); ?></a>
				<?php wp_nonce_field( 'wc-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Save shipping and tax options.
	 */
	public function wc_setup_shipping_taxes_save() {
		check_admin_referer( 'wc-setup' );

		$enable_shipping = isset( $_POST['woocommerce_calc_shipping'] );
		$enable_taxes    = isset( $_POST['woocommerce_calc_taxes'] );

		if ( $enable_shipping ) {
			update_option( 'woocommerce_ship_to_countries', '' );
			WC_Admin_Notices::add_notice( 'no_shipping_methods' );
		} else {
			update_option( 'woocommerce_ship_to_countries', 'disabled' );
		}

		update_option( 'woocommerce_calc_taxes', $enable_taxes ? 'yes' : 'no' );
		update_option( 'woocommerce_prices_include_tax', sanitize_text_field( $_POST['woocommerce_prices_include_tax'] ) );

		if ( $enable_taxes ) {
			$locale_info = include( WC()->plugin_path() . '/i18n/locale-info.php' );
			$tax_rates   = array();
			$country     = WC()->countries->get_base_country();
			$state       = WC()->countries->get_base_state();

			if ( isset( $locale_info[ $country ] ) ) {
				if ( isset( $locale_info[ $country ]['tax_rates'][ $state ] ) ) {
					$tax_rates = $locale_info[ $country ]['tax_rates'][ $state ];
				} elseif ( isset( $locale_info[ $country ]['tax_rates'][''] ) ) {
					$tax_rates = $locale_info[ $country ]['tax_rates'][''];
				}
				if ( isset( $locale_info[ $country ]['tax_rates']['*'] ) ) {
					$tax_rates = array_merge( $locale_info[ $country ]['tax_rates']['*'], $tax_rates );
				}
			}
			if ( $tax_rates ) {
				$loop = 0;
				foreach ( $tax_rates as $rate ) {
					$tax_rate = array(
						'tax_rate_country'  => $rate['country'],
						'tax_rate_state'    => $rate['state'],
						'tax_rate'          => $rate['rate'],
						'tax_rate_name'     => $rate['name'],
						'tax_rate_priority' => isset( $rate['priority'] ) ? absint( $rate['priority'] ) : 1,
						'tax_rate_compound' => 0,
						'tax_rate_shipping' => $rate['shipping'] ? 1 : 0,
						'tax_rate_order'    => $loop ++,
						'tax_rate_class'    => ''
					);
					WC_Tax::_insert_tax_rate( $tax_rate );
				}
			}
		}

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Simple array of gateways to show in wizard.
	 * @return array
	 */
	protected function get_wizard_payment_gateways() {
		$gateways = array(
			'paypal-braintree' => array(
				'name'        => __( 'PayPal by Braintree', 'woocommerce' ),
				'image'       => WC()->plugin_url() . '/assets/images/paypal-braintree.png',
				'description' => sprintf( __( 'Safe and secure payments using credit cards or your customer\'s PayPal account. %sLearn more about PayPal%s.', 'woocommerce' ), '<a href="https://wordpress.org/plugins/woocommerce-gateway-paypal-powered-by-braintree/" target="_blank">', '</a>' ),
				'class'       => 'featured featured-row-last',
				'repo-slug'   => 'woocommerce-gateway-paypal-powered-by-braintree',
			),
			'paypal-ec' => array(
				'name'        => __( 'PayPal Express Checkout', 'woocommerce' ),
				'image'       => WC()->plugin_url() . '/assets/images/paypal.png',
				'description' => sprintf( __( 'Safe and secure payments using credit cards or your customer\'s PayPal account. %sLearn more about PayPal%s.', 'woocommerce' ), '<a href="https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/" target="_blank">', '</a>' ),
				'class'       => 'featured featured-row-last',
				'repo-slug'   => 'woocommerce-gateway-paypal-express-checkout',
			),
			'stripe' => array(
				'name'        => __( 'Stripe', 'woocommerce' ),
				'image'       => WC()->plugin_url() . '/assets/images/stripe.png',
				'description' => sprintf( __( 'A modern and robust way to accept credit card payments on your store. %sLearn more about Stripe%s.', 'woocommerce' ), '<a href="https://wordpress.org/plugins/woocommerce-gateway-stripe/" target="_blank">', '</a>' ),
				'class'       => 'featured featured-row-first',
				'repo-slug'   => 'woocommerce-gateway-stripe',
			),
			'paypal' => array(
				'name'        => __( 'PayPal Standard', 'woocommerce' ),
				'description' => __( 'Accept payments via PayPal using account balance or credit card.', 'woocommerce' ),
				'image'       => '',
				'class'       => '',
				'settings'    => array(
					'email' => array(
						'label'       => __( 'PayPal email address', 'woocommerce' ),
						'type'        => 'email',
						'value'       => get_option( 'admin_email' ),
						'placeholder' => __( 'PayPal email address', 'woocommerce' ),
					),
				),
			),
			'cheque' => array(
				'name'        => _x( 'Check Payments', 'Check payment method', 'woocommerce' ),
				'description' => __( 'A simple offline gateway that lets you accept a check as method of payment.', 'woocommerce' ),
				'image'       => '',
				'class'       => '',
			),
			'bacs' => array(
				'name'        => __( 'Bank Transfer (BACS) Payments', 'woocommerce' ),
				'description' => __( 'A simple offline gateway that lets you accept BACS payment.', 'woocommerce' ),
				'image'       => '',
				'class'       => '',
			),
			'cod' => array(
				'name'        => __( 'Cash on Delivery', 'woocommerce' ),
				'description' => __( 'A simple offline gateway that lets you accept cash on delivery.', 'woocommerce' ),
				'image'       => '',
				'class'       => '',
			)
		);

		$country = WC()->countries->get_base_country();

		if ( 'US' === $country ) {
			unset( $gateways['paypal-ec'] );
		} else {
			unset( $gateways['paypal-braintree'] );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			unset( $gateways['paypal-braintree'] );
			unset( $gateways['paypal-ec'] );
			unset( $gateways['stripe'] );
		}

		return $gateways;
	}

	/**
	 * Payments Step.
	 */
	public function wc_setup_payments() {
		$gateways = $this->get_wizard_payment_gateways();
		?>
		<h1><?php _e( 'Payments', 'woocommerce' ); ?></h1>
		<form method="post" class="wc-wizard-payment-gateway-form">
			<p><?php printf( __( 'WooCommerce can accept both online and offline payments. %2$sAdditional payment methods%3$s can be installed later and managed from the %1$scheckout settings%3$s screen.', 'woocommerce' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '" target="_blank">', '<a href="' . admin_url( 'admin.php?page=wc-addons&view=payment-gateways' ) . '" target="_blank">', '</a>' ); ?></p>

			<ul class="wc-wizard-payment-gateways">
				<?php foreach ( $gateways as $gateway_id => $gateway ) : ?>
					<li class="wc-wizard-gateway wc-wizard-gateway-<?php echo esc_attr( $gateway_id ); ?> <?php echo esc_attr( $gateway['class'] ); ?>">
						<div class="wc-wizard-gateway-enable">
							<input type="checkbox" name="wc-wizard-gateway-<?php echo esc_attr( $gateway_id ); ?>-enabled" class="input-checkbox" value="yes" />
							<label>
								<?php if ( $gateway['image'] ) : ?>
									<img src="<?php echo esc_attr( $gateway['image'] ); ?>" alt="<?php echo esc_attr( $gateway['name'] ); ?>" />
								<?php else : ?>
									<?php echo esc_html( $gateway['name'] ); ?>
								<?php endif; ?>
							</label>
						</div>
						<div class="wc-wizard-gateway-description">
							<?php echo wp_kses_post( wpautop( $gateway['description'] ) ); ?>
						</div>
						<?php if ( ! empty( $gateway['settings'] ) ) : ?>
							<table class="form-table wc-wizard-gateway-settings">
								<?php foreach ( $gateway['settings'] as $setting_id => $setting ) : ?>
									<tr>
										<th scope="row"><label for="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"><?php echo esc_html( $setting['label'] ); ?>:</label></th>
										<td>
											<input
												type="<?php echo esc_attr( $setting['type'] ); ?>"
												id="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"
												name="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"
												class="input-text"
												value="<?php echo esc_attr( $setting['value'] ); ?>"
												placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>"
												/>
										</td>
									</tr>
								<?php endforeach; ?>
							</table>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="wc-setup-actions step">
				<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woocommerce' ); ?>" name="save_step" />
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'woocommerce' ); ?></a>
				<?php wp_nonce_field( 'wc-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Payments Step save.
	 */
	public function wc_setup_payments_save() {
		check_admin_referer( 'wc-setup' );

		$gateways = $this->get_wizard_payment_gateways();

		foreach ( $gateways as $gateway_id => $gateway ) {
			// If repo-slug is defined, download and install plugin from .org.
			if ( ! empty( $gateway['repo-slug'] ) && ! empty( $_POST[ 'wc-wizard-gateway-' . $gateway_id . '-enabled' ] ) ) {
				wp_schedule_single_event( time() + 10, 'woocommerce_plugin_background_installer', array( $gateway_id, $gateway ) );
			}

			$settings_key        = 'woocommerce_' . $gateway_id . '_settings';
			$settings            = array_filter( (array) get_option( $settings_key, array() ) );
			$settings['enabled'] = ! empty( $_POST[ 'wc-wizard-gateway-' . $gateway_id . '-enabled' ] ) ? 'yes' : 'no';

			if ( ! empty( $gateway['settings'] ) ) {
				foreach ( $gateway['settings'] as $setting_id => $setting ) {
					$settings[ $setting_id ] = wc_clean( $_POST[ $gateway_id . '_' . $setting_id ] );
				}
			}

			update_option( $settings_key, $settings );
		}

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Actions on the final step.
	 */
	private function wc_setup_ready_actions() {
		WC_Admin_Notices::remove_notice( 'install' );

		if ( isset( $_GET['wc_tracker_optin'] ) && isset( $_GET['wc_tracker_nonce'] ) && wp_verify_nonce( $_GET['wc_tracker_nonce'], 'wc_tracker_optin' ) ) {
			update_option( 'woocommerce_allow_tracking', 'yes' );
			WC_Tracker::send_tracking_data( true );

		} elseif ( isset( $_GET['wc_tracker_optout'] ) && isset( $_GET['wc_tracker_nonce'] ) && wp_verify_nonce( $_GET['wc_tracker_nonce'], 'wc_tracker_optout' ) ) {
			update_option( 'woocommerce_allow_tracking', 'no' );
		}
	}

	/**
	 * Final step.
	 */
	public function wc_setup_ready() {
		$this->wc_setup_ready_actions();
		shuffle( $this->tweets );
		?>
		<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://woocommerce.com/" data-text="<?php echo esc_attr( $this->tweets[0] ); ?>" data-via="WooThemes" data-size="large">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

		<h1><?php _e( 'Your Store is Ready!', 'woocommerce' ); ?></h1>

		<?php if ( 'unknown' === get_option( 'woocommerce_allow_tracking', 'unknown' ) ) : ?>
			<div class="woocommerce-message woocommerce-tracker">
				<p><?php printf( __( 'Want to help make WooCommerce even more awesome? Allow WooThemes to collect non-sensitive diagnostic data and usage information. %sFind out more%s.', 'woocommerce' ), '<a href="https://woocommerce.com/usage-tracking/" target="_blank">', '</a>' ); ?></p>
				<p class="submit">
					<a class="button-primary button button-large" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc_tracker_optin', 'true' ), 'wc_tracker_optin', 'wc_tracker_nonce' ) ); ?>"><?php _e( 'Allow', 'woocommerce' ); ?></a>
					<a class="button-secondary button button-large skip"  href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc_tracker_optout', 'true' ), 'wc_tracker_optout', 'wc_tracker_nonce' ) ); ?>"><?php _e( 'No thanks', 'woocommerce' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<div class="wc-setup-next-steps">
			<div class="wc-setup-next-steps-first">
				<h2><?php _e( 'Next Steps', 'woocommerce' ); ?></h2>
				<ul>
					<li class="setup-product"><a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product&tutorial=true' ) ); ?>"><?php _e( 'Create your first product!', 'woocommerce' ); ?></a></li>
				</ul>
			</div>
			<div class="wc-setup-next-steps-last">
				<h2><?php _e( 'Learn More', 'woocommerce' ); ?></h2>
				<ul>
					<li class="video-walkthrough"><a href="https://docs.woocommerce.com/document/woocommerce-101-video-series/?utm_source=setupwizard&utm_medium=product&utm_content=videos&utm_campaign=woocommerceplugin"><?php _e( 'Watch the WC 101 video walkthroughs', 'woocommerce' ); ?></a></li>
					<li class="newsletter"><a href="https://woocommerce.com/woocommerce-onboarding-email/?utm_source=setupwizard&utm_medium=product&utm_content=newsletter&utm_campaign=woocommerceplugin"><?php _e( 'Get eCommerce advice in your inbox', 'woocommerce' ); ?></a></li>
					<li class="learn-more"><a href="https://docs.woocommerce.com/documentation/plugins/woocommerce/getting-started/?utm_source=setupwizard&utm_medium=product&utm_content=docs&utm_campaign=woocommerceplugin"><?php _e( 'Learn more about getting started', 'woocommerce' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}
}

new WC_Admin_Setup_Wizard();
