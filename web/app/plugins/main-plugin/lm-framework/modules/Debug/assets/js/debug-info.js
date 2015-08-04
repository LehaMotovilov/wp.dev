(function($){
	"use strict";

	$(document).ready(function(){

		$('#test-email').on('submit',function(){
			var data = {
					action: 'lm_send_test_mail',
					email: $(this).find('#email').val(),
					nonce: ajax_object.ajax_nonce
				},
				$ajax_response = $('.ajax-response');

			// Clear ajax_response div.
			$ajax_response.html('').removeClass('error').removeClass('updated');

			// Do ajax post.
			$.post(ajax_object.ajax_url, data, function(response) {
				if (response.success) {
					$ajax_response
						.html('<p>' + response.data.wp_mail + '<br/>' + response.data.php_mail + '<br/>' + response.data.message + '</p>')
						.addClass('updated');
				} else {
					$ajax_response
						.html('<p>' + response.data + '</p>')
						.addClass('error');
				}
			});
			return false;
		});

	});

})(jQuery);