<?php

/**
 * API Class for all responses.
 */
class LM_API_Response {

	/**
	 * Send response.
	 * @param @response array Response data
	 */
	public static function response( $response ) {
		// Mitigate possible JSONP Flash attacks
		// http://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/
		self::send_header( 'X-Content-Type-Options', 'nosniff' );

		if ( $response['status'] == 'error' ) {
			wp_send_json_error( $response['error_message'] );
		} else {
			wp_send_json_success( $response['data'] );
		}
	}

	/**
	 * Send a HTTP header
	 *
	 * @param string $key Header key
	 * @param string $value Header value
	 */
	protected function send_header( $key, $value ) {
		// Sanitize as per RFC2616 (Section 4.2):
		//   Any LWS that occurs between field-content MAY be replaced with a
		//   single SP before interpreting the field value or forwarding the
		//   message downstream.
		$value = preg_replace( '/\s+/', ' ', $value );
		header( sprintf( '%s: %s', $key, $value ) );
	}

}