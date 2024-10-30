<?php
/**
 * Option page of the plugin
 *
 * @package Elementor_Benchmark
 */

?>

<div style="padding:0 20px;" class="wrap fs-section fs-full-size-wrapper streak_crm_page">
  <h2 class="nav-tab-wrapper">
    <a href="admin.php?page=benchmarkemail-elementor-integration" class="nav-tab fs-tab nav-tab-active home">Settings</a>
  </h2>
	<h1>Integration Settings</h1>
	<h4>You can get these information from <a target="_blank" href="https://ui.benchmarkemail.com/Integrate#API">Benchmark Documentation</a> or From the <a  target="_blank" href="https://wisersteps.com/docs/elementor-pro-form-widget-benchmark-email-integration/setup-the-plugin/">Plugin Documentation</a></h4>



	<form method="post" action="options.php">
		<?php
			settings_fields( 'ebma_option_page' );
			do_settings_sections( 'ebma_option_page' );
		?>
		<style>
			.benchmarkemail_page p{
				display: inline;
				background: #0ea52f;
				padding: 5px 10px;
				color: #fff;
			}
			.benchmarkemail_page p.error{
				background: #d43636;
			}
		</style>
		<table class="form-table benchmarkemail_page">
			<tbody>

				<tr>
				<th scope="row"><label for="ebma_api_key"><?php esc_html_e( 'API Key', $this->plugin_name ); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="ebma_api_key" value="<?php echo esc_attr( get_option( 'ebma_api_key' ) ); ?>">
						<?php if ( ! empty( $this->validate_api_key() ) ) { ?>
						<p>Connected</p>
					<?php } elseif ( ! $this->validate_api_key() && get_option( 'ebma_api_key' ) !== '' ) { ?>
						<p class="error">Not connected</p>
					<?php } ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button(); ?>
	</form>
</div>

