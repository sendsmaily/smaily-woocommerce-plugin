<?php
/*
Plugin Name: WooCommerce Smaily plugin
Description: Smaily email marketing and automation extension plugin for WooCommerce (set up opt-in form, client sync and output RSS-feed for easy product import into template.
Version: 1.0.0.
Author: Esteplogic
Author URI: https://esteplogic.com
Text Domain: smaily
*/

/**
 * Check if WooCommerce is active
 **/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( !defined( 'MyPlugin_DIR' ) ) define( 'MyPlugin_DIR', __DIR__ );
define( 'SMAILEY__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
require_once( SMAILEY__PLUGIN_DIR . 'includes/class.smailey-admin.php' );
require_once( SMAILEY__PLUGIN_DIR . 'includes/class.smailey-front.php' );
register_activation_hook ( __FILE__, 'on_activate' );
register_activation_hook ( __FILE__, 'smailey_install_data' );
register_uninstall_hook(__FILE__,'on_deactivate');
require( ABSPATH . WPINC . '/pluggable.php' );

wp_set_internal_encoding();

do_action( 'plugins_loaded' );

wp_functionality_constants( );

wp_magic_quotes();

do_action( 'sanitize_comment_cookies' );

$wp_the_query = new WP_Query();

$wp_query =& $wp_the_query;

$GLOBALS['wp_rewrite'] = new WP_Rewrite();
add_action( 'plugins_loaded', 'smailey_init' );

if (  in_array(  'woocommerce/woocommerce.php',  apply_filters( 'active_plugins', get_option( 'active_plugins' ) )  ) ) {
}else{
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action('admin_notices', 'smailey_plugin_admin_notices');
				flush_rewrite_rules();

}

function smailey_plugin_admin_notices() {
        echo "<div class='update-message notice inline notice-warning notice-alt'><p> Woocommerce  Smaily not able to acticate . Woocommerce needed to function properly</p></div>";
}

function smailey_rewrite_rule() {
		flush_rewrite_rules();

	  add_rewrite_tag("%rss-feed%", '([^/]*)');
	  add_rewrite_rule(
        'rss-feed/?',
        'wp-content/plugins/Smaily/page-rss-feed.php',
        'top'
    );
	
	  add_rewrite_tag("%smaily-cron%", '([^/]*)');
	  add_rewrite_rule(
        'smaily-cron/?',
        'wp-content/plugins/Smaily/page-smailey-cron.php',
        'top'
    );
}
	add_action( 'init', 'smailey_rewrite_rule' );

 function smailey_init(){
	$smailey_admin =  Smailey_Admin::instance();
	$smailey_front =  Smailey_Front::instance();
	$smailey_admin->init();	
	$smailey_front->init();
	//$TemplateFileTargetURL = get_stylesheet_directory() . '/page-rss-feed.php';
	//if ( !file_exists( $TemplateFileTargetURL ) ) CopyFile();
	//if ( get_page_by_title( 'RSS Feed', $output, 'page' ) == NULL ) do_insert(); 
}

function pageid(){
		$pageid = get_page_by_title( 'RSS Feed', $output, 'page' );
		$page['page_id'] = $pageid->ID;
		$page['guid'] = $pageid->guid;
	return $page;
}


 function samiley_newsletter() {
		require_once plugin_dir_path( __FILE__ ) . 'frontend/smailey_page.php';

	}
	// Register and load the widget
function smaily_widget() {
    register_widget( 'smaily_widget' );
}

if (  in_array(  'Smaily/smailey.php',  apply_filters( 'active_plugins', get_option( 'active_plugins' ) )  ) && is_enable() ) {	
	add_shortcode( 'smaily_newsletter','samiley_newsletter');


add_action( 'widgets_init', 'smaily_widget' );
}
// Creating the widget 
class smaily_widget extends WP_Widget {
 
function __construct() {
parent::__construct(
 
// Base ID of your widget
'smaily_widget', 
 
// Widget name will appear in UI
__('Smaily Newsletter', 'smaily_widget'), 
 
// Widget description
array( 'description' => __( 'Smaily Newsletter widget', 'smaily_widget' ), ) 
);
}
 
// Creating widget front-end
 
public function widget( $args, $instance ) {
$title = apply_filters( 'widget_title', $instance['title'] );
 
// before and after widget arguments are defined by themes
echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];
 
// This is where you run the code and display the output
		require plugin_dir_path( __FILE__ ) . 'frontend/smailey_page.php';
echo $args['after_widget'];
}
         
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( '', 'wpb_widget_domain' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php 
}
     
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
return $instance;
}
} // Class wpb_widget ends here




    /* function CopyFile() {

      $TemplateFileSourceURL = MyPlugin_DIR . '/page-rss-feed.php'; 
      $TemplateFileTargetURL = get_stylesheet_directory() . '/page-rss-feed.php'; 

      if ( !file_exists( $TemplateFileSourceURL ) ) {
        return FALSE;
      }

      $GetTemplate = file_get_contents( $TemplateFileSourceURL );
      if ( !$GetTemplate ) {
        return FALSE;
      }

      $WriteTemplate = file_put_contents( $TemplateFileTargetURL, $GetTemplate );
      if ( !$WriteTemplate ) {
        return FALSE;
      }
      return TRUE;
    }	 */

