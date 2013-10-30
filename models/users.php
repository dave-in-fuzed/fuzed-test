<?php
class Users extends CI_Model {

	var $first_name		= '';
	var $last_name		= '';
	var $email_add		= '';
	var $password		= '';
	var $access_type	= 0;
	var $authcode		= 'authenticated';
	var $signupdate		= '';
	var $status			= 1;


	function __construct()
    {
        parent::__construct();
        
        $user_id= $this->session->userdata('user_id') != '' ? $this->session->userdata('user_id') : 0;
        $user	= $this->get_entry($user_id);
        
        if(count($user) > 0 && $user->timezone != '')
        	date_default_timezone_set($user->timezone);
        else 
        	date_default_timezone_set('UTC');
        	
    }
	
    function validate($email = '', $password = '')
    {

		$query = $this->db->query('SELECT * FROM users WHERE email = "'.$email.'"  AND password = "'.$password.'" AND status = 1');
		
		$user = array();
		
		if ($query->num_rows() > 0)
		{
		   foreach ($query->result() as $row)
		   {
			
			$user = array('first_name' => $row->first_name, 
						  'last_name' => $row->last_name, 
						  'email' => $row->email, 
						  'is_logged_in' => true, 
						  'user_id' => $row->id,
						  'access_type' => $row->access_type);
			}
		}
		
		return $user;
    }
	
	function global_total_user($access_type = 0) {
	
		$query = $this->Hh_p01->query('SELECT count(*) as total FROM users WHERE access_type = '.$access_type);

		$que = $query->row();
		return $que->total;
	}
	
	function get_entry($id = 0)
    {
    	$que = array();
		$query = $this->db->query('SELECT * FROM users WHERE id = '.$id);
		if ($query->num_rows() > 0) {
			$que = $query->row();
		}
		
		return $que;
    }
    
    function get_entry_by_email($email = 0)
    {
    	$user = array();
		$query = $this->db->query("SELECT * FROM users WHERE email = '".trim($email)."'");
		if ($query->num_rows() > 0) {
			$user = $query->row();
		}
		
		return $user;
    }
    
    function get_entry_authcode($authcode = '')
    {
    	$que = array();
		$query = $this->db->query("SELECT * FROM users WHERE authcode = '".$authcode."'");
		if ($query->num_rows() > 0) {
			$que = $query->row();
		}
		
		return $que;
    }


    function insert_entry()
    {
    
    	$current_plan = array('period' => $this->input->post('billing_cycle'),
    						 'amount' => $this->input->post('amount'));
    
		$data = array('first_name' => $this->input->post('first_name'),
			'last_name'		=> $this->input->post('last_name'),
			'email'			=> $this->input->post('email'),
			'password'		=> $this->input->post('password'),	
			'status'		=> $this->input->post('status'),
			'access_type'	=> $this->input->post('access_type'),
			'signupdate'	=> date("Y-m-d H:i:s"),
			'authcode'		=> 'authenticated',
			'nextbilling_date' => $this->input->post('nextbilling_date'),
			'current_plan'	=> json_encode($current_plan),
			'fuze_limit'	=> $this->input->post('fuze_limit')
			);

		$this->db->insert('users', $data);
		
		// add to goals
		$this->addToGoals($this->input->post('email'), $this->input->post('access_type'));
		
		return $this->db->insert_id();
	
    }
    
