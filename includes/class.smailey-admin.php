<?php
/**
 * Class Smailey_Admin.
 */
class Smailey_Admin {
 	protected static $_instance = null;
	public $smailey_results;
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    public function init() {
		
		add_action('admin_menu', array($this,'smailey_menu'));
		$this->smailey_results = get_smailey_results();
    }

	public function smailey_menu(){
		add_submenu_page( 'woocommerce', __( 'Smailey', 'woocommerce' ),  __( 'Smaily email marketing and automation', 'woocommerce' ) , 'manage_options', 'smailey-settings', array($this,'smailey_page'));
	}
	
	public function smailey_page(){
		unset($_POST['smailey_submit']);
		if($_POST){
			pageid();
			global $wpdb;
			if(!array_key_exists("enable",$_POST))
				$_POST['enable'] = 'off';
				
				$_POST['syncronize_additional'] = serialize($_POST['syncronize_additional']);
				$wpdb->update("{$wpdb->prefix}samiley",$_POST,['id'=>1]);
				header("Refresh:0");
/* 					if(!empty($_POST['rss_feed'])):
						$pageid = pageid();
						$guid['guid'] = $pageid['guid'].$_POST['rss_feed'].'/';
						$wpdb->update("{$wpdb->prefix}posts",$guid,['ID'=>$pageid['page_id']]);
					endif; */
				$this->notify('notice-success','Record Saved');	
		}
		require_once( plugin_dir_path( __FILE__ ) . '../admin/smailey_page.php' );
	}
	
	public function notify($alert,$message){
	
	echo '<div id="message" class="updated notice '.$alert.' is-dismissible"><p>'.$message.'.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}
	
	public function getConfig($field = false){
		return ($field)?$this->smailey_results['result'][$field]:$this->smailey_results['result'];
		
	}
	
	public function getSubdomain(){
		$subdomain = $this->getConfig('subdomain');
		$host = parse_url($subdomain);
		$subdomain = explode('.',$host['host']);
		return $subdomain[0];
	}
	
	public function getAutoresponders(){
		if( empty($_SESSION['Smaily_autoresponder']) ){
			$_list = $this->callApi('autoresponder',['page'=>1,'limit'=>100,'status'=>['ACTIVE']]);
			$list = [];
			foreach($_list as $r){
				if( !empty($r['id']) && !empty($r['name']) )
				$list[$r['id']] = trim($r['name']);
			}
			$_SESSION['Smaily_autoresponder'] = $list;
		
		} else 
			$list = (array)$_SESSION['Smaily_autoresponder'];
		return $list;
	}
	
	public function callApi($endpoint,$data,$method='GET'){
		$subdomain = $this->getSubdomain();
		$username = trim($this->getConfig('username'));
		$password = trim($this->getConfig('password'));
		$apiUrl = "https://".$subdomain.".sendsmaily.net/api/".trim($endpoint,'/').".php";
		$data = http_build_query($data);
		if( $method == 'GET' )
			$apiUrl = $apiUrl.'?'.$data;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if( $method == 'POST' ){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = json_decode(@curl_exec($ch),true);
		$error = false;	
		if( curl_errno($ch) )
			$result = ["code"=>0,"message"=>curl_error($ch)];

		curl_close($ch); 
		return $result;
	}

}
 
