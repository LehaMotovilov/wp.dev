<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Performs tax calculations and loads tax rates
 *
 * @class 		WC_Tax
 * @version		2.2.0
 * @package		WooCommerce/Classes
 * @category	Class
 * @author 		WooThemes
 */
class WC_Tax {

	/**
	 * Precision.
	 *
	 * @var int
	 */
	public static $precision;

	/**
	 * Round at subtotal.
	 *
	 * @var bool
	 */
	public static $round_at_subtotal;

	/**
	 * Load options.
	 *
	 * @access public
	 */
	public static function init() {
		self::$precision         = WC_ROUNDING_PRECISION;
		self::$round_at_subtotal = 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' );
	}

	/**
	 * Calculate tax for a line.
	 * @param  float  $price              Price to calc tax on
	 * @param  array  $rates              Rates to apply
	 * @param  boolean $price_includes_tax Whether the passed price has taxes included
	 * @param  boolean $suppress_rounding  Whether to suppress any rounding from taking place
	 * @return array                      Array of rates + prices after tax
	 */
	public static function calc_tax( $price, $rates, $price_includes_tax = false, $suppress_rounding = false ) {
		// Work in pence to X precision
		$price = self::precision( $price );

		if ( $price_includes_tax ) {
			$taxes = self::calc_inclusive_tax( $price, $rates );
		} else {
			$taxes = self::calc_exclusive_tax( $price, $rates );
		}

		// Round to precision
		if ( ! self::$round_at_subtotal && ! $suppress_rounding ) {
			$taxes = array_map( 'round', $taxes ); // Round to precision
		}

		// Remove precision
		$price     = self::remove_precision( $price );
		$taxes     = array_map( array( __CLASS__, 'remove_precision' ), $taxes );

		return apply_filters( 'woocommerce_calc_tax', $taxes, $price, $rates, $price_includes_tax, $suppress_rounding );
	}

	/**
	 * Calculate the shipping tax using a passed array of rates.
	 *
	 * @param   float		Price
	 * @param	array		Taxation Rate
	 * @return  array
	 */
	public static function calc_shipping_tax( $price, $rates ) {
		return self::calc_exclusive_tax( $price, $rates );
	}

	/**
	 * Multiply cost by pow precision.
	 * @param  float $price
	 * @return float
	 */
	private static function precision( $price ) {
		return $price * ( pow( 10, self::$precision ) );
	}

	/**
	 * Divide cost by pow precision.
	 * @param  float $price
	 * @return float
	 */
	private static function remove_precision( $price ) {
		return $price / ( pow( 10, self::$precision ) );
	}

	/**
	 * Round to precision.
	 *
	 * Filter example: to return rounding to .5 cents you'd use:
	 *
	 * function euro_5cent_rounding( $in ) {
	 *      return round( $in / 5, 2 ) * 5;
	 * }
	 * add_filter( 'woocommerce_tax_round', 'euro_5cent_rounding' );
	 * @return double
	 */
	public static function round( $in ) {
		return apply_filters( 'woocommerce_tax_round', round( $in, self::$precision ), $in );
	}

	/**
	 * Calc tax from inclusive price.
	 *
	 * @param  float $price
	 * @param  array $rates
	 * @return array
	 */
	public static function calc_inclusive_tax( $price, $rates ) {
		$taxes = array();

		$regular_tax_rates = $compound_tax_rates = 0;

		foreach ( $rates as $key => $rate )
			if ( $rate['compound'] == 'yes' )
				$compound_tax_rates = $compound_tax_rates + $rate['rate'];
			else
				$regular_tax_rates  = $regular_tax_rates + $rate['rate'];

		$regular_tax_rate 	= 1 + ( $regular_tax_rates / 100 );
		$compound_tax_rate 	= 1 + ( $compound_tax_rates / 100 );
		$non_compound_price = $price / $compound_tax_rate;

		foreach ( $rates as $key => $rate ) {
			if ( ! isset( $taxes[ $key ] ) )
				$taxes[ $key ] = 0;

			$the_rate      = $rate['rate'] / 100;

			if ( $rate['compound'] == 'yes' ) {
				$the_price = $price;
				$the_rate  = $the_rate / $compound_tax_rate;
			} else {
				$the_price = $non_compound_price;
				$the_rate  = $the_rate / $regular_tax_rate;
			}

			$net_price       = $price - ( $the_rate * $the_price );
			$tax_amount      = $price - $net_price;
			$taxes[ $key ]   += apply_filters( 'woocommerce_price_inc_tax_amount', $tax_amount, $key, $rate, $price );
		}

		return $taxes;
	}

