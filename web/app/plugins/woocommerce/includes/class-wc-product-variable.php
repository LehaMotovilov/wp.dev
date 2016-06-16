<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Variable Product Class.
 *
 * The WooCommerce product class handles individual product data.
 *
 * @class 		WC_Product_Variable
 * @version		2.0.0
 * @package		WooCommerce/Classes/Products
 * @category	Class
 * @author 		WooThemes
 */
class WC_Product_Variable extends WC_Product {

	/** @public array Array of child products/posts/variations. */
	public $children = null;

	/** @private array Array of variation prices. */
	private $prices_array = array();

	/**
	 * Constructor.
	 *
	 * @param mixed $product
	 */
	public function __construct( $product ) {
		$this->product_type = 'variable';
		parent::__construct( $product );
	}

	/**
	 * Get the add to cart button text.
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_text() {
		$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Select options', 'woocommerce' ) : __( 'Read More', 'woocommerce' );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Set stock level of the product.
	 *
	 * @param mixed $amount (default: null)
	 * @param string $mode can be set, add, or subtract
	 * @return int Stock
	 */
	public function set_stock( $amount = null, $mode = 'set' ) {
		$this->total_stock = '';
		delete_transient( 'wc_product_total_stock_' . $this->id . WC_Cache_Helper::get_transient_version( 'product' ) );
		return parent::set_stock( $amount, $mode );
	}

	/**
	 * Performed after a stock level change at product level.
	 */
	public function check_stock_status() {
		$set_child_stock_status = '';

		if ( ! $this->backorders_allowed() && $this->get_stock_quantity() <= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
			$set_child_stock_status = 'outofstock';
		} elseif ( $this->backorders_allowed() || $this->get_stock_quantity() > get_option( 'woocommerce_notify_no_stock_amount' ) ) {
			$set_child_stock_status = 'instock';
		}

		if ( $set_child_stock_status ) {
			foreach ( $this->get_children() as $child_id ) {
				if ( 'yes' !== get_post_meta( $child_id, '_manage_stock', true ) ) {
					wc_update_product_stock_status( $child_id, $set_child_stock_status );
				}
			}

			// Children statuses changed, so sync self
			self::sync_stock_status( $this->id );
		}
	}

	/**
	 * Set stock status.
	 */
	public function set_stock_status( $status ) {
		$status = 'outofstock' === $status ? 'outofstock' : 'instock';

		if ( update_post_meta( $this->id, '_stock_status', $status ) ) {
			do_action( 'woocommerce_product_set_stock_status', $this->id, $status );
		}
	}

	/**
	 * Return a products child ids.
	 *
	 * @param  boolean $visible_only Only return variations which are not hidden
	 * @return array of children ids
	 */
	public function get_children( $visible_only = false ) {
		$key            = $visible_only ? 'visible' : 'all';
		$transient_name = 'wc_product_children_' . $this->id;

		// Get value of transient
		if ( ! is_array( $this->children ) ) {
			$this->children = get_transient( $transient_name );
		}

		// Get value from DB
		if ( empty( $this->children ) || ! is_array( $this->children ) || ! isset( $this->children[ $key ] ) ) {
			$args = array(
				'post_parent' => $this->id,
				'post_type'   => 'product_variation',
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'fields'      => 'ids',
				'post_status' => 'publish',
				'numberposts' => -1
			);

			if ( $visible_only ) {
				if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
					$args['meta_query'][] = array(
						'key'     => '_stock_status',
						'value'   => 'instock',
						'compare' => '=',
					);
				}
			}

			$args                   = apply_filters( 'woocommerce_variable_children_args', $args, $this, $visible_only );
			$this->children[ $key ] = get_posts( $args );

			set_transient( $transient_name, $this->children, DAY_IN_SECONDS * 30 );
		}

