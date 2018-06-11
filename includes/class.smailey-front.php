<?php
class Smailey_Front{
	protected static $_instance = null;
	public $smailey_results;
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_stylesheet' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		//$this->cronSubscribeAll(getList());
    }
	
	private static function get_asset_url( $path = false ) {
		return plugins_url().'/Smaily/assets/'. $path;
	}
	
	public function load_stylesheet(){
		wp_enqueue_style('smailey-style',self::get_asset_url('css/style.css'));
	}
	
	public function load_scripts(){
		//self::get_asset_url();
	}

	
	public function validateEmailFormat($email){
	$pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
	 if(!preg_match($pattern, $email)){
				throw new Exception('Email format invalid!');
	 }
	}
	
	public function validateGuestSubscription(){
		if(!get_current_user_id()){
			throw new Exception('Please Login for subscription!');
		}
	}
	
	public function validateEmailAvailable($email){
		if(validateSubscription($email)):
			throw new Exception('Already subscribed to newsletter!');
		endif;
	}
	
	public function submit($post=false){


		if ($post && $post['email']) {
            $email = (string)$post['email'];
			
		try{
			    $lastname = esc_attr( get_the_author_meta( 'last_name', get_current_user_id() ) );
			    $gender = esc_attr( get_the_author_meta( 'gender', get_current_user_id() ) );
				$dob = esc_attr( get_the_author_meta( 'dob', get_current_user_id() ) );
				$website = esc_attr( get_the_author_meta( 'website', get_current_user_id() ) );
				$store = esc_attr( get_the_author_meta( 'store', get_current_user_id() ) );
				$name = $post['firstname'];
			    $this->validateEmailFormat($post['email']);
                //$this->validateGuestSubscription();
                $this->validateEmailAvailable($post['email']);
				if($this->isEnabled()){
					$user = wp_get_current_user();
					$user = $user->data;
					$autoresponder_id = $this->getConfig('autoresponder');
					$extra  = array(
										'name'=>$name,
										'subscription_type' => (get_current_user_id())?'SUBSCRIBER':'CUSTOMER',
										'firstname' => ($user->display_name)?$user->display_name:$name,
										'lastname' => ($lastname)?$lastname:'',
										'addresses' => ($user->user_email)?$user->user_email:$email,
										'prefix' => 'Null',
										'customer_id' => ($user->ID)?$user->ID:'',
										'customer_group' => (get_current_user_id())?$this->get_current_user_role():'Guest',
										'gender' => ($gender)?$gender:'',
										'birthday' => ($dob)?$dob:'',
										'store' => ($website)?$website:'',
										'website' => ($store)?$store:'',
									);
									
									/* print_r($extra);
									die; */
					$response = $this->subscribeAutoresponder($autoresponder_id,$email,$extra);
					if( @$response['message'] == 'OK' ){
						$_POST['success'] = 'Thank you for your subscription !';
						subscribed($autoresponder_id,$email,$extra);
					} else {
						throw new Exception(@$response['message']);	
					}
				}
		}catch(Exception $e){
			$_POST['error'] = $e->getMessage();
		}
		}
	}
	
	public function get_current_user_role( $user = null ) {
	$user = $user ? new WP_User( $user ) : wp_get_current_user();
	return $user->roles ? $user->roles[0] : false;
	}
	public function getConfig($field = false){
		$this->smailey_results = get_smailey_results();
		return ($field)?$this->smailey_results['result'][$field]:$this->smailey_results['result'];
		
	}
	
	public function subscribeAutoresponder($aid,$email,$data=[]){
	
	$address = [
			'email'=>$email,
		];
		if( !empty($data) ){
			$fields= unserialize($this->getConfig('syncronize_additional'));
			foreach($data as $field => $val){
					if( in_array($field,$fields) || $field == 'name' ){
					$address[$field] = trim($val); 	
					}
			}
		} 
		
				$post  = [
			'autoresponder' => $aid,
			'addresses' => [$address],
		];
		
		$response = $this->callApi('autoresponder',$post,'POST');
		return $response;
	}
	
	public function cronSubscribeAll($list){
		$data = [];
		$fields= unserialize($this->getConfig('syncronize_additional'));
		foreach($list as $row){
				$_data = [
					'email'=>$row['email'],
					'is_unsubscribed' => 0
				];
				foreach($row as $field => $val){
					if( in_array($field,$fields) ){
						$_data[$field] = trim($val); 	
					}
				}
				$data[] = $_data;
			}
			
		$response = $this->callApi('contact',$data,'POST');
		return $response;
	}
	
	public function isEnabled(){
		if(trim($this->getConfig('enable')) == 'on'):
			return true;
		else:
			return false;
		endif;
	}
	public function getSubdomain(){
		$subdomain = $this->getConfig('subdomain');
		$host = parse_url($subdomain);
		$subdomain = explode('.',$host['host']);
		return $subdomain[0];
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