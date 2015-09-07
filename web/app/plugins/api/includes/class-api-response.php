<?php

/**
 * API Class for all responses.
 */
class LM_API_Response {

	// Default response JSON
	public $response_type = 'json';

	// Response status can be success or error
	private $status;

	// Code like HTTP status codes
	private $code;

	// Response body
	private $response_data;

	// Response error message
	private $error_message;

	// Handle all response codes and messages.
	protected static $messages = [
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',

		// Success 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',  // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',

		// 306 is deprecated but reserved
		307 => 'Temporary Redirect',

		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	];

	/**
	 * Setup response status.
	 * status:
	 * 1 - success
	 * 2 - error
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Setup response status code.
	 * @link https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	 * @link https://dev.twitter.com/overview/api/response-codes
	 */
	public function set_code( $code ) {
		$this->code = $code;
	}

	/**
	 * Setup response body message.
	 */
	public function set_response_data( $data ) {
		$this->response_data = $data;
	}

	/**
	 * Setup error_message.
	 */
	public function set_error_message( $message ) {
		$this->status = 'error';
		$this->error_message = $message;
	}

	/**
	 * Send a JSON/XML response.
	 * @todo json/xml
	 */
	public function send() {
		if ( $this->response_type == 'json' ) {
			if ( $this->status == 'error' ) {
				$resp['success'] = false;
				$resp['code'] = $this->code;
				$resp['data'] = $this->error_message;
			} else {
				$this->set_code( 200 );
				$resp['success'] = true;
				$resp['code'] = $this->code;
				$resp['data'] = $this->response_data;
			}

			// Set http response code based on $resp['code']
			$status = $this->setup_status_code( $this->code );
			@header( $status );

			// Send json body and die().
			wp_send_json( $resp );
		}
	}

	/**
	 * Return formated status for http header.
	 * @param int $code
	 * @return string
	 */
	private function setup_status_code( $code ) {
		$code = absint( $code );
		if ( isset( self::$messages[$code] ) && !empty( self::$messages[$code] ) ) {
			$status = sprintf( 'Status: %d %s', $code, self::$messages[$code] );
		} else {
			$status = sprintf( 'Status: %d', $code );
		}

		return $status;
	}

}