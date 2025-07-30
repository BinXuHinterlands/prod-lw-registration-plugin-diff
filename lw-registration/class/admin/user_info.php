<?php 
$user_id = 0;
if(isset($user->ID)){
	$user_id = $user->ID;	
}

$lw_area_code = get_user_meta( $user_id, 'lw_area_code', true);
$lw_emergency_area_code = get_user_meta( $user_id, 'lw_emergency_area_code', true);

$lw_mobilephone = get_user_meta( $user_id, 'lw_mobilephone', true);
$lw_registration_birthday = get_user_meta( $user_id, 'lw_registration_birthday', true);
$lw_registration_guardian_first_name = get_user_meta( $user_id, 'lw_registration_guardian_first_name', true);
$lw_registration_guardian_last_name = get_user_meta( $user_id, 'lw_registration_guardian_last_name', true);
$lw_registration_guardian_email = get_user_meta( $user_id, 'lw_registration_guardian_email', true);

$lw_registration_guardian_mobile_phone = get_user_meta( $user_id, 'lw_registration_guardian_mobile_phone', true);
$lw_contact_you = get_user_meta( $user_id, 'lw_contact_you', true);
$lw_registration_pronouns = get_user_meta( $user_id, 'lw_registration_pronouns', true);
$lw_sibling_spent_time = get_user_meta( $user_id, 'lw_sibling_spent_time', true);
$lw_form_type= get_user_meta( $user_id, 'lw_form_type', true);
$lw_registration_guardian_email= get_user_meta( $user_id, 'lw_registration_guardian_email', true);


 ?>
           
        	<h3>Registration Info: most data is deleted once sent to CRM</h3>
             <table class="form-table">
            
            <tr>
            	<th>Registration Form</th>
                <td>
                   <select name="lw_form_type" id="lw_form_type">
                   	<option value="">None</option>
                   	<option value="form_a" <?php echo (isset($lw_form_type) && $lw_form_type=="form_a")?"selected":""?>>Known to STL</option>
                   	<option value="form_b"  <?php echo (isset($lw_form_type) && $lw_form_type=="form_b")?"selected":""?>>Known to WG</option>
                   	<option value="form_c"  <?php echo (isset($lw_form_type) && $lw_form_type=="form_c")?"selected":""?>>Public</option>
                   </select> 
                </td>
            </tr>
            </table>
           	<div class="form_a">
           		 <table class="form-table">
            
             <tr>
            	<th>Birthday</th>
                <td>
                    <input type="date" name="form_a_lw_registration_birthday" value="<?php echo $lw_registration_birthday; ?>" class="form-control" />
                    
                </td>
            </tr>
            
            <tr>
            	<th>Pronouns</th>
                <td>
                    <input type="text" name="form_a_lw_registration_pronouns" value="<?php echo $lw_registration_pronouns; ?>"  class="form-control" />
                    
                </td>
            </tr>
           
           <tr>
            	<th>Area Code</th>
                <td>
                     <select name="form_a_lw_area_code" id="form_a_lw_area_code">
                     	<option value="+61" <?php echo ($lw_area_code=="+61")?"selected":""; ?>>+61</option>
                        <option value="+64" <?php echo ($lw_area_code=="+64")?"selected":""; ?>>+64</option>
                     </select>
                    
                </td>
            </tr>
            
            <tr>
            	<th>Phone Number</th>
                <td>
                    <input type="text" name="form_a_lw_mobilephone" value="<?php echo $lw_mobilephone; ?>" class="form-control" />
                    
                </td>
            </tr>
            
           
            <tr>
            	<th>Emergency Contact's First Name</th>
                <td>
                    <input type="text" name="form_a_lw_registration_guardian_first_name" value="<?php echo $lw_registration_guardian_first_name; ?>" class="form-control" />
                    
                </td>
            </tr>
            
              <tr>
            	<th>Emergency Contact's Last Name</th>
                <td>
                    <input type="text" name="form_a_lw_registration_guardian_last_name" value="<?php echo $lw_registration_guardian_last_name; ?>" class="form-control" />
                    
                </td>
            </tr>
            
            
              <tr>
            	<th>Emergency Contact's Email</th>
                <td>
                    <input type="text" name="form_a_lw_registration_guardian_email" value="<?php echo $lw_registration_guardian_email; ?>" class="form-control" />
                    
                </td>
            </tr>
            
            <tr>
            	<th>Emergency Contact Area Code</th>
                <td>
                     <select name="form_a_lw_emergency_area_code" id="form_a_lw_emergency_area_code">
                     	<option value="+61" <?php echo ($lw_emergency_area_code=="+61")?"selected":""; ?>>+61</option>
                        <option value="+64" <?php echo ($lw_emergency_area_code=="+64")?"selected":""; ?>>+64</option>
                     </select>
                    
                </td>
            </tr>
            
              <tr>
            	<th>Emergency Contact's Phone Number</th>
                <td>
                    <input type="text" name="form_a_lw_registration_guardian_mobile_phone" value="<?php echo $lw_registration_guardian_mobile_phone; ?>" class="form-control" />
                    
                </td>
            </tr>
           
            <tr>
            	<th>Anything we need to know before we contact your emergency contact?</th>
                <td>
                   <textarea placeholder="Tell us a good time to call, or any other communication preferences " name="form_a_lw_contact_you"><?php echo $lw_contact_you; ?></textarea>
                    
                </td>
            </tr>
            
           
            
        </table>
       		 </div>
             
             
             <div class="form_b">
           		 <table class="form-table">
          	 <tr>
            	<th>Pronouns</th>
                <td>
                    <input type="text" name="form_b_lw_registration_pronouns" value="<?php echo $lw_registration_pronouns; ?>"  class="form-control" />
                    
                </td>
            </tr>
          
            <tr>
            	<th>Birthday</th>
                <td>
                    <input type="date" name="form_b_lw_registration_birthday" value="<?php echo $lw_registration_birthday; ?>" class="form-control" />
                    
                </td>
            </tr>
            
           </table>
       		 </div>
        		<div class="form_c">
           		 <table class="form-table">
           <tr>
            	<th>Pronouns</th>
                <td>
                    <input type="text" name="form_c_lw_registration_pronouns" value="<?php echo $lw_registration_pronouns; ?>"  class="form-control" />
                    
                </td>
            </tr>
          
            
            <tr>
            	<th>Birthday</th>
                <td>
                    <input type="date" name="form_c_lw_registration_birthday" value="<?php echo $lw_registration_birthday; ?>" class="form-control" />
                    
                </td>
            </tr>
            
              <tr>
            	<th>Have you or your sibling spent time in hospital and/or have a chronic or serious health condition or disability?</th>
                <td>
                    <input type="radio" name="form_c_lw_sibling_spent_time" <?php echo ($lw_sibling_spent_time=="yes")?"checked":""; ?> value="yes" class="form-control " />Yes<br>
                    <input type="radio" name="form_c_lw_sibling_spent_time" value="no"  <?php echo ($lw_sibling_spent_time=="" || $lw_sibling_spent_time=="no")?"checked":""; ?> class="form-control " />No
                    
                </td>
            </tr>
            
             <tr>
            	<th>Area Code</th>
                <td>
                     <select name="form_c_lw_area_code" id="form_c_lw_area_code">
                     	<option value="+61" <?php echo ($lw_area_code=="+61")?"selected":""; ?>>+61</option>
                        <option value="+64" <?php echo ($lw_area_code=="+64")?"selected":""; ?>>+64</option>
                     </select>
                    
                </td>
            </tr>
            <tr>
            	<th>Phone Number</th>
                <td>
                    <input type="text" name="form_c_lw_mobilephone" value="<?php echo $lw_mobilephone; ?>" class="form-control" />
                    
                </td>
            </tr>
           
            <tr>
            	<th>Emergency Contact's First Name</th>
                <td>
                    <input type="text" name="form_c_lw_registration_guardian_first_name" value="<?php echo $lw_registration_guardian_first_name; ?>" class="form-control" />
                    
                </td>
            </tr>
            
              <tr>
            	<th>Emergency Contact's Last Name</th>
                <td>
                    <input type="text" name="form_c_lw_registration_guardian_last_name" value="<?php echo $lw_registration_guardian_last_name; ?>" class="form-control" />
                    
                </td>
            </tr>
            <tr>
            	<th>Emergency Contact's Email</th>
                <td>
                    <input type="text" name="form_c_lw_registration_guardian_email" value="<?php echo $lw_registration_guardian_email; ?>" class="form-control" />
                    
                </td>
            </tr>
            <tr>
            	<th>Emergency Contact Area Code</th>
                <td>
                     <select name="form_c_lw_emergency_area_code" id="form_c_lw_emergency_area_code">
                     	<option value="+61" <?php echo ($lw_emergency_area_code=="+61")?"selected":""; ?>>+61</option>
                        <option value="+64" <?php echo ($lw_emergency_area_code=="+64")?"selected":""; ?>>+64</option>
                     </select>
                    
                </td>
            </tr>
              <tr>
            	<th>Emergency Contact's Phone Number</th>
                <td>
                    <input type="text" name="form_c_lw_registration_guardian_mobile_phone" value="<?php echo $lw_registration_guardian_mobile_phone; ?>" class="form-control" />
                    
                </td>
            </tr>
            
            <tr>
            	<th>Anything we need to know before we contact your emergency contact?</th>
                <td>
                   <textarea placeholder="Tell us a good time to call, or any other communication preferences " name="form_c_lw_contact_you"><?php echo $lw_contact_you; ?></textarea>
                    
                </td>
            </tr>
            
           
            
        </table>
       		 </div>
       
        <script>
        	function showLWRegistrationFields(){
				var lw_form_type = jQuery("#lw_form_type").val();
				jQuery(".form_a,.form_b,.form_c").hide();
				if(lw_form_type=="form_a"){
					jQuery(".form_a").show();	
				}else if(lw_form_type=="form_b"){
					jQuery(".form_b").show();	
				}else if(lw_form_type=="form_c") {
					jQuery(".form_c").show();	
				}
					
			}
			
			jQuery(document).ready(function(e) {
                showLWRegistrationFields();
            });
			jQuery(document).on("change","#lw_form_type",function(){
				  showLWRegistrationFields();
			});
        </script>