function on_activate() {
    global $wpdb;
    $samiley = "
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}samiley` (
              `id` int(11) NOT NULL AUTO_INCREMENT ,
              `enable` varchar(10) DEFAULT NULL,
              `subdomain` varchar(255) DEFAULT NULL,
              `username` varchar(255) DEFAULT NULL,
              `password` varchar(255) DEFAULT NULL,
              `autoresponder` varchar(255) DEFAULT NULL,
              `syncronize_additional` varchar(255) DEFAULT NULL,
              `rss_feed` varchar(255) DEFAULT NULL,
              `syncronize` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ";

    dbDelta( $samiley );
	
	$samiley_newsletter = "
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}samiley_newsletter` (
              `id` int(11) NOT NULL AUTO_INCREMENT ,
              `user_id` int(11) DEFAULT NULL,
              `email` varchar(255) DEFAULT NULL,
              `name` varchar(255) DEFAULT NULL,
              `subscription_type` varchar(255) DEFAULT NULL,
              `customer_group` varchar(255) DEFAULT NULL,
              `customer_id` varchar(255) DEFAULT NULL,
              `prefix` varchar(255) DEFAULT NULL,
              `firstname` varchar(255) DEFAULT NULL,
              `lastname` varchar(255) DEFAULT NULL,
              `gender` varchar(255) DEFAULT NULL,
              `birthday` varchar(255) DEFAULT NULL,
              `store` varchar(255) DEFAULT NULL,
              `website` varchar(255) DEFAULT NULL,
              `autoresponder` varchar(255) DEFAULT NULL,
              `status` varchar(255) DEFAULT NULL,
              `created_at` timestamp,
			  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ";

    dbDelta( $samiley_newsletter );
}

function smailey_install_data() {
	global $wpdb;
	$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}samiley",'ARRAY_A');
	if(empty($result)){
		$table_name = $wpdb->prefix . 'samiley';	
		$wpdb->insert( $table_name, array( 'enable'=>'off' ) );	
	}
}

function on_deactivate(){
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}samiley`");
    $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}samiley_newsletter`");
}


/**
*Helper Functions
*
**/

function autoresponder($val,$arr){
	if(is_array($arr))
		return (!empty($arr))?((in_array($val,@$arr))?'selected':''):'';
	elseif(!empty($arr))
		return (!empty($arr))?(($arr == $val)?'selected':''):'';
	else
		return 'selected';


}