	/**
	 * Calc tax from exclusive price.
	 *
	 * @param  float $price
	 * @param  array $rates
	 * @return array
	 */
	public static function calc_exclusive_tax( $price, $rates ) {
		$taxes = array();

		if ( ! empty( $rates ) ) {
			// Multiple taxes
			foreach ( $rates as $key => $rate ) {

				if ( $rate['compound'] == 'yes' )
					continue;

				$tax_amount = $price * ( $rate['rate'] / 100 );

				// ADVANCED: Allow third parties to modify this rate
				$tax_amount = apply_filters( 'woocommerce_price_ex_tax_amount', $tax_amount, $key, $rate, $price );

				// Add rate
				if ( ! isset( $taxes[ $key ] ) )
					$taxes[ $key ] = $tax_amount;
				else
					$taxes[ $key ] += $tax_amount;
			}

			$pre_compound_total = array_sum( $taxes );

			// Compound taxes
			foreach ( $rates as $key => $rate ) {

				if ( $rate['compound'] == 'no' )
					continue;

				$the_price_inc_tax = $price + ( $pre_compound_total );

				$tax_amount = $the_price_inc_tax * ( $rate['rate'] / 100 );

				// ADVANCED: Allow third parties to modify this rate
				$tax_amount = apply_filters( 'woocommerce_price_ex_tax_amount', $tax_amount, $key, $rate, $price, $the_price_inc_tax, $pre_compound_total );

				// Add rate
				if ( ! isset( $taxes[ $key ] ) )
					$taxes[ $key ] = $tax_amount;
				else
					$taxes[ $key ] += $tax_amount;
			}
		}

		return $taxes;
	}

