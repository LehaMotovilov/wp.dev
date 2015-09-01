<?php

/**
 *
 */
class Posts {

	private $allowed_post_types = [ 'post' ];
	private $page = 1;
	private $posts_per_page = 10;
	private $post_type = 'post';

	/**
	 * Return array with posts and pagination params.
	 * @return array
	 */
	public function get_index() {
		// Setup defaults
		$page = $this->page;
		$posts_per_page = $this->posts_per_page;
		$post_type = $this->post_type;

		// Get page for pagination
		if ( isset( $_GET['page'] )&& !empty( $_GET['page'] ) && is_numeric( $_GET['page'] ) ) {
			$page = absint( $_GET['page'] );
		}

		// Get post_per_page param.
		if ( isset( $_GET['posts_per_page'] ) && !empty( $_GET['posts_per_page'] ) && is_numeric( $_GET['posts_per_page'] ) ) {
			$posts_per_page = absint( $_GET['posts_per_page'] );
		}

		// Get post_type param.
		if ( isset( $_GET['post_type'] ) && !empty( $_GET['post_type'] ) && is_string( $_GET['post_type'] ) ) {
			if ( in_array( $_GET['post_type'], $this->allowed_post_types ) ) {
				$post_type = sanitize_text_field( $_GET['post_type'] );
			} else {
				return new WP_Error( 'error', 'post_type value not allowed' );
			}
		}

		// Run query
		$query = new WP_Query( [
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => $posts_per_page,
			'paged' => absint( $page ),
			'no_found_rows' => false,
			'cache_results' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
		] );

		return [
			'pagination' => [
				'total' => $query->found_posts,
				'posts_per_page' => $query->query_vars['posts_per_page'],
				'max_num_pages' => $query->max_num_pages,
				'page' => $query->query_vars['paged']
			],
			'posts' => $query->posts,
		];
	}

}