    function register_user()
    {
		
		date_default_timezone_set('Australia/Canberra');
		$this->authcode = $authcode = $this->randPass(9);
		
		$access_type = $this->input->post('access_type');
		
		$price 	= $this->acctype_fuzed_price($access_type);
		$plan	=  $this->acctype_fuzed_desc($access_type);
		$fuze_limit	=  $this->acctype_fuzed_limit($access_type);
		
		$current_plan = array('period' => 'per Month',
    						 'amount' => $price);
    						 
		$data = array(
			'is_id' 		=> $this->input->post('is_id'),
			'first_name' 	=> $this->input->post('first_name'),
			'last_name'		=> $this->input->post('last_name'),
			'email'			=> $this->input->post('email'),
			'access_type'	=> $access_type,
			'password'		=> $this->input->post('password'),
			'authcode'		=> $authcode,
			'authcode_cons' => $authcode,
			'ip_address' 	=> $_SERVER['REMOTE_ADDR'],
			'signupdate' 	=> date("Y-m-d H:i:s"),
			'current_plan' 	=> json_encode($current_plan),
			'fuze_limit'	=> $fuze_limit
			);
		
		$user = $this->get_entry_by_email($this->input->post('email'));
		
		if(count($user) > 0) {
			$this->authcode = $authcode = $user->authcode;
			$id = $user->id;
		} else {
			$this->db->insert('users', $data);
			$id = $this->db->insert_id();
		}
		//add to infusion soft
		$this->register_infusion($data);
		
		// add to goals
        $this->addToGoals($this->input->post('email'), $access_type);
        
		return array($id, $authcode, $this->input->post('is_id'));
	
    }

    function update_user($user_id = 0)
    {
    	$user = $this->get_entry($user_id);
    	
    	$current_plan = array('period' => $this->input->post('billing_cycle'),
    						 'amount' => $this->input->post('amount'));
    	
		$data = array(
			'first_name' 	=> $this->input->post('first_name'),
			'last_name'		=> $this->input->post('last_name'),
			'email'			=> $this->input->post('email'),
			'password'		=> $this->input->post('password'),
			'access_type' 	=> $this->input->post('access_type'),
			'status'		=> $this->input->post('status'),
			'nextbilling_date' => $this->input->post('nextbilling_date'),
			'current_plan'	=> json_encode($current_plan),
			'fuze_limit'	=> $this->input->post('fuze_limit')
		);

        $this->db->update('users', $data, array('id' => $user_id));
        
        //check if the update is the access_type
        if($user->access_type != $this->input->post('access_type'))
        	$goals = $this->addToGoals($user->email, $this->input->post('access_type'));
        	
    }
    
    function update_user_account($user_id = 0)
    {
    
    	$user = $this->get_entry($user_id);
    
		$data = array(
			'first_name' 	=> $this->input->post('first_name'),
			'last_name'		=> $this->input->post('last_name'),
			'email'			=> $this->input->post('email'),
			'password'		=> $this->input->post('password'),
			'timezone'		=> $this->input->post('timezone')
		);

        $this->db->update('users', $data, array('id' => $user_id));
        
        //update services data when chaging the timezone only
        if($user->timezone != $this->input->post('timezone')) {
        	
        	$data = $this->Services->saveUserMedias($user_id, 1);
			$data = $this->Services->saveUserMedias($user_id, 2);
			$data = $this->Services->saveUserMedias($user_id, 3);
			$data = $this->Services->saveUserMedias($user_id, 4);
			
		}
        
    }
    
    function update_billing($user_id = 0, $pin_details = '', $card_info = '')
    {
    
		$data = array('card_info' => $card_info,
					  'pin_details' => $pin_details
					);

        $this->db->update('users', $data, array('id' => $user_id));
    }
	
	function deactivate_client($id = 0)
    {
	
		$query = $this->db->query('UPDATE users SET status = 0 WHERE id = '.$id);
		
		return true;
    }
    
    function activate_client($id = 0)
    {
	
		$query = $this->db->query('UPDATE users SET status = 1 WHERE id = '.$id);
		
		return true;
    }
    
    function delete_user($id = 0)
    {
	
		$query = $this->db->query('DELETE FROM users WHERE id = '.$id);
		
		return true;
    }
    
