<?php

if (!class_exists('LW_REGISTRATION_FRONT_CLASS')) {

    class LW_REGISTRATION_FRONT_CLASS {

        public function __construct() {

			add_action('wp_enqueue_scripts', array(&$this, 'lw_registration_styles_and_script'),30);

			$AjaxActionArray=array('lw_invitation_registration_action','lw_registration_action','lw_login_action','lw_forgot_password_action','lw_check_email_address','lw_in_login_time');

			foreach($AjaxActionArray as $singleAction)

			{

				add_action("wp_ajax_".$singleAction,array(&$this,$singleAction));

				add_action("wp_ajax_nopriv_".$singleAction,array(&$this,$singleAction));	

			}
			
			add_action('template_notices', array(&$this,'lw_template_notices'));
		}

		public function lw_registration_styles_and_script(){
			global $lw_general_settings;
			
			$pageId = get_the_ID();
			
			if($pageId == $lw_general_settings['login_redirect'] || $pageId==$lw_general_settings['invitation'] || $pageId==$lw_general_settings['registration'] || $pageId==$lw_general_settings['login']){
				wp_enqueue_style( 'lw_registration_all.min.css', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',array(),time());
				wp_enqueue_style( 'LW-style.css',LW_REGISTRATION_URL.'/assets/css/LW-style.css',array(),strval(filemtime(plugin_dir_path(__FILE__).'../../assets/css/LW-style.css')));
			
				wp_enqueue_script( 'lw_registration_jquery.validate.min', LW_REGISTRATION_ASSETS_URL . '/js/jquery.validate.min.js',array(),time());
				wp_enqueue_script( 'lw_registration-js', LW_REGISTRATION_ASSETS_URL . '/js/lw_registration.js',array(),strval(filemtime(plugin_dir_path(__FILE__).'../../assets/js/lw_registration.js')),true);
                if($pageId==$lw_general_settings['registration']){
                    wp_enqueue_script( 'lw_recaptcha-js', 'https://www.google.com/recaptcha/api.js?render=6LdX0hspAAAAAGKczmfpxU3PON-KxPYZztLtFMOG');
                }
				wp_localize_script( 'lw_registration-js', 'lw_registration', array(
                        "ajaxUrl"=>admin_url( 'admin-ajax.php' ),
                        "site_key" => get_option('recaptcha_site_key')
                ) );
				wp_dequeue_script( 'boss-validate-js' );
				wp_deregister_script( 'boss-validate-js' );
			}
			wp_enqueue_script( 'lw_modal', LW_REGISTRATION_ASSETS_URL . '/js/lw_modal.js',array(),strval(filemtime(plugin_dir_path(__FILE__).'../../assets/js/lw_modal.js')));
			wp_enqueue_style( 'lw_modal.css',LW_REGISTRATION_URL.'/assets/css/lw_modal.css',array(),strval(filemtime(plugin_dir_path(__FILE__).'../../assets/css/lw_modal.css')), false );


		}
		
		public function lw_forgot_password_action(){
			$postData = $_POST;

			$user_login = $postData['lw_email_address'];
			 global $wpdb, $wp_hasher;
    		$user_login = sanitize_text_field($user_login);
			$login = trim($user_login);
			$user_data = get_user_by('email', $login);
			if(empty($user_data)){
				$user_data = get_user_by('login', $login);
			}
//			do_action('lostpassword_post'); //Do not know what that action it is.
			if ( !$user_data ) {
				echo json_encode(array("message"=>"The Email address is incorrect.",'status'=>0));
				exit;	
			}
			// redefining user_login ensures we return the right case in the email
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			do_action('retreive_password', $user_login);  // Misspelled and deprecated
			do_action('retrieve_password', $user_login);
			$allow = apply_filters('allow_password_reset', true, $user_data->ID);

			if ( ! $allow ){
				echo json_encode(array("message"=>"Sorry something wrong.Please try again later.",'status'=>0));
				exit;	
			}
			else if ( is_wp_error($allow) ){
				echo json_encode(array("message"=>"Sorry something wrong.Please try again later.",'status'=>0));
				exit;
			}
			$key = wp_generate_password( 20, false );
			do_action( 'retrieve_password_key', $user_login, $key );
		
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}
			$hashed = $wp_hasher->HashPassword( $key );    
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_login ) );
			$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
			$message .= network_home_url( '/' ) . "\r\n\r\n";
			$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
			$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
			$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
			$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
		
			if ( is_multisite() )
				$blogname = $GLOBALS['current_site']->site_name;
			else
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		
			$title = sprintf( __('[%s] Password Reset'), $blogname );
		
			$title = apply_filters('retrieve_password_title', $title);
			
			if ( $message && !wp_mail($user_email, $title, $message) ){
				
				echo json_encode(array("message"=>"The e-mail could not be sent.",'status'=>0));
				exit;
			}else{
					echo json_encode(array("message"=>"Link for password reset has been emailed to you. Please check your email.",'status'=>1));
				exit;
			}
	
		}
		public function lw_login_action(){
			$postData = $_POST;
			$lw_general_settings = get_option('lw_general_settings');
			
			$creds = array();
			$creds['user_login'] = $postData['lw_username'];
			$creds['user_password'] = $postData['lw_password'];
			$creds['remember'] = true;
			$user = wp_signon( $creds,true);
			
			//do_action('wp_login', $user->user_email);
			
			
			if ( is_wp_error($user) ){
				foreach ($user->errors['invalid_username'] as $err) {
						if (preg_match('/\bsuspended\b/', $err)) { 

						echo json_encode(array("message"=>"You're unable to login right now, this account does not currently have community access. For more info and account support please email us at livewire@starlight.org.au or or call us on AUS: 02 8425 5971 or NZ: 0800 000 680 ",'status'=>0));

						exit;
				
					}
				}
                
                if(!empty($user->errors['bp_account_not_activated'])){
					$errors = [];
					foreach ($user->errors['bp_account_not_activated'] as $err) {
						$errors[]=$err;
					}
					echo json_encode(array("message"=>implode(', ',$errors),'status'=>0));
					exit;
				}
                
				echo json_encode(array("message"=>"There is a login error, please contact livewire@starlight.org.au.",'status'=>0));
				exit;
				
			}else{
				$blocked_status = array_shift( wp_get_object_terms( $user->ID, 'blocked_status', array( 'fields' => 'ids' ) ) );
				$profile_type = array_shift( wp_get_object_terms( $user->ID, 'profile_type', array( 'fields' => 'ids' ) ) );
				//$profile_type==85 || $blocked_status=="44", replace the below false
				if(false){
					nocache_headers();
					wp_clear_auth_cookie();
					echo json_encode(array("message"=>"Redirect...",'status'=>1,"redirect"=>site_url()."/graduate-block"));
					exit;	
				}else{
				
						wp_set_current_user($user->ID, $user->user_email);
						wp_set_auth_cookie($user->ID);
		
						update_option('lw_login','yes');
						update_user_meta($user->ID,'lw_last_login_ip_address',lw_get_client_ip());
						update_user_meta($user->ID,'lw_login_time',0);
			
						
						if(in_array("administrator",$user->roles)){
							$redirect= admin_url();	
						}else{
							$redirect= get_the_permalink($lw_general_settings['login_redirect']);
						}
						
						echo json_encode(array("message"=>"You're logged in!",'status'=>1,'redirect'=>$redirect));
						exit;	
					}
			}
		}
		public function lw_registration_action(){
			global $lw_general_settings;


			$postData = $_POST;
            $response = $this->checkRecaptcha($postData);
            $THRESHOLD = get_option('recaptcha_limitation');
            //... The Captcha is valid you can continue with the rest of your code
            //... Add code to filter access using $response . score
            if ($response->success==true && $response->score <= $THRESHOLD) {
                echo json_encode(array("message"=>"Oops... It seems like you are a bot, we cannot let you in.",'status'=>0));
                exit;
            }

			$redirect= "";
			
			global $wpdb;
				
				$lw_form_type = $postData['lw_form_type'];
				$cc_email_address= array();
				if(!empty($lw_general_settings['cc_cmail_recipient'])){
					$cc_email_address[$lw_general_settings['cc_cmail_recipient']] = $lw_general_settings['cc_cmail_recipient'];
				}
				$birthday_string = $postData['lw_birthday_month'].$postData['lw_birthday_day'];
				if($lw_form_type=="form_a"){
					$redirect= get_the_permalink($lw_general_settings['redirect_registration_known_to_starlight']);
					if(!empty($postData['lw_registration_guardian_email'])){
						$cc_email_address[$postData['lw_registration_guardian_email']] = $postData['lw_registration_guardian_email'];
					}
					
					$user_login = $postData['lw_username'];
					$user_email = $postData['lw_registration_email_address'];
					$first_name = $postData['lw_first_name'];
					$last_name = $postData['lw_last_name'];
					$display_name = $postData['lw_first_name'].' '.$postData['lw_last_name'];
					$updatedMetaData = array(
											'lw_invitation_id'=>$postData['lw_invitation_id'],
											'lw_form_type'=>$postData['lw_form_type'],
											'lw_registration_pronouns'=>$postData['lw_registration_pronouns'],
											'lw_registration_birthday'=>$postData['lw_birthday_year']."-".$postData['lw_birthday_month']."-".$postData['lw_birthday_day'],
											
											'lw_area_code'=>$postData['lw_area_code'], 	
											'lw_emergency_area_code'=>$postData['lw_emergency_area_code'],
											'lw_mobilephone'=>str_replace('0', '', substr($postData['lw_mobilephone'], 0, 1)).substr($postData['lw_mobilephone'], 1),
											'lw_registration_guardian_first_name'=>$postData['lw_registration_guardian_first_name'],
											'lw_registration_guardian_last_name'=>$postData['lw_registration_guardian_last_name'],
											'lw_registration_guardian_email'=>$postData['lw_registration_guardian_email'],
											'lw_registration_guardian_mobile_phone'=>str_replace('0', '', substr($postData['lw_registration_guardian_mobile_phone'], 0, 1)).substr($postData['lw_registration_guardian_mobile_phone'], 1),
											'lw_contact_you'=>$postData['lw_contact_you']
					);
						
				}
				
				if($lw_form_type=="form_b"){
					$redirect= get_the_permalink($lw_general_settings['redirect_registration_known_to_wish_granting']);
					$user_login = $postData['lw_username'];
					$user_email = $postData['lw_registration_email'];
					$first_name = $postData['lw_first_name'];
					$last_name = $postData['lw_last_name'];
					$display_name = $postData['lw_first_name'].' '.$postData['lw_last_name'];
					$updatedMetaData = array('lw_form_type'=>$postData['lw_form_type'],
											'lw_registration_pronouns'=>$postData['lw_registration_pronouns'],
											'lw_registration_birthday'=>$postData['lw_birthday_year']."-".$postData['lw_birthday_month']."-".$postData['lw_birthday_day']
					);
						
				}
						
						
				if($lw_form_type=="form_c"){
					
					if(!empty($postData['lw_registration_guardian_email'])){
						$cc_email_address[$postData['lw_registration_guardian_email']] = $postData['lw_registration_guardian_email'];
					}
					
					$user_login = $postData['lw_username'];
					$user_email = $postData['lw_registration_email_address'];
					$first_name = $postData['lw_first_name'];
					$last_name = $postData['lw_last_name'];
					$display_name = $postData['lw_first_name'].' '.$postData['lw_last_name'];
					$updatedMetaData = array('lw_form_type'=>$postData['lw_form_type'],
											'lw_registration_pronouns'=>$postData['lw_registration_pronouns'],
											'lw_registration_birthday'=>$postData['lw_birthday_year']."-".$postData['lw_birthday_month']."-".$postData['lw_birthday_day'],
											'lw_sibling_spent_time'=>$postData['lw_sibling_spent_time'],
											'lw_area_code'=>$postData['lw_area_code'],
											'lw_emergency_area_code'=>$postData['lw_emergency_area_code'],
											'lw_mobilephone'=>str_replace('0', '', substr($postData['lw_mobilephone'], 0, 1)).substr($postData['lw_mobilephone'], 1),
											'lw_registration_guardian_first_name'=>$postData['lw_registration_guardian_first_name'],
											'lw_registration_guardian_last_name'=>$postData['lw_registration_guardian_last_name'],
											'lw_registration_guardian_email'=>$postData['lw_registration_guardian_email'],
											'lw_registration_guardian_mobile_phone'=>str_replace('0', '', substr($postData['lw_registration_guardian_mobile_phone'], 0, 1)).substr($postData['lw_registration_guardian_mobile_phone'], 1),
											'lw_contact_you'=>$postData['lw_contact_you']
					);
					
			}
		
				$checkUsername = get_user_by("login",$user_login);
				
				if(isset($checkUsername->ID) && $checkUsername->ID>0){
					echo json_encode(array("message"=>"Oops... Sorry this username already exists. Please try a new username.",'status'=>0));
				    exit;	
				}
				$checkEmail = get_user_by("email",$user_email);
				
				if(isset($checkEmail->ID) && $checkEmail->ID>0){
					echo json_encode(array("message"=>"Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address",'status'=>0));
					exit;	
				}
		
								
				$user_data = array(
					'user_login' => $user_login,
					'user_email' => $user_email,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'display_name' => $display_name,
					'user_pass' => $postData['lw_password'],
					'role' => 'subscriber'
				);
				
				if($lw_form_type=="form_c"){
					//$user_id = bp_core_signup_user($user_login,$postData['lw_password'],$user_email,array('field_1'=>$display_name,'field_2'=>$last_name,'field_3'=>$first_name,'profile_field_ids'=>"1,2,3",'password'=>md5($postData['lw_password'])));	
					$redirect = get_the_permalink($lw_general_settings['pending_registration']);	
					$user_id = wp_insert_user($user_data);
					wp_set_object_terms( $user_id,85 , 'profile_type', false );
            		clean_object_term_cache( $user_id, 'profile_type' );
					
					wp_set_object_terms( $user_id,44 , 'blocked_status', false );
            		clean_object_term_cache( $user_id, 'blocked_status' );
					
			
				}else if($lw_form_type=="form_a"){
					//$user_id = bp_core_signup_user($user_login,$postData['lw_password'],$user_email,array('field_1'=>$display_name,'field_2'=>$last_name,'field_3'=>$first_name,'profile_field_ids'=>"1,2,3",'password'=>md5($postData['lw_password'])));	
					//$redirect = get_the_permalink($lw_general_settings['pending_registration']);	
					$user_id = wp_insert_user($user_data);
					wp_set_object_terms( $user_id,84 , 'profile_type', false );
            		clean_object_term_cache( $user_id, 'profile_type' );

					wp_set_object_terms( $user_id,'Not Blocked' , 'blocked_status', false );
            		clean_object_term_cache( $user_id, 'blocked_status' );
					
							
				}else if($lw_form_type=="form_b"){
					//$user_id = bp_core_signup_user($user_login,$postData['lw_password'],$user_email,array('field_1'=>$display_name,'field_2'=>$last_name,'field_3'=>$first_name,'profile_field_ids'=>"1,2,3",'password'=>md5($postData['lw_password'])));	
					//$redirect = get_the_permalink($lw_general_settings['pending_registration']);	
					$user_id = wp_insert_user($user_data);
					wp_set_object_terms( $user_id,83 , 'profile_type', false );
            		clean_object_term_cache( $user_id, 'profile_type' );
					
				}else{
					$user_id = wp_insert_user($user_data);
					
					
				}
				
				if (is_wp_error( $user_id ) ) {
					echo json_encode(array("message"=>"Sorry something wrong.Please try again later.",'status'=>0));
					exit;
				
				}else{
					registrationEmailSend($user_email,$postData,$cc_email_address);
					if($postData['lw_invitation_id']>0){
						$wpdb->update(TABLES_LW_REGISTRATION_INVITATION,array('status'=>2),array('id'=>$postData['lw_invitation_id']));	
					}
					
					foreach($updatedMetaData as $k=>$v){
						update_user_meta($user_id,$k,$v);
						
					}
					update_user_meta($user_id,'nickname',$user_login);
					update_user_meta($user_id, 'birthmmdd' . $birthday_string, $birthday_string);
					
					do_action ('lw_crm_sync' , $user_id );
					//update member type / profile type 'livewire-member' to registered members
						$post_id = $user_id; 
						$taxonomy_term_id = 86; 

						$existing_relationship = $wpdb->get_row($wpdb->prepare(
								"SELECT * FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id = %d",
								$post_id,
								$taxonomy_term_id
						));

						if (!$existing_relationship) {
								$wpdb->insert(
										$wpdb->term_relationships,
										array(
												'object_id' => $post_id,
												'term_taxonomy_id' => $taxonomy_term_id,
												'term_order' => 0 
										),
										array(
												'%d',
												'%d',
												'%d'
										)
								);
								$wpdb->query($wpdb->prepare(
										"UPDATE $wpdb->term_taxonomy SET count = count + 1 WHERE term_taxonomy_id = %d",
										$taxonomy_term_id
								));
						}
						
					$lwAdminUsers = new lwAdminUsers(); 
					$lwAdminUsers->processUserBirthday($user_id);
				 
					if($lw_form_type=="form_a" || $lw_form_type=="form_b"){
						$creds['user_login'] = $user_login;
						$creds['user_password'] = $postData['lw_password'];
						$creds['remember'] = true;
						$user = wp_signon( $creds,true);
						wp_set_current_user($user->ID, $user->user_email);
						wp_set_auth_cookie($user->ID);
				
						update_option('lw_login','yes');
					
					if(in_array("administrator",$user->roles)){
							$redirect= admin_url();	
						}else{
							$redirect= get_the_permalink($lw_general_settings['login_redirect']);
						}		
					}
					
					// global $wpdb;
					// $wpdb->query("UPDATE ".$wpdb->prefix."bp_xprofile_data SET `value` = '' WHERE user_id='".$user_id."' and field_id='2'");
					
					echo json_encode(array("message"=>"You're logged in!",'status'=>1,'redirect'=>$redirect));
					exit;
				}
			
		}
		
		
		public function lw_template_notices()
		{
			?>
				<script>
					jQuery( document ).ready(function() {
						try {
							if(jQuery('.bp-sitewide-notice').length>0){
							//	jQuery('.widget-area.sidebar-right .bb-sticky-sidebar').prepend('<div id="bp-sitewide-notice-container" class="widget widget_block"><div style="display:none"></div></div>');
								//jQuery(".bp-template-notice.bp-sitewide-notice").insertAfter("#bp-sitewide-notice-container div");
							}
						}
						catch(err) {}
					});				
				</script>
				<style>
					/*.bp-feedback.bp-sitewide-notice{display:none !important}
					.site-content .widget-area .bp-feedback.bp-sitewide-notice{ display:block !important; top:0px !important}
					#bp-sitewide-notice-container{ margin-bottom:15px}
					#bp-sitewide-notice-container .bp-feedback.bp-sitewide-notice{padding:0px !important; border-bottom:0px !important}*/
				</style>
			<?php		
		}
		
		
		public function lw_in_login_time(){
			global $current_user;
			$get_lw_login_time = get_user_meta($current_user->ID,'lw_login_time',true);
			if($get_lw_login_time==""){
				$get_lw_login_time = 0;	
			}
			$lw_login_time = $get_lw_login_time+1;
			update_user_meta($current_user->ID,'lw_login_time',$lw_login_time);
			$responseReturn['status'] =1;
			echo json_encode($responseReturn);
			exit;	

			
		}
		public function lw_check_email_address(){
			$email_address = $_POST['email_address'];
			
			global $wpdb;
				$checkRegistration = get_user_by("email",$email_address);
				
				if(isset($checkRegistration->ID) && $checkRegistration->ID>0){
					$is_exits  = 1;
					
						$responseReturn['status'] =0;
						$responseReturn['message'] ='Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address.';
						echo json_encode($responseReturn);
						exit;	
				}
				if(!isset($checkRegistration->ID) ){
					$checkRegistration = get_user_by("login",$email_address);
					if(isset($checkRegistration->ID) && $checkRegistration->ID>0){
						$is_exits  = 1;
						$responseReturn['status'] =0;
						$responseReturn['message'] ='Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address.';
						echo json_encode($responseReturn);
						exit;	
					}
				}
				
				$message  = "Success";
				$responseReturn['status'] =1;
				echo json_encode($responseReturn);
					exit;
		}
		
		public function lw_invitation_registration_action(){
			$postData = $_POST;
			
			global $wpdb,$current_user;
			$checkRegistration = $wpdb->get_results('SELECT * FROM '.TABLES_LW_REGISTRATION_INVITATION.' WHERE  email="'.$postData['email'].'"',ARRAY_A);
			$is_exits = 0;
			if(!empty($checkRegistration)){
				$is_exits  = 1;
		
				$responseReturn['status'] =0;
				$responseReturn['message'] ='Each email address can only receive one invitation.';
				echo json_encode($responseReturn);
				exit;			
				
			}
			if($is_exits==0){
				$checkRegistration = get_user_by("email",$postData['email']);
				
				if(isset($checkRegistration->ID) && $checkRegistration->ID>0){
					$is_exits  = 1;
					
						$responseReturn['status'] =0;
						$responseReturn['message'] ='Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address.';
						echo json_encode($responseReturn);
						exit;	
				}
				if(!isset($checkRegistration->ID) ){
					$checkRegistration = get_user_by("login",$postData['email']);
					if(isset($checkRegistration->ID) && $checkRegistration->ID>0){
						$is_exits  = 1;
						$responseReturn['status'] =0;
						$responseReturn['message'] ='Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address.';
						echo json_encode($responseReturn);
						exit;	
					}
				}
			}
			if($is_exits ==0){
				
				$responseReturn['lw_generate_link'] ="";
				$lw_general_settings = get_option('lw_general_settings');
				
				$data_array['user_id']=$current_user->ID;
				$data_array['first_name']=$postData['first_name'];
				$data_array['last_name']=$postData['last_name'];
				$data_array['email']=$postData['email'];
				$data_array['invitation_type']=$postData['invitation_type'];
				$data_array['staff_name'] = $postData['staff_name'];
				$data_array['expire_at']= date("Y-m-d H:i:s" ,strtotime("+".$lw_general_settings['token_expire_hours']." hours",strtotime(date("Y-m-d H:i:s"))));
				$wpdb->insert(TABLES_LW_REGISTRATION_INVITATION,$data_array); 
				$insertedId = $wpdb->insert_id;
				
				
				$tokenData = $_REQUEST['id']."::".$data_array['first_name']." ".$data_array['last_name']." ".$data_array['email'];
				$token = lw_registration_encrypt_decrypt('encrypt',$tokenData);
				$wpdb->update(TABLES_LW_REGISTRATION_INVITATION,array('token'=>$token),array('id'=>$insertedId));
				if(isset($postData['lw_generate_link']) && $postData['lw_generate_link']==1){
			
					if(isset($lw_general_settings['registration']) && $lw_general_settings['registration']>0 && $token!=""){
						//log out the current user
						
						$registration_url = get_the_permalink($lw_general_settings['registration'])."?token=".$token;
						$responseReturn['lw_generate_link'] =$registration_url;
					}
				}else{
					invitationEmailSend(array('first_name'=>$postData['first_name'],'last_name'=>$postData['last_name'],'email'=>$postData['email']
				,'token'=>$token,'invitation_type'=>$postData['invitation_type']));	
				}
				$process='add';
				$message  = "Invitation successfully sent.";
				$responseReturn['status'] =1;
			}else if($is_exits ==1){
				$process='exits';
				$message  = "Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address. ";
				$responseReturn['status'] =0;
			}
			
				$responseReturn['message'] =$message;
				echo json_encode($responseReturn);
				exit;	
		}

        /**
         * @param array $postData
         * @return array|void
         */
        function checkRecaptcha(array $postData)
        {
            if (isset($postData['recaptcha_token'])) {
                $captcha = $postData['recaptcha_token'];
            } else {
                $captcha = false;
            }
            if (!$captcha) {
                echo json_encode(array("message" => "reCaptcha is not working. Please contact the server administrator.", 'status' => 0));
                exit;
            } else {
                $secret = get_option('recaptcha_secret_key');
                $response = file_get_contents(
                    "https://www.google.com/recaptcha/api/siteverify?secret=" . $secret . "&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']
                );

                // use json_decode to extract json response
                $response = json_decode($response);

                if ($response->success === false) {
                    echo json_encode(array("message" => "Oops... There is an error with google recaptcha server, please contact the server administrator.", 'status' => 0));
                    exit;
                }
            }
            return $response;
        }

    }
}

new LW_REGISTRATION_FRONT_CLASS();

?>