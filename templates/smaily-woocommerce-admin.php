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
	$cb_enabled              = intval( $result['enable_checkbox'] );
	$cb_auto_checked         = intval( $result['checkbox_auto_checked'] );
	$cb_order_selected       = $result['checkbox_order'];
	$cb_loc_selected         = $result['checkbox_location'];
}
$autoresponder_list  = DataHandler::get_autoresponder_list();
// get_autoresponder_list will return empty array only if error with current credentials.
$autoresponder_error = empty( $autoresponder_list ) && ! empty( $result['subdomain'] );
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
			<li>
				<a href="#checkout_subscribe" class="nav-tab">
					<?php echo esc_html__( 'Checkout Opt-in', 'smaily' ); ?>
				</a>
			</li>
		</ul>
		</div>
		<div class="message-display"></div>
		<?php if ( $autoresponder_error ) : ?>
		<div class="error-autoresponders error notice">
			<p>
			<?php
				esc_html_e(
					'There seems to be a problem with your connection to Smaily. Please revalidate your credentials!',
					'smaily'
				);
			?>
			</p>
		</div>
		<?php endif; ?>
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
								'To add a subscribe widget, use Widgets menu. Validate credentials before using.',
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
							'customer_group'   => __( 'Customer Group', 'smaily' ),
							'customer_id'      => __( 'Customer ID', 'smaily' ),
							'user_dob'         => __( 'Date Of Birth', 'smaily' ),
							'first_registered' => __( 'First Registered', 'smaily' ),
							'first_name'       => __( 'Firstname', 'smaily' ),
							'user_gender'      => __( 'Gender', 'smaily' ),
							'last_name'        => __( 'Lastname', 'smaily' ),
							'nickname'         => __( 'Nickname', 'smaily' ),
							'user_phone'       => __( 'Phone', 'smaily' ),
							'site_title'       => __( 'Site Title', 'smaily' ),
						];
						// Add options for select and select them if allready saved before.
						foreach ( $sync_options as $value => $name ) {
							$selected = in_array( $value, $sync_additional ) ? 'selected' : '';
							echo( "<option value='$value' $selected>$name</option>" );
						}
						?>
						</select>
						<small
							id="syncronize-help"
							class="form-text text-muted">
							<?php
							echo esc_html__(
								'Select fields you wish to synchronize along with subscriber email and store URL',
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
									'product_description' => __( 'Product Description', 'smaily' ),
									'product_sku'         => __( 'Product SKU', 'smaily' ),
									'product_quantity'    => __( 'Product Quantity', 'smaily' ),
									'product_base_price'  => __( 'Product Base Price', 'smaily' ),
									'product_price'       => __( 'Product Price', 'smaily' ),
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

		<div id="checkout_subscribe">
			<table class="form-table">
				<tbody>
					<tr class="form-field">
						<th scope="row">
							<label for="checkbox_description">
							<?php echo esc_html__( 'Subscription checkbox', 'smaily' ); ?>
							</label>
						</th>
						<td id="checkbox_description">
						<?php
						esc_html_e(
							'Customers can subscribe by checking "subscribe to newsletter" checkbox on checkout page.',
							'smaily'
						);
						?>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="checkbox_enable">
								<?php echo esc_html__( 'Enabled', 'smaily' ); ?>
							</label>
						</th>
						<td>
							<input
								name  ="enable_checkbox"
								type  ="checkbox"
								class ="smaily-toggle"
								id    ="checkbox-enable"
								<?php checked( $cb_enabled ); ?>/>
							<label for="checkbox-enable"></label>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="checkbox_auto_checked">
								<?php echo esc_html__( 'Checked by default', 'smaily' ); ?>
							</label>
						</th>
						<td>
							<input
								name  ="checkbox_auto_checked"
								type  ="checkbox"
								id    ="checkbox-auto-checked"
								<?php checked( $cb_auto_checked ); ?>
							/>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="checkbox_location">
							<?php echo esc_html__( 'Location', 'smaily' ); ?>
							</label>
						</th>
						<td id="smaily_checkout_display_location">
							<select id="cb-before-after" name="checkbox_order">
								<option value="before" <?php echo( 'before' === $cb_order_selected ? 'selected' : '' ); ?> >
									<?php echo esc_html__( 'Before', 'smaily' ); ?>
								</option>
								<option value="after" <?php echo( 'after' === $cb_order_selected ? 'selected' : '' ); ?>>
									<?php echo esc_html__( 'After', 'smaily' ); ?>
								</option>
							</select>
							<select id="checkbox-location" name="checkbox_location">
								<?php
								$cb_loc_available = array(
									'order_notes'                => __( 'Order notes', 'smaily' ),
									'checkout_billing_form'      => __( 'Billing form', 'smaily' ),
									'checkout_shipping_form'     => __( 'Shipping form', 'smaily' ),
									'checkout_registration_form' => __( 'Registration form', 'smaily' ),
								);
								// Display option and select saved value.
								foreach ( $cb_loc_available as $loc_value => $loc_translation ) : ?>
									<option
										value="<?php echo esc_html( $loc_value ); ?>"
										<?php echo $cb_loc_selected === $loc_value ? 'selected' : ''; ?>
									>
										<?php echo esc_html( $loc_translation ); ?>
									</option>
								<?php endforeach; ?>
							</select>
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
