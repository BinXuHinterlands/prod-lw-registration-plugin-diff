<?php

if (!class_exists('LW_REGISTRATION_ADMIN_CLASS')) {

    class LW_REGISTRATION_ADMIN_CLASS {
		
		public function __construct() {
		    add_action('admin_menu', array(&$this, 'lw_registration_admin_menu'));
			add_action( 'admin_enqueue_scripts', array(&$this,'lw_registration_admin_scripts'));
			
			$ajaxActionsArray = array('lw_invitation_admin_action','lw_invitation_admin_resend');
			
			foreach($ajaxActionsArray as $single){
				add_action("wp_ajax_".$single, array(&$this,strtolower($single)));	
				add_action("wp_ajax_nopriv_".$single, array(&$this,strtolower($single)));	
			}
			
			add_action( 'show_user_profile', array(&$this,'lw_registration_profile_fields' ));
			add_action( 'edit_user_profile', array(&$this,'lw_registration_profile_fields' ));
			add_action( 'user_new_form', array(&$this,'lw_registration_profile_fields' ));
			
			add_action( 'personal_options_update', array(&$this,'lw_profile_fields_callback' ));
			add_action( 'edit_user_profile_update', array(&$this,'lw_profile_fields_callback' ));
			add_action( 'edit_user_created_user', array(&$this,'lw_profile_fields_callback' ));
		

		}
        public function lw_registration_admin_menu() {
			   add_menu_page(__('LW Invitation', 'lw_registration'), __('LW Invitation', 'lw_registration'), 'manage_options', 'lw_invitation', array(&$this, 'lw_invitation'));
			   add_submenu_page('lw_invitation', 'Settings', 'Settings', 'manage_options', "lw_invitation_settings", array(&$this,'lw_invitation_general_settings'));	
		}
		public function lw_registration_profile_fields($user) {
			include(LW_REGISTRATION_CLASS.'/admin/user_info.php');
		}
		public function lw_profile_fields_callback($user_id){
			global $wpdb;
			//indian codes to update user meta
			$updatedMetaData = array();
			$lw_form_type = $_POST['lw_form_type'];
				if($lw_form_type=="form_a"){
					$today_birtday = $_POST['form_a_lw_registration_birthday'];
					
					$updatedMetaData = array(
											'lw_form_type'=>$_POST['lw_form_type'],
											'lw_registration_birthday'=>$_POST['form_a_lw_registration_birthday'],
											'lw_registration_pronouns'=>$_POST['form_a_lw_registration_pronouns'],
											'lw_area_code'=>$_POST['form_a_lw_area_code'],
											'lw_mobilephone'=>$_POST['form_a_lw_mobilephone'],
											'lw_emergency_area_code'=>$_POST['lw_emergency_area_code'],
											'lw_registration_guardian_first_name'=>$_POST['form_a_lw_registration_guardian_first_name'],
											'lw_registration_guardian_last_name'=>$_POST['form_a_lw_registration_guardian_last_name'],
											'lw_registration_guardian_email'=>$_POST['form_a_lw_registration_guardian_email'],
											'lw_registration_guardian_mobile_phone'=>$_POST['form_a_lw_registration_guardian_mobile_phone'],
											'lw_contact_you'=>$_POST['form_a_lw_contact_you']
					);
						
				}
				
				if($lw_form_type=="form_b"){
					$today_birtday = $_POST['form_b_lw_registration_birthday'];
					
					$updatedMetaData = array('lw_form_type'=>$_POST['lw_form_type'],
											'lw_registration_birthday'=>$_POST['form_b_lw_registration_birthday'],
											'lw_registration_pronouns'=>$_POST['form_b_lw_registration_pronouns']
					);
						
				}
						
						
				if($lw_form_type=="form_c"){
					$today_birtday = $_POST['form_c_lw_registration_birthday'];
					
					$updatedMetaData = array('lw_form_type'=>$_POST['lw_form_type'],
											'lw_registration_pronouns'=>$_POST['form_c_lw_registration_pronouns'],
											'lw_registration_birthday'=>$_POST['form_c_lw_registration_birthday'],
											'lw_sibling_spent_time'=>$_POST['form_c_lw_sibling_spent_time'],
											'lw_emergency_area_code'=>$_POST['lw_emergency_area_code'],
											'lw_area_code'=>$_POST['form_c_lw_area_code'],
											'lw_mobilephone'=>$_POST['form_c_lw_mobilephone'],
											'lw_registration_guardian_first_name'=>$_POST['form_c_lw_registration_guardian_first_name'],
											'lw_registration_guardian_last_name'=>$_POST['form_c_lw_registration_guardian_last_name'],
											'lw_registration_guardian_email'=>$_POST['form_c_lw_registration_guardian_email'],
											'lw_registration_guardian_mobile_phone'=>$_POST['form_c_lw_registration_guardian_mobile_phone'],
											'lw_contact_you'=>$_POST['form_c_lw_contact_you']
					);
					
	
		}

			foreach($updatedMetaData as $k=>$v){
							update_user_meta($user_id,$k,$v);
			}
		
			$birthday_string = '';
		
			$lw_registration_birthday = get_user_meta($user_id, 'lw_registration_birthday', TRUE);
			
			//stop updating birthday meta key if no birth data
			if(empty($lw_registration_birthday)){
				return;
			}
			
			//check if it has date format error
			$timestamp = strtotime($lw_registration_birthday);
	
			if($timestamp == FALSE||date('Y-m-d', $timestamp) !== $lw_registration_birthday){
				return;
			}
			
			if (empty($lw_registration_birthday)) {
				$result = $wpdb->query("delete from ".$wpdb->prefix."usermeta where meta_key LIKE 'birthmmdd%' and user_id= ".$user_id."");
				return $user_id;
			}

			$birthday_mmdd = explode("-", $lw_registration_birthday);
			$birthday_mmdd = $birthday_mmdd[1].$birthday_mmdd[2];
			error_log($birthday_mmdd);
			if(!empty($birthday_mmdd)){
				// $query = $wpdb->prepare("delete from ".$wpdb->prefix."usermeta where meta_key LIKE 'birthmmdd%' and meta_key != 'birthmmdd%s' and user_id = %d and meta_value != '%s'", $birthday_mmdd, $user_id, $birthday_mmdd);
				$query = $wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}usermeta 
					WHERE meta_key LIKE 'birthmmdd%%' 
					AND meta_key != %s 
					AND user_id = %d 
					AND meta_value != %s", 
					'birthmmdd' . $birthday_mmdd, 
					$user_id, 
					$birthday_mmdd
				);
				$wpdb->query($query);

				update_user_meta($user_id, 'birthmmdd'. $birthday_mmdd, $birthday_mmdd);
			}
			$lwAdminUsers = new lwAdminUsers(); 
			$lwAdminUsers->processUserBirthday($user_id);
		}
		
		public function lw_invitation_general_settings() {
				require_once(LW_REGISTRATION_CLASS.'/admin/general_settings.php');
		}
		public function lw_invitation() {
				require_once(LW_REGISTRATION_CLASS.'/admin/lw_invitation.php');
		}
		public function lw_registration_admin_scripts() {
		   if(isset($_REQUEST['page']) && ($_REQUEST['page']=='lw_invitation' || $_REQUEST['page']=='lw_invitation_settings')){
				wp_enqueue_style( 'LW-style-admin-css', LW_REGISTRATION_ASSETS_URL . '/css/LW-style-admin.css');
				wp_enqueue_style( 'lw_registration_bootstrap-css', LW_REGISTRATION_ASSETS_URL . '/css/bootstrap.min.css');
				wp_enqueue_style( 'lw_registration_dataTables.bootstrap4.min-css', '//cdn.datatables.net/1.10.23/css/dataTables.bootstrap4.min.css');
				wp_enqueue_style( 'lw_registration_all.min.css', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
				
				wp_register_style( 'select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
				wp_register_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
				wp_enqueue_style( 'select2css' );
				wp_enqueue_script( 'select2' );
				
				
				wp_enqueue_script( 'lw_registration_jquery.validate.min', LW_REGISTRATION_ASSETS_URL . '/js/jquery.validate.min.js');
				wp_enqueue_script( 'lw_registration_jquery.dataTables.min.js', '//cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js');
				wp_enqueue_script( 'lw_registration_bootstrap.min.js', '//cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js');
				wp_enqueue_script( 'lw_registration_dataTables.bootstrap5.min.js', '//cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js');
				wp_enqueue_script( 'lw_registration_admin-js', LW_REGISTRATION_ASSETS_URL . '/js/lw_registration_admin.js');
				wp_localize_script( 'lw_registration_admin-js', 'lw_registration', array("ajaxUrl"=>admin_url( 'admin-ajax.php' )) );
		   }
		}
		public function lw_invitation_admin_action(){
			parse_str($_POST['data'], $postData);
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
						$responseReturn['message'] ='Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address.						';
						echo json_encode($responseReturn);
						exit;	
				}
				if(!isset($checkRegistration->ID) ){
					$checkRegistration = get_user_by("login",$postData['email']);
					if(isset($checkRegistration->ID) && $checkRegistration->ID>0){
						$is_exits  = 1;
						$responseReturn['status'] =0;
						$responseReturn['message'] ='Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address.						';
						echo json_encode($responseReturn);
						exit;	
					}
				}
			}
			if($is_exits ==0){
				$lw_general_settings = get_option('lw_general_settings');
				$data_array['user_id']=$current_user->ID;
				$data_array['first_name']=$postData['first_name'];
				$data_array['last_name']=$postData['last_name'];
				$data_array['email']=$postData['email'];
				$data_array['invitation_type']=$postData['invitation_type'];
				$data_array['expire_at']= date("Y-m-d H:i:s" ,strtotime("+".$lw_general_settings['token_expire_hours']." hours",strtotime(date("Y-m-d H:i:s"))));
				$wpdb->insert(TABLES_LW_REGISTRATION_INVITATION,$data_array); 
				$insertedId = $wpdb->insert_id;
				
				
				$tokenData = $_REQUEST['id']."::".$data_array['first_name']." ".$data_array['last_name']." ".$data_array['email'];
				$token = lw_registration_encrypt_decrypt('encrypt',$tokenData);
				$wpdb->update(TABLES_LW_REGISTRATION_INVITATION,array('token'=>$token),array('id'=>$insertedId));
				
				
				invitationEmailSend(array('first_name'=>$postData['first_name'],'last_name'=>$postData['last_name'],'email'=>$postData['email']
				,'token'=>$token,'invitation_type'=>$postData['invitation_type']));	
			
				$process='add';
				$message  = "Invitation successfully inserted.";
				$responseReturn['status'] =1;
			}else if($is_exits ==1){
				$process='exits';
				$message  = "Sorry this email is already in use, if you previously set up an account with us email livewire@starlight.org.au to reactivate it, otherwise try another email address.				";
				$responseReturn['status'] =0;
			}
			
				$responseReturn['message'] =$message;
				echo json_encode($responseReturn);
				exit;	
		}
		
		public function lw_invitation_admin_resend(){
			
			global $wpdb;
			$lw_general_settings = get_option('lw_general_settings');
				
			$checkRegistration = $wpdb->get_row('SELECT * FROM '.TABLES_LW_REGISTRATION_INVITATION.' WHERE  id="'.$_POST['id'].'"',ARRAY_A);
			$invitation_type=(int)$checkRegistration['invitation_type'];
			if(!empty($checkRegistration)){
				$responseReturn['status'] =1;
				$message = "Invitation has been successfully send.";
				$tokenData = $_POST['id']."::".$checkRegistration['first_name']." ".$checkRegistration['last_name']." ".$checkRegistration['email'];
				$token = lw_registration_encrypt_decrypt('encrypt',$tokenData);
				$data_array['expire_at']= date("Y-m-d H:i:s" ,strtotime("+".$lw_general_settings['token_expire_hours']." hours",strtotime(date("Y-m-d H:i:s"))));
				//extend 30 days if its wishgranting
				if($invitation_type==1){
						$data_array['expire_at']= date("Y-m-d H:i:s" ,strtotime("+30 days",strtotime(date("Y-m-d H:i:s"))));
				}

				$data_array['token']= $token;
				$data_array['status']= 0;
				
				invitationEmailSend(array('first_name'=>$checkRegistration['first_name'],'last_name'=>$checkRegistration['last_name'],'email'=>$checkRegistration['email']
				,'token'=>$token,'invitation_type'=>$invitation_type));	

				
				$wpdb->update(TABLES_LW_REGISTRATION_INVITATION,$data_array,array('id'=>$_POST['id']));
				
			}else{
				$message = "Sorry, Something worng. Please try again later.";
				$responseReturn['status'] =0;
					
			}
			
			$responseReturn['message'] =$message;
			echo json_encode($responseReturn);
			exit;	
		} 
	}
	
}
new LW_REGISTRATION_ADMIN_CLASS();