function get_smailey_results(){
global $wpdb;
$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}samiley",'ARRAY_A');
$result = $result[0];
$syncronize_additional = unserialize($result['syncronize_additional']);
return compact('result','syncronize_additional');
}
function rss_token(){
	$result = get_smailey_results();
	return $result['result']['rss_feed'];
}
function is_enable(){
	$result = get_smailey_results();
	return ($result['result']['enable'] == 'on')?true:false;
}

function subscribed($autoresponder_id,$email,$extra){
	global $wpdb;
	$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}samiley_newsletter where email='$email'",'ARRAY_A');
	if(empty($result)){
		$date = date('Y-m-d H:i:s');
		$insert = $wpdb->query("INSERT INTO `{$wpdb->prefix}samiley_newsletter`(`user_id`, `email`,`name`,`subscription_type`,`customer_group`,`customer_id`,`prefix`,`firstname`,`lastname`,`gender`,`birthday`,`store`,`website`,`autoresponder`,`status`,`created_at`) VALUES ('$extra[customer_id]', '$email','$extra[name]','$extra[subscription_type]','$extra[customer_group]','$extra[customer_id]','$extra[prefix]','$extra[firstname]','$extra[lastname]','$extra[gender]','$extra[birthday]','$extra[store]','$extra[website]','$autoresponder_id','1','$date')");
	}
	
	
}

function validateSubscription($email){
	global $wpdb;
	$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}samiley_newsletter where email='$email' AND status=1",'ARRAY_A');
	if(!empty($result)){
		return $result[0]['id'];
	}
	else{
		return false;
	}
}
function validateEmail($email){
	global $wpdb;
	$result = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}users where user_email='$email'",'ARRAY_A');
	if(!empty($result)){
		return $result[0]['ID'];
	}
	else{
		return false;
	}
}

function custom_user_profile_fields($user){
    if(is_object($user)){
        $gender = esc_attr( get_the_author_meta( 'gender', $user->ID ) );
        $dob = esc_attr( get_the_author_meta( 'dob', $user->ID ) );
        $website = esc_attr( get_the_author_meta( 'website', $user->ID ) );
        $store = esc_attr( get_the_author_meta( 'store', $user->ID ) );
	}else{
        $gender = null;
        $dob = null;
        $website = null;
        $store = null;
	}
    ?>
    <h3>Extra profile information</h3>
    <table class="form-table">
        <tr>
            <th><label for="company">Gender</label></th>
            <td>
                <input type="radio" class="regular-text" name="gender" value="male" <?= ($gender == 'male')?'checked':''; ?> />Male<br />
                <input type="radio" class="regular-text" name="gender" value="female" <?= ($gender == 'female')?'checked':''; ?> />Female<br />
            </td>
        </tr>
		        <tr>
            <th><label for="company">Date of Birth</label></th>
            <td>
                <input type="text" class="regular-text" name="dob" value="<?php echo $dob; ?>" id="company" /><br />
            </td>
        </tr>
		        <tr>
            <th><label for="company">Website</label></th>
            <td>
                <input type="text" class="regular-text" name="website" value="<?php echo $website; ?>" id="company" /><br />
            </td>
        </tr>
		        <tr>
            <th><label for="company">Store</label></th>
            <td>
                <input type="text" class="regular-text" name="store" value="<?php echo $store; ?>" id="company" /><br />
            </td>
        </tr>
    </table>
<?php
}
add_action( 'show_user_profile', 'custom_user_profile_fields' );
add_action( 'edit_user_profile', 'custom_user_profile_fields' );
add_action( "user_new_form", "custom_user_profile_fields" );

function save_custom_user_profile_fields($user_id){
    # again do this only if you can
    if(!current_user_can('manage_options'))
        return false;
 
    # save my custom field
    update_user_meta($user_id, 'gender', $_POST['gender']);
    update_user_meta($user_id, 'dob', $_POST['dob']);
    update_user_meta($user_id, 'website', $_POST['website']);
    update_user_meta($user_id, 'store', $_POST['store']);
}
add_action('user_register', 'save_custom_user_profile_fields');
add_action('profile_update', 'save_custom_user_profile_fields');

