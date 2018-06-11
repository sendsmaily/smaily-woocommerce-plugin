<style>.zoho_input{width: 25em !important;} small{ display:block; }</style>
<?php 
$result = get_smailey_results();
$syncronize_additional =  $result['syncronize_additional'];
$result = $result['result'];
if($result['username'] && $result['password'] ):
	require_once plugin_dir_path( __FILE__ ) .'../includes/class.smailey-admin.php';
	$autoresponders = $this->getAutoresponders();
endif;
?>
<h1>General settings </h1>
<form method="POST" name="zoho_crm" action="" class="validate" novalidate="novalidate">
<table class="form-table">
	<tbody>
	<tr class="form-field form-required">
		<th scope="row"><label for="enable">Enable Smaily extension plugin <span class="description"></span></label></th>
		<td><input name="enable" value="on" <?= ($result['enable'] == 'on')?'checked':'' ?> aria-required="true" maxlength="60" type="checkbox"></td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="subdomain">Subdomain <span class="description"></span></label></th>
		<td><input name="subdomain" class="zoho_input" value="<?= ($result['subdomain'])?$result['subdomain']:'' ?>" type="text">
		 <small id="emailHelp" class="form-text text-muted">For example "demo" from https://demo.sendsmaily.net/</small></td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="username">API username <span class="description"></span></label></th>
		<td><input name="username" class="zoho_input" value="<?= ($result['username'])?$result['username']:'' ?>" type="text">
		</td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="password">API password <span class="description"></span></label></th>
		<td><input name="password" class="zoho_input" value="<?= ($result['password'])?$result['password']:'' ?>" type="password">
		<small id="emailHelp" class="form-text text-muted"><a href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user" target="_blank">How to create API credentials?</a></small></td>
	</tr>
	<?php if (is_array($autoresponders) || is_object($autoresponders))
			{ ?>
			<tr class="form-field form-required">
			<th scope="row"><label for="password">Subscribe form<span class="description"></span></label></th>
			<td>To add a subscribe form, use shortcode [smaily_newsletter]</td>
	</tr>
	<?php } ?>
	<tr class="form-field form-required">
		<th scope="row"><label for="autoresponder">Autoresponder ID (confirmation email) <span class="description"></span></label></th>
		<td>
		<select name="autoresponder" class="zoho_input" >
			<option value="">-Select-</option>
		<?php	if (is_array($autoresponders) || is_object($autoresponders))
			{
				foreach ($autoresponders as $key=>$value)
				{ ?>
				<option value="<?= $key ?>" <?= autoresponder($key,$result['autoresponder']); ?>><?= $value ?></option>
		<?php	}
			}
		?>
		</select>
		<small id="emailHelp" class="form-text text-muted"><a href="http://help.smaily.com/en/support/solutions/articles/16000017234-creating-an-autoresponder" target="_blank">How to set up an autoresponder for confirmation emails?</a></small>
		</td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="syncronize_additional">Syncronize additional fields <span class="description"></span></label></th>
		<td>
		<select name="syncronize_additional[]" class="zoho_input" multiple style="height:250px;">
			<option value="subscription_type" <?= autoresponder('subscription_type',$syncronize_additional) ?>>Subscription Type</option>
			<option value="customer_group" <?= autoresponder('customer_group',$syncronize_additional) ?>>Customer Group</option>
			<option value="customer_id" <?= autoresponder('customer_id',$syncronize_additional) ?>>Customer ID</option>
			<option value="prefix" <?= autoresponder('prefix',$syncronize_additional) ?>>Prefix</option>
			<option value="firstname" <?= autoresponder('firstname',$syncronize_additional) ?>>Firstname</option>
			<option value="lastname" <?= autoresponder('lastname',$syncronize_additional) ?>>Lastname</option>
			<option value="gender" <?= autoresponder('gender',$syncronize_additional) ?>>Gender</option>
			<option value="birthday" <?= autoresponder('birthday',$syncronize_additional) ?>>Date Of Birth</option>
			<option value="website" <?= autoresponder('website',$syncronize_additional) ?>>Website</option>
			<option value="store" <?= autoresponder('store',$syncronize_additional) ?>>Store</option>
		</select>
		<small id="emailHelp" class="form-text text-muted">Select fields you wish to synchronize along with subscription data</small>
		</td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="rss_feed">Product RSS feed token <span class="description"></span></label></th>
		<td><input name="rss_feed" class="zoho_input" value="<?= ($result['rss_feed'])?$result['rss_feed']:'' ?>" type="text">
		<small id="emailHelp" class="form-text text-muted"><?= get_site_url() ?>/rss-feed?token=[TOKEN]. Copy this URL into your template editor's RSS block, replace [TOKEN] with token value in this field.</small>
</td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="rss_feed">How often do you want contacts syncronized? <span class="description"></span></label></th>
		<td>To schedule automatic sync, set up CRON in your hosting and use URL <?= get_site_url() ?>/smaily-cron/.</td>
	</tr>
	</tbody>
</table>
<p class="submit">
	<input name="smailey_submit" class="button button-primary" value="Update" type="submit">
</p>
</form>