<?php
/**
 *
 */
class Posts {

	private $allowed_post_types = [ 'post' ];
	private $page = 1;
	private $posts_per_page = 5;
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
		if ( isset( $_GET['page'] ) && !empty( $_GET['page'] ) && is_numeric( $_GET['page'] ) ) {
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
				return new WP_Error( '400', 'post_type value not allowed' );
			}
		}

		// Run query
		$query = new WP_Query( [
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => $posts_per_page,
			'paged' => absint( $page ),
			'ignore_sticky_posts' => true,
			'no_found_rows' => false,
			'cache_results' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
		] );

		return [
			'pagination' => [
				'posts_total' => $query->found_posts,
				'posts_per_page' => $query->query_vars['posts_per_page'],
				'max_num_pages' => $query->max_num_pages,
				'page' => $query->query_vars['paged']
			],
			'posts' => $query->posts,
		];
	}

	/**
	 * Insert new posts to DB.
	 * @return array
	 */
	public function post_index() {
		// If empty posts?
		if ( ! isset( $_POST['posts'] ) || empty( $_POST['posts'] ) ) {
			return new WP_Error( '400', 'You forget about posts array.' );
		}

		$inserted_posts = [];
		foreach ( $_POST['posts'] as $post ) {
			if ( ! is_wp_error( $post_id = $this->insert_post( $post ) ) ) {
				$inserted_posts[] = [
					'post_id' => $post_id,
					'post_url' => get_permalink( absint( $post_id ) )
				];
			}
		}

		return [
			'total_from_post' => count( $_POST['posts'] ),
			'total_inserted' => count( $inserted_posts ),
			'inserted_posts' => $inserted_posts
		];
	}

	/**
	 * Return single post based on post_id from GET.
	 * @return array
	 */
	public function get_single() {
		// Get post_id param.
		if ( isset( $_GET['post_id'] ) && !empty( $_GET['post_id'] ) && is_numeric( $_GET['post_id'] ) ) {
			// We don't trust params from somewhere..
			// So use absint() :)
			$post_id = absint( $_GET['post_id'] );
		} else {
			return new WP_Error( '400', 'You forget about post_id param.' );
		}

		// Get post can return WP_Post|array|null
		$post = get_post( $post_id, $output = OBJECT );

		// If not found
		if ( empty( $post ) || is_wp_error( $post ) ) {
			return new WP_Error( '404', 'Post not found. Try another post_id.' );
		}

		return [
			'post' => $post
		];
	}

	/**
	 * Insert new post to DB.
	 * @return array
	 */
	public function post_single() {
		// If empty title?
		if ( ! isset( $_POST['title'] ) || empty( $_POST['title'] ) ) {
			return new WP_Error( '400', 'You forget about title param.' );
		} else {
			$post['title'] = $_POST['title'];
		}

		// If empty content?
		if ( ! isset( $_POST['content'] ) || empty( $_POST['content'] ) ) {
			return new WP_Error( '400', 'You forget about content param.' );
		} else {
			$post['content'] = $_POST['content'];
		}

		$inserted_posts = [];
		if ( ! is_wp_error( $post_id = $this->insert_post( $post ) ) ) {
			$inserted_posts[] = [
				'post_id' => $post_id,
				'post_url' => get_permalink( absint( $post_id ) )
			];
		}

		return [
			'total_from_post' => count( $_POST['title'] ),
			'total_inserted' => count( $inserted_posts ),
			'inserted_posts' => $inserted_posts
		];
	}

	/**
	 * Update single post.
	 * @return array
	 */
	public function put_single() {

	}

	/**
	 * Delete one post form DB.
	 * @return array
	 */
	public function delete_single() {
		$_request = LM_API_Helper::get_put_content();
		if ( isset( $_request['post_id'] ) && !empty( $_request['post_id'] ) && is_numeric( $_request['post_id'] ) ) {
			// We don't trust params from somewhere..
			// So use absint() :)
			$post_id = absint( $_request['post_id'] );
		} else {
			return new WP_Error( '400', 'You forget about post_id param.' );
		}


		// Get post can return WP_Post|array|null
		$post = get_post( $post_id, $output = OBJECT );

		// If not found
		if ( empty( $post ) || is_wp_error( $post ) ) {
			return new WP_Error( '404', 'Post not found. Try another post_id.' );
		}

		// Try to delete post.
		if ( ! wp_delete_post( $post_id, $force_delete = true ) ) {
			return new WP_Error( '500', 'Internal error.' );
		}

		return [
			'message' => sprintf( 'Post "%d" deleted.', $post_id )
		];
	}

	/**
	 * Wrapper for wp_insert_post()
	 * @param array $post
	 * @return int|object Returns post_id on success or WP_Error on fail.
	 */
	private function insert_post( $post ) {
		$new_post = [
			'post_title' => $post['title'],
			'post_content' => $post['content'],
			'post_type' => 'post',
			'post_status' => 'publish',
			'ping_status' => 'closed',
			'comment_status' => 'closed'
		];
		return wp_insert_post( $new_post, $wp_error = true );
	}

}