		return apply_filters( 'woocommerce_get_children', $this->children[ $key ], $this, $visible_only );
	}

	/**
	 * Get child product.
	 *
	 * @access public
	 * @param mixed $child_id
	 * @return WC_Product_Variation
	 */
	public function get_child( $child_id ) {
		return wc_get_product( $child_id, array(
			'parent_id' => $this->id,
			'parent' 	=> $this
		) );
	}

	/**
	 * Returns whether or not the product has any child product.
	 *
	 * @access public
	 * @return bool
	 */
	public function has_child() {
		return sizeof( $this->get_children() ) ? true : false;
	}

	/**
	 * Returns whether or not the product is on sale.
	 * @return bool
	 */
	public function is_on_sale() {
		$is_on_sale = false;
		$prices     = $this->get_variation_prices();

		if ( $prices['regular_price'] !== $prices['sale_price'] && $prices['sale_price'] === $prices['price'] ) {
			$is_on_sale = true;
		}
		return apply_filters( 'woocommerce_product_is_on_sale', $is_on_sale, $this );
	}

	/**
	 * Get the min or max variation regular price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string
	 */
	public function get_variation_regular_price( $min_or_max = 'min', $display = false ) {
		$prices = $this->get_variation_prices( $display );
		$price  = 'min' === $min_or_max ? current( $prices['regular_price'] ) : end( $prices['regular_price'] );
		return apply_filters( 'woocommerce_get_variation_regular_price', $price, $this, $min_or_max, $display );
	}

	/**
	 * Get the min or max variation sale price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string
	 */
	public function get_variation_sale_price( $min_or_max = 'min', $display = false ) {
		$prices = $this->get_variation_prices( $display );
		$price  = 'min' === $min_or_max ? current( $prices['sale_price'] ) : end( $prices['sale_price'] );
		return apply_filters( 'woocommerce_get_variation_sale_price', $price, $this, $min_or_max, $display );
	}

	/**
	 * Get the min or max variation (active) price.
	 * @param  string $min_or_max - min or max
	 * @param  boolean  $display Whether the value is going to be displayed
	 * @return string
	 */
	public function get_variation_price( $min_or_max = 'min', $display = false ) {
		$prices = $this->get_variation_prices( $display );
		$price  = 'min' === $min_or_max ? current( $prices['price'] ) : end( $prices['price'] );
		return apply_filters( 'woocommerce_get_variation_price', $price, $this, $min_or_max, $display );
	}

	/**
	 * Get an array of all sale and regular prices from all variations. This is used for example when displaying the price range at variable product level or seeing if the variable product is on sale.
	 *
	 * Can be filtered by plugins which modify costs, but otherwise will include the raw meta costs unlike get_price() which runs costs through the woocommerce_get_price filter.
	 * This is to ensure modified prices are not cached, unless intended.
	 *
	 * @param  bool $display Are prices for display? If so, taxes will be calculated.
	 * @return array() Array of RAW prices, regular prices, and sale prices with keys set to variation ID.
	 */
	public function get_variation_prices( $display = false ) {
		global $wp_filter;

		/**
		 * Transient name for storing prices for this product (note: Max transient length is 45)
		 * @since 2.5.0 a single transient is used per product for all prices, rather than many transients per product.
		 */
		$transient_name = 'wc_var_prices_' . $this->id;

		/**
		 * Create unique cache key based on the tax location (affects displayed/cached prices), product version and active price filters.
		 * DEVELOPERS should filter this hash if offering conditonal pricing to keep it unique.
		 * @var string
		 */
		if ( $display ) {
			$price_hash = array( get_option( 'woocommerce_tax_display_shop', 'excl' ), WC_Tax::get_rates() );
		} else {
			$price_hash = array( false );
		}

		$filter_names = array( 'woocommerce_variation_prices_price', 'woocommerce_variation_prices_regular_price', 'woocommerce_variation_prices_sale_price' );

		foreach ( $filter_names as $filter_name ) {
			if ( ! empty( $wp_filter[ $filter_name ] ) ) {
				$price_hash[ $filter_name ] = array();

				foreach ( $wp_filter[ $filter_name ] as $priority => $callbacks ) {
					$price_hash[ $filter_name ][] = array_values( wp_list_pluck( $callbacks, 'function' ) );
				}
			}
		}

		$price_hash = md5( json_encode( apply_filters( 'woocommerce_get_variation_prices_hash', $price_hash, $this, $display ) ) );

		// If the value has already been generated, we don't need to grab the values again.
		if ( empty( $this->prices_array[ $price_hash ] ) ) {

			// Get value of transient
			$prices_array = array_filter( (array) json_decode( strval( get_transient( $transient_name ) ), true ) );

			// If the product version has changed, reset cache
			if ( empty( $prices_array['version'] ) || $prices_array['version'] !== WC_Cache_Helper::get_transient_version( 'product' ) ) {
				$this->prices_array = array( 'version' => WC_Cache_Helper::get_transient_version( 'product' ) );
			}

			// If the prices are not stored for this hash, generate them
			if ( empty( $prices_array[ $price_hash ] ) ) {
				$prices         = array();
				$regular_prices = array();
				$sale_prices    = array();
				$variation_ids  = $this->get_children( true );

				foreach ( $variation_ids as $variation_id ) {
					if ( $variation = $this->get_child( $variation_id ) ) {
						$price         = apply_filters( 'woocommerce_variation_prices_price', $variation->price, $variation, $this );
						$regular_price = apply_filters( 'woocommerce_variation_prices_regular_price', $variation->regular_price, $variation, $this );
						$sale_price    = apply_filters( 'woocommerce_variation_prices_sale_price', $variation->sale_price, $variation, $this );

						// Skip empty prices
						if ( '' === $price ) {
							continue;
						}

						// If sale price does not equal price, the product is not yet on sale
						if ( $sale_price === $regular_price || $sale_price !== $price ) {
							$sale_price = $regular_price;
						}

						// If we are getting prices for display, we need to account for taxes
						if ( $display ) {
							if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
								$price         = '' === $price ? ''         : $variation->get_price_including_tax( 1, $price );
								$regular_price = '' === $regular_price ? '' : $variation->get_price_including_tax( 1, $regular_price );
								$sale_price    = '' === $sale_price ? ''    : $variation->get_price_including_tax( 1, $sale_price );
							} else {
								$price         = '' === $price ? ''         : $variation->get_price_excluding_tax( 1, $price );
								$regular_price = '' === $regular_price ? '' : $variation->get_price_excluding_tax( 1, $regular_price );
								$sale_price    = '' === $sale_price ? ''    : $variation->get_price_excluding_tax( 1, $sale_price );
							}
						}

						$prices[ $variation_id ]         = wc_format_decimal( $price, wc_get_price_decimals() );
						$regular_prices[ $variation_id ] = wc_format_decimal( $regular_price, wc_get_price_decimals() );
						$sale_prices[ $variation_id ]    = wc_format_decimal( $sale_price . '.00', wc_get_price_decimals() );
					}
				}

				asort( $prices );
				asort( $regular_prices );
				asort( $sale_prices );

				$prices_array[ $price_hash ] = array(
					'price'         => $prices,
					'regular_price' => $regular_prices,
					'sale_price'    => $sale_prices,
				);

				set_transient( $transient_name, json_encode( $prices_array ), DAY_IN_SECONDS * 30 );
			}

			/**
			 * Give plugins one last chance to filter the variation prices array which has been generated.
			 */
			$this->prices_array[ $price_hash ] = apply_filters( 'woocommerce_variation_prices', $prices_array[ $price_hash ], $this, $display );
		}

		/**
		 * Return the values.
		 */
		return $this->prices_array[ $price_hash ];
	}

	/**
	 * Returns the price in html format.
	 *
	 * @access public
	 * @param string $price (default: '')
	 * @return string
	 */
	public function get_price_html( $price = '' ) {
		$prices = $this->get_variation_prices( true );

		// No variations, or no active variation prices
		if ( $this->get_price() === '' || empty( $prices['price'] ) ) {
			$price = apply_filters( 'woocommerce_variable_empty_price_html', '', $this );
		} else {
			$min_price = current( $prices['price'] );
			$max_price = end( $prices['price'] );
			$price     = $min_price !== $max_price ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_price ), wc_price( $max_price ) ) : wc_price( $min_price );
			$is_free   = $min_price == 0 && $max_price == 0;

			if ( $this->is_on_sale() ) {
				$min_regular_price = current( $prices['regular_price'] );
				$max_regular_price = end( $prices['regular_price'] );
				$regular_price     = $min_regular_price !== $max_regular_price ? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $min_regular_price ), wc_price( $max_regular_price ) ) : wc_price( $min_regular_price );
				$price             = apply_filters( 'woocommerce_variable_sale_price_html', $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix(), $this );
			} elseif ( $is_free ) {
				$price = apply_filters( 'woocommerce_variable_free_price_html', __( 'Free!', 'woocommerce' ), $this );
			} else {
				$price = apply_filters( 'woocommerce_variable_price_html', $price . $this->get_price_suffix(), $this );
			}
		}
		return apply_filters( 'woocommerce_get_price_html', $price, $this );
	}

	/**
	 * Return an array of attributes used for variations, as well as their possible values.
	 *
	 * @return array of attributes and their available values
	 */
	public function get_variation_attributes() {
		global $wpdb;

		$variation_attributes = array();
		$attributes           = $this->get_attributes();
		$child_ids            = $this->get_children( true );

		if ( ! empty( $child_ids ) ) {
			foreach ( $attributes as $attribute ) {
				if ( empty( $attribute['is_variation'] ) ) {
					continue;
				}

				// Get possible values for this attribute, for only visible variations.
				$values = array_unique( $wpdb->get_col( $wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id IN (" . implode( ',', array_map( 'esc_sql', $child_ids ) ) . ")",
					wc_variation_attribute_name( $attribute['name'] )
				) ) );

				// empty value indicates that all options for given attribute are available
				if ( in_array( '', $values ) ) {
					$values = $attribute['is_taxonomy'] ? wp_get_post_terms( $this->id, $attribute['name'], array( 'fields' => 'slugs' ) ) : wc_get_text_attributes( $attribute['value'] );

				// Get custom attributes (non taxonomy) as defined
				} elseif ( ! $attribute['is_taxonomy'] ) {
					$text_attributes          = wc_get_text_attributes( $attribute['value'] );
					$assigned_text_attributes = $values;
					$values                   = array();

					// Pre 2.4 handling where 'slugs' were saved instead of the full text attribute
					if ( version_compare( get_post_meta( $this->id, '_product_version', true ), '2.4.0', '<' ) ) {
						$assigned_text_attributes = array_map( 'sanitize_title', $assigned_text_attributes );

						foreach ( $text_attributes as $text_attribute ) {
							if ( in_array( sanitize_title( $text_attribute ), $assigned_text_attributes ) ) {
								$values[] = $text_attribute;
							}
						}
					} else {
						foreach ( $text_attributes as $text_attribute ) {
							if ( in_array( $text_attribute, $assigned_text_attributes ) ) {
								$values[] = $text_attribute;
							}
						}
					}
				}

				$variation_attributes[ $attribute['name'] ] = array_unique( $values );
			}
		}

		return $variation_attributes;
	}

	/**
	 * If set, get the default attributes for a variable product.
	 *
	 * @access public
	 * @return array
	 */
	public function get_variation_default_attributes() {
		$default = isset( $this->default_attributes ) ? $this->default_attributes : '';
		return apply_filters( 'woocommerce_product_default_attributes', array_filter( (array) maybe_unserialize( $default ) ), $this );
	}

	/**
	 * Check if variable product has default attributes set.
	 *
	 * @access public
	 * @return bool
	 */
	public function has_default_attributes() {
		if ( ! $this->get_variation_default_attributes() ) {
			return true;
		}
		return false;
	}

	/**
	 * If set, get the default attributes for a variable product.
	 *
	 * @param string $attribute_name
	 * @return string
	 */
	public function get_variation_default_attribute( $attribute_name ) {
		$defaults       = $this->get_variation_default_attributes();
		$attribute_name = sanitize_title( $attribute_name );
		return isset( $defaults[ $attribute_name ] ) ? $defaults[ $attribute_name ] : '';
	}

	/**
	 * Match a variation to a given set of attributes using a WP_Query.
	 * @since  2.4.0
	 * @param  $match_attributes
	 * @return int Variation ID which matched, 0 is no match was found
	 */
	public function get_matching_variation( $match_attributes = array() ) {
		global $wpdb;

		$query_args = array(
			'post_parent' => $this->id,
			'post_type'   => 'product_variation',
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
			'fields'      => 'ids',
			'post_status' => 'publish',
			'numberposts' => 1,
			'meta_query'  => array()
		);

		foreach ( $this->get_attributes() as $attribute ) {
			if ( ! $attribute['is_variation'] ) {
				continue;
			}

			$attribute_field_name = 'attribute_' . sanitize_title( $attribute['name'] );

			if ( ! isset( $match_attributes[ $attribute_field_name ] ) ) {
				return 0;
			}

			$value = wc_clean( $match_attributes[ $attribute_field_name ] );

			$query_args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => $attribute_field_name,
					'value'   => array( '', $value ),
					'compare' => 'IN'
				),
				array(
					'key'     => $attribute_field_name,
					'compare' => 'NOT EXISTS'
				)
			);

		}

		// Allow large queries in case user has many variations
		$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );

		$matches = get_posts( $query_args );

		if ( $matches && ! is_wp_error( $matches ) ) {
			return current( $matches );

		/**
		 * Pre 2.4 handling where 'slugs' were saved instead of the full text attribute.
		 * Fallback is here because there are cases where data will be 'synced' but the product version will remain the same. @see WC_Product_Variable::sync_attributes.
		 */
	 	} elseif ( version_compare( get_post_meta( $this->id, '_product_version', true ), '2.4.0', '<' ) ) {
			return $match_attributes === array_map( 'sanitize_title', $match_attributes ) ? 0 : $this->get_matching_variation( array_map( 'sanitize_title', $match_attributes ) );

		} else {
			return 0;
		}
	}

	/**
	 * Get an array of available variations for the current product.
	 * @return array
	 */
	public function get_available_variations() {
		$available_variations = array();

		foreach ( $this->get_children() as $child_id ) {
			$variation = $this->get_child( $child_id );

			// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked
			if ( empty( $variation->variation_id ) || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
				continue;
			}

			// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price)
			if ( apply_filters( 'woocommerce_hide_invisible_variations', false, $this->id, $variation ) && ! $variation->variation_is_visible() ) {
				continue;
			}

			$available_variations[] = $this->get_available_variation( $variation );
		}

		return $available_variations;
	}

	/**
	 * Returns an array of data for a variation. Used in the add to cart form.
	 * @since  2.4.0
	 * @param  WC_Product $variation Variation product object or ID
	 * @return array
	 */
	public function get_available_variation( $variation ) {
		if ( is_numeric( $variation ) ) {
			$variation = $this->get_child( $variation );
		}

		if ( has_post_thumbnail( $variation->get_variation_id() ) ) {
			$attachment_id     = get_post_thumbnail_id( $variation->get_variation_id() );
			$attachment        = wp_get_attachment_image_src( $attachment_id, 'shop_single' );
			$full_attachment   = wp_get_attachment_image_src( $attachment_id, 'full' );
			$attachment_object = get_post( $attachment_id );
			$image             = $attachment ? current( $attachment ) : '';
			$image_link        = $full_attachment ? current( $full_attachment ) : '';
			$image_title       = get_the_title( $attachment_id );
			$image_alt         = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
			$image_caption     = $attachment_object->post_excerpt;
			$image_srcset      = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : false;
			$image_sizes       = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : false;

			if ( empty( $image_alt ) ) {
				$image_alt = $image_title;
			}
		} else {
			$image = $image_link = $image_title = $image_alt = $image_srcset = $image_sizes = $image_caption = '';
		}

		$availability      = $variation->get_availability();
		$availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . wp_kses_post( $availability['availability'] ) . '</p>';
		$availability_html = apply_filters( 'woocommerce_stock_html', $availability_html, $availability['availability'], $variation );

		return apply_filters( 'woocommerce_available_variation', array(
			'variation_id'           => $variation->variation_id,
			'variation_is_visible'   => $variation->variation_is_visible(),
			'variation_is_active'    => $variation->variation_is_active(),
			'is_purchasable'         => $variation->is_purchasable(),
			'display_price'          => $variation->get_display_price(),
			'display_regular_price'  => $variation->get_display_price( $variation->get_regular_price() ),
			'attributes'             => $variation->get_variation_attributes(),
			'image_src'              => $image,
			'image_link'             => $image_link,
			'image_title'            => $image_title,
			'image_alt'              => $image_alt,
			'image_caption'          => $image_caption,
			'image_srcset'			 => $image_srcset ? $image_srcset : '',
			'image_sizes'			 => $image_sizes ? $image_sizes : '',
			'price_html'             => apply_filters( 'woocommerce_show_variation_price', $variation->get_price() === "" || $this->get_variation_price( 'min' ) !== $this->get_variation_price( 'max' ), $this, $variation ) ? '<span class="price">' . $variation->get_price_html() . '</span>' : '',
			'availability_html'      => $availability_html,
			'sku'                    => $variation->get_sku(),
			'weight'                 => $variation->get_weight() . ' ' . esc_attr( get_option('woocommerce_weight_unit' ) ),
			'dimensions'             => $variation->get_dimensions(),
			'min_qty'                => 1,
			'max_qty'                => $variation->backorders_allowed() ? '' : $variation->get_stock_quantity(),
			'backorders_allowed'     => $variation->backorders_allowed(),
			'is_in_stock'            => $variation->is_in_stock(),
			'is_downloadable'        => $variation->is_downloadable() ,
			'is_virtual'             => $variation->is_virtual(),
			'is_sold_individually'   => $variation->is_sold_individually() ? 'yes' : 'no',
			'variation_description'  => $variation->get_variation_description(),
		), $this, $variation );
	}

	/**
	 * Sync variable product prices with the children lowest/highest prices.
	 */
	public function variable_product_sync( $product_id = '' ) {
		if ( empty( $product_id ) ) {
			$product_id = $this->id;
		}

		// Sync prices with children
		self::sync( $product_id );

		// Re-load prices
		$this->price = get_post_meta( $product_id, '_price', true );

		foreach ( array( 'price', 'regular_price', 'sale_price' ) as $price_type ) {
			$min_variation_id_key        = "min_{$price_type}_variation_id";
			$max_variation_id_key        = "max_{$price_type}_variation_id";
			$min_price_key               = "_min_variation_{$price_type}";
			$max_price_key               = "_max_variation_{$price_type}";
			$this->$min_variation_id_key = get_post_meta( $product_id, '_' . $min_variation_id_key, true );
			$this->$max_variation_id_key = get_post_meta( $product_id, '_' . $max_variation_id_key, true );
			$this->$min_price_key        = get_post_meta( $product_id, '_' . $min_price_key, true );
			$this->$max_price_key        = get_post_meta( $product_id, '_' . $max_price_key, true );
		}
	}

	/**
	 * Sync variable product stock status with children.
	 * @param  int $product_id
	 */
	public static function sync_stock_status( $product_id ) {
		$children = get_posts( array(
			'post_parent' 	=> $product_id,
			'posts_per_page'=> -1,
			'post_type' 	=> 'product_variation',
			'fields' 		=> 'ids',
			'post_status'	=> 'publish'
		) );

		$stock_status = 'outofstock';

		foreach ( $children as $child_id ) {
			$child_stock_status = get_post_meta( $child_id, '_stock_status', true );
			$child_stock_status = $child_stock_status ? $child_stock_status : 'instock';
			if ( 'instock' === $child_stock_status ) {
				$stock_status = 'instock';
				break;
			}
		}

		wc_update_product_stock_status( $product_id, $stock_status );
	}

	/**
	 * Sync the variable product's attributes with the variations.
	 */
	public static function sync_attributes( $product_id, $children = false ) {
		if ( ! $children ) {
			$children = get_posts( array(
				'post_parent' 	=> $product_id,
				'posts_per_page'=> -1,
				'post_type' 	=> 'product_variation',
				'fields' 		=> 'ids',
				'post_status'	=> 'any'
			) );
		}

		/**
		 * Pre 2.4 handling where 'slugs' were saved instead of the full text attribute.
		 * Attempt to get full version of the text attribute from the parent and UPDATE meta.
		 */
		if ( version_compare( get_post_meta( $product_id, '_product_version', true ), '2.4.0', '<' ) ) {
			$parent_attributes = array_filter( (array) get_post_meta( $product_id, '_product_attributes', true ) );

			foreach ( $children as $child_id ) {
				$all_meta = get_post_meta( $child_id );

				foreach ( $all_meta as $name => $value ) {
					if ( 0 !== strpos( $name, 'attribute_' ) ) {
						continue;
					}
					if ( sanitize_title( $value[0] ) === $value[0] ) {
						foreach ( $parent_attributes as $attribute ) {
							if ( $name !== 'attribute_' . sanitize_title( $attribute['name'] ) ) {
								continue;
							}
							$text_attributes = wc_get_text_attributes( $attribute['value'] );
							foreach ( $text_attributes as $text_attribute ) {
								if ( sanitize_title( $text_attribute ) === $value[0] ) {
									update_post_meta( $child_id, $name, $text_attribute );
									break;
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Sync the variable product with it's children.
	 */
	public static function sync( $product_id ) {
		global $wpdb;

		$children = get_posts( array(
			'post_parent' 	=> $product_id,
			'posts_per_page'=> -1,
			'post_type' 	=> 'product_variation',
			'fields' 		=> 'ids',
			'post_status'	=> 'publish'
		) );

		// No published variations - product won't be purchasable.
		if ( ! $children ) {
			update_post_meta( $product_id, '_price', '' );
			delete_transient( 'wc_products_onsale' );

			if ( is_admin() && 'publish' === get_post_status( $product_id ) ) {
				WC_Admin_Meta_Boxes::add_error( __( 'This variable product has no active variations. Add or enable variations to allow this product to be purchased.', 'woocommerce' ) );
			}

		// Loop the variations
		} else {

			// Set the variable product to be virtual/downloadable if all children are virtual/downloadable
			foreach ( array( '_downloadable', '_virtual' ) as $meta_key ) {
				$all_variations_yes = true;

				foreach ( $children as $child_id ) {
					if ( 'yes' != get_post_meta( $child_id, $meta_key, true ) ) {
						$all_variations_yes = false;
						break;
					}
				}

				update_post_meta( $product_id, $meta_key, ( true === $all_variations_yes ) ? 'yes' : 'no' );
			}

			// Main active prices
			$min_price            = null;
			$max_price            = null;
			$min_price_id         = null;
			$max_price_id         = null;

			// Regular prices
			$min_regular_price    = null;
			$max_regular_price    = null;
			$min_regular_price_id = null;
			$max_regular_price_id = null;

			// Sale prices
			$min_sale_price       = null;
			$max_sale_price       = null;
			$min_sale_price_id    = null;
			$max_sale_price_id    = null;

			foreach ( array( 'price', 'regular_price', 'sale_price' ) as $price_type ) {
				foreach ( $children as $child_id ) {
					$child_price = get_post_meta( $child_id, '_' . $price_type, true );

					// Skip non-priced variations
					if ( $child_price === '' ) {
						continue;
					}

					// Skip hidden variations
					if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
						$stock = get_post_meta( $child_id, '_stock', true );
						if ( $stock !== "" && $stock <= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
							continue;
						}
					}

					// Find min price
					if ( is_null( ${"min_{$price_type}"} ) || $child_price < ${"min_{$price_type}"} ) {
						${"min_{$price_type}"}    = $child_price;
						${"min_{$price_type}_id"} = $child_id;
					}

					// Find max price
					if ( $child_price > ${"max_{$price_type}"} ) {
						${"max_{$price_type}"}    = $child_price;
						${"max_{$price_type}_id"} = $child_id;
					}
				}

				// Store prices
				update_post_meta( $product_id, '_min_variation_' . $price_type, ${"min_{$price_type}"} );
				update_post_meta( $product_id, '_max_variation_' . $price_type, ${"max_{$price_type}"} );

				// Store ids
				update_post_meta( $product_id, '_min_' . $price_type . '_variation_id', ${"min_{$price_type}_id"} );
				update_post_meta( $product_id, '_max_' . $price_type . '_variation_id', ${"max_{$price_type}_id"} );
			}

			// Sync _price meta
			delete_post_meta( $product_id, '_price' );
			add_post_meta( $product_id, '_price', $min_price, false );
			add_post_meta( $product_id, '_price', $max_price, false );
			delete_transient( 'wc_products_onsale' );

			// Sync attributes
			self::sync_attributes( $product_id, $children );

			do_action( 'woocommerce_variable_product_sync', $product_id, $children );
		}
	}
}
