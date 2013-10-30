<?php

class Raplet extends CI_Controller {

	function __construct() {
		parent::__construct();
		
		$this->load->model('RapletOAP');
	}	
	
	public function index()
	{
		
		$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
		
		$parameters = array();
		
		if($callback == 'metadata') {
			
			$parameters =  array('name' => '',
							'description' => '',
							'welcome_text' => '<p><a href="http://www.fuzedapp.com">Fuzed</a> is an essential resource for tech entrepreneurs and investors.</p>',
							'icon_url' => '<img src="https://connect.fuzedapp.com/images/favicon.ico">',
							'preview_url' => '<img src="https://connect.fuzedapp.com/images/favicon.ico">',
							'provider_name' => "Rapportive",
							'provider_url'=> "http://rapportive.com/",
							'data_provider_name' => "Fuzed API",
							'data_provider_url' => "http://www.fuzedapp.com/"
						);
		
		} else {
 			
 			$sec_code = $this->uri->segment(3);
 			$user_id = $this->RapletOAP->setModel($sec_code);
 			
			$css = 'p {
						margin: 0; 
						padding: 2px;
						font-size: 12px;
					} 
					p input{
						padding: 5px;
						width: 93%;
					} 
					p select {
						padding: 5px;
						width: 77%;
					}
					p a:hover {
						text-decoration: underline;
					} 
					p.tags {
						padding: 3px 8px; 
						border-bottom: 1px solid #ccc;
					} 			
					p.head {
						padding: 4px 8px; 
						cursor: pointer; 
						position: relative; 
						background-color: #eee; 
						font-size:12px; 
						font-weight: bold;
					} 
					p.sub_head {
						padding: 3px 8px; 
						position: relative; 
						background-color: #eee; 
						font-weight: bold;
						margin: 3px 0 3px 0;
					} 
					div.info { 
						padding: 5px 10px 15px;
						color:#444; 
						font-size: 12px; 
					}
					#tag_list {
						padding: 2px; 
					}
					#view_contact {
						cursor: pointer;
					}
					#view_contact:hover {
						text-decoration: underline;
					}
					#addtag {
						width: auto;
					}
					.remove {
						cursor: pointer;
						color: blue;
						float: right;
					}
					.remove:hover {
						text-decoration: underline;
					}
					.link {
						color: blue;
					}
					';
					
				$js = ' var loading = "<div align=\'center\'><img src=\'https://connect.fuzedapp.com/images/loading9.gif\' /></div>";
			
						$("div.info").hide();
						$("#contact_info").hide(); 
						$("p.head").click(function(){ 
							$(this).next("div.info").slideToggle(600);
						});
					
						$("#view_contact").click(function(){ 
							$(this).next("#contact_info").slideToggle(600);
						});
					
						$("#add_cont").click(function(){ 
							var valid = true;
							if($("#ontraport #fname").val() == "") {
								$("#ontraport #fname").css("border", "1px solid red");
								valid = false;
							}
						
							if($("#ontraport #sname").val() == "") {
								$("#ontraport #sname").css("border", "1px solid red");
								valid = false;
							}
						
							if($("#ontraport #email").val() == "") {
								$("#ontraport #email").css("border", "1px solid red");
								valid = false;
							}
						
							if(valid) {
													
								var fname = $("#ontraport #fname").val();
								var sname = $("#ontraport #sname").val();
								var email = $("#ontraport #email").val();
								var cp = $("#ontraport #cp").val();
								var address2 = $("#ontraport #address2").val();
								var zipcode = $("#ontraport #zipcode").val();
								var city = $("#ontraport #city").val();
								var state = $("#ontraport #state").val();
									
								var url = "fname=" + fname + "&sname=" + sname + "&email=" + email +
											"&cp=" + cp + "&address2=" + address2 + "&zipcode=" + zipcode + "&city=" + city +
											"&state=" + state;
							
								$("#contact_info").html(loading);
							
								$.post("https://'.$_SERVER['HTTP_HOST'].'/raplet/addcontact/'.$sec_code.'", url, function(data){
								
									if(data != "") {
										
										var txt = data.split("{::}");
									
										$("#contact_info").html(txt[0]);
										$("#per_info").html(txt[1]);
										
										$("#contact_info").slideToggle(600);
										$("#view_contact").html("Update Contact");
									
										$("#tag_list").show();
									
										$("#update_cont").click(function(){ 
											var valid = true;
											if($("#ontraport #fname").val() == "") {
												$("#ontraport #fname").css("border", "1px solid red");
												valid = false;
											}
						
											if($("#ontraport #sname").val() == "") {
												$("#ontraport #sname").css("border", "1px solid red");
												valid = false;
											}
						
											if($("#ontraport #email").val() == "") {
												$("#ontraport #email").css("border", "1px solid red");
												valid = false;
											}
						
											if(valid) {
												var oap_id = $("#ontraport #oap_id").val();
												var fname = $("#ontraport #fname").val();
												var sname = $("#ontraport #sname").val();
												var email = $("#ontraport #email").val();
												var cp = $("#ontraport #cp").val();
												var address2 = $("#ontraport #address2").val();
												var zipcode = $("#ontraport #zipcode").val();
												var city = $("#ontraport #city").val();
												var state = $("#ontraport #state").val();
									
												var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
															"&cp=" + cp + "&address2=" + address2 + "&zipcode=" + zipcode + "&city=" + city +
															"&state=" + state;
							
												$("#contact_info").html(loading);
							
												$.post("https://'.$_SERVER['HTTP_HOST'].'/raplet/updatecontact/'.$sec_code.'", url, function(data){
								
													if(data != "") {
														var txt = data.split("{::}");
									
														$("#contact_info").html(txt[0]);
														$("#per_info").html(txt[1]);
														
														$("#contact_info").slideToggle(600);
														$("#view_contact").html("Update Contact");
													
													} else {
														$("#msg").html("There is an unknown issue. Please try again.");
													}
												});
											}
						
										});
									
									} else {
										$("#msg").html("There is an unknown issue. Please try again.");
									}
								});
							}
						
						});
					
						$("#update_cont").click(function(){ 
							var valid = true;
							if($("#ontraport #fname").val() == "") {
								$("#ontraport #fname").css("border", "1px solid red");
								valid = false;
							}
						
							if($("#ontraport #sname").val() == "") {
								$("#ontraport #sname").css("border", "1px solid red");
								valid = false;
							}
						
							if($("#ontraport #email").val() == "") {
								$("#ontraport #email").css("border", "1px solid red");
								valid = false;
							}
						
							if(valid) {
								var oap_id = $("#ontraport #oap_id").val();
								var fname = $("#ontraport #fname").val();
								var sname = $("#ontraport #sname").val();
								var email = $("#ontraport #email").val();
								var cp = $("#ontraport #cp").val();
								var address2 = $("#ontraport #address2").val();
								var zipcode = $("#ontraport #zipcode").val();
								var city = $("#ontraport #city").val();
								var state = $("#ontraport #state").val();
									
								var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
											"&cp=" + cp + "&address2=" + address2 + "&zipcode=" + zipcode + "&city=" + city +
											"&state=" + state;
							
								$("#contact_info").html(loading);
							
								$.post("https://'.$_SERVER['HTTP_HOST'].'/raplet/updatecontact/'.$sec_code.'", url, function(data){
								
									if(data != "") {
									
										var txt = data.split("{::}");
									
										$("#contact_info").html(txt[0]);
										$("#per_info").html(txt[1]);

										$("#contact_info").slideToggle(600);
										$("#view_contact").html("Update Contact");
									
										$("#update_cont").click(function(){ 
											var valid = true;
											if($("#ontraport #fname").val() == "") {
												$("#ontraport #fname").css("border", "1px solid red");
												valid = false;
											}
						
											if($("#ontraport #sname").val() == "") {
												$("#ontraport #sname").css("border", "1px solid red");
												valid = false;
											}
						
											if($("#ontraport #email").val() == "") {
												$("#ontraport #email").css("border", "1px solid red");
												valid = false;
											}
						
											if(valid) {
											
												var oap_id = $("#ontraport #oap_id").val();
												var fname = $("#ontraport #fname").val();
												var sname = $("#ontraport #sname").val();
												var email = $("#ontraport #email").val();
												var cp = $("#ontraport #cp").val();
												var address2 = $("#ontraport #address2").val();
												var zipcode = $("#ontraport #zipcode").val();
												var city = $("#ontraport #city").val();
												var state = $("#ontraport #state").val();
												
												var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
															"&cp=" + cp + "&address2=" + address2 + "&zipcode=" + zipcode + "&city=" + city +
															"&state=" + state ;
							
												$("#contact_info").html(loading);
							
												$.post("https://'.$_SERVER['HTTP_HOST'].'/raplet/updatecontact/'.$sec_code.'", url, function(data){
								
													if(data != "") {
														var txt = data.split("{::}");
									
														$("#contact_info").html(txt[0]);
														$("#per_info").html(txt[1]);
														
														$("#contact_info").slideToggle(600);
														$("#view_contact").html("Update Contact");
													
													} else {
														$("#msg").html("There is an unknown issue. Please try again.");
													}
												});
											}
						
										});
								
									} else {
										$("#msg").html("There is an unknown issue. Please try again.");
									}
								});
							}
						
						});
					
						$("#addtag").click(function(){ 
							var valid = true;
						
							if($("#ontraport #tag_id").val() == 0) {
								$("#ontraport #tag_id").css("border", "1px solid red");
								valid = false;
							}
						
							if(valid) {
								var tag_id = $("#ontraport #tag_id").val();
								var oap_id = $("#ontraport #oap_id").val();
								var email =  $("#ontraport #email").val();
								var url = "tag_name=" + tag_id + "&oap_id=" + oap_id + "&email=" + email;

								$("#tag_p").html(loading);
							
								$.post("https://'.$_SERVER['HTTP_HOST'].'/raplet/addcontacttag/'.$sec_code.'", url, function(data) {
									$("#tag_p").html(data);	
								
									$(".remove").click(function(){ 
					
										var tag_name = $(this).attr("rel");
										var id = $(this).attr("db");
			
										var oap_id = $("#ontraport #oap_id").val();
										var email =  $("#ontraport #email").val();
										var url = "tag_name=" + tag_name + "&oap_id=" + oap_id + "&email=" + email;
									
										$(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
									
										$.post("https://'.$_SERVER['HTTP_HOST'].'/raplet/removecontacttag/'.$sec_code.'", url, function(data) {
											$("#" + id).hide();
										});
									});
								
								});
							}
						
						});
					
						$(".remove").click(function(){ 
					
							var tag_name = $(this).attr("rel");
							var id = $(this).attr("db");
			
							var oap_id = $("#ontraport #oap_id").val();
							var email =  $("#ontraport #email").val();
							var url = "tag_name=" + tag_name + "&oap_id=" + oap_id + "&email=" + email;
						
							$(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
						
							$.post("https://'.$_SERVER['HTTP_HOST'].'/raplet/removecontacttag/'.$sec_code.'", url, function(data) {
								$("#" + id).hide();
							});
						});
						';					
 			
 			if ($user_id != '' && $user_id > 0) {
 
				if(isset($_GET['email'])) {
				   $email_plain = htmlentities($_GET['email']);
				   $contact= $this->RapletOAP->getInfoOAP($email_plain);
				   $email = '';
				   if(count($contact) > 0) {
					   $email = strlen($email_plain) > 24 ? substr($email_plain, 0, 24).'...' : $email_plain;
					   $email = '<a title="'.$_GET['email'].'" href="mailto:'.$_GET['email'].'">'.$email.'</a>';
					}
				} else {
				   $email_plain = '';
				   $email = '';
				   $contact = null;
				}
 
				if(isset($_GET['twitter_username'])) {
				   $twitter = htmlentities($_GET['twitter_username']);
				} else {
				   $twitter = "Twitter account not found.";
				}
				
				//print_r($contact['tagid']);
				//print_r($contact['tags']);
			
				$tags = ''; $id = 0;
				if(isset($contact['tags']) && count($contact['tags']) > 0) {
					foreach($contact['tags'] as $tag) {
					
						if($tag != '') {
						
							$tag_name = strlen($tag) > 19 ? substr($tag, 0, 19).'...' : $tag;
							$tags .= '<p class="tags" id="'.$contact['tagid'][$id].'"> <img src="https://connect.fuzedapp.com/images/tag.png" /> <span title="'.$tag.'">'.$tag_name.'</span> <span class="remove" title="Click to remove this tag." db="'.$contact['tagid'][$id].'" rel="'.$tag.'" return false;"><img src="https://connect.fuzedapp.com/images/remove.png" /></span></p>';	
							$id++;
						}
						//echo $id.' - '.$contact['tagid'][$id];
						//$id++;
					
					}
				}
				
				$sequence = '';
				if(isset($contact['sequences']) && count($contact['sequences']) > 0) {
					foreach($contact['sequences'] as $seq) {
						if($seq != '') {
							$seque 	= $this->RapletOAP->getSequenceName($seq);
							$sequence .= '<p class="tags">'.$seque.'</p>';
						}					
					}
				}
						
				$tag_select = '';
				$tag_list = $this->RapletOAP->getTagOAP();
				if(count($tag_list) > 0) {
					$tag_select = '<select id="tag_id" name="tag_id[]">';
					$tag_select .= '<option value="0">Select Tag</option>';
					//$tag_select .= '<option value="-99">[Add New Tag]</option>';
					foreach($tag_list as $tag) {
						$tag_select .= '<option value="'.$tag['tag_name'].'">'.$tag['tag_name'].'</option>';
					}
					$tag_select .= '</select><input type="button" title="Add Tag" value="+" name="addtag" id="addtag" />';
				}
			
				if(count($contact) > 0) { 
					$action = 'Update Contact';
					$save_id = 'update_cont';
					$tag_html = '<div id="tag_list">
									<p class="sub_head">Tags</p>
									<div id="tag_p">'.$tags.'</div>
									<p>'.$tag_select.'</p>
									<p class="sub_head">Sequences</p>
									<p>'.$sequence.'</p>
								 </div>';
				
					$fname 	= isset($contact['fname']) ? $contact['fname'] : '';
					$sname 	= isset($contact['sname']) ? $contact['sname'] : '';
					
					$cp 	= isset($contact['cp']) ? $contact['cp'] : '';
					$address2= isset($contact['address2']) ? $contact['address2'] : '';
					$zipcode= isset($contact['zipcode']) ? $contact['zipcode'] : '';
					$city 	= isset($contact['city']) ? $contact['city'] : '';
					$state 	= isset($contact['state']) ? $contact['state'] : '';
			
					$name = $fname.' '.$sname;
					$name = strlen($name) > 24 ? substr($name, 0, 24).'...' : $name;
					$name = '<p title="'.$fname.' '.$sname.'">'.$name.'</p>';
					
					$other_info = '<p>'.$cp.'</p>
									<p>'.$address2.'</p>
									<p>'.$city.'</p>
									<p>'.$state.' '.$zipcode.'</p>';

			   
				} else {
					$action = 'Add Contact';
					$save_id = 'add_cont';
					$tag_html = '<div id="tag_list" style="display:none;">
									<p class="sub_head">Tags</p>
									<div id="tag_p"></div>
									<p>'.$tag_select.'</p>
									<p class="sub_head">Sequences</p>
									<p>'.$sequence.'</p>									
								 </div>';
								 								 
					if(isset($_GET['name'])) {
					   $ln = explode(' ', htmlentities($_GET['name']));
				   
					   $fname = isset($ln[0]) ? $ln[0] : '';
					   $sname = isset($ln[1]) ? $ln[1] : '';
				   
					} else {
					   $fname = '';
					   $sname = '';
					}
					
					$cp = '';
					$address2= '';
					$zipcode= '';
					$city 	= '';
					$state 	= '';
					$name = '';
					$other_info = '';
					
				}
			
				$id	= isset($contact['id']) ? $contact['id'] : 0;

				$html = '<p class="head">
							<img src="https://connect.fuzedapp.com/images/favicon.ico">
							Fuzed Contact Record
						</p>
						<div class="info">
							<form id="ontraport">
								<div id="per_info">
									'.$name.'
									<p>'.$email.'</p>
									'.$other_info.'
								</div>
								<p id="view_contact" class="sub_head">'.$action.'</p>
								<div id="contact_info">
									<input type="hidden" value="'.$id.'" name="oap_id" id="oap_id" />
									<p><input type="text" value="'.$fname.'" name="fname" id="fname" placeholder="First Name" /></p>
									<p><input type="text" value="'.$sname.'" name="sname" id="sname" placeholder="Surname" /></p>
									<p><input type="text" value="'.$email_plain.'" name="email" id="email" placeholder="Email" /></p>
									<p><input type="text" value="'.$cp.'" name="cp" id="cp" placeholder="Cellphone Number" /></p>
									<p><input type="text" value="'.$address2.'" name="address2" id="address2" placeholder="Address2" /></p>
									<p><input type="text" value="'.$zipcode.'" name="zipcode" id="zipcode" placeholder="Zip Code" /></p>
									<p><input type="text" value="'.$city.'" name="city" id="city" placeholder="City" /></p>
									<p><select id="state" name="state"><option value="">No State</option><option value="AL">Alabama</option><option value="AK">Alaska</option><option value="AZ">Arizona</option><option value="AR">Arkansas</option><option value="CA">California</option><option value="CO">Colorado</option><option value="CT">Connecticut</option><option value="DE">Delaware</option><option value="DC">D.C.</option><option value="FL">Florida</option><option value="GA">Georgia</option><option value="HI">Hawaii</option><option value="ID">Idaho</option><option value="IL">Illinois</option><option value="IN">Indiana</option><option value="IA">Iowa</option><option value="KS">Kansas</option><option value="KY">Kentucky</option><option value="LA">Louisiana</option><option value="ME">Maine</option><option value="MD">Maryland</option><option value="MA">Massachusetts</option><option value="MI">Michigan</option><option value="MN">Minnesota</option><option value="MS">Mississippi</option><option value="MO">Missouri</option><option value="MT">Montana</option><option value="NE">Nebraska</option><option value="NV">Nevada</option><option value="NH">New Hampshire</option><option value="NM">New Mexico</option><option value="NJ">New Jersey</option><option value="NY">New York</option><option value="NC">North Carolina</option><option value="ND">North Dakota</option><option value="OH">Ohio</option><option value="OK">Oklahoma</option><option value="OR">Oregon</option><option value="PA">Pennsylvania</option><option value="RI">Rhode Island</option><option value="SC">South Carolina</option><option value="SD">South Dakota</option><option value="TN">Tennessee</option><option value="TX">Texas</option><option value="UT">Utah</option><option value="VT">Vermont</option><option value="VA">Virginia</option><option value="WA">Washington</option><option value="WV">West Virginia</option><option value="WI">Wisconsin</option><option value="WY">Wyoming</option><option value="AB">Alberta</option><option value="BC">British Columbia</option><option value="MB">Manitoba</option><option value="NB">New Brunswick</option><option value="NL">Newfoundland and Labrador</option><option value="NS">Nova Scotia</option><option value="NT">Northwest Territories</option><option value="NU">Nunavut</option><option value="ON">Ontario</option><option value="PE">Prince Edward Island</option><option value="QC">Quebec</option><option value="SK">Saskatchewan</option><option value="YT">Yukon</option><option value="ACT">(AU) Australian Capital Territory</option><option value="NSW">(AU) New South Wales</option><option value="VIC">(AU) Victoria</option><option value="QLD">(AU) Queensland</option><option value="AU_NT">(AU) Northern Territory</option><option value="AU_WA">(AU) Western Australia</option><option value="SA">(AU) South Australia</option><option value="AU_TAS">(AU) Tasmania</option><option value="GP">(AF) Gauteng</option><option value="WP">(AF) Western Cape</option><option value="EC">(AF) Eastern Cape</option><option value="KZN">(AF) Kwa-Zulu Natal</option><option value="NW">(AF) North West</option><option value="AF_NC">(AF) Northern Cape</option><option value="MP">(AF) Mpumulanga</option><option value="FS">(AF) Free State</option></select></p>						
									<p><input type="button" value="'.$action.'" name="save" id="'.$save_id.'" /></p>
								</div>
								'.$tag_html.'
								<p id="msg"></p>
							</form>
						</div>';

			} else if($user_id == -98) {
					$html = '<p class="head">
								<img src="https://connect.fuzedapp.com/images/favicon.ico">
								Fuzed Contact Record
							</p>
							<div class="info">
								<p> Unfortunately, your account cannot be retrieve from <a class="link" href="http://fuzedapp.com">fuzedapp.com.</p>
							</div>';
			} else if($user_id == -97) { 
					$html = '<p class="head">
								<img src="https://connect.fuzedapp.com/images/favicon.ico">
								Fuzed Contact Record
							</p>
							<div class="info">
								<p> Sorry, you need to upgrade your subscription before you can access this feature from <a class="link" href="http://fuzedapp.com">fuzedapp.com.</p>
							</div>';
			} else if($user_id == -96) { 
					$html = '<p class="head">
								<img src="https://connect.fuzedapp.com/images/favicon.ico">
								Fuzed Contact Record
							</p>
							<div class="info">
								<p>ONTRAPORT or OfficeAutoPilot needs to connect with your <a class="link" href="https://'.$_SERVER['HTTP_HOST'].'/main/account_settings">Fuzed</a> account.</p>
							</div>';
			}
			
			$js .= '$("#ontraport #state option").filter(function() {
						return this.text == "'.$state.'"; 
					}).attr("selected", true);';
			
			$parameters['html'] = $html;
			$parameters['css'] = $css;
			$parameters['js'] = $js;
			$parameters['status'] = 200;
 
		}
		
		$this->output->set_header("Content-Type: application/json");
		
		//We encode our response as JSON and prepend the callback to it
		$object = $callback."(".json_encode($parameters).")";
		
		echo $object;
	
	}
	
	public function disp()
	{
			$this->load->model('RapletOAP');
			
			$data = $this->RapletOAP->getInfoOAP('dave@multimediamarketingshow.com');
			
			echo '<pre>';
			print_r($data);
			echo '</pre>';
	
	}
	
	public function addcontact() {
		
		$fname = $_POST['fname'];
		$sname = $_POST['sname'];
		$email = $_POST['email'];
		$cp 	= $_POST['cp'];
		$address2= $_POST['address2'];
		$zipcode= $_POST['zipcode'];
		$city 	= $_POST['city'];
		$state 	= $_POST['state'];
		
		$str = '';
			
		$sec_code = $this->uri->segment(3);
 		$user_id = $this->RapletOAP->setModel($sec_code);
		
		if($user_id > 0) {
			
			$data = array($fname, $sname, $email, $cp, $address2, $zipcode, $city, $state);
			$tag = isset($_POST['tags']) ? $_POST['tags'] : '';
			
			$conID = $this->RapletOAP->addContactOAP($data, $tag);
			
			$state_options = $this->RapletOAP->state_options($state);
			
			if(isset($conID->contact->attributes()->id) && $conID->contact->attributes()->id != '') {
		
				$id = (int)$conID->contact->attributes()->id;
			 
				$str = '<input type="hidden" value="'.$id.'" name="oap_id" id="oap_id" />
						<p><input type="text" value="'.$fname.'" name="fname" id="fname" placeholder="First Name" /></p>
						<p><input type="text" value="'.$sname.'" name="sname" id="sname" placeholder="Surname" /></p>
						<p><input type="text" value="'.$email.'" name="email" id="email" placeholder="Email" /></p>
						<p><input type="text" value="'.$cp.'" name="cp" id="cp" placeholder="Cellphone Number" /></p>
						<p><input type="text" value="'.$address2.'" name="address2" id="address2" placeholder="Address2" /></p>
						<p><input type="text" value="'.$zipcode.'" name="zipcode" id="zipcode" placeholder="Zip Code" /></p>
						<p><input type="text" value="'.$city.'" name="city" id="city" placeholder="City" /></p>
						<p><select id="state" name="state">'.$state_options.'</select></p>
						<p><input type="button" value="Update Contact" name="save" id="update_cont" /></p>{::}';
				
				$email_plain = $email;
				
				$email = strlen($email_plain) > 24 ? substr($email_plain, 0, 24).'...' : $email_plain;
				$email = '<a title="'.$email_plain.'" href="mailto:'.$email_plain.'">'.$email.'</a>';
				
				$state_name = $this->RapletOAP->state_name($state);
				
				$str .= $fname.' '.$sname.'
						<p>'.$email.'</p>
						<p>'.$cp.'</p>
						<p>'.$address2.'</p>
						<p>'.$city.'</p>
						<p>'.$state.' '.$zipcode.'</p>';
				
			}
		
			echo $str;	
		} else {
			echo 'The response doesnt have a correct User ID.';
		}
	}
	
	public function updatecontact() {
		
		$fname = $_POST['fname'];
		$sname = $_POST['sname'];
		$email = $_POST['email'];
		$oap_id = $_POST['oap_id'];
		$cp 	= $_POST['cp'];
		$address2= $_POST['address2'];
		$zipcode= $_POST['zipcode'];
		$city 	= $_POST['city'];
		$state 	= $_POST['state'];
		
		$str = '';
		
		$sec_code = $this->uri->segment(3);
 		$user_id = $this->RapletOAP->setModel($sec_code);
		
		if($user_id > 0) {
		
			$data = array($oap_id, $fname, $sname, $email, $cp, $address2, $zipcode, $city, $state);
			$conID = $this->RapletOAP->updateContactOAP($data);

			if(isset($conID->contact->attributes()->id) && $conID->contact->attributes()->id != '') {
				$id = $conID->contact->attributes()->id;
				$state_options = $this->RapletOAP->state_options($state);
			 
				$str = '<input type="hidden" value="'.$id.'" name="oap_id" id="oap_id" />
						<p><input type="text" value="'.$fname.'" name="fname" id="fname" placeholder="First Name" /></p>
						<p><input type="text" value="'.$sname.'" name="sname" id="sname" placeholder="Surame" /></p>
						<p><input type="text" value="'.$email.'" name="email" id="email" placeholder="Email" /></p>
						<p><input type="text" value="'.$cp.'" name="cp" id="cp" placeholder="Cellphone Number" /></p>
						<p><input type="text" value="'.$address2.'" name="address2" id="address2" placeholder="Address2" /></p>
						<p><input type="text" value="'.$zipcode.'" name="zipcode" id="zipcode" placeholder="Zip Code" /></p>
						<p><input type="text" value="'.$city.'" name="city" id="city" placeholder="City" /></p>
						<p><select id="state" name="state">'.$state_options.'</select></p>						
						<p><input type="button" value="Update Contact" name="save" id="update_cont" /></p>{::}';

				$email_plain = $email;
				
				$email = strlen($email_plain) > 24 ? substr($email_plain, 0, 24).'...' : $email_plain;
				$email = '<a title="'.$email_plain.'" href="mailto:'.$email_plain.'">'.$email.'</a>';
				
				$state_name = $this->RapletOAP->state_name($state);
				
				$str .= $fname.' '.$sname.'
				<p>'.$email.'</p>
				<p>'.$cp.'</p>
				<p>'.$address2.'</p>
				<p>'.$city.'</p>
				<p>'.$state.' '.$zipcode.'</p>'; 						
				
			}
		
			echo $str;	
		} else {
			echo 'The response doesnt have a correct User ID.';
		}
	}
	
	public function addcontacttag() {
		
		$oap_id = $_POST['oap_id'];
		$tag_name = $_POST['tag_name'];
		$email = $_POST['email'];
		
		$sec_code = $this->uri->segment(3);
 		$user_id = $this->RapletOAP->setModel($sec_code);
		
		if($user_id > 0) {
		
			$info = $this->RapletOAP->addContactTagOAP($oap_id, $tag_name, $email);
		
			$contact = $this->RapletOAP->getInfoOAP($email);
		
			$tag_str = ''; $id = 0;
			if(isset($contact['tags']) && count($contact['tags']) > 0) {
				foreach($contact['tags'] as $tag) {
				
					if($tag != '') {
							$tag_name = strlen($tag) > 19 ? substr($tag, 0, 19).'...' : $tag;
							$tag_str .= '<p class="tags" id="'.$contact['tagid'][$id].'"> <img src="https://connect.fuzedapp.com/images/tag.png" /> <span title="'.$tag.'">'.$tag_name.'</span> <span class="remove" title="Click to remove this tag." db="'.$contact['tagid'][$id].'" rel="'.$tag.'" return false;"><img src="https://connect.fuzedapp.com/images/remove.png" /></span></p>';	
							$id++;
					}
								
				}
			}
		
			echo $tag_str;
		} else {
			echo 'The response doesnt have a correct User ID. ';
		}
		
	}
	
	public function removecontacttag() {
		
		$oap_id = $_POST['oap_id'];
		$tag_name = $_POST['tag_name'];
		$email = $_POST['email'];
		
		$sec_code = $this->uri->segment(3);
 		$user_id = $this->RapletOAP->setModel($sec_code);
		
		if($user_id > 0) {
			$info = $this->RapletOAP->removeTagOAP($oap_id, $tag_name, $email);
			echo $info->contact->tag;
		} else {
			echo 'The data has not seen any user ID.';
		}
		
	}

}