<?php

require_once APPPATH.'libraries/infusionsoft/isdk.php';

class RapletIS extends CI_Model {
	
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
			
			$is_conn = $this->Services->getServiceUserConnection($user->id, 2);
			$raplet = $this->Services->getConnectionFuzeRaplet($user->id);
			$is = isset($raplet->is) ? $raplet->is : 0;
			
			if($user->access_type != 6 && count($is_conn) > 0 && $is == 1) {
				$this->user_id = $user->id;	
				$return_code = $this->user_id;
			} else if($user->access_type != 6 && count($is_conn) < 1 && $is == 1) {
				$return_code = -96;
			} else {
				$return_code = -98;
			}
		} 
		
		return $return_code;
    
    }
    
    function setModelFreshDesk($code = null) {
    	
    	$return_code = -97;
    	$query = $this->db->query("SELECT * FROM users WHERE raplet_code = '".$code."'");
		if ($query->num_rows() > 0) {
		
			$user = $query->row();
			
			$is_conn = $this->Services->getServiceUserConnection($user->id, 2);
			$freshdesk = $this->Services->getConnectionFuzeFreshdesk($user->id);
			$is = isset($freshdesk->is) ? $freshdesk->is : 0;
			
			if($user->access_type != 6 && count($is_conn) > 0 && $is == 1) {
				$this->user_id = $user->id;	
				$return_code = $this->user_id;
			} else if($user->access_type != 6 && count($is_conn) < 1 && $is == 1) {
				$return_code = -96;
			} else {
				$return_code = -98;
			}
		} 
		
		return $return_code;
    
    }

	function getInfoIS($email = '', $user_id = null)
    {
		
		$data = array();
		if($this->user_id != '') {
			$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
			if(count($keys) > 0) {
				$appName = $keys->appname;
				$apiKey	 = $keys->key;
	
				$isapp = new iSDK;
				$isapp->cfgCon($appName, $apiKey);
			
				$returnFields = array('Id', 'FirstName', 'LastName', 'StreetAddress1', 'City', 'PostalCode',
							'State', 'Phone1', 'Phone2', 'Company', 'ContactNotes'); 
				$is_data = $isapp->findByEmail($email, $returnFields);
			
				$fname = '';
				$sname = '';
				$cp	= '';
				$address = '';
				$zipcode = '';
				$city = '';
				$state = '';
				$phone = '';
				$company = '';
				$note = '';
				
				$tags = array();
				$tagid = array();
			
				//echo '<pre>';
				//print_r($oap_data);
				//echo '</pre>';
			
				if (count($is_data) > 0) {

					$find_id = isset($is_data[0]['Id']) ? $is_data[0]['Id'] : 0;
					$fname	= isset($is_data[0]['FirstName']) ? $is_data[0]['FirstName'] : '';
					$sname	= isset($is_data[0]['LastName']) ? $is_data[0]['LastName'] : '';
					$email	= $email;
					$cp		= isset($is_data[0]['Phone2']) ? $is_data[0]['Phone2'] : '';
					$address = isset($is_data[0]['StreetAddress1']) ? $is_data[0]['StreetAddress1'] : '';
					$zipcode = isset($is_data[0]['PostalCode']) ? $is_data[0]['PostalCode'] : '';
					$city = isset($is_data[0]['City']) ? $is_data[0]['City'] : '';
					$state = isset($is_data[0]['State']) ? $is_data[0]['State'] : '';
					$phone = isset($is_data[0]['Phone1']) ? $is_data[0]['Phone1'] : '';
					$company = isset($is_data[0]['Company']) ? $is_data[0]['Company'] : '' ;
					$note = isset($is_data[0]['ContactNotes']) ? $is_data[0]['ContactNotes'] : '' ;
					
					$returnFields = array('GroupId', 'ContactGroup', 'DateCreated');
					$query = array('ContactId' => $find_id);
					$group = $isapp->dsQuery("ContactGroupAssign", 1000, 0, $query, $returnFields);	
					$tag = array();
					
					foreach($group as $item) {
						$tag[] = array(
							'GroupId' => $item['GroupId'],
							'ContactGroup' => $item['ContactGroup']
							 );
					}
					
					$tags = $this->Functions->subval_sort($tag, 'ContactGroup');
					
					
					$returnFields = array('CampaignId', 'Campaign', 'Status');
					$query = array('ContactId' => $find_id);
					$sequence = $isapp->dsQuery("Campaignee", 1000, 0, $query, $returnFields);
					
					

					$tag = array(); 

					$data = array('id' => $find_id, 'fname' => $fname, 'sname' => $sname,
									'phone' => $phone, 'company' => $company,
									'cp' => $cp, 'address' => $address, 'zipcode' => $zipcode,
									'city' => $city, 'state' => $state, 'tags' => $tag, 
									'tagid' => $tags, 'sequences' => $sequence, 'note' => $note);	

				} 
			}
		} else {
			$data = array();
		}
		return $data;
    }
    
    function getTagIS($user_id = null)
    {
	
		$data = array();
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$isapp = new iSDK;
			$isapp->cfgCon($appName, $apiKey);
			
			$returnFields = array('Id', 'GroupName','GroupCategoryId', 'GroupDescription');
			$query = array('GroupName' => '%%');
			$groups = $isapp->dsQuery("ContactGroup",1000,0, $query, $returnFields);

			foreach($groups as $item) {
			
				$data[] = array(
					'tag_id' => $item['Id'],
					'tag_name' => $item['GroupName']
					 );
			}
		}
		
		$sort = $this->Functions->subval_sort($data, 'tag_name');
		
		return $sort;
    }
    
    function addContactIS($cont_info = array(), $tags = array())
    {
		
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$isapp = new iSDK;
			$isapp->cfgCon($appName, $apiKey);

			$conID = $isapp->addCon($cont_info);
		}
		
		return $conID;
    }
    
    function updateContactIS($id = 0, $cont_info = array())
    {
		
		$conID = array();
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$isapp = new iSDK;
			$isapp->cfgCon($appName, $apiKey);
			
			$conID = $isapp->updateCon($id, $cont_info);
		}
		
		return $conID;
    }
    
    function addContactTagIS($contactId = null, $groupId = null, $email = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$isapp = new iSDK;
			$isapp->cfgCon($appName, $apiKey);
			$grp_ct = $isapp->grpAssign($contactId, $groupId);
		}
		
		return $grp_ct;
    }
    
    function removeTagIS($contactId = null, $tag_name = null, $email = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$isapp = new iSDK;
			$isapp->cfgCon($appName, $apiKey);
			$grp_ct = $isapp->grpRemove($contactId, $tag_name);
		}
		
		return $grp_ct;
    }
    
    function addContactSeqIS($contactId = null, $seq_id = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$isapp = new iSDK;
			$isapp->cfgCon($appName, $apiKey);
			$grp_ct = $isapp->campAssign($contactId, $seq_id);
		}
		
		return $grp_ct;
    }
    
    function removeContactSequenceIS($contactId = null, $seq_id = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;
			
			$isapp = new iSDK;
			$isapp->cfgCon($appName, $apiKey);
			$grp_ct = $isapp->campRemove($contactId, $seq_id);
		}
		
		return $grp_ct;
    }
    
    function getSequencesIS()
    {
		
		$data = array();
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
		if(count($keys) > 0) {
			$appName = $keys->appname;
			$apiKey	 = $keys->key;

			$isapp = new iSDK;
			$isapp->cfgCon($appName, $apiKey);
			
			$returnFields = array('Id', 'Name', 'Status');
			$query = array('Name' => '%%');
			$groups = $isapp->dsQuery("Campaign",1000,0, $query, $returnFields);

			foreach($groups as $item) {
			
				$data[] = array(
					'seq_id' => $item['Id'],
					'seq_name' => $item['Name']
					 );
			}
		}
		
		$sort = $this->Functions->subval_sort($data, 'seq_name');
	
		return $sort;
    }
    
    function getSequenceName($seq_id = null)
    {

		$sequence = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
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
    
    function addNoteIS($contactId = null, $note = null)
    {
		
		$tag_str = '';
		$keys = $this->Services->getServiceUserConnection($this->user_id, 2);
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
    
}