function getSubscribers(){
	global $wpdb;
	$subscribers = $wpdb->get_results("
		SELECT * FROM {$wpdb->prefix}samiley_newsletter
		ORDER BY created_at DESC
		LIMIT 500
	");
	
	return $subscribers;
}

function getCustomer(){
	global $wpdb;
	$customers = $wpdb->get_results("
		SELECT * FROM {$wpdb->prefix}users
		ORDER BY user_registered DESC
		LIMIT 500
	");
	
	return $customers;
	
}

function getCustomers($id = false){

	if($id){
		$user_meta=get_userdata($id);
			$lastname = esc_attr( get_the_author_meta( 'last_name',$id ) );
			$gender = esc_attr( get_the_author_meta( 'gender', get_current_user_id() ) );
			$dob = esc_attr( get_the_author_meta( 'dob', get_current_user_id() ) );
			$website = esc_attr( get_the_author_meta( 'website', get_current_user_id() ) );
			$store = esc_attr( get_the_author_meta( 'store', get_current_user_id() ) );
				$customer_data = [
									'email' => $user_meta->user_email,
									'name' => $user_meta->display_name,
									'subscription_type' => 'Customer',
									'customer_group' => $user_meta->roles[0],
									'customer_id' => $id,
									'prefix' => '',
									'firstname' => $user_meta->display_name,
									'lastname' =>($lastname)?$lastname:'',
									'gender' => ($gender)?$gender:'',
									'birthday' => ($dob)?$dob:'',
									'store' => ($website)?$website:'',
									'website' => ($store)?$store:'',


								]; 
		return $customer_data;
	}else{
		
		$customer_data = [];
		$customers = getCustomer();
		foreach($customers as $val){
			
			$user_meta=get_userdata($val->ID);
			$lastname = esc_attr( get_the_author_meta( 'last_name',$val->ID ) );
			$gender = esc_attr( get_the_author_meta( 'gender', get_current_user_id() ) );
			$dob = esc_attr( get_the_author_meta( 'dob', get_current_user_id() ) );
			$website = esc_attr( get_the_author_meta( 'website', get_current_user_id() ) );
			$store = esc_attr( get_the_author_meta( 'store', get_current_user_id() ) );
				$customer_data[] = [
									'email' => $user_meta->user_email,
									'name' => $user_meta->display_name,
									'subscription_type' => 'Customer',
									'customer_group' => $user_meta->roles[0],
									'customer_id' => $val->ID,
									'prefix' => '',
									'firstname' => $user_meta->display_name,
									'lastname' =>($lastname)?$lastname:'',
									'gender' => ($gender)?$gender:'',
									'birthday' => ($dob)?$dob:'',
									'store' => ($website)?$website:'',
									'website' => ($store)?$store:'',


								]; 
		}	
		return $customer_data;
	}

}


function getList($limit = 500){
		$list = [];
		$subscribers = getSubscribers();
		foreach($subscribers as $subscriber){
			$id = validateEmail($subscriber->email);
			if($id){
				$customer = getCustomers($id);
				$list[] = [
							'email' => ($customer['email'])?$customer['email']:$subscriber->email,
							'name' =>  ($customer['name'])?$customer['name']:$subscriber->name,
							'subscription_type' =>  ($customer['subscription_type'])?$customer['subscription_type']:$subscriber->subscription_type,
							'customer_group' =>  ($customer['customer_group'])?$customer['customer_group']:$subscriber->customer_group,
							'customer_id' =>  ($customer['customer_id'])?$customer['customer_id']:$subscriber->customer_id,
							'prefix' =>  ($customer['prefix'])?$customer['prefix']:$subscriber->prefix,
							'firstname' =>  ($customer['firstname'])?$customer['firstname']:$subscriber->firstname,
							'lastname' => ($customer['lastname'])?$customer['lastname']:$subscriber->lastname,
							'gender' =>  ($customer['gender'])?$customer['gender']:$subscriber->gender,
							'birthday' =>  ($customer['birthday'])?$customer['birthday']:$subscriber->birthday,
							'store' =>  ($customer['store'])?$customer['store']:$subscriber->store,
							'website' =>  ($customer['website'])?$customer['website']:$subscriber->website,


				]; 
			}else{
				$list[] = [
							'email' => $subscriber->email,
							'name' =>  $subscriber->name,
							'subscription_type' =>  $subscriber->subscription_type,
							'customer_group' =>  $subscriber->customer_group,
							'customer_id' =>  $subscriber->customer_id,
							'prefix' =>  $subscriber->prefix,
							'firstname' =>  $subscriber->firstname,
							'lastname' => $subscriber->lastname,
							'gender' =>  $subscriber->gender,
							'birthday' =>  $subscriber->birthday,
							'store' =>  $subscriber->store,
							'website' =>  $subscriber->website,


				]; 
			}
		}
	
	return $list;
    }
	
	function getLatestProducts($limit = 50){
			global $wpdb;
	$products = $wpdb->get_results("
		SELECT * FROM {$wpdb->prefix}posts
		WHERE post_type = 'product' AND post_status='publish'
		ORDER BY post_date DESC
		LIMIT $limit
	");
	
	return $products;

	}


	function generateRssFeed($limit = 50){
		
		$products = getLatestProducts($limit);
		 $baseUrl = get_site_url();
		$currencysymbol = get_woocommerce_currency_symbol();
		$items= [];
		foreach($products as $prod){
		$product = new WC_Product( $prod->ID );
	 
			 $price = $product->get_regular_price();			
			$splcPrice = $product->get_sale_price();
			$discount = 0;
				if( $splcPrice  == 0 )
					$splcPrice = $price;
			
				if( $splcPrice < $price && $price > 0 )
					$discount = ceil(($price-$splcPrice)/$price*100);
			
			$price ='$'.number_format($price,2,'.',',');
			$splcPrice = '$'.number_format($splcPrice,2,'.',',');
			
			$url = get_permalink( $prod->ID);
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $prod->ID), 'single-post-thumbnail' );
			
			$image = $image[0];
			$createTime = strtotime($prod->post_date);
			$price_fields ='';
			if( $discount > 0 ){
				$price_fields = '
			  <smly:old_price>'.$price.'</smly:old_price>
			  <smly:discount>-'.$discount.'%</smly:discount>';	
			}
		
			$items[] = '<item>
			  <title>'.$prod->post_title .'</title>
			  <link>'.$url.'</link>
			  <guid isPermaLink="True">'.$url.'</guid>
			  <pubDate>'.date("D, d M Y H:i:s",$createTime).'</pubDate>
			  <description>'.htmlentities($prod->post_content).'</description>
			  <enclosure url="'.$image.'" />
			  <smly:price>'.$splcPrice.'</smly:price>'.$price_fields.'
			</item>
			';	
		}
		 $rss = '<?xml version="1.0" encoding="utf-8"?><rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0"><channel><title>Store</title><link>'.$baseUrl.'</link><description>Product Feed</description><lastBuildDate>'.date("D, d M Y H:i:s").'</lastBuildDate>
			';
		$rss .= implode(' ',$items);
		$rss .='</channel></rss>';	
		header('Content-Type: application/xml');
		echo $rss; 
	}
	
	
/* 
function do_insert() {	
		$post = array(
			'post_title'	=> 'RSS Feed',
			'page_template'	=> 'page-rss-feed.php', 
			'post_status'	=> 'publish',
			'post_type'		=> 'page' 
		);
		wp_insert_post($post); 
}  */
	
if(isset($_POST)):
	$smailey_front =  Smailey_Front::instance();
	$smailey_front->submit($_POST);
endif;