	/**
	 * Searches for all matching country/state/postcode tax rates.
	 *
	 * @param array $args
	 * @return array
	 */
	public static function find_rates( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'country'   => '',
			'state'     => '',
			'city'      => '',
			'postcode'  => '',
			'tax_class' => ''
		) );

		extract( $args, EXTR_SKIP );

		if ( ! $country ) {
			return array();
		}

		$postcode          = wc_normalize_postcode( wc_clean( $postcode ) );
		$cache_key         = WC_Cache_Helper::get_cache_prefix( 'taxes' ) . 'wc_tax_rates_' . md5( sprintf( '%s+%s+%s+%s+%s', $country, $state, $city, $postcode, $tax_class ) );
		$matched_tax_rates = wp_cache_get( $cache_key, 'taxes' );

		if ( false === $matched_tax_rates ) {
			$matched_tax_rates = self::get_matched_tax_rates( $country, $state, $postcode, $city, $tax_class );
			wp_cache_set( $cache_key, $matched_tax_rates, 'taxes' );
		}

		return apply_filters( 'woocommerce_find_rates', $matched_tax_rates, $args );
	}

	/**
	 * Searches for all matching country/state/postcode tax rates.
	 *
	 * @param array $args
	 * @return array
	 */
	public static function find_shipping_rates( $args = array() ) {
		$rates          = self::find_rates( $args );
		$shipping_rates = array();

		if ( is_array( $rates ) ) {
			foreach ( $rates as $key => $rate ) {
				if ( 'yes' === $rate['shipping'] ) {
					$shipping_rates[ $key ] = $rate;
				}
			}
		}

		return $shipping_rates;
	}

	/**
	 * Loop through a set of tax rates and get the matching rates (1 per priority).
	 *
	 * @param  string $country
	 * @param  string $state
	 * @param  string $postcode
	 * @param  string $city
	 * @param  string $tax_class
	 * @return array
	 */
	private static function get_matched_tax_rates( $country, $state, $postcode, $city, $tax_class ) {
		global $wpdb;

		// Query criteria - these will be ANDed
		$criteria   = array();
		$criteria[] = $wpdb->prepare( "tax_rate_country IN ( %s, '' )", strtoupper( $country ) );
		$criteria[] = $wpdb->prepare( "tax_rate_state IN ( %s, '' )", strtoupper( $state ) );
		$criteria[] = $wpdb->prepare( "tax_rate_class = %s", sanitize_title( $tax_class ) );

		// Pre-query postcode ranges for PHP based matching.
		$postcode_search = wc_get_wildcard_postcodes( $postcode, $country );
		$postcode_ranges = $wpdb->get_results( "SELECT tax_rate_id, location_code FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE location_type = 'postcode' AND location_code LIKE '%...%';" );

		if ( $postcode_ranges ) {
			$matches = wc_postcode_location_matcher( $postcode, $postcode_ranges, 'tax_rate_id', 'location_code', $country );
			if ( ! empty( $matches ) ) {
				foreach ( $matches as $matched_postcodes ) {
					$postcode_search = array_merge( $postcode_search, $matched_postcodes );
				}
			}
		}

		$postcode_search = array_unique( $postcode_search );

		/**
		 * Location matching criteria - ORed
		 * Needs to match:
		 * 	- rates with no postcodes and cities
		 * 	- rates with a matching postcode and city
		 * 	- rates with matching postcode, no city
		 * 	- rates with matching city, no postcode
		 */
		$locations_criteria   = array();
		$locations_criteria[] = "locations.location_type IS NULL";
		$locations_criteria[] = "
			locations.location_type = 'postcode' AND locations.location_code IN ('" . implode( "','", array_map( 'esc_sql', $postcode_search ) ) . "')
			AND (
				( locations2.location_type = 'city' AND locations2.location_code = '" . esc_sql( strtoupper( $city ) ) . "' )
				OR NOT EXISTS (
					SELECT sub.tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rate_locations as sub
					WHERE sub.location_type = 'city'
					AND sub.tax_rate_id = tax_rates.tax_rate_id
				)
			)
		";
		$locations_criteria[] = "
			locations.location_type = 'city' AND locations.location_code = '" . esc_sql( strtoupper( $city ) ) . "'
			AND NOT EXISTS (
				SELECT sub.tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rate_locations as sub
				WHERE sub.location_type = 'postcode'
				AND sub.tax_rate_id = tax_rates.tax_rate_id
			)
		";
		$criteria[] = '( ( ' . implode( ' ) OR ( ', $locations_criteria ) . ' ) )';

		$found_rates = $wpdb->get_results( "
			SELECT tax_rates.*
			FROM {$wpdb->prefix}woocommerce_tax_rates as tax_rates
			LEFT OUTER JOIN {$wpdb->prefix}woocommerce_tax_rate_locations as locations ON tax_rates.tax_rate_id = locations.tax_rate_id
			LEFT OUTER JOIN {$wpdb->prefix}woocommerce_tax_rate_locations as locations2 ON tax_rates.tax_rate_id = locations2.tax_rate_id
			WHERE 1=1 AND " . implode( ' AND ', $criteria ) . "
			GROUP BY tax_rate_id
			ORDER BY tax_rate_priority, tax_rate_order
		" );

		$matched_tax_rates = array();
		$found_priority    = array();

		foreach ( $found_rates as $found_rate ) {
			if ( in_array( $found_rate->tax_rate_priority, $found_priority ) ) {
				continue;
			}

			$matched_tax_rates[ $found_rate->tax_rate_id ] = array(
				'rate'     => $found_rate->tax_rate,
				'label'    => $found_rate->tax_rate_name,
				'shipping' => $found_rate->tax_rate_shipping ? 'yes' : 'no',
				'compound' => $found_rate->tax_rate_compound ? 'yes' : 'no'
			);

			$found_priority[] = $found_rate->tax_rate_priority;
		}

		return apply_filters( 'woocommerce_matched_tax_rates', $matched_tax_rates, $country, $state, $postcode, $city, $tax_class );
	}

	/**
	 * Get the customer tax location based on their status and the current page.
	 *
	 * Used by get_rates(), get_shipping_rates().
	 *
	 * @param  $tax_class string Optional, passed to the filter for advanced tax setups.
	 * @return array
	 */
	public static function get_tax_location( $tax_class = '' ) {
		$location = array();

		if ( ! empty( WC()->customer ) ) {
			$location = WC()->customer->get_taxable_address();
		} elseif ( wc_prices_include_tax() || 'base' === get_option( 'woocommerce_default_customer_address' ) || 'base' === get_option( 'woocommerce_tax_based_on' ) ) {
			$location = array(
				WC()->countries->get_base_country(),
				WC()->countries->get_base_state(),
				WC()->countries->get_base_postcode(),
				WC()->countries->get_base_city()
			);
		}

		return apply_filters( 'woocommerce_get_tax_location', $location, $tax_class );
	}

	/**
	 * Get's an array of matching rates for a tax class.
	 * @param string $tax_class
	 * @return  array
	 */
	public static function get_rates( $tax_class = '' ) {
		$tax_class         = sanitize_title( $tax_class );
		$location          = self::get_tax_location( $tax_class );
		$matched_tax_rates = array();

		if ( sizeof( $location ) === 4 ) {
			list( $country, $state, $postcode, $city ) = $location;

			$matched_tax_rates = self::find_rates( array(
				'country' 	=> $country,
				'state' 	=> $state,
				'postcode' 	=> $postcode,
				'city' 		=> $city,
				'tax_class' => $tax_class
			) );
		}

		return apply_filters( 'woocommerce_matched_rates', $matched_tax_rates, $tax_class );
	}

	/**
	 * Get's an array of matching rates for the shop's base country.
	 *
	 * @param   string	Tax Class
	 * @return  array
	 */
	public static function get_base_tax_rates( $tax_class = '' ) {
		return apply_filters( 'woocommerce_base_tax_rates', self::find_rates( array(
			'country' 	=> WC()->countries->get_base_country(),
			'state' 	=> WC()->countries->get_base_state(),
			'postcode' 	=> WC()->countries->get_base_postcode(),
			'city' 		=> WC()->countries->get_base_city(),
			'tax_class' => $tax_class
		) ), $tax_class );
	}

	/**
	 * Alias for get_base_tax_rates().
	 *
	 * @deprecated 2.3
	 * @param   string	Tax Class
	 * @return  array
	 */
	public static function get_shop_base_rate( $tax_class = '' ) {
		return self::get_base_tax_rates( $tax_class );
	}

	/**
	 * Gets an array of matching shipping tax rates for a given class.
	 *
	 * @param   string	Tax Class
	 * @return  mixed
	 */
	public static function get_shipping_tax_rates( $tax_class = null ) {
		// See if we have an explicitly set shipping tax class
		if ( $shipping_tax_class = get_option( 'woocommerce_shipping_tax_class' ) ) {
			$tax_class = 'standard' === $shipping_tax_class ? '' : $shipping_tax_class;
		}

		$location          = self::get_tax_location( $tax_class );
		$matched_tax_rates = array();

		if ( sizeof( $location ) === 4 ) {
			list( $country, $state, $postcode, $city ) = $location;

			if ( ! is_null( $tax_class ) ) {
				// This will be per item shipping
				$matched_tax_rates = self::find_shipping_rates( array(
					'country' 	=> $country,
					'state' 	=> $state,
					'postcode' 	=> $postcode,
					'city' 		=> $city,
					'tax_class' => $tax_class
				) );

			} else {

				// This will be per order shipping - loop through the order and find the highest tax class rate
				$cart_tax_classes = WC()->cart->get_cart_item_tax_classes();

				// If multiple classes are found, use the first one. Don't bother with standard rate, we can get that later.
				if ( sizeof( $cart_tax_classes ) > 1 && ! in_array( '', $cart_tax_classes ) ) {
					$tax_classes = self::get_tax_classes();

					foreach ( $tax_classes as $tax_class ) {
						$tax_class = sanitize_title( $tax_class );
						if ( in_array( $tax_class, $cart_tax_classes ) ) {
							$matched_tax_rates = self::find_shipping_rates( array(
								'country' 	=> $country,
								'state' 	=> $state,
								'postcode' 	=> $postcode,
								'city' 		=> $city,
								'tax_class' => $tax_class
							) );
							break;
						}
					}

				// If a single tax class is found, use it
				} elseif ( sizeof( $cart_tax_classes ) == 1 ) {
					$matched_tax_rates = self::find_shipping_rates( array(
						'country' 	=> $country,
						'state' 	=> $state,
						'postcode' 	=> $postcode,
						'city' 		=> $city,
						'tax_class' => $cart_tax_classes[0]
					) );
				}
			}

			// Get standard rate if no taxes were found
			if ( ! sizeof( $matched_tax_rates ) ) {
				$matched_tax_rates = self::find_shipping_rates( array(
					'country' 	=> $country,
					'state' 	=> $state,
					'postcode' 	=> $postcode,
					'city' 		=> $city
				) );
			}
		}

		return $matched_tax_rates;
	}

	/**
	 * Return true/false depending on if a rate is a compound rate.
	 *
	 * @param   int		key
	 * @return  bool
	 */
	public static function is_compound( $key ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate_compound FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %s", $key ) ) ? true : false;
	}

	/**
	 * Return a given rates label.
	 *
	 * @param mixed $key_or_rate Tax rate ID, or the db row itself in object format
	 * @return  string
	 */
	public static function get_rate_label( $key_or_rate ) {
		global $wpdb;

		if ( is_object( $key_or_rate ) ) {
			$key       = $key_or_rate->tax_rate_id;
			$rate_name = $key_or_rate->tax_rate_name;
		} else {
			$key       = $key_or_rate;
			$rate_name = $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate_name FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %s", $key ) );
		}

		if ( ! $rate_name ) {
			$rate_name = WC()->countries->tax_or_vat();
		}

		return apply_filters( 'woocommerce_rate_label', $rate_name, $key );
	}

	/**
	 * Return a given rates percent.
	 *
	 * @param mixed $key_or_rate Tax rate ID, or the db row itself in object format
	 * @return  string
	 */
	public static function get_rate_percent( $key_or_rate ) {
		global $wpdb;

		if ( is_object( $key_or_rate ) ) {
			$key      = $key_or_rate->tax_rate_id;
			$tax_rate = $key_or_rate->tax_rate;
		} else {
			$key      = $key_or_rate;
			$tax_rate = $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %s", $key ) );
		}

		return apply_filters( 'woocommerce_rate_percent', floatval( $tax_rate ) . '%', $key );
	}

	/**
	 * Get a rates code. Code is made up of COUNTRY-STATE-NAME-Priority. E.g GB-VAT-1, US-AL-TAX-1.
	 *
	 * @access public
	 * @param mixed $key_or_rate Tax rate ID, or the db row itself in object format
	 * @return string
	 */
	public static function get_rate_code( $key_or_rate ) {
		global $wpdb;

		if ( is_object( $key_or_rate ) ) {
			$key  = $key_or_rate->tax_rate_id;
			$rate = $key_or_rate;
		} else {
			$key  = $key_or_rate;
			$rate = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %s", $key ) );
		}

		$code_string = '';

		if ( null !== $rate ) {
			$code   = array();
			$code[] = $rate->tax_rate_country;
			$code[] = $rate->tax_rate_state;
			$code[] = $rate->tax_rate_name ? $rate->tax_rate_name : 'TAX';
			$code[] = absint( $rate->tax_rate_priority );
			$code_string = strtoupper( implode( '-', array_filter( $code ) ) );
		}

		return apply_filters( 'woocommerce_rate_code', $code_string, $key );
	}

	/**
	 * Round tax lines and return the sum.
	 *
	 * @param   array
	 * @return  float
	 */
	public static function get_tax_total( $taxes ) {
		return array_sum( array_map( array( __CLASS__, 'round' ), $taxes ) );
	}

	/**
	 * Get store tax classes.
	 * @return array
	 */
	public static function get_tax_classes() {
		return array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
	}

	/**
	 * format the city.
	 * @param  string $city
	 * @return string
	 */
	private static function format_tax_rate_city( $city ) {
		return strtoupper( trim( $city ) );
	}

	/**
	 * format the state.
	 * @param  string $state
	 * @return string
	 */
	private static function format_tax_rate_state( $state ) {
		$state = strtoupper( $state );
		return $state === '*' ? '' : $state;
	}

	/**
	 * format the country.
	 * @param  string $country
	 * @return string
	 */
	private static function format_tax_rate_country( $country ) {
		$country = strtoupper( $country );
		return $country === '*' ? '' : $country;
	}

	/**
	 * format the tax rate name.
	 * @param  string $name
	 * @return string
	 */
	private static function format_tax_rate_name( $name ) {
		return $name ? $name : __( 'Tax', 'woocommerce' );
	}

	/**
	 * format the rate.
	 * @param  double $rate
	 * @return string
	 */
	private static function format_tax_rate( $rate ) {
		return number_format( (double) $rate, 4, '.', '' );
	}

	/**
	 * format the priority.
	 * @param  string $priority
	 * @return int
	 */
	private static function format_tax_rate_priority( $priority ) {
		return absint( $priority );
	}

	/**
	 * format the class.
	 * @param  string $class
	 * @return string
	 */
	public static function format_tax_rate_class( $class ) {
		$class = sanitize_title( $class );
		$sanitized_classes = array_map( 'sanitize_title', self::get_tax_classes() );
		if ( ! in_array( $class, $sanitized_classes ) ) {
			$class = '';
		}
		return $class === 'standard' ? '' : $class;
	}

	/**
	 * Prepare and format tax rate for DB insertion.
	 * @param  array $tax_rate
	 * @return array
	 */
	private static function prepare_tax_rate( $tax_rate ) {
		foreach ( $tax_rate as $key => $value ) {
			if ( method_exists( __CLASS__, 'format_' . $key ) ) {
				$tax_rate[ $key ] = call_user_func( array( __CLASS__, 'format_' . $key ), $value );
			}
		}
		return $tax_rate;
	}

	/**
	 * Insert a new tax rate.
	 *
	 * Internal use only.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param  array $tax_rate
	 *
	 * @return int tax rate id
	 */
	public static function _insert_tax_rate( $tax_rate ) {
		global $wpdb;

		$wpdb->insert( $wpdb->prefix . 'woocommerce_tax_rates', self::prepare_tax_rate( $tax_rate ) );

		WC_Cache_Helper::incr_cache_prefix( 'taxes' );

		do_action( 'woocommerce_tax_rate_added', $wpdb->insert_id, $tax_rate );

		return $wpdb->insert_id;
	}

	/**
	 * Get tax rate.
	 *
	 * Internal use only.
	 *
	 * @since 2.5.0
	 * @access private
	 *
	 * @param int $tax_rate_id
	 * @param string $output_type
	 *
	 * @return array
	 */
	public static function _get_tax_rate( $tax_rate_id, $output_type = ARRAY_A ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->prefix}woocommerce_tax_rates
			WHERE tax_rate_id = %d
		", $tax_rate_id ), $output_type );
	}

	/**
	 * Update a tax rate.
	 *
	 * Internal use only.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param int $tax_rate_id
	 * @param array $tax_rate
	 */
	public static function _update_tax_rate( $tax_rate_id, $tax_rate ) {
		global $wpdb;

		$tax_rate_id = absint( $tax_rate_id );

		$wpdb->update(
			$wpdb->prefix . "woocommerce_tax_rates",
			self::prepare_tax_rate( $tax_rate ),
			array(
				'tax_rate_id' => $tax_rate_id
			)
		);

		WC_Cache_Helper::incr_cache_prefix( 'taxes' );

		do_action( 'woocommerce_tax_rate_updated', $tax_rate_id, $tax_rate );
	}

	/**
	 * Delete a tax rate from the database.
	 *
	 * Internal use only.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param  int $tax_rate_id
	 */
	public static function _delete_tax_rate( $tax_rate_id ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %d;", $tax_rate_id ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %d;", $tax_rate_id ) );

		WC_Cache_Helper::incr_cache_prefix( 'taxes' );

		do_action( 'woocommerce_tax_rate_deleted', $tax_rate_id );
	}

	/**
	 * Update postcodes for a tax rate in the DB.
	 *
	 * Internal use only.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param  int $tax_rate_id
	 * @param  string $postcodes String of postcodes separated by ; characters
	 * @return string
	 */
	public static function _update_tax_rate_postcodes( $tax_rate_id, $postcodes ) {
		if ( ! is_array( $postcodes ) ) {
			$postcodes = explode( ';', $postcodes );
		}
		// No normalization - postcodes are matched against both normal and formatted versions to support wildcards.
		self::_update_tax_rate_locations( $tax_rate_id, array_diff( array_map( 'trim',  array_map( 'strtoupper', array_filter( $postcodes ) ) ), array( '*' ) ), 'postcode' );
	}

	/**
	 * Update cities for a tax rate in the DB.
	 *
	 * Internal use only.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param  int $tax_rate_id
	 * @param  string $cities
	 * @return string
	 */
	public static function _update_tax_rate_cities( $tax_rate_id, $cities ) {
		if ( ! is_array( $cities ) ) {
			$cities = explode( ';', $cities );
		}
		$cities = array_filter( array_diff( array_map( array( __CLASS__, 'format_tax_rate_city' ), $cities ), array( '*' ) ) );

		self::_update_tax_rate_locations( $tax_rate_id, $cities, 'city' );
	}

	/**
	 * Updates locations (postcode and city).
	 *
	 * Internal use only.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param  int $tax_rate_id
	 * @param string $type
	 * @return string
	 */
	private static function _update_tax_rate_locations( $tax_rate_id, $values, $type ) {
		global $wpdb;

		$tax_rate_id = absint( $tax_rate_id );

		$wpdb->query(
			$wpdb->prepare( "
				DELETE FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %d AND location_type = %s;
				", $tax_rate_id, $type
			)
		);

		if ( sizeof( $values ) > 0 ) {
			$sql = "( '" . implode( "', $tax_rate_id, '" . esc_sql( $type ) . "' ),( '", array_map( 'esc_sql', $values ) ) . "', $tax_rate_id, '" . esc_sql( $type ) . "' )";

			$wpdb->query( "
				INSERT INTO {$wpdb->prefix}woocommerce_tax_rate_locations ( location_code, tax_rate_id, location_type ) VALUES $sql;
				" );
		}

		WC_Cache_Helper::incr_cache_prefix( 'taxes' );
	}

	/**
	 * Used by admin settings page.
	 *
	 * @param string $tax_class
	 *
	 * @return array|null|object
	 */
	public static function get_rates_for_tax_class( $tax_class ) {
		global $wpdb;

		// Get all the rates and locations. Snagging all at once should significantly cut down on the number of queries.
		$rates     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rates` WHERE `tax_rate_class` = %s ORDER BY `tax_rate_order`;", sanitize_title( $tax_class ) ) );
		$locations = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}woocommerce_tax_rate_locations`" );

		if ( ! empty( $rates ) ) {
			// Set the rates keys equal to their ids.
			$rates = array_combine( wp_list_pluck( $rates, 'tax_rate_id' ), $rates );
		}

		// Drop the locations into the rates array.
		foreach ( $locations as $location ) {
			// Don't set them for unexistent rates.
			if ( ! isset( $rates[ $location->tax_rate_id ] ) ) {
				continue;
			}
			// If the rate exists, initialize the array before appending to it.
			if ( ! isset( $rates[ $location->tax_rate_id ]->{$location->location_type} ) ) {
				$rates[ $location->tax_rate_id ]->{$location->location_type} = array();
			}
			$rates[ $location->tax_rate_id ]->{$location->location_type}[] = $location->location_code;
		}

		return $rates;
	}
}
WC_Tax::init();
