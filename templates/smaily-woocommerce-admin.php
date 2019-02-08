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
	$autoresponder_name      = $result['autoresponder'];
	$autoresponder_id        = $result['autoresponder_id'];
	$cart_autoresponder_name = $result['cart_autoresponder'];
	$cart_autoresponder_id   = $result['cart_autoresponder_id'];
}
$autoresponder_list = DataHandler::get_autoresponder_list();
?>
<div class="wrap">
	<h1><span id="smaily-title"><span id="capital-s">S</span>maily</span> Plugin Settings
		<div class="loader"></div>
	</h1>
	<div id="tabs">
		<div class="nav-tab-wrapper">
		<ul id="tabs-list">
			<li><a href="#general" class="nav-tab nav-tab-active">General</a></li>
			<li><a href="#customer" class="nav-tab">Customer Synchronization</a></li>
			<li><a href="#cart" class="nav-tab">Abandoned Cart</a></li>
		</ul>
		</div>
		<div class="message-display"></div>

		<div id="general">
		<form method="POST" id="startupForm" action="">
			<?php wp_nonce_field( 'settings-nonce', 'nonce', false ); ?>
			<table class="form-table">
				<tbody>
					<tr class="form-field">
						<th scope="row"><label for="subdomain">Subdomain </label></th>
						<td><input id="subdomain" name="subdomain" value="<?php echo ( $result['subdomain'] ) ? $result['subdomain'] : ''; ?>" type="text">
						<small id="subdomain-help" class="form-text text-muted">
							For example <strong>"demo"</strong> from https://<strong>demo</strong>.sendsmaily.net/
						</small>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="username">API username </label></th>
						<td><input id="username" name="username" value="<?php echo ( $result['username'] ) ? $result['username'] : ''; ?>" type="text">
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="password">API password </label></th>
						<td><input id="password" name="password" value="<?php echo ( $result['password'] ) ? $result['password'] : '';?>" type="password">
						<small id="password-help" class="form-text text-muted">
							<a href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user" target="_blank">
								How to create API credentials?
							</a>
						</small>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="password">Subscribe Widget</label></th>
						<td>To add a subscribe widget, use Widgets menu. Fill out settings before using.</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label>Product RSS feed</label></th>
						<td><strong><?php echo esc_url( get_site_url() ); ?>/smaily-rss-feed</strong> Copy this URL into your template editor's RSS block, to receive RSS-feed.</td>
					</tr>
				</tbody>
			</table>

			<?php if ( empty( $autoresponder_list ) ) : ?>
			<button id="validate-credentials-btn" type="submit" name="continue" class="button-primary"> Validate API information </button>
			<?php endif; ?>
		</form>
		</div>

		<div id="customer">
		<form  method="POST" id="advancedForm" action="">
			<table  class="form-table">
				<tbody>
				<tr class="form-field">
					<th scope="row"><label for="enable">Enable Customer synchronization </label></th>
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
					<th scope="row"><label for="autoresponder">Autoresponder ID </label></th>
					<td>
						<select id="autoresponders-list" name="autoresponder"  >
							<?php
							// Show selected autoresponder.
							if ( ! empty( $autoresponder_name ) && ! empty( $autoresponder_id ) ) {
								$autoresponder = [ 'name' => $autoresponder_name, 'id' => $autoresponder_id ];
								echo '<option value="' . htmlentities( json_encode( $autoresponder ) ) . '">' . $autoresponder_name . ' - (selected)</option>';
							} else {
								echo '<option value="">-Select-</option>';
							}
							// Show all autoresponders from Smaily.
							if ( ! empty( $autoresponder_list ) && ! array_key_exists( 'empty', $autoresponder_list ) ) {
								foreach ( $autoresponder_list as $autoresponder ) {
									echo '<option value="' . htmlentities( json_encode( $autoresponder ) ) . '">' . $autoresponder['name'] . '</option>';
								}
							}
							// Show info that no autoresponders available.
							if ( array_key_exists( 'empty', $autoresponder_list ) ) {
								echo '<option value="">No autoresponders created</option>';
							}
							?>
						</select>
						<small id="autoresponder-help" class="form-text text-muted"><a href="http://help.smaily.com/en/support/solutions/articles/16000017234-creating-an-autoresponder" target="_blank">How to set up an autoresponder for confirmation emails?</a></small>
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="syncronize_additional">Syncronize additional fields </label></th>
					<td>
						<select name="syncronize_additional[]"  multiple="multiple" style="height:250px;">
						<?php
						// All available option fields.
						$sync_options = [
							'store'            => 'Store Name',
							'customer_group'   => 'Customer Group',
							'customer_id'      => 'Customer ID',
							'first_registered' => 'First Registered',
							'first_name'       => 'Firstname',
							'last_name'        => 'Lastname',
							'nickname'         => 'Nickname',
							'user_dob'         => 'Date Of Birth',
							'user_gender'      => 'Gender',
							'user_url'         => 'Website',
							'user_phone'       => 'Phone',
						];
						// Add options for select and select them if allready saved before.
						foreach ( $sync_options as $value => $name ) {
							$selected = in_array( $value, $sync_additional ) ? 'selected' : "";
							echo( "<option value='$value' $selected>$name</option>" );
						}
						?>
						</select>
						<small id="syncronize-help" class="form-text text-muted">Select fields you wish to synchronize along with subscriber email</small>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<div id="cart">
			<table class="form-table">
				<tbody>
					<tr class="form-field">
						<th scope="row"><label for="enable_cart">Enable Abandoned Cart reminder</label></th>
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
						<th scope="row"><label for="cart-autoresponder">Cart Autoresponder ID </label></th>
						<td>
							<select id="cart-autoresponders-list" name="cart_autoresponder"  >
								<?php
								if ( ! empty( $cart_autoresponder_name ) && ! empty( $cart_autoresponder_id ) ) {
									$cart_autoresponder = [ 'name' => $cart_autoresponder_name, 'id' => $cart_autoresponder_id ];
									echo '<option value="' . htmlentities( json_encode( $cart_autoresponder ) ) . '">' . $cart_autoresponder_name . ' - (selected)</option>';
								} else {
									echo '<option value="">-Select-</option>';
								}
								// Show all autoresponders from Smaily.
								if ( ! empty( $autoresponder_list ) && ! array_key_exists( 'empty', $autoresponder_list ) ) {
									foreach ( $autoresponder_list as $autoresponder ) {
										echo '<option value="' . htmlentities( json_encode( $autoresponder ) ) . '">' . $autoresponder['name'] . '</option>';
									}
								}
								// Show info that no autoresponders available.
								if ( array_key_exists( 'empty', $autoresponder_list ) ) {
									echo '<option value="">No autoresponders created</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="cart_options">Additional cart fields </label></th>
						<td>
							<select name="cart_options[]"  multiple="multiple" style="height:250px;">
								<?php
								// All available option fields.
								$cart_fields = [
									'first_name'                => 'Customer First Name',
									'last_name'                 => 'Customer Last Name',
									'product_name'              => 'Product Name',
									'product_description'       => 'Product Description (long)',
									'product_description_short' => 'Product Description (short)',
									'product_sku'               => 'Product SKU',
									'product_quantity'          => 'Product Quantity',
									'product_subtotal'          => 'Product Row Subtotal',
								];
								// Add options for select and select them if allready saved before.
								foreach ( $cart_fields as $value => $name ) {
									$select = in_array( $value, $cart_options ) ? 'selected' : "";
									echo( "<option value='$value' $select>$name</option>" );
								}
								?>
							</select>
							<small id="cart-options-help" class="form-text text-muted">Select fields wish to send to Smaily template along with subscriber email and store url.</small>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="cart-delay">Cart cutoff time</label></th>
						<td> Consider cart abandoned after:
							<input	id="cart_cutoff"
									name="cart_cutoff"
									style="width:65px;"
									value="<?php echo ( $result['cart_cutoff'] ) ? $result['cart_cutoff'] : ''; ?>"
									type="number"
									min="10">
							minute(s)
							<small id="cart-delay-help" class="form-text text-muted">Minimum 10 minutes.</small>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label for="cart-delay">Email delay</label></th>
						<td> Send abandoned cart email after :
							<input	id="cart_delay"
									name="cart_delay"
									style="width:65px;"
									value="<?php echo ( $result['cart_delay'] ) ? $result['cart_delay'] : ''; ?>"
									type="number"
									min="1">
							hour(s)
							<small id="cart-delay-help" class="form-text text-muted">Minimum 1 hour.</small>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		</div>
		<button type="submit" name="save" class="button-primary"> Save Settings </button>
	</form>
</div>
