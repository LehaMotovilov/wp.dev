<?php
// Class with cool functions :)
use LM\Modules\Debug\Debug_Info;

$info = new Debug_Info();
$fields = $info->get_emails_info();
?>
<?php if ( !empty( $fields ) ): ?>
	<div class="ajax-response"></div>
	<div class="postbox debug-info">
		<h3><?php _e( 'Send test mail', 'lm-framework' ); ?></h3>
		<form action="<?php echo admin_url( '/tools.php?page=debug_info&tab=emails' ) ?>" name="test-email" id="test-email" method="post">
			<input type="email" name="email" id="email" placeholder="<?php esc_attr_e( 'Email', 'lm-framework' ) ?>" value="" required>
			<?php submit_button( __( 'Send', 'lm-framework' ), $type = 'primary', $name = 'submit', $wrap = false, $other_attributes = null ) ?>
		</form>
	</div>
	<div class="postbox debug-info">
		<table class="form-table">
			<tbody>
				<?php foreach ( $fields as $item ): ?>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( $item['key'] ); ?>"><?php echo $item['title']; ?></label></th>
						<td>
						<?php if ( is_array( $item['value'] ) ): ?>
							<pre><?php print_r( $item['value'] ); ?></pre>
						<?php else: ?>
							<?php echo $item['value']; ?>
						<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php else: ?>
	<div class="no-plugin-results"><?php _e( 'No information available.', 'lm-framework' ); ?></div>
<?php endif; ?>