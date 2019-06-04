<?php

use Smaily_Inc\Base\DataHandler;

// Get results from database and fill form if results allready available.
$settings = DataHandler::get_smaily_results();
if ( isset( $settings ) ) {
	$sync_additional         = $settings['syncronize_additional'];
	$cart_options            = $settings['cart_options'];
	$result                  = $settings['result'];
	$is_enabled              = $result['enable'];
	$cart_enabled            = $result['enable_cart'];
	$cart_autoresponder_name = $result['cart_autoresponder'];
	$cart_autoresponder_id   = $result['cart_autoresponder_id'];
}
$autoresponder_list = DataHandler::get_autoresponder_list();
?>
<div class="wrap smaily-settings">
	<h1>
		<span id="smaily-title">
			<span id="capital-s">S</span>maily
		</span>
		<?php echo esc_html__( 'Plugin Settings', 'smaily' ); ?>
		<div class="loader"></div>
	</h1>
	<div id="tabs">
		<div class="nav-tab-wrapper">
		<ul id="tabs-list">
			<li>
				<a href="#general" class="nav-tab nav-tab-active">
					<?php echo esc_html__( 'General', 'smaily' ); ?>
				</a>
			</li>
			<li>
				<a href="#customer" class="nav-tab">
					<?php echo esc_html__( 'Customer Synchronization', 'smaily' ); ?>
				</a>
			</li>
			<li>
				<a href="#cart" class="nav-tab">
					<?php echo esc_html__( 'Abandoned Cart', 'smaily' ); ?>
				</a>
			</li>
		</ul>
		</div>
		<div class="message-display"></div>

		<div id="general">
		<form method="POST" id="startupForm" action="">
			<?php wp_nonce_field( 'settings-nonce', 'nonce', false ); ?>
			<table class="form-table">
				<tbody>
					<tr class="form-field">
						<th scope="row">
							<label for="subdomain">
								<?php echo esc_html__( 'Subdomain', 'smaily' ); ?>
							</label>
						</th>
						<td>
						<input
							id="subdomain"
							name="subdomain"
							value="<?php echo ( $result['subdomain'] ) ? $result['subdomain'] : ''; ?>"
							type="text">
						<small id="subdomain-help" class="form-text text-muted">
							<?php
							printf(
								/* translators: 1: Openin strong tag 2: Closing strong tag */
								esc_html__(
									'For example %1$s"demo"%2$s from https://%1$sdemo%2$s.sendsmaily.net/',
									'smaily'
								),
								'<strong>',
								'</strong>'
							);
							?>
						</small>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="username">
							<?php echo esc_html__( 'API username', 'smaily' ); ?>
							</label>
						</th>
						<td>
						<input
							id="username"
							name="username"
							value="<?php echo ( $result['username'] ) ? $result['username'] : ''; ?>"
							type="text">
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="password">
							<?php echo esc_html__( 'API password', 'smaily' ); ?>
							</label>
						</th>
						<td>
						<input
							id="password"
							name="password"
							value="<?php echo ( $result['password'] ) ? $result['password'] : ''; ?>"
							type="password">
						<small id="password-help" class="form-text text-muted">
							<a
								href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user"
								target="_blank">
								<?php echo esc_html__( 'How to create API credentials?', 'smaily' ); ?>
							</a>
						</small>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="password">
							<?php echo esc_html__( 'Subscribe Widget', 'smaily' ); ?>
							</label>
						</th>
						<td>
							<?php
							echo esc_html__(
								'To add a subscribe widget, use Widgets menu. Fill out settings before using.',
								'smaily'
							);
							?>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label>
							<?php echo esc_html__( 'Product RSS feed', 'smaily' ); ?>
							</label>
						</th>
						<td>
							<strong>
								<?php echo esc_url( get_site_url() ); ?>/smaily-rss-feed
							</strong>
							<?php
							echo esc_html__(
								"Copy this URL into your template editor's RSS block, to receive RSS-feed.",
								'smaily'
							);
							?>
							</td>
					</tr>
				</tbody>
			</table>

			<?php if ( empty( $autoresponder_list ) ) : ?>
			<button
				id="validate-credentials-btn"
				type="submit" name="continue"
				class="button-primary">
				<?php echo esc_html__( 'Validate API information', 'smaily' ); ?>
			</button>
			<?php endif; ?>
		</form>
		</div>

		<div id="customer">
		<form  method="POST" id="advancedForm" action="">
			<table  class="form-table">
				<tbody>
				<tr class="form-field">
					<th scope="row">
						<label for="enable">
							<?php echo esc_html__( 'Enable Customer synchronization', 'smaily' ); ?>
						</label>
					</th>
					<td>
						<input
							name  ="enable"
							type  ="checkbox"
							<?php echo ( isset( $is_enabled ) && $is_enabled == 1 ) ? 'checked' : ' '; ?>
							class ="smaily-toggle"
							id    ="enable-checkbox" />
						<label for="enable-checkbox"></label>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row">
						<label for="syncronize_additional">
						<?php echo esc_html__( 'Syncronize additional fields', 'smaily' ); ?>
						</label>
					</th>
					<td>
						<select name="syncronize_additional[]"  multiple="multiple" style="height:250px;">
						<?php
						// All available option fields.
						$sync_options = [
							'store'            => __( 'Store Name', 'smaily' ),
							'customer_group'   => __( 'Customer Group', 'smaily' ),
							'customer_id'      => __( 'Customer ID', 'smaily' ),
							'first_registered' => __( 'First Registered', 'smaily' ),
							'first_name'       => __( 'Firstname', 'smaily' ),
							'last_name'        => __( 'Lastname', 'smaily' ),
							'nickname'         => __( 'Nickname', 'smaily' ),
							'user_dob'         => __( 'Date Of Birth', 'smaily' ),
							'user_gender'      => __( 'Gender', 'smaily' ),
							'user_url'         => __( 'Website', 'smaily' ),
							'user_phone'       => __( 'Phone', 'smaily' ),
						];
						// Add options for select and select them if allready saved before.
						foreach ( $sync_options as $value => $name ) {
							$selected = in_array( $value, $sync_additional ) ? 'selected' : "";
							echo( "<option value='$value' $selected>$name</option>" );
						}
						?>
						</select>
						<small
							id="syncronize-help"
							class="form-text text-muted">
							<?php
							echo esc_html__(
								'Select fields you wish to synchronize along with subscriber email',
								'smaily'
							);
							?>
						</small>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<div id="cart">
			<table class="form-table">
				<tbody>
					<tr class="form-field">
						<th scope="row">
							<label for="enable_cart">
							<?php echo esc_html__( 'Enable Abandoned Cart reminder', 'smaily' ); ?>
							</label>
						</th>
						<td>
							<input
								name ="enable_cart"
								type="checkbox"
								<?php echo ( isset( $cart_enabled ) && $cart_enabled == 1 ) ? 'checked' : ' '; ?>
								class="smaily-toggle"
								id="enable-cart-checkbox" />
							<label for="enable-cart-checkbox"></label>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="cart-autoresponder">
							<?php echo esc_html__( 'Cart Autoresponder ID', 'smaily' ); ?>
							</label>
						</th>
						<td>
							<select id="cart-autoresponders-list" name="cart_autoresponder"  >
								<?php
								if ( ! empty( $cart_autoresponder_name ) && ! empty( $cart_autoresponder_id ) ) {
									$cart_autoresponder = [
										'name' => $cart_autoresponder_name,
										'id'   => $cart_autoresponder_id,
									];
									echo '<option value="' .
										htmlentities( json_encode( $cart_autoresponder ) ) . '">' .
										esc_html( $cart_autoresponder_name ) .
										esc_html__( ' - (selected)', 'smaily' ) .
										'</option>';
								} else {
									echo '<option value="">' . esc_html__( '-Select-', 'smaily' ) . '</option>';
								}
								// Show all autoresponders from Smaily.
								if ( ! empty( $autoresponder_list ) && ! array_key_exists( 'empty', $autoresponder_list ) ) {
									foreach ( $autoresponder_list as $autoresponder ) {
										echo '<option value="' . htmlentities( json_encode( $autoresponder ) ) . '">' .
											esc_html( $autoresponder['name'] ) .
										'</option>';
									}
								}
								// Show info that no autoresponders available.
								if ( array_key_exists( 'empty', $autoresponder_list ) ) {
									echo '<option value="">' .
										esc_html__( 'No autoresponders created', 'smaily' ) .
										'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="cart_options">
							<?php echo esc_html__( 'Additional cart fields', 'smaily' ); ?>
							</label>
						</th>
						<td>
							<select name="cart_options[]"  multiple="multiple" style="height:250px;">
								<?php
								// All available option fields.
								$cart_fields = [
									'first_name'          => __( 'Customer First Name', 'smaily' ),
									'last_name'           => __( 'Customer Last Name', 'smaily' ),
									'product_name'        => __( 'Product Name', 'smaily' ),
									'product_description' => __( 'Product Description (long)', 'smaily' ),
									'product_description_short' => __( 'Product Description (short)', 'smaily' ),
									'product_sku'         => __( 'Product SKU', 'smaily' ),
									'product_quantity'    => __( 'Product Quantity', 'smaily' ),
									'product_subtotal'    => __( 'Product Row Subtotal', 'smaily' ),
								];
								// Add options for select and select them if allready saved before.
								foreach ( $cart_fields as $value => $name ) {
									$select = in_array( $value, $cart_options ) ? 'selected' : '';
									echo( "<option value='$value' $select>$name</option>" );
								}
								?>
							</select>
							<small id="cart-options-help" class="form-text text-muted">
							<?php
							echo esc_html__(
								'Select fields wish to send to Smaily template along with subscriber email and store url.',
								'smaily'
							);
							?>
							</small>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="cart-delay">
							<?php echo esc_html__( 'Cart cutoff time', 'smaily' ); ?>
							</label>
						</th>
						<td> <?php echo esc_html__( 'Consider cart abandoned after:', 'smaily' ); ?>
							<input	id="cart_cutoff"
									name="cart_cutoff"
									style="width:65px;"
									value="<?php echo ( $result['cart_cutoff'] ) ? $result['cart_cutoff'] : ''; ?>"
									type="number"
									min="10">
							<?php echo esc_html__( 'minute(s)', 'smaily' ); ?>
							<small id="cart-delay-help" class="form-text text-muted">
							<?php echo esc_html__( 'Minimum 10 minutes.', 'smaily' ); ?>
							</small>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		</div>
		<button type="submit" name="save" class="button-primary"> 
		<?php echo esc_html__( 'Save Settings', 'smaily' ); ?>
		</button>
	</form>
</div>