	function randPass($length, $strength=8) {
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength >= 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength >= 2) {
			$vowels .= "AEUY";
		}
		if ($strength >= 4) {
			$consonants .= '1234567890';
		}
		if ($strength >= 8) {
			$consonants .= '=&$%';
		}

		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}
	
	function sendEmail($from = array(), $recipient = array(),  $subject = null, $message = null, $cc = array(), $bcc = array(), $debug = false)
	{
		
		$this->load->library('email');
		
		$config['protocol'] = 'sendmail';
		$config['mailpath'] = '/usr/sbin/sendmail';
		$config['charset'] = 'iso-8859-1';
		$config['wordwrap'] = TRUE;
		$config['mailtype'] = 'html';

		$this->email->initialize($config);

		$this->email->from($from['email'], $from['name']);
		$this->email->to($recipient);
		
		if(count($cc) > 0)
			$this->email->cc($cc);
		
		if(count($bcc) > 0)
			$this->email->bcc($bcc);

		$this->email->subject($subject);
		$this->email->message($message);

		$this->email->send();
		
		if($debug)
			echo $this->email->print_debugger();
	
	}
	
	function active_fuze($user_id = 0)
    {
		$fuze = 0;
		$query 	= $this->db->query("SELECT count(*) as cnt FROM user_fuzed_services WHERE status = 1 AND user_id = ".$user_id);
		if ($query->num_rows() > 0) {
			$fuze_items = $query->row();
			$fuze = $fuze_items->cnt;
		}
		
		return $fuze;
    }
    
    function total_fuze($user_id = 0)
    {
		$fuze = 0;
		$query 	= $this->db->query("SELECT count(*) as cnt FROM user_fuzed_services WHERE user_id = ".$user_id);
		if ($query->num_rows() > 0) {
			$fuze_items = $query->row();
			$fuze = $fuze_items->cnt;
		}
		
		return $fuze;
    }
    
    function acctype_fuzed_limit($aclevel = 0)
    {
		$level = 0;
		if($aclevel == 3)
			$level = 5;
		else if($aclevel == 4)
			$level = 15;
		else if($aclevel == 5 || $aclevel == 1 || $aclevel == 9)
			$level = -99;
		else if($aclevel == 6)
			$level = 0;
		else if($aclevel == 7)
			$level = 5;
		else if($aclevel == 8)
			$level = 15;
		
		return $level;
    } 
    
    function user_fuzed_limit($user_id = 0)
    {
		$fuze = 0;
		$query 	= $this->db->query("SELECT * FROM users WHERE id = ".$user_id);
		if ($query->num_rows() > 0) {
			$user = $query->row();
			$fuze = $user->fuze_limit;
		}
		
		return $fuze;
    }
    
    function acctype_fuzed_price($aclevel = 0)
    {
		$price = 0;
		if($aclevel == 3)
			$price = 29;
		else if($aclevel == 4)
			$price = 49;
		else if($aclevel == 5)
			$price = 99;
		else if($aclevel == 1 || $aclevel == 6)
			$price = 0;
		else if($aclevel == 7)
			$price = 29;
		else if($aclevel == 8)
			$price = 49;
		else if($aclevel == 9)
			$price = 99;

		return $price;
    }

    function acctype_fuzed_desc($aclevel = 0)
    {
		$desc = '';
		if($aclevel == 1) 
			$desc = 'Administrator';
		else if($aclevel == 3)
			$desc = 'Basic Plan';
		else if($aclevel == 4)
			$desc = 'Deluxe Plan';
		else if($aclevel == 5)
			$desc = 'Unlimited Plan';
		else if($aclevel == 6)
			$desc = 'Disable';			
		elseif($aclevel == 7)
			$desc = 'Basic Plan';
		else if($aclevel == 8)
			$desc = 'Deluxe Plan';
		else if($aclevel == 9)
			$desc = 'Unlimited Plan';

		return $desc;
    }
    
    public function register_infusion($user_info = array()) 
	{
		require_once APPPATH.'libraries/infusionsoft/isdk.php';
		
		$appName = 'gn126';
		$apiKey	 = '7c1b4e34acb56d4f977194af4fe36d85';
		
		$isapp = new iSDK;
		$isapp->cfgCon($appName, $apiKey);
		
		$email = $user_info['email'];
		$returnFields = array('Id', 'FirstName', 'LastName');
		$is_data = $isapp->findByEmail($email, $returnFields);
		
		if (count($is_data) > 0) 
			$contactId = $is_data[0]['Id'];
		else {
			$conDat = array('FirstName' => $user_info['first_name'], 'LastName'  => $user_info['last_name'], 'Email' => $user_info['email']);
			$contactId = $isapp->addCon($conDat);
		}
	
		$search_term = 'Fuzed';
		$returnFields = array('Id','GroupName');
		$group = $isapp->dsFind('ContactGroup', 5, 0,'GroupName', $search_term ,$returnFields);
	
		if(count($group) > 0) {
			$groupId = $group[0]['Id'];
		} else {
			$conDat = array('GroupName' => $search_term, 'GroupDescription'  => 'Registered in fuzedapp.com service.');
			$conID = $isapp->dsAdd("ContactGroup", $conDat);
			$groupId = $conID;
		}
		
		$grp_ct = $isapp->grpAssign($contactId, $groupId);
		
		return $grp_ct;
	}
	
	public function update_infusion_user($contactId = 0, $user_info = array()) 
	{
		require_once APPPATH.'libraries/infusionsoft/isdk.php';
		
		$appName = 'gn126';
		$apiKey	 = '7c1b4e34acb56d4f977194af4fe36d85';
		
		$isapp = new iSDK;
		$isapp->cfgCon($appName, $apiKey);
	
		$conDat = array('StreetAddress1' => $user_info['address_line1'],
						'City' => $user_info['address_city'],
						'State' => $user_info['address_state'],
						'PostalCode' => $user_info['address_postcode'],
						'Country' => $user_info['country_long']
						);
						
		$conID = $isapp->updateCon($contactId, $conDat);
		
		return $conID;
	}
	
	public function addToGoals($email = null, $access_type = 0) 
	{
		require_once APPPATH.'libraries/infusionsoft/isdk.php';
		
		$appName = 'gn126';
		$apiKey	 = '7c1b4e34acb56d4f977194af4fe36d85';
		
		$isapp = new iSDK;
		$isapp->cfgCon($appName, $apiKey);
		
		$returnFields = array('Id', 'FirstName', 'LastName');
		$is_data = $isapp->findByEmail($email, $returnFields);
		
		if (count($is_data) > 0) 
			$contactId = $is_data[0]['Id'];
		else {
			$conDat = array('FirstName' => '', 'LastName'  => '', 'Email' => $email);
			$contactId = $isapp->addCon($conDat);
		}
		
		if($access_type == 3)
			$callName = 'basicuser';
		else if($access_type == 4)
			$callName = 'deluxeuser';
		else if($access_type == 5)
			$callName = 'unliuser';
		else if($access_type == 6)
			$callName = 'disableuser';
		else if($access_type == 7)
			$callName = 'opfreeuser';
		else if($access_type == 8)
			$callName = 'opdeluxeuser';
		else if($access_type == 9)
			$callName = 'opunliuser';
		
		$Integration = 'gn126';
		
		$goal = $isapp->achieveGoal($Integration, $callName, $contactId);
		
		return $goal;
	}
	
	function subval_sort($a, $subkey) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		asort($b, SORT_NUMERIC);
		foreach($b as $k=>$v) {
			$c[] = $a[$k];
		}
		
		return $c;
	}
	
	function generateRapletCode() {
	
		$code = $this->randPass(8,6);
		$query 	= $this->db->query("SELECT * FROM users WHERE raplet_code = '".$code."'");
		if ($query->num_rows() > 0) {
			$code = $this->generateRapletCode();
		}
		
		return $code;
		
	}
	
	function getRapletCode($user_id) {
		
		$query 	= $this->db->query("SELECT * FROM users WHERE raplet_code <> '' AND id = ".$user_id);
		if ($query->num_rows() > 0) {
			$user = $query->row();
			$code = $user->raplet_code;	
		} else {
			$code = $this->generateRapletCode();
			$query 	= $this->db->query("UPDATE users SET raplet_code = '".$code."' WHERE id = ".$user_id);
		}
		
		return $code;
		
	}
	
	function getZendeskUrl($user_id) { 
		
		$query 	= $this->db->query("SELECT * FROM users WHERE id = ".$user_id);
		$code = '';
		if ($query->num_rows() > 0) {
			$user = $query->row();
			$code = $user->zendesk_url;	
		}
		
		return $code;
		
	}
	
	function getLastChargeToken($user_id = 0) {
	
		$token = '';
		$query 	= $this->db->query("SELECT max(id) as maxid FROM user_pin_transactions WHERE user_id = ".$user_id." AND SUBSTR(charge_details, 1, 24) = '{\"response\":{\"token\":\"ch' ");
		if ($query->num_rows() > 0) {
			$que = $query->row();
			$id = $que->maxid;
			
			$pin = $this->db->query("SELECT charge_details FROM user_pin_transactions WHERE id = ".$id);
			$pn  = $pin->row();
			
			$tk = json_decode($pn->charge_details);
			
			$token = $tk->response->token;
			
		}
		
		return $token;
	}
	
	function country_states($country = null) {
		
		$state = array();
		
		if($country == 'US') {
	
			$state = array('Alabama' => 'AL',
				   'Alaska' => 'AK',
				   'American Samoa' => 'AS',
					'Arizona' => 'AZ',
					'Arkansas' => 'AR',
					'California' => 'CA',
					'Colorado' => 'CO',
					'Connecticut' => 'CT',
					'Delaware' => 'DE',
					'District of Columbia' => 'DC',
					'Federated States of Micronesia*' => 'FM',
					'Florida' => 'FL',
					'Georgia' => 'GA',
					'Guam' => 'GU',
					'Hawaii' => 'HI',
					'Idaho' => 'ID',
					'Illinois' => 'IL',
					'Indiana' => 'IN',
					'Iowa' => 'IA',
					'Kansas' => 'KS',
					'Kentucky' => 'KY',
					'Louisiana' => 'LA',
					'Maine' => 'ME',
					'Marshall Islands*' => 'MH',
					'Maryland' => 'MD',
					'Massachusetts' => 'MA',
					'Michigan' => 'MI',
					'Minnesota' => 'MN',
					'Mississippi' => 'MS',
					'Missouri' => 'MO',
					'Montana' => 'MT',
					'Nebraska' => 'NE',
					'Nevada' => 'NV',
					'New Hampshire' => 'NH',
					'New Jersey' => 'NJ',
					'New Mexico' => 'NM',
					'New York' => 'NY',
					'North Carolina' => 'NC',
					'North Dakota' => 'ND',
					'Northern Mariana Islands' => 'MP',
					'Ohio' => 'OH',
					'Oklahoma' => 'OK',
					'Oregon' => 'OR',
					'Palau*' => 'PW',
					'Pennsylvania' => 'PA',
					'Puerto Rico' => 'PR',
					'Rhode Island' => 'RI',
					'South Carolina' => 'SC',
					'South Dakota' => 'SD',
					'Tennessee' => 'TN',
					'Texas' => 'TX',
					'Utah' => 'UT',
					'Vermont' => 'VT',
					'Virgin Island' => 'VI',
					'Virginia' => 'VA',
					'Washington' => 'WA',
					'West Virginia' => 'WV',
					'Wisconsin' => 'WI',
					'Wyoming' => 'WY'
					);
		
		} else if($country == 'AU') {		
					
			$state = array(	'Australian Capital Territory' => 'ACT',
					'New South Wales' => 'NSW',
					'Northern Territory' => 'NT',
					'Queensland' => 'QLD',
					'South Australia' => 'SA',
					'Tasmania' => 'TAS',
					'Victoria' => 'VIC',
					'Western Australia' => 'WA'
					);
					
		} else if($country == 'GB') {
		
			$state = array(	
					'ALD' => 'Alderney',
					'ATM' => 'County Antrim',
					'ARM' => 'County Armagh',
					'AVN' => 'Avon',
					'BFD' => 'Bedfordshire',
					'BRK' => 'Berkshire',
					'BDS' => 'Borders',
					'BUX' => 'Buckinghamshire',
					'CBE' => 'Cambridgeshire',
					'CTR' => 'Central ',
					'CHS' => 'Cheshire',
					'CVE' => 'Cleveland ',
					'CLD' => 'Clwyd',
					'CNL' => 'Cornwall',
					'CBA' => 'Cumbria',
					'DYS' => 'Derbyshire',
					'DVN' => 'Devon',
					'DOR' => 'Dorse',
					'DWN' => 'County Down',
					'DGL' => 'Dumfries and Galloway',
					'DHM' => 'County Durham',
					'DFD' => 'Dyfed',
					'ESX' => 'Essex',
					'FMH' => 'County Fermanagh',
					'FFE' => 'Fife',
					'GNM' => 'Mid Glamorgan',
					'GNS' => 'South Glamorgan',
					'GNW' => 'West Glamorgan',
					'GLR' => 'Gloucester',
					'GRN' => 'Grampian', 
					'GUR' => 'Guernsey',
					'GWT' => 'Gwent',
					'GDD' => 'Gwynedd',
					'HPH' => 'Hampshire',
					'HWR' => 'Hereford and Worcester',
					'HFD' => 'Hertfordshire',
					'HLD' => 'Highlands',
					'HBS' => 'Humberside',
					'IOM' => 'Isle of Man',
					'IOW' => 'Isle of Wight',
					'JER' => 'Jersey',
					'KNT' => 'Kent',
					'LNH' => 'Lancashire',
					'LEC' => 'Leicestershire',
					'LCN' => 'Lincolnshire',
					'LDN' => 'Greater London',
					'LDR' => 'County Londonderry',
					'LTH' => 'Lothian',
					'MCH' => 'Greater Manchester',
					'MSY' => 'Merseyside',
					'NOR' => 'Norfolk',
					'NHM' => 'Northamptonshire',
					'NLD' => 'Northumberland',
					'NOT' => 'Nottinghamshire',
					'ORK' => 'Orkney',
					'OFE' => 'Oxfordshire',
					'PWS' => 'Powys',
					'SPE' => 'Shropshire',
					'SRK' => 'Sark',
					'SLD' => 'Shetland',
					'SOM' => 'Somerset',
					'SFD' => 'Staffordshire',
					'SCD' => 'Strathclyde',
					'SFK' => 'Suffolk',
					'SRY' => 'Surrey',
					'SXE' => 'East Sussex',
					'SXW' => 'West Sussex',
					'TYS' => 'Tayside',
					'TWR' => 'Tyne and Wear',
					'TYR' => 'County Tyrone',
					'WKS' => 'Warwickshire',
					'WIL' => 'Western Isles',
					'WMD' => 'West Midlands',
					'WLT' => 'Wiltshire',
					'YSN' => 'North Yorkshire',
					'YSS' => 'South Yorkshire',
					'YSW' => 'West Yorkshire'
				);
		}
		
		$data = array();
		
		if(count($state) > 0) {
			foreach($state as $item => $val) {
				$data[] = array($item, $val);
			}
		}
		
		
		return $data;
	}
	
	function check_charge_to_disable($user_id) {
		
		$past_7_days = date("Y-m-d", strtotime('-7 days')).' 00:00:00';
		
		$to_disable = false;
		$events = $this->db->query("SELECT * from user_pin_transactions WHERE transaction_date >= '".$past_7_days."' AND user_id = ".$user_id." AND status = 0");
		if ($events->num_rows() > 13) {
			$to_disable = true;
		}
		
		return $to_disable;

	}
	
	function time_zone_list() {
	
		$timezones = array(
			'Pacific/Midway'       => "(GMT-11:00) Midway Island",
			'US/Samoa'             => "(GMT-11:00) Samoa",
			'US/Hawaii'            => "(GMT-10:00) Hawaii",
			'US/Alaska'            => "(GMT-09:00) Alaska",
			'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
			'America/Tijuana'      => "(GMT-08:00) Tijuana",
			'US/Arizona'           => "(GMT-07:00) Arizona",
			'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
			'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
			'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
			'America/Mexico_City'  => "(GMT-06:00) Mexico City",
			'America/Monterrey'    => "(GMT-06:00) Monterrey",
			'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
			'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
			'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
			'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
			'America/Bogota'       => "(GMT-05:00) Bogota",
			'America/Lima'         => "(GMT-05:00) Lima",
			'America/Caracas'      => "(GMT-04:30) Caracas",
			'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
			'America/La_Paz'       => "(GMT-04:00) La Paz",
			'America/Santiago'     => "(GMT-04:00) Santiago",
			'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
			'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
			'Greenland'            => "(GMT-03:00) Greenland",
			'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
			'Atlantic/Azores'      => "(GMT-01:00) Azores",
			'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
			'Africa/Casablanca'    => "(GMT) Casablanca",
			'Europe/Dublin'        => "(GMT) Dublin",
			'Europe/Lisbon'        => "(GMT) Lisbon",
			'Europe/London'        => "(GMT) London",
			'Africa/Monrovia'      => "(GMT) Monrovia",
			'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
			'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
			'Europe/Berlin'        => "(GMT+01:00) Berlin",
			'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
			'Europe/Brussels'      => "(GMT+01:00) Brussels",
			'Europe/Budapest'      => "(GMT+01:00) Budapest",
			'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
			'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
			'Europe/Madrid'        => "(GMT+01:00) Madrid",
			'Europe/Paris'         => "(GMT+01:00) Paris",
			'Europe/Prague'        => "(GMT+01:00) Prague",
			'Europe/Rome'          => "(GMT+01:00) Rome",
			'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
			'Europe/Skopje'        => "(GMT+01:00) Skopje",
			'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
			'Europe/Vienna'        => "(GMT+01:00) Vienna",
			'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
			'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
			'Europe/Athens'        => "(GMT+02:00) Athens",
			'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
			'Africa/Cairo'         => "(GMT+02:00) Cairo",
			'Africa/Harare'        => "(GMT+02:00) Harare",
			'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
			'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
			'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
			'Europe/Kiev'          => "(GMT+02:00) Kyiv",
			'Europe/Minsk'         => "(GMT+02:00) Minsk",
			'Europe/Riga'          => "(GMT+02:00) Riga",
			'Europe/Sofia'         => "(GMT+02:00) Sofia",
			'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
			'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
			'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
			'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
			'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
			'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
			'Asia/Tehran'          => "(GMT+03:30) Tehran",
			'Europe/Moscow'        => "(GMT+04:00) Moscow",
			'Asia/Baku'            => "(GMT+04:00) Baku",
			'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
			'Asia/Muscat'          => "(GMT+04:00) Muscat",
			'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
			'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
			'Asia/Kabul'           => "(GMT+04:30) Kabul",
			'Asia/Karachi'         => "(GMT+05:00) Karachi",
			'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
			'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
			'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
			'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
			'Asia/Almaty'          => "(GMT+06:00) Almaty",
			'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
			'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
			'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
			'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
			'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
			'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
			'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
			'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
			'Australia/Perth'      => "(GMT+08:00) Perth",
			'Asia/Singapore'       => "(GMT+08:00) Singapore",
			'Asia/Taipei'          => "(GMT+08:00) Taipei",
			'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
			'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
			'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
			'Asia/Seoul'           => "(GMT+09:00) Seoul",
			'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
			'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
			'Australia/Darwin'     => "(GMT+09:30) Darwin",
			'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
			'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
			'Australia/Canberra'   => "(GMT+10:00) Canberra",
			'Pacific/Guam'         => "(GMT+10:00) Guam",
			'Australia/Hobart'     => "(GMT+10:00) Hobart",
			'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
			'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
			'Australia/Sydney'     => "(GMT+10:00) Sydney",
			'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
			'Asia/Magadan'         => "(GMT+12:00) Magadan",
			'Pacific/Auckland'     => "(GMT+12:00) Auckland",
			'Pacific/Fiji'         => "(GMT+12:00) Fiji",
		);
		
		return $timezones;
	}
	
}

?>