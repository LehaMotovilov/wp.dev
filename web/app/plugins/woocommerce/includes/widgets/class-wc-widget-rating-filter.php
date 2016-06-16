<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rating Filter Widget and related functions.
 *
 *
 * @author   WooThemes
 * @category Widgets
 * @package  WooCommerce/Widgets
 * @version  2.6.0
 * @extends  WC_Widget
 */
class WC_Widget_Rating_Filter extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_rating_filter';
		$this->widget_description = __( 'Filter products by rating when viewing product archives and categories.', 'woocommerce' );
		$this->widget_id          = 'woocommerce_rating_filter';
		$this->widget_name        = __( 'WooCommerce Average Rating Filter', 'woocommerce' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Average Rating', 'woocommerce' ),
				'label' => __( 'Title', 'woocommerce' )
			)
		);
		parent::__construct();
	}

	/**
	 * Get current page URL for layered nav items.
	 * @return string
	 */
	protected function get_page_base_url() {
		if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
			$link = home_url();
		} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id('shop') ) ) {
			$link = get_post_type_archive_link( 'product' );
		} else {
			$link = get_term_link( get_query_var('term'), get_query_var('taxonomy') );
		}

		// Min/Max
		if ( isset( $_GET['min_price'] ) ) {
			$link = add_query_arg( 'min_price', wc_clean( $_GET['min_price'] ), $link );
		}

		if ( isset( $_GET['max_price'] ) ) {
			$link = add_query_arg( 'max_price', wc_clean( $_GET['max_price'] ), $link );
		}

		// Orderby
		if ( isset( $_GET['orderby'] ) ) {
			$link = add_query_arg( 'orderby', wc_clean( $_GET['orderby'] ), $link );
		}

		/**
		 * Search Arg.
		 * To support quote characters, first they are decoded from &quot; entities, then URL encoded.
		 */
		if ( get_search_query() ) {
			$link = add_query_arg( 's', rawurlencode( htmlspecialchars_decode( get_search_query() ) ), $link );
		}

		// Post Type Arg
		if ( isset( $_GET['post_type'] ) ) {
			$link = add_query_arg( 'post_type', wc_clean( $_GET['post_type'] ), $link );
		}

		// All current filters
		if ( $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes() ) {
			foreach ( $_chosen_attributes as $name => $data ) {
				$filter_name = sanitize_title( str_replace( 'pa_', '', $name ) );
				if ( ! empty( $data['terms'] ) ) {
					$link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
				}
				if ( 'or' == $data['query_type'] ) {
					$link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
				}
			}
		}

		return $link;
	}

	/**
	 * Count products after other filters have occured by adjusting the main query.
	 * @param  int $rating
	 * @return int
	 */
	protected function get_filtered_product_count( $rating ) {
		global $wpdb;

		$tax_query  = WC_Query::get_main_tax_query();
		$meta_query = WC_Query::get_main_meta_query();

		// Unset current rating filter
		foreach ( $meta_query as $key => $query ) {
			if ( ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		// Set new rating filter
		$meta_query[] = array(
			'key'           => '_wc_average_rating',
			'value'         => $rating,
			'compare'       => '>=',
			'type'          => 'DECIMAL',
			'rating_filter' => true
		);

		$meta_query = new WP_Meta_Query( $meta_query );
		$tax_query  = new WP_Tax_Query( $tax_query );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$sql  = "SELECT COUNT( {$wpdb->posts}.ID ) FROM {$wpdb->posts} ";
		$sql .= $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= " WHERE {$wpdb->posts}.post_type = 'product' AND {$wpdb->posts}.post_status = 'publish' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		return absint( $wpdb->get_var( $sql ) );
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $wp_the_query;

		if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
			return;
		}

		if ( ! $wp_the_query->post_count ) {
			return;
		}

		ob_start();

		$found      = false;
		$min_rating = isset( $_GET['min_rating'] ) ? absint( $_GET['min_rating'] ) : '';

		$this->widget_start( $args, $instance );

		echo '<ul>';

		for ( $rating = 4; $rating >= 1; $rating-- ) {
			$count = $this->get_filtered_product_count( $rating );

			if ( ! $count ) {
				continue;
			}

			$found = true;
			$link  = $this->get_page_base_url();
			$link  = $min_rating !== $rating ? add_query_arg( 'min_rating', $rating, $link ) : $link;

			echo '<li class="wc-layered-nav-rating ' . ( ! empty( $_GET['min_rating'] ) && $rating === absint( $_GET['min_rating'] ) ? 'chosen' : '' ) . '">';

			echo '<a href="' . esc_url( apply_filters( 'woocommerce_rating_filter_link', $link ) ) . '">';

			echo '<span class="star-rating" title="' . esc_attr( sprintf( __( 'Rated %s and above', 'woocommerce' ), $rating ) ). '">
					<span style="width:' . esc_attr( ( $rating / 5 ) * 100 ) . '%">' . sprintf( __( 'Rated %s and above', 'woocommerce'), $rating ) . '</span>
				</span> (' . esc_html( $count ) . ')';

			echo '</a>';

			echo '</li>';
		}

		echo '</ul>';

		$this->widget_end( $args );

		if ( ! $found ) {
			ob_end_clean();
		} else {
			echo ob_get_clean();
		}
	}
}
