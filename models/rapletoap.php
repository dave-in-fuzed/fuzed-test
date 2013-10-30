<?php

require_once APPPATH.'libraries/oap/OAPApi.class.php';

class RapletOAP extends CI_Model {
	
	var $code = '';
	var $user_id = '';
	
	function __construct()
    {
        parent::__construct();
    }
    
    function setModel($code = null) {
    	
    	$return_code = -97;
    	$query = $this->db->query("SELECT * FROM users WHERE raplet_code = '".$code."'");
		if ($query->num_rows() > 0) {
		
			$user = $query->row();
			
			$oap_conn = $this->Services->getServiceUserConnection($user->id, 3);
			$raplet = $this->Services->getConnectionFuzeRaplet($user->id);
			$oap = isset($raplet->oap) ? $raplet->oap : 0;
			
			if($user->access_type != 6 && count($oap_conn) > 0 && $oap == 1) {
				$this->user_id = $user->id;	
				$return_code = $this->user_id;
			} else if($user->access_type != 6 && count($oap_conn) < 1 && $oap == 1) {
				$return_code = -96;
			} else {
				$return_code = -98;
			}
		}
		
		$this->code = $code;
	
		return $return_code;
    
    }
    
    function setModelFreshdesk($code = null) {
    	
    	$return_code = -97;
    	$query = $this->db->query("SELECT * FROM users WHERE raplet_code = '".$code."'");
		if ($query->num_rows() > 0) {
		
			$user = $query->row();
			
			$oap_conn = $this->Services->getServiceUserConnection($user->id, 3);
			$freshdesk = $this->Services->getConnectionFuzeFreshdesk($user->id);
			$oap = isset($freshdesk->oap) ? $freshdesk->oap : 0;
			
			if($user->access_type != 6 && count($oap_conn) > 0 && $oap == 1) {
				$this->user_id = $user->id;	
				$return_code = $this->user_id;
			} else if($user->access_type != 6 && count($oap_conn) < 1 && $oap == 1) {
				$return_code = -96;
			} else {
				$return_code = -98;
			}
		}
		
		$this->code = $code;
	
		return $return_code;
    
    }

	function getInfoOAP($email = '', $user_id = null)
    {
		
		
		$data = array();
		if($this->user_id != '') {
			$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
			if(count($keys) > 0) {
				$appName = $keys->appname;
				$apiKey	 = $keys->key;

				$oap = new OapApi($appName, $apiKey);
				$oap_data = $oap->findByEmail($email);
			
				$fname = '';
				$sname = '';
				$cp	= '';
				$address = '';
				$zipcode = '';
				$city = '';
				$state = '';
				$phone = '';
				$company = '';
				
				$tags = array();
				$tagid = array();
			
				//echo '<pre>';
				//print_r($oap_data);
				//echo '</pre>';
			
				if (count($oap_data) > 0) {

					$find_id = (int)$oap_data->contact->attributes()->id;
				
					foreach($oap_data->contact->Group_Tag as $user_data) {
				
						if ($user_data->attributes()->name == 'Contact Information') {
						
							foreach($user_data->field as $field) {
						
								if($field->attributes() == 'First Name')
									$fname	= (string)$field;
						
								if($field->attributes() == 'Last Name')
									$sname	= (string)$field;
							
								if($field->attributes() == 'E-Mail')
									$email	= (string)$field;
									
								if($field->attributes() == 'Cell Phone')
									$cp	= (string)$field;
								
								if($field->attributes() == 'Address')
									$address = (string)$field;
								
								if($field->attributes() == 'Zip Code')
									$zipcode = (string)$field;
								
								if($field->attributes() == 'City')
									$city = (string)$field;
									
								if($field->attributes() == 'State')
									$state = (string)$field;
									
								if($field->attributes() == 'Office Phone' && $field != '()-')
									$phone = (string)$field;
									
								if($field->attributes() == 'Company')
									$company = (string)$field;									
						
							}
					
						}
					
						if ($user_data->attributes()->name == 'Sequences and Tags') {
							
							$sequences	= explode('*/*', $user_data->field[0]);
							$group   = explode('*/*', $user_data->field[1]);
							$tagid   = explode('*/*', $user_data->field[2]);
							
							//print_r($group);
							//print_r($tagid);
							
							
							$tag = array();
							$id = 0;
							if(count($group) > 0) {
								foreach($group as $item) {
									if($item != '') {
										//$tag_name = strlen($tag) > 18 ? substr($tag, 0, 18).'...' : $tag;
										//$tags .= '<p class="tags" id="'.$contact['tagid'][$id].'"> <img src="https://connect.fuzedapp.com/images/tag.png" /> <span title="'.$tag.'">'.$tag_name.'</span> <span class="remove" title="Click to remove this tag." db="'.$contact['tagid'][$id].'" rel="'.$tag.'" return false;"><img src="https://connect.fuzedapp.com/images/remove.png" /></span></p>';
										$tag[] = array(
														'tag_name' => $item,
														'tag_id' => (isset($tagid[$id]) ? $tagid[$id] : 0)
														);
									}
									$id++;
								}	
								$tags = $this->Functions->subval_sort($tag, 'tag_name');
							}
						}
					}
				
					$data = array('id' => $find_id, 'fname' => $fname, 'sname' => $sname,
									'phone' => $phone, 'company' => $company,
									'cp' => $cp, 'address' => $address, 'zipcode' => $zipcode,
									'city' => $city, 'state' => $state, 'tags' => $tags, 
									'tagid' => $tagid, 'sequences' => $sequences);	

				} 
			}
		} else {
			$data = array();
		}
		return $data;
    }
    
