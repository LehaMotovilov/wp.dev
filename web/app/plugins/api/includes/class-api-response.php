<?php

/**
 * API Class for all responses.
 */
class LM_API_Response {

	public $response_type = 'json';

	private $status;
	private $response_data;
	private $error_message;

	public function set_status( $status ) {
		$this->status = $status;
	}

	public function set_response_data( $data ) {
		$this->response_data = $data;
	}

	public function set_error_message( $message ) {
		$this->status = 'error';
		$this->error_message = $message;
	}

	/**
	 * @todo json/xml
	 */
	public function send() {
		if ( $this->response_type == 'json' ) {
			if ( $this->status == 'error' ) {
				wp_send_json_error( $this->error_message );
			} else {
				wp_send_json_success( $this->response_data );
			}
		}
	}

}