    function getTagOAP($user_id = null)
    {
	
		$data = array();
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap = new OapApi($appName, $apiKey);
			$tag 	= $oap->pullTag();
				
			foreach ($tag->tag as $value) {
		
				$data[] = array(
						'tag_id' => (int)$value->attributes()->id,
						'tag_name' => trim((string)$value)
						 );
		
			}
		}
		
		$sort = $this->Functions->subval_sort($data, 'tag_name');
		
		return $sort;
    }
    
    function addContactOAP($cont_info = array(), $tags = array())
    {
		
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap = new OapApi($appName, $apiKey);
			//$conDat = array($cont_info[0], $cont_info[1], $cont_info[2]);
			$conID = $oap->addContactLong($cont_info);
		}
		
		return $conID;
    }
    
    function updateContactOAP($cont_info = array())
    {
		
		$conID = array();
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap = new OapApi($appName, $apiKey);
			
			//$conDat = array($cont_info[0], $cont_info[1], $cont_info[2], $cont_info[3]);
			$conID = $oap->updateContactLong($cont_info);
		}
		
		return $conID;
    }
    
    function addContactTagOAP($contactId = null, $tag_name = null, $email = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap 	= new OapApi($appName, $apiKey);
			$grp_ct = $oap->addContactTag($contactId, $tag_name);
		}
		
		return $grp_ct;
    }
    
    function removeTagOAP($contactId = null, $tag_name = null, $email = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap 	= new OapApi($appName, $apiKey);
			$grp_ct = $oap->removeContactTag($contactId, $tag_name);
		}
		
		return $grp_ct;
    }
    
    function addContactSeqOAP($contactId = null, $seq_id = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap 	= new OapApi($appName, $apiKey);
			$grp_ct = $oap->addContactSequence($contactId, $seq_id);
		}
		
		return $grp_ct;
    }
    
    function removeContactSequenceOAP($contactId = null, $seq_id = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap 	= new OapApi($appName, $apiKey);
			$grp_ct = $oap->removeContactSequence($contactId, $seq_id);
		}
		
		return $grp_ct;
    }
    
    function getSequencesOAP()
    {
		
		$data = array();
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap = new OapApi($appName, $apiKey);
			$seq = $oap->pullSequences();
		}
		
		foreach ($seq->sequence as $value){						
			$data[] = array(
						'seq_id' => (int)$value->attributes()->id,
						'seq_name' => trim((string)$value)
						 );
		} 
		
		$sort = $this->Functions->subval_sort($data, 'seq_name');
		
		return $sort;
    }
    
    function getSequenceName($seq_id = null)
    {

		$sequence = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap 	= new OapApi($appName, $apiKey);
			$seq = $oap->pullSequences();
		}
		
		foreach ($seq->sequence as $value){						
			$id = (int)$value->attributes()->id;
			if($id == $seq_id)
				$sequence = (string)$value;
		} 
	
		return $sequence;
    }
    
    function addNoteOAP($contactId = null, $note = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 3);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$oap 	= new OapApi($appName, $apiKey);
			$grp_ct = $oap->addNote($contactId, $note);
		}
		
		return $grp_ct;
    }
    
    function state() {
    	
    	$state = array(""=>"No State",
    					"AL"=>"Alabama",
    					"AK"=>"Alaska",
    					"AZ"=>"Arizona",
    					"AR"=>"Arkansas",
    					"CA"=>"California",
    					"CO"=>"Colorado",
    					"CT"=>"Connecticut",
    					"DE"=>"Delaware",
    					"DC"=>"D.C.",
    					"FL"=>"Florida",
    					"GA"=>"Georgia",
    					"HI"=>"Hawaii",
    					"ID"=>"Idaho",
    					"IL"=>"Illinois",
    					"IN"=>"Indiana",
    					"IA"=>"Iowa",
    					"KS"=>"Kansas",
    					"KY"=>"Kentucky",
    					"LA"=>"Louisiana",
    					"ME"=>"Maine",
    					"MD"=>"Maryland",
    					"MA"=>"Massachusetts",
    					"MI"=>"Michigan",
    					"MN"=>"Minnesota",
    					"MS"=>"Mississippi",
    					"MO"=>"Missouri",
    					"MT"=>"Montana",
    					"NE"=>"Nebraska",
    					"NV"=>"Nevada",
    					"NH"=>"New Hampshire",
    					"NM"=>"New Mexico",
    					"NJ"=>"New Jersey",
    					"NY"=>"New York",
    					"NC"=>"North Carolina",
    					"ND"=>"North Dakota",
    					"OH"=>"Ohio",
    					"OK"=>"Oklahoma",
    					"OR"=>"Oregon",
    					"PA"=>"Pennsylvania",
    					"RI"=>"Rhode Island",
    					"SC"=>"South Carolina",
    					"SD"=>"South Dakota",
    					"TN"=>"Tennessee",
    					"TX"=>"Texas",
    					"UT"=>"Utah",
    					"VT"=>"Vermont",
    					"VA"=>"Virginia",
    					"WA"=>"Washington",
    					"WV"=>"West Virginia",
    					"WI"=>"Wisconsin",
    					"WY"=>"Wyoming",
    					"AB"=>"Alberta",
    					"BC"=>"British Columbia",
    					"MB"=>"Manitoba",
    					"NB"=>"New Brunswick",
    					"NL"=>"Newfoundland and Labrador",
    					"NS"=>"Nova Scotia",
    					"NT"=>"Northwest Territories",
    					"NU"=>"Nunavut",
    					"ON"=>"Ontario",
    					"PE"=>"Prince Edward Island",
    					"QC"=>"Quebec",
    					"SK"=>"Saskatchewan",
    					"YT"=>"Yukon",
    					"ACT"=>"(AU) Australian Capital Territory",
    					"NSW"=>"(AU) New South Wales",
    					"VIC"=>"(AU) Victoria",
    					"QLD"=>"(AU) Queensland",
    					"AU_NT"=>"(AU) Northern Territory",
    					"AU_WA"=>"(AU) Western Australia",
    					"SA"=>"(AU) South Australia",
    					"AU_TAS"=>"(AU) Tasmania",
    					"GP"=>"(AF) Gauteng",
    					"WP"=>"(AF) Western Cape",
    					"EC"=>"(AF) Eastern Cape",
    					"KZN"=>"(AF) Kwa-Zulu Natal",
    					"NW"=>"(AF) North West",
    					"AF_NC"=>"(AF) Northern Cape",
    					"MP"=>"(AF) Mpumulanga",
    					"FS"=>"(AF) Free State",
    					);
    	
    	return $state;
    }
    
    
    
    function state_options($sel = '') {
    	
    	$options = '';
    	foreach($this->state() as $value => $text) {

    		$selected = '';
    		if($value == $sel)
    			$selected = 'selected'; 
    		
    		$options .= '<option '.$selected.' value="'.$value.'">'.$text.'</option>';
    	
    	}
    	return $options;
    }
    
    function state_name($sel = '') {
    	
    	$sname = '';
    	foreach($this->state() as $value => $text) {
    		if($value == $sel)
    			$sname = $text;     	
    	}
    	return $sname;
    }
    
    function js_functions($state) {
    	
    	$sec_code = $this->code;
    	$str = '<style>
    			.gray-line {
    				border-top: 2px solid #D5D5D5 !important;
    			}
    			.dark-line {
    				border-top: 2px solid #AAAAAA !important;
    			}
    			p {
					margin: 0; 
					padding: 2px;
					font-size: 13px;
				} 
				p input{
					padding: 5px;
					width: 93%;
				} 
				p select {
					padding: 5px;
					width: 77%;
					height: 30px;
					margin: 0;
				}
				p a:hover {
					text-decoration: underline;
				} 
				p.tags {
					padding: 3px 8px; 
					border-bottom: 1px solid #eee;
					font-size: 14px;
				} 
				#head_oap {
   					color: #999;
					cursor: pointer;
					font-size: 17px;
					font-weight: 300;
					padding-left: 15px; 
					margin-top: 3px;
					background: url("https://connect.fuzedapp.com/images/left_icon.png") no-repeat scroll left center transparent;
				} 	
				#head_oap.active{
   					color: #333;
   					background: url("https://connect.fuzedapp.com/images/down_icon.png") no-repeat scroll left center transparent;
				} 		
				#head_is {
   					color: #999;
					cursor: pointer;
					font-size: 17px;
					font-weight: 300;
					padding-left: 15px;
					margin-top: 3px;
					background: url("https://connect.fuzedapp.com/images/left_icon.png") no-repeat scroll left center transparent;
				}
				#head_is.active{
   					color: #333;
   					background: url("https://connect.fuzedapp.com/images/down_icon.png") no-repeat scroll left center transparent;					
				}
				p.sub_head {
					position: relative; 
					color: #333; 
					font-weight: bold;
					margin: 4px 0;
					font-size: 14px;
				} 
				div.info_oap { 
					color: #333; 
					font-size: 13px; 
				}
				div.info_is { 
					color:#333; 
					font-size: 13px; 
				}
				#per_info {
					margin-left: 10px;
				}				
				#per_info_is {
					margin-left: 10px;
				}
				#tag_list {
					padding: 2px; 
				}
				#view_contact {
					cursor: pointer;
					color: #0066CC !important;
				}
				#view_contact:hover {
					text-decoration: underline;
				}
				#view_contact_is {
					cursor: pointer;
					color: #0066CC !important;
				}
				#view_contact_is:hover {
					text-decoration: underline;
				}
				#addtag {
					width: 15px;
					margin: 4px;
				}
				#addseq {
					width: 15px;
					margin: 4px;
				}
				#addtag_is {
					width: 15px;
					margin: 4px;
				}
				#addseq_is {
					width: 15px;
					margin: 4px;
				}					
				.remove, .remove_seq {
					cursor: pointer;
					color: blue;
					float: right;
				}
				.remove_is, .remove_seq_is {
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
				#ontraport {
					margin: 0;
				}
				</style>
				'; 
    	
    	$str .= '<script type="text/javascript">	
					var loading = "<div align=\'center\'><img src=\'https://connect.fuzedapp.com/images/loading9.gif\' /></div>";
					
					jQuery("#head_oap").parent("div").parent("div").parent(".widget").addClass("gray-line");
					
					jQuery("#contactDetails .info_oap").hide();
					jQuery("#contact_info").hide(); 
					jQuery("#head_oap").click(function(){ 
						jQuery("#contactDetails .info_oap").slideToggle(600);
						if(jQuery(this).hasClass("active")) {
							jQuery(this).removeClass();
							jQuery(this).parent("div").parent("div").parent(".widget").addClass("gray-line");
							jQuery(this).parent("div").parent("div").parent(".widget").removeClass("dark-line");
						} else {
							jQuery(this).addClass("active");
							jQuery(this).parent("div").parent("div").parent(".widget").addClass("dark-line");
							jQuery(this).parent("div").parent("div").parent(".widget").removeClass("gray-line");
						}
					});
					
					jQuery("#head_is").click(function(){ 
						jQuery("#contactDetails .info_is").slideToggle(600);
						if(jQuery(this).hasClass("active")) {
							jQuery(this).removeClass();
							jQuery(this).parent("div").parent("div").parent(".widget").addClass("gray-line");
							jQuery(this).parent("div").parent("div").parent(".widget").removeClass("dark-line");
						} else {
							jQuery(this).addClass("active");
							jQuery(this).parent("div").parent("div").parent(".widget").addClass("dark-line");
							jQuery(this).parent("div").parent("div").parent(".widget").removeClass("gray-line");
						}
					});
					
					jQuery("div.info_is").hide();
					jQuery("#contact_info_is").hide(); 
					jQuery("p.head_is").click(function(){ 
						jQuery(this).next("div.info_is").slideToggle(600);
					});
				
					jQuery("#view_contact").click(function(){ 
						jQuery(this).next("#contact_info").slideToggle(600);
					});
					
					jQuery("#view_contact_is").click(function(){ 
						jQuery(this).next("#contact_info_is").slideToggle(600);
					});
				
					jQuery("#add_cont").click(function(){ 
						var valid = true;
					
						if(jQuery("#ontraport #email").val() == "") {
							jQuery("#ontraport #email").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
												
							var fname = jQuery("#ontraport #fname").val();
							var sname = jQuery("#ontraport #sname").val();
							var email = jQuery("#ontraport #email").val();
							var cp = jQuery("#ontraport #cp").val();
							var phone = jQuery("#ontraport #phone").val();
							var company = jQuery("#ontraport #company").val();
							var address = jQuery("#ontraport #address").val();
							var zipcode = jQuery("#ontraport #zipcode").val();
							var city = jQuery("#ontraport #city").val();
							var state = jQuery("#ontraport #state").val();
								
							var url = "fname=" + fname + "&sname=" + sname + "&email=" + email +
										"&cp=" + cp + "&address=" + address + "&zipcode=" + zipcode + "&city=" + city +
										"&state=" + state + "&phone=" + phone + "&company=" + company;
						
							jQuery("#contact_info").html(loading);
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/addcontact/'.$sec_code.'", url, function(data){
							
								if(data != "") {
									
									var txt = data.split("{::}");
								
									jQuery("#contact_info").html(txt[0]);
									jQuery("#per_info").html(txt[1]);
									
									jQuery("#contact_info").slideToggle(600);
									jQuery("#view_contact").html("Update Contact");
								
									jQuery("#tag_list").show();
								
									jQuery("#update_cont").click(function(){ 
										var valid = true;
					
										if(jQuery("#ontraport #email").val() == "") {
											jQuery("#ontraport #email").css("border", "1px solid red");
											valid = false;
										}
					
										if(valid) {
											var oap_id = jQuery("#ontraport #oap_id").val();
											var fname = jQuery("#ontraport #fname").val();
											var sname = jQuery("#ontraport #sname").val();
											var email = jQuery("#ontraport #email").val();
											var cp = jQuery("#ontraport #cp").val();
											var phone = jQuery("#ontraport #phone").val();
											var company = jQuery("#ontraport #company").val();
											var address = jQuery("#ontraport #address").val();
											var zipcode = jQuery("#ontraport #zipcode").val();
											var city = jQuery("#ontraport #city").val();
											var state = jQuery("#ontraport #state").val();
								
											var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
														"&cp=" + cp + "&address=" + address + "&zipcode=" + zipcode + "&city=" + city +
														"&state=" + state + "&phone=" + phone + "&company=" + company;
						
											jQuery("#contact_info").html(loading);
						
											jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/updatecontact/'.$sec_code.'", url, function(data){
							
												if(data != "") {
													var txt = data.split("{::}");
								
													jQuery("#contact_info").html(txt[0]);
													jQuery("#per_info").html(txt[1]);
													
													jQuery("#contact_info").slideToggle(600);
													jQuery("#view_contact").html("Update Contact");
												
												} else {
													jQuery("#msg").html("There is an unknown issue. Please try again.");
												}
											});
										}
					
									});
								
								} else {
									jQuery("#msg").html("There is an unknown issue. Please try again.");
								}
							});
						}
					
					});
				
					jQuery("#update_cont").click(function(){ 
						var valid = true;
					
						if(jQuery("#ontraport #email").val() == "") {
							jQuery("#ontraport #email").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
							var oap_id = jQuery("#ontraport #oap_id").val();
							var fname = jQuery("#ontraport #fname").val();
							var sname = jQuery("#ontraport #sname").val();
							var email = jQuery("#ontraport #email").val();
							var cp = jQuery("#ontraport #cp").val();
							var phone = jQuery("#ontraport #phone").val();
							var company = jQuery("#ontraport #company").val();								
							var address = jQuery("#ontraport #address").val();
							var zipcode = jQuery("#ontraport #zipcode").val();
							var city = jQuery("#ontraport #city").val();
							var state = jQuery("#ontraport #state").val();
								
							var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
										"&cp=" + cp + "&address=" + address + "&zipcode=" + zipcode + "&city=" + city +
										"&state=" + state + "&phone=" + phone + "&company=" + company;
						
							jQuery("#contact_info").html(loading);
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/updatecontact/'.$sec_code.'", url, function(data){
							
								if(data != "") {
								
									var txt = data.split("{::}");
								
									jQuery("#contact_info").html(txt[0]);
									jQuery("#per_info").html(txt[1]);

									jQuery("#contact_info").slideToggle(600);
									jQuery("#view_contact").html("Update Contact");
								
									jQuery("#update_cont").click(function(){ 
										var valid = true;
										if(jQuery("#ontraport #fname").val() == "") {
											jQuery("#ontraport #fname").css("border", "1px solid red");
											valid = false;
										}
					
										if(jQuery("#ontraport #sname").val() == "") {
											jQuery("#ontraport #sname").css("border", "1px solid red");
											valid = false;
										}
					
										if(jQuery("#ontraport #email").val() == "") {
											jQuery("#ontraport #email").css("border", "1px solid red");
											valid = false;
										}
					
										if(valid) {
										
											var oap_id = jQuery("#ontraport #oap_id").val();
											var fname = jQuery("#ontraport #fname").val();
											var sname = jQuery("#ontraport #sname").val();
											var email = jQuery("#ontraport #email").val();
											var cp = jQuery("#ontraport #cp").val();
											var phone = jQuery("#ontraport #phone").val();
											var company = jQuery("#ontraport #company").val();
											var address = jQuery("#ontraport #address").val();
											var zipcode = jQuery("#ontraport #zipcode").val();
											var city = jQuery("#ontraport #city").val();
											var state = jQuery("#ontraport #state").val();
											
											var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
														"&cp=" + cp + "&address=" + address + "&zipcode=" + zipcode + "&city=" + city +
														"&state=" + state + "&phone=" + phone + "&company=" + company;
						
											jQuery("#contact_info").html(loading);
						
											jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/updatecontact/'.$sec_code.'", url, function(data){
							
												if(data != "") {
													var txt = data.split("{::}");
								
													jQuery("#contact_info").html(txt[0]);
													jQuery("#per_info").html(txt[1]);
													
													jQuery("#contact_info").slideToggle(600);
													jQuery("#view_contact").html("Update Contact");
												
												} else {
													jQuery("#msg").html("There is an unknown issue. Please try again.");
												}
											});
										}
					
									});
							
								} else {
									jQuery("#msg").html("There is an unknown issue. Please try again.");
								}
							});
						}
					
					});
				
					jQuery("#addtag").click(function(){ 
						var valid = true;
					
						if(jQuery("#ontraport #tag_id").val() == 0) {
							jQuery("#ontraport #tag_id").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
							var tag_id = jQuery("#ontraport #tag_id").val();
							var oap_id = jQuery("#ontraport #oap_id").val();
							var email =  jQuery("#ontraport #email").val();
							var url = "tag_name=" + tag_id + "&oap_id=" + oap_id + "&email=" + email;

							jQuery("#tag_p").html(loading);
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/addcontacttag/'.$sec_code.'", url, function(data) {
								jQuery("#tag_p").html(data);	
							
								jQuery(".remove").click(function(){ 
				
									var tag_name = jQuery(this).attr("rel");
									var id = jQuery(this).attr("db");
		
									var oap_id = jQuery("#ontraport #oap_id").val();
									var email =  jQuery("#ontraport #email").val();
									var url = "tag_name=" + tag_name + "&oap_id=" + oap_id + "&email=" + email;
									
									if(confirm("Are you sure to delete this tag?")) {
										jQuery(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
										jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/removecontacttag/'.$sec_code.'", url, function(data) {
											jQuery("#" + id).hide();
										});
									}
									
								});
							
							});
						}
					
					}); 
					
					jQuery("#addseq").click(function(){ 
						var valid = true;
					
						if(jQuery("#ontraport #seq_id").val() == 0) {
							jQuery("#ontraport #seq_id").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
							var seq_id = jQuery("#ontraport #seq_id").val();
							var oap_id = jQuery("#ontraport #oap_id").val();
							var email =  jQuery("#ontraport #email").val();
							var url = "seq_id=" + seq_id + "&oap_id=" + oap_id + "&email=" + email;

							jQuery("#seq_p").html(loading);
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/addcontactseq/'.$sec_code.'", url, function(data) {
								jQuery("#seq_p").html(data);	
							
								jQuery(".remove_seq").click(function(){ 
									
									var oap_id = jQuery("#ontraport #oap_id").val();
									var seq_id = jQuery(this).attr("rel");
									var email =  jQuery("#ontraport #email").val();
									var id = jQuery(this).attr("db");

									var url = "seq_id=" + seq_id + "&oap_id=" + oap_id + "&email=" + email;
									
									if(confirm("Are you sure to delete this sequence?")) {
										jQuery(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
										jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/removecontactseq/'.$sec_code.'", url, function(data) {
											jQuery("#seq_" + id).hide();
										});
									}
								});
							
							});
						}
					});
				
					jQuery(".remove").click(function(){ 
				
						var tag_name = jQuery(this).attr("rel");
						var id = jQuery(this).attr("db");
		
						var oap_id = jQuery("#ontraport #oap_id").val();
						var email =  jQuery("#ontraport #email").val();
						var url = "tag_name=" + tag_name + "&oap_id=" + oap_id + "&email=" + email;
						if(confirm("Are you sure to delete this tag?")) {
							jQuery(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/removecontacttag/'.$sec_code.'", url, function(data) {
								jQuery("#" + id).hide();
							});
						}
					});
					
					jQuery(".remove_seq").click(function(){ 
				
						var seq_id = jQuery(this).attr("rel");
						var id = jQuery(this).attr("db");
		
						var oap_id = jQuery("#ontraport #oap_id").val();
						var email =  jQuery("#ontraport #email").val();
						var url = "seq_id=" + seq_id + "&oap_id=" + oap_id + "&email=" + email;
						if(confirm("Are you sure to delete this sequence?")) {
							jQuery(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/removecontactseq/'.$sec_code.'", url, function(data) {
								jQuery("#seq_" + id).hide();
							});
						}
					});
					
					
					jQuery("#addnote").click(function(){ 
						var valid = true;
					
						if(jQuery("#ontraport #note").val() == "") {
							jQuery("#ontraport #note").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
							var oap_id = jQuery("#ontraport #oap_id").val();
							var note = jQuery("#ontraport #note").val();
							var email =  jQuery("#ontraport #email").val();
							var url = "note=" + note + "&oap_id=" + oap_id + "&email=" + email;

							jQuery("#note_load").html(loading);
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/addcontactnote/'.$sec_code.'", url, function(data) {
								var txt = "<i>New Note:</i> " + data + "<br />" + jQuery("#note_p").html();
								jQuery("#note_p").html(txt);
								
								jQuery("#note_load").html("");
								jQuery("#note").val("");				
							});
						}
					});
					
					// IS js
					
					jQuery("#add_cont_is").click(function(){ 
						var valid = true;
					
						if(jQuery("#infusionsoft #email").val() == "") {
							jQuery("#infusionsoft #email").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
												
							var fname = jQuery("#infusionsoft #fname").val();
							var sname = jQuery("#infusionsoft #sname").val();
							var email = jQuery("#infusionsoft #email").val();
							var cp = jQuery("#infusionsoft #cp").val();
							var phone = jQuery("#infusionsoft #phone").val();
							var company = jQuery("#infusionsoft #company").val();
							var address = jQuery("#infusionsoft #address").val();
							var zipcode = jQuery("#infusionsoft #zipcode").val();
							var city = jQuery("#infusionsoft #city").val();
							var state = jQuery("#infusionsoft #state").val();
							var note = jQuery("#infusionsoft #note_is").val();
								
							var url = "fname=" + fname + "&sname=" + sname + "&email=" + email +
										"&cp=" + cp + "&address=" + address + "&zipcode=" + zipcode + "&city=" + city +
										"&state=" + state + "&phone=" + phone + "&company=" + company + "&note=" + note;
						
							jQuery("#contact_info_is").html(loading);
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/addcontactis/'.$sec_code.'", url, function(data){
							
								if(data != "") {
									
									var txt = data.split("{::}");
								
									jQuery("#contact_info_is").html(txt[0]);
									jQuery("#per_info_is").html(txt[1]);
									
									jQuery("#contact_info_is").slideToggle(600);
									jQuery("#view_contact_is").html("Update Contact");
								
									jQuery("#tag_list_is").show();
								
									jQuery("#update_cont_is").click(function(){ 
										var valid = true;
					
										if(jQuery("#infusionsoft #email").val() == "") {
											jQuery("#infusionsoft #email").css("border", "1px solid red");
											valid = false;
										}
					
										if(valid) {
											var oap_id = jQuery("#infusionsoft #oap_id").val();
											var fname = jQuery("#infusionsoft #fname").val();
											var sname = jQuery("#infusionsoft #sname").val();
											var email = jQuery("#infusionsoft #email").val();
											var cp = jQuery("#infusionsoft #cp").val();
											var phone = jQuery("#infusionsoft #phone").val();
											var company = jQuery("#infusionsoft #company").val();
											var address = jQuery("#infusionsoft #address").val();
											var zipcode = jQuery("#infusionsoft #zipcode").val();
											var city = jQuery("#infusionsoft #city").val();
											var state = jQuery("#infusionsoft #state").val();
											var note = jQuery("#infusionsoft #note_is").val();
								
											var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
														"&cp=" + cp + "&address=" + address + "&zipcode=" + zipcode + "&city=" + city +
														"&state=" + state + "&phone=" + phone + "&company=" + company + "&note=" + note;
						
											jQuery("#contact_info_is").html(loading);
						
											jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/updatecontactis/'.$sec_code.'", url, function(data){
							
												if(data != "") {
													var txt = data.split("{::}");
								
													jQuery("#contact_info_is").html(txt[0]);
													jQuery("#per_info_is").html(txt[1]);
													
													jQuery("#contact_info_is").slideToggle(600);
													jQuery("#view_contact_is").html("Update Contact");
												
												} else {
													jQuery("#msg").html("There is an unknown issue. Please try again.");
												}
											});
										}
					
									});
								
								} else {
									jQuery("#msg").html("There is an unknown issue. Please try again.");
								}
							});
						}
					
					});
				
					jQuery("#update_cont_is").click(function(){ 
						var valid = true;
					
						if(jQuery("#infusionsoft #email").val() == "") {
							jQuery("#infusionsoft #email").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
							var oap_id = jQuery("#infusionsoft #oap_id").val();
							var fname = jQuery("#infusionsoft #fname").val();
							var sname = jQuery("#infusionsoft #sname").val();
							var email = jQuery("#infusionsoft #email").val();
							var cp = jQuery("#infusionsoft #cp").val();
							var phone = jQuery("#infusionsoft #phone").val();
							var company = jQuery("#infusionsoft #company").val();								
							var address = jQuery("#infusionsoft #address").val();
							var zipcode = jQuery("#infusionsoft #zipcode").val();
							var city = jQuery("#infusionsoft #city").val();
							var state = jQuery("#infusionsoft #state").val();
							var note = jQuery("#infusionsoft #note_is").val();
								
							var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
										"&cp=" + cp + "&address=" + address + "&zipcode=" + zipcode + "&city=" + city +
										"&state=" + state + "&phone=" + phone + "&company=" + company + "&note=" + note;
						
							jQuery("#contact_info_is").html(loading);
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/updatecontactis/'.$sec_code.'", url, function(data){
							
								if(data != "") {
								
									var txt = data.split("{::}");
								
									jQuery("#contact_info_is").html(txt[0]);
									jQuery("#per_info_is").html(txt[1]);

									jQuery("#contact_info_is").slideToggle(600);
									jQuery("#view_contact_is").html("Update Contact");
								
									jQuery("#update_cont_is").click(function(){ 
										var valid = true;
					
										if(jQuery("#infusionsoft #email").val() == "") {
											jQuery("#infusionsoft #email").css("border", "1px solid red");
											valid = false;
										}
					
										if(valid) {
										
											var oap_id = jQuery("#infusionsoft #oap_id").val();
											var fname = jQuery("#infusionsoft #fname").val();
											var sname = jQuery("#infusionsoft #sname").val();
											var email = jQuery("#infusionsoft #email").val();
											var cp = jQuery("#infusionsoft #cp").val();
											var phone = jQuery("#infusionsoft #phone").val();
											var company = jQuery("#infusionsoft #company").val();
											var address = jQuery("#infusionsoft #address").val();
											var zipcode = jQuery("#infusionsoft #zipcode").val();
											var city = jQuery("#infusionsoft #city").val();
											var state = jQuery("#infusionsoft #state").val();
											
											var url = "oap_id=" + oap_id + "&fname=" + fname + "&sname=" + sname + "&email=" + email +
														"&cp=" + cp + "&address=" + address + "&zipcode=" + zipcode + "&city=" + city +
														"&state=" + state + "&phone=" + phone + "&company=" + company;
						
											jQuery("#contact_info_is").html(loading);
						
											jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/updatecontactis/'.$sec_code.'", url, function(data){
							
												if(data != "") {
													var txt = data.split("{::}");
								
													jQuery("#contact_info_is").html(txt[0]);
													jQuery("#per_info_is").html(txt[1]);
													
													jQuery("#contact_info_is").slideToggle(600);
													jQuery("#view_contact_is").html("Update Contact");
												
												} else {
													jQuery("#msg").html("There is an unknown issue. Please try again.");
												}
											});
										}
					
									});
							
								} else {
									jQuery("#msg").html("There is an unknown issue. Please try again.");
								}
							});
						}
					
					});
				
					jQuery("#addtag_is").click(function(){ 
						var valid = true;
					
						if(jQuery("#infusionsoft #tag_id_is").val() == 0) {
							jQuery("#infusionsoft #tag_id_is").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
							var tag_id = jQuery("#infusionsoft #tag_id_is").val();
							var oap_id = jQuery("#infusionsoft #oap_id").val();
							var email =  jQuery("#infusionsoft #email").val();
							var url = "tag_name=" + tag_id + "&oap_id=" + oap_id + "&email=" + email;

							jQuery("#tag_p_is").html(loading); 
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/addcontacttagis/'.$sec_code.'", url, function(data) {

								jQuery("#tag_p_is").html(data);	
								jQuery(".remove_is").click(function(){ 
				
									var tag_name = jQuery(this).attr("rel");
									var id = jQuery(this).attr("db");
		
									var oap_id = jQuery("#infusionsoft #oap_id").val();
									var email =  jQuery("#infusionsoft #email").val();
									var url = "tag_name=" + tag_name + "&oap_id=" + oap_id + "&email=" + email;
									
									if(confirm("Are you sure to delete this tag?")) {
										jQuery(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
										jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/removecontacttagis/'.$sec_code.'", url, function(data) {
											jQuery("#" + id + "_is").hide();
										});
									}
									
								});
							
							});
						}
					
					});
					
					jQuery("#addseq_is").click(function(){ 
						var valid = true;
					
						if(jQuery("#infusionsoft #seq_id_is").val() == 0) {
							jQuery("#infusionsoft #seq_id_is").css("border", "1px solid red");
							valid = false;
						}
					
						if(valid) {
							var seq_id = jQuery("#infusionsoft #seq_id_is").val();
							var oap_id = jQuery("#infusionsoft #oap_id").val();
							var email =  jQuery("#infusionsoft #email").val();
							var url = "seq_id=" + seq_id + "&oap_id=" + oap_id + "&email=" + email;

							jQuery("#seq_p_is").html(loading);
						
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/addcontactseqis/'.$sec_code.'", url, function(data) {
								jQuery("#seq_p_is").html(data);	
							
								jQuery(".remove_seq_is").click(function(){ 
									
									var oap_id = jQuery("#infusionsoft #oap_id").val();
									var seq_id = jQuery(this).attr("rel");
									var email =  jQuery("#infusionsoft #email").val();
									var id = jQuery(this).attr("db");

									var url = "seq_id=" + seq_id + "&oap_id=" + oap_id + "&email=" + email;
									
									if(confirm("Are you sure to delete this sequence?")) {
										jQuery(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
										jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/removecontactseqis/'.$sec_code.'", url, function(data) {
											jQuery("#seq_" + id).hide();
										});
									}
								});
							
							});
						}
					});
				
					jQuery(".remove_is").click(function(){ 
				
						var tag_name = jQuery(this).attr("rel");
						var id = jQuery(this).attr("db");
		
						var oap_id = jQuery("#infusionsoft #oap_id").val();
						var email =  jQuery("#infusionsoft #email").val();
						var url = "tag_name=" + tag_name + "&oap_id=" + oap_id + "&email=" + email;
						if(confirm("Are you sure to delete this tag?")) {
							jQuery(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/removecontacttagis/'.$sec_code.'", url, function(data) {
								jQuery("#" + id + "_is").hide();
							});
						}
					});
					
					jQuery(".remove_seq_is").click(function(){ 
				
						var seq_id = jQuery(this).attr("rel");
						var id = jQuery(this).attr("db");
		
						var oap_id = jQuery("#infusionsoft #oap_id").val();
						var email =  jQuery("#infusionsoft #email").val();
						var url = "seq_id=" + seq_id + "&oap_id=" + oap_id + "&email=" + email;
						if(confirm("Are you sure to delete this sequence?")) {
							jQuery(this).html("<img src=\'https://connect.fuzedapp.com/images/loading9.gif\' height=\'11\' width=\'11\' />");
							jQuery.post("https://'.$_SERVER['HTTP_HOST'].'/freshdesk/removecontactseqis/'.$sec_code.'", url, function(data) {
								jQuery("#seq_" + id + "_is").hide();
							});
						}
					});
					
					jQuery("#ontraport #state option").filter(function() {
							return this.text == "'.$state.'"; 
					}).attr("selected", true);
			</script>';
    
    	return $str;
    
    }
    
}