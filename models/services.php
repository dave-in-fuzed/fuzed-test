<?php
class Services extends CI_Model {


	function __construct()
    {
        parent::__construct();
        
        $user_id= $this->session->userdata('user_id') != '' ? $this->session->userdata('user_id') : 0;
        $user	= $this->Users->get_entry($user_id);
        
        if(count($user) > 0 && $user->timezone != '')
        	date_default_timezone_set($user->timezone);
        else 
        	date_default_timezone_set('UTC');
    
    }
	
	
	function getService($id = 0)
    {
    	$que = array();
    	
		if($id != 0) {
			$query = $this->db->query('SELECT * FROM services WHERE id = '.$id);
		
			if ($query->num_rows() > 0) {
				$que = $query->row();
			}
		}
		
		return $que;
		
    }
	
    function getName($id = 0)
    {
    	$fname = '';
    	if($id != 0) {
			$name = $this->getService($id);
			$fname = $name->service_name;
		}
		
		return $fname;
		
    }
    
    function getSubServiceNameId($id = 0)
    {
		$name = array(0, '');
		if($id != 0) {
			$query = $this->db->query('SELECT service_description, service_id FROM sub_services WHERE id = '.$id);
			if ($query->num_rows() > 0) {
				$que = $query->row();
				$name = array($que->service_id, $que->service_description);
			}
		}
		return $name;
		
    }
    
    function getServiceUserConnection($user_id = 0, $serv_id = 0)
    {
		
		$data = array();
		
		$query = $this->db->query('SELECT connection FROM user_services_connection WHERE user_id = '.$user_id.' AND service_id = '.$serv_id);
		
		if ($query->num_rows() > 0) {
			$que = $query->row();
			$data = json_decode($que->connection);
		}
		
		return $data;
		
    }
    
    
    function saveServiceUserConnection($user_id = 0, $serv_id = 0, $connection = array())
    {
		
		$data = json_encode($connection);
		
		$query = $this->db->query("INSERT INTO user_services_connection SET user_id = ".$user_id.", service_id = ".$serv_id.", connection = '".$data."', status = 1");
		
		if ($this->db->insert_id() > 0)	
			return 1;
		else
			return 0;
		
    }
    
	function getSubService($sub_id = 0)
    {
		
		$data = array();
		
		$query = $this->db->query('SELECT * FROM sub_services WHERE id = '.$sub_id);
		
		if ($query->num_rows() > 0) {
			$data = $query->row();
		}
		
		return $data;
		
    }
    
    
    function saveSubServiceUser($user_id = 0, $fuzed_name = '', $sub_serv_1 = 0,  $sub_serv_2 = 0, $values_1 = array(), $values_2 = array(), $status = 2)
    {
		
		$data_1 = json_encode($values_1);
		$data_2 = json_encode($values_2);
		
		$query = $this->db->query("INSERT INTO user_fuzed_services SET user_id = ".$user_id.", fuze_name = '".$fuzed_name."',sub_service_first = ".$sub_serv_1.", sub_service_second = '".$sub_serv_2."', 
									values_first = '".$data_1."', values_second = '".$data_2."', datetime_created = '".date("Y-m-d H:i:s")."', status = ".$status); // status (1 - run, 2 for pause)
		
		if ($this->db->insert_id() > 0)	
			return 1;
		else
			return 0;
		
    }
    
    
    function updatefuzedServiceUser($sub_serv = 0, $sub_service1, $sub_service2, $fuzed_name = '', $values_1 = array(), $values_2 = array())
    {
		
		$data_1 = json_encode($values_1);
		$data_2 = json_encode($values_2);
		
		$query = $this->db->query("UPDATE user_fuzed_services SET sub_service_first = ".$sub_service1." , sub_service_second = ".$sub_service2." , values_first = '".$data_1."', fuze_name = '".$fuzed_name."', values_second = '".$data_2."', datetime_updated = '".date("Y-m-d H:i:s")."' WHERE id = ".$sub_serv);
		
		return 1;
		
    }
    
        
    function getServiceHtml($serv_id = 0)
    {
		
		$form = '';
		
		$query = $this->db->query("SELECT connection_form FROM services WHERE id = ".$serv_id);
		
		if ($query->num_rows() > 0) {
			$que = $query->row();
			$form = $que->connection_form;
		}
		
		return $form;
		
    }
    
    function getSubServiceHtml($serv_id = 0)
    {
		
		$form = '';
		
		$query = $this->db->query("SELECT variables_form FROM sub_services WHERE id = ".$serv_id);
		
		if ($query->num_rows() > 0) {
			$que = $query->row();
			$form = $que->variables_form;
		}
		
		return $form;
		
    }
    
    
    function getFuzeUserService($fuzed_id = 0)
    {
		
		$data = array();
		
		$query = $this->db->query("SELECT * FROM user_fuzed_services WHERE id = ".$fuzed_id);
		
		if ($query->num_rows() > 0) {
			$data = $query->row();
		}
		
		return $data;
		
    }
    
    function offUserfFuzes($serv_id = 0, $user_id = 0)
    {
		
		$data = array();
		
		$query = $this->db->query("SELECT * FROM sub_services WHERE service_id = ".$serv_id." AND enable = 1");
		
		if ($query->num_rows() > 0) {
			$data = $query->result();
			
			foreach($data as $fuze) {
				
				$que = $this->db->query("UPDATE user_fuzed_services SET status = 2 WHERE user_id = ".$user_id." AND sub_service_first = ".$fuze->id);
				$que = $this->db->query("UPDATE user_fuzed_services SET status = 2 WHERE user_id = ".$user_id." AND sub_service_second = ".$fuze->id);
			
			}
			
		}
		
		return $data;
		
    }
    
    function unsubcribeUser($user_id = 0)
    {
		
		$data = array();
		$que = $this->db->query("UPDATE users SET access_type = 6 WHERE id = ".$user_id); 
		
		
		$query = $this->db->query("SELECT * FROM services WHERE status = 1");
		
		if ($query->num_rows() > 0) {
			$data = $query->result();
			
			foreach($data as $fuze) {
			
				$this->offUserfFuzes($fuze->id, $user_id);
			
			}
			
		}
		
		return $data;
		
    }
    
    function clearUserCache($user_id = 0, $serv_id = 0) {
    	
    	$que = 0;
    	switch ($serv_id) {
			case 1:
    			$que = $this->db->query("DELETE FROM user_media WHERE user_id = ".$user_id." AND service_id = ".$serv_id);
    			break;
    		case 2: 
    			$que = $this->db->query("DELETE FROM user_tags WHERE user_id = ".$user_id." AND service_id = ".$serv_id);
    			break;
    		case 3: 
    			$que = $this->db->query("DELETE FROM user_tags WHERE user_id = ".$user_id." AND service_id = ".$serv_id);
    			break;
    		case 4: 
    			$que = $this->db->query("DELETE FROM user_media WHERE user_id = ".$user_id." AND service_id = ".$serv_id);
    			break;
    	}
    		
    	return $que;
    }
    
    function saveUserMedias($user_id = 0, $serv_id = 0) {
		
		$data = array();
		
		switch ($serv_id) {
			case 1:
				require_once APPPATH.'libraries/wistia/WistiaApi.class.php';
			
				$wsapi_key = $this->getServiceUserConnection($user_id, $serv_id);
				if(count($wsapi_key) > 0) {
										
					$query = $this->db->query("DELETE FROM user_media WHERE user_id = ".$user_id." AND service_id = ".$serv_id);

					$wsitia = new WistiaApi($wsapi_key->key);
					$projects = $wsitia->projectList();
					
					foreach($projects as $proj_item) {
					
						$media = $wsitia->mediaList($proj_item->id);
				
						foreach($media as $item) {
							$data = array(
									'user_id' => $user_id, 
									'service_id' => $serv_id,
									'project_id' => $proj_item->id,
									'wistia_id' => $item->id,
									'media_id' => $item->hashed_id, 
									'name' => $item->name,
									'datestarted' => date("Y-m-d H:i:s", strtotime($item->created))
									 );
						
							//$que = $query->row();
						
							//if($que->cnt < 1)
							$query = $this->db->insert("user_media", $data);
						
						}
					}
				}
				break;
			
			case 2:
				require_once APPPATH.'libraries/infusionsoft/isdk.php';
			
				$keys = $this->getServiceUserConnection($user_id, $serv_id);
				if(count($keys) > 0) {
					$appName = $keys->appname;
					$apiKey	 = $keys->key;
				
					$isapp = new iSDK;
					$isapp->cfgCon($appName, $apiKey);
			
					$returnFields = array('Id', 'GroupName','GroupCategoryId', 'GroupDescription');
					$query = array('GroupName' => '%%');
					$groups = $isapp->dsQuery("ContactGroup",1000,0, $query, $returnFields);
					
					$query = $this->db->query("DELETE FROM user_tags WHERE user_id = ".$user_id." AND service_id = ".$serv_id);
					
					foreach($groups as $item) {
											
						$data = array(
								'user_id' => $user_id, 
								'service_id' => $serv_id,
								'tag_id' => $item['Id'],
								'tag_name' => $item['GroupName'] 
								 );
						
						$query = $this->db->query("SELECT count(*) as cnt FROM user_tags WHERE user_id = ".$user_id." AND service_id= ".$serv_id." AND tag_id = ".$item['Id']);
						$que = $query->row();
						
						if($que->cnt < 1)
							$query = $this->db->insert("user_tags", $data);
					}
				}
				break;
				
			case 3:
				require_once APPPATH.'libraries/oap/OAPApi.class.php';
				
				$keys = $this->getServiceUserConnection($user_id, $serv_id);
				if(count($keys) > 0) {
					$appName = $keys->appname;
					$apiKey	 = $keys->key;
	
					$oap = new OapApi($appName, $apiKey);
					//$oap->enableDebugging();
					$tag = $oap->pullTag();
					
					$query = $this->db->query("DELETE FROM user_tags WHERE user_id = ".$user_id." AND service_id = ".$serv_id);
					
					foreach ($tag->tag as $value){						
						
						$data = array(
								'user_id' => $user_id, 
								'service_id' => $serv_id,
								'tag_id' => (int)$value->attributes()->id,
								'tag_name' => (string)$value
								 );
						
						$query = $this->db->query("SELECT count(*) as cnt FROM user_tags WHERE user_id = ".$user_id." AND service_id= ".$serv_id." AND tag_id = ".(int)$value->attributes()->id);
						$que = $query->row();
						
						if($que->cnt < 1)
							$query = $this->db->insert("user_tags", $data);
						
					} 
					
				}
				break;
				
			case 4:
				require_once APPPATH.'libraries/gtw/gtw.class.php';
				
				$keys = $this->getServiceUserConnection($user_id, $serv_id);
				if(count($keys) > 0) {
					
					$query = $this->db->query("DELETE FROM user_media WHERE user_id = ".$user_id." AND service_id = ".$serv_id);
					
					$token = $keys->access_token;
					$orgKey	 = $keys->organizer_key;
	
					$gtw = new GtwApi($token, $orgKey);
					//$gtw->enableDebugging();
					$webinars 			= $gtw->getWebinars();
					$upcomingwebinars 	= $gtw->getWebinars(2);
					
					foreach ($webinars as $value) {
						
						$data = array(
							'user_id' => $user_id, 
							'service_id' => $serv_id,
							'media_id' => (string)$value->webinarKey, 
							'name' => $value->subject,
							'datestarted' => date("Y-m-d H:i:s", strtotime($value->times[0]->startTime)),
							'dateended' => date("Y-m-d H:i:s", strtotime($value->times[0]->endTime))
							 );
													
						//$query = $this->db->query("SELECT count(*) as cnt FROM user_media WHERE user_id = ".$user_id." AND service_id= ".$serv_id." AND media_id = '".(string)$value->webinarKey."'" );
						//$que = $query->row();
					
						//if($que->cnt < 1)
						$query = $this->db->insert("user_media", $data);
						
					} 
					
					foreach ($upcomingwebinars as $value) {
						
						$data = array(
							'user_id' => $user_id, 
							'service_id' => $serv_id,
							'media_id' => (string)$value->webinarKey, 
							'name' => $value->subject,
							'datestarted' => date("Y-m-d H:i:s", strtotime($value->times[0]->startTime)),
							'dateended' => date("Y-m-d H:i:s", strtotime($value->times[0]->endTime))
							 );
													
					//$query = $this->db->query("SELECT count(*) as cnt FROM user_media WHERE user_id = ".$user_id." AND service_id= ".$serv_id." AND media_id = '".(string)$value->webinarKey."'" );
					//$que = $query->row();
					
					//if($que->cnt < 1)
						$query = $this->db->insert("user_media", $data);
						
					} 
				}
				break;
			case 5:
				require_once APPPATH.'libraries/dropbox/dropbox.class.php';
				
				$keys = $this->Services->getServiceUserConnection($user_id, $serv_id);
				if(count($keys) > 0) {
					
					$query = $this->db->query("DELETE FROM dropbox_folders WHERE user_id = ".$user_id." AND service_id = ".$serv_id);
					$dropbox = new dropboxApi($keys->access_token); 
					$content = $dropbox->getFolders();
					$root = array();
					foreach ($content->contents as $value) {
						if($value->is_dir == 1) {
							
							$root[] = array($value->path, $value->path);	
							$folders = $dropbox->getFolderFolders($value->path);
							if(count($folders) > 0) {
								foreach($folders as $item) {
									$path = explode('/', $item->path);
									$add_dash = '';
									for($i=0; $i<(count($path)-2); $i++)
										$add_dash .= ' -';
									
									$path_name = $add_dash.' /'.$path[count($path) - 1];
									
									$tosave = array(
										'user_id' => $user_id, 
										'service_id' => $serv_id,
										'path' => $item->path, 
										'path_name' => $path_name
										 );
						
									$query = $this->db->insert("dropbox_folders", $tosave);
								}
							}
						}
					} 
				
					foreach($root as $item) {
						$tosave = array(
							'user_id' => $user_id, 
							'service_id' => $serv_id,
							'path' => $item[0], 
							'path_name' => $item[1]
							 );
							
						$query = $this->db->insert("dropbox_folders", $tosave);
					}
					
					
				}
				break;	
		}
		
		return $data;
    
    }
    
    
    function getSampleData($sub_service_id = 0, $values = null) {
		
		$user_id = $this->session->userdata('user_id');
		$services = $this->getSubServiceNameId($sub_service_id);
		
		$serv_id = $services[0];
		$wistia_values = json_decode($values);
		$gtw_values = json_decode($values);
		$dropbox = json_decode($values);

		$fuze = $this->getFuzeUserService($wistia_values->fuzed_id);
		
		$fuzed_created 	= isset($fuze->datetime_created) ? strtotime($fuze->datetime_created) : time();
		$fuzed_created_date = date('Y-m-d', $fuzed_created);
		
		$add_emails	= array();
		
		switch ($serv_id) {
			case 1:
				require_once APPPATH.'libraries/wistia/WistiaApi.class.php';
			
				$wsapi_key = $this->getServiceUserConnection($user_id, $serv_id);
				if(count($wsapi_key) > 0) {

					$wsitia = new WistiaApi($wsapi_key->key);
					
					
					
					if($wistia_values->media_id != '' && $wistia_values->media_id != -99) {

						$filter = array('media_id' => $wistia_values->media_id, 'per_page' => 100);

						$media = $wsitia->mediaEventList($filter);
						
						//echo '<pre>--';
						//print_r($media);
						//echo '</pre>';
						
						$media_details = $wsitia->mediaShow($wistia_values->media_id);
						$duration = (isset($media_details->duration) ? $media_details->duration : 0) + (60 * 10);
						$now_time = strtotime(date("Y-m-d H:i:s"));
				
						$event_list = array();
						// capture only events that more than the date was fuze created and recieved_at is less than now() + 10min + media length
						foreach($media as $item) {
				
							$event_id = explode('/', $item->iframe_heatmap_url);
							$received_at = strtotime($item->received_at);
					
							$after_duration = $received_at + $duration;
					
							$filter = array($event_id[6], $item->email);
							$percent_viewed = $this->Functions->get_max_viewed($media, $filter);
					
							$entry = array('event_id' => $event_id[6], 'email' => $item->email, 'percent_viewed' => $percent_viewed * 100, 'media_id' => $item->media_id, 'received_at' => $received_at);
							if(!in_array($entry, $event_list) && $item->email != '' && $received_at >= $fuzed_created &&  $now_time >= $after_duration)
								$event_list[] = $entry;
					
						}
		
						foreach($event_list as $item) {
				
							$percent_viewed = $item['percent_viewed'];
				
							$sat_cri = false;
							if($wistia_values->condition == '=' && $percent_viewed == $wistia_values->filter_value) {
								$sat_cri = true;
							} else if ($wistia_values->condition == '>' && $percent_viewed > $wistia_values->filter_value) {
								$sat_cri = true;
							} else if ($wistia_values->condition == '<' && $percent_viewed < $wistia_values->filter_value) {
								$sat_cri = true;
							} else if ($wistia_values->condition == '<>' && $percent_viewed >= $wistia_values->filter_value && $percent_viewed <= $wistia_values->filter_value2) {
								$sat_cri = true;
							}
							
							if($sat_cri) {
		
								$email = urldecode($item['email']);
								$add_emails[] = array('email'=> $email, 'percent_viewed'=> $percent_viewed, 'event_id' => $item['event_id'], 'media_id' => $item['media_id'], 'received_at' => $item['received_at']);
					
							}				
						}
			
					} else if($wistia_values->media_id != '' && $wistia_values->media_id == -99) {
			
						$media_list = $wsitia->mediaList($wistia_values->project_id);
		
						foreach($media_list as $media_item) {
		
							$filter = array('media_id' => $media_item->hashed_id, 'per_page' => 100);

							$media = $wsitia->mediaEventList($filter);
							$media_details = $wsitia->mediaShow($media_item->hashed_id);
							$duration = (isset($media_details->duration) ? $media_details->duration : 0) + (60 * 10);
							$now_time = strtotime(date("Y-m-d H:i:s"));
			
							$event_list = array();
							// capture only events that more than the date was fuze created and recieved_at is less than now() + 10min + media length
							foreach($media as $item) {
			
								$event_id = explode('/', $item->iframe_heatmap_url);
								$received_at = strtotime($item->received_at);
				
								$after_duration = $received_at + $duration;
				
								//echo $event_id[6].': '.date("Y-m-d H:i:s", $after_duration).' === '.date("Y-m-d H:i:s", $now_time).'<br />';
				
								$filter = array($event_id[6], $item->email);
								$percent_viewed = $this->Functions->get_max_viewed($media, $filter);
				
								$entry = array('event_id' => $event_id[6], 'email' => $item->email, 'percent_viewed' => $percent_viewed * 100, 'media_id' => $item->media_id, 'received_at' => $received_at);
								if(!in_array($entry, $event_list) && $item->email != '')
									$event_list[] = $entry;
				
							}

							foreach($event_list as $item) {
			
								$percent_viewed = $item['percent_viewed'];
			
								$sat_cri = false;
								if($wistia_values->condition == '=' && $percent_viewed == $wistia_values->filter_value) {
									$sat_cri = true;
								} else if ($wistia_values->condition == '>' && $percent_viewed > $wistia_values->filter_value) {
									$sat_cri = true;
								} else if ($wistia_values->condition == '<' && $percent_viewed < $wistia_values->filter_value) {
									$sat_cri = true;
								} else if ($wistia_values->condition == '<>' && $percent_viewed >= $wistia_values->filter_value && $percent_viewed <= $wistia_values->filter_value2) {
									$sat_cri = true;
								}
						
								if($sat_cri) {
	
									$email = urldecode($item['email']);
									$add_emails[] = array('email'=> $email, 'percent_viewed'=> $percent_viewed, 'event_id' => $item['event_id'], 'media_id' => $item['media_id'], 'received_at' => $item['received_at']);

								}				
							}
						}
					}
				}
				break;
			
			case 4:
				require_once APPPATH.'libraries/gtw/gtw.class.php';
				$add_emails = array();
				$keys = $this->getServiceUserConnection($user_id, $serv_id);
				if(count($keys) > 0) {
					
					$token = $keys->access_token;
					$orgKey	 = $keys->organizer_key;
	
					$gtw = new GtwApi($token, $orgKey);
					$emails = array();
					//check if it has media submitted
					if($gtw_values->media_id != '') {
						
						switch($sub_service_id) {
							case 7:
				
								$sessionTime = $gtw->getWebinarSessionTime($gtw_values->media_id);
				
								$total_time = 0;
								foreach($sessionTime as $time) {
				
									$total_time = strtotime($time->endTime) - strtotime($time->startTime);
					
									$webinar_end = strtotime($time->endTime) + (60 * 30);  // add 30 minutes before running the cron to make the the API propagate
									$date_now	= strtotime(date("Y-m-d H:i:s"));
					
									if($webinar_end <= $date_now) {
					
										$attendees = $gtw->getSessionAttendees($time->webinarKey, $time->sessionKey);
										$attendee_list	= array();
										
										//echo '<pre> attendees';
										//print_r($attendees);
										//echo '</pre>';
						
										foreach($attendees as $item) {
							
											$arr_data = array('email' => $item->email, 'sessionKey' => $time->sessionKey, 'registrantKey' => $item->registrantKey, 'received_at' => isset($item->attendance[0]->joinTime) ? strtotime($item->attendance[0]->joinTime) : 0);
											if(!in_array($arr_data, $emails))
												$attendee_list[] = $arr_data;
								
										}
						
										//echo '<pre> attendee_list';
										//print_r($attendee_list);
										//echo '</pre>';
						
										foreach($attendee_list as $item) {
				
											$session_details = $this->Functions->get_gtw_max_viewed($attendees, $item['email']);
											$attend_percent =  ($session_details[0] / $total_time) * 100;
				
											//$attend_percent = ($item->attendanceTimeInSeconds / $total_time) * 100;
											$zinc[] = array('email' => $item['email'], 'webinar_hrs' =>  ($total_time/60)/60, 'total_hrs' => ($session_details[0]/60)/60);
				
											$sat_cri = false;
											if($gtw_values->filter_value != '') {
												if($gtw_values->condition == '=' && $attend_percent == $gtw_values->filter_value) {
													$sat_cri = true;
												} else if ($gtw_values->condition == '>' && $attend_percent > $gtw_values->filter_value) {
													$sat_cri = true;
												} else if ($gtw_values->condition == '<' && $attend_percent < $gtw_values->filter_value) {
													$sat_cri = true;
												} else if ($gtw_values->condition == '<>' && $attend_percent >= $gtw_values->filter_value && $attend_percent <= $gtw_values->filter_value2) {
													$sat_cri = true;
												}
											} else {
												$sat_cri = true;
											}
		
											if($sat_cri && $item['email'] != '') {
				
												$email = urldecode($item['email']);
												
												$arr_vis = array('email'=> $email, 'percent_viewed' => $attend_percent, 'received_at' => $item['received_at']);
												//if(!in_array($arr_vis, $add_emails)) 
												$add_emails[] = $arr_vis;
					
											}				
										}
									}
								}
								break;
							
							case 8:
								$attended = array();
								$sessionTime = $gtw->getWebinarSessionTime($gtw_values->media_id);
								
								$total_time = 0;
								$with_session = false;
								foreach($sessionTime as $time) {
					
									$total_time = strtotime($time->endTime) - strtotime($time->startTime);
									$webinar_end = strtotime($time->endTime) + (60 * 30);  // add 30 minutes before running the cron to make the the API propagate
									$date_now	= strtotime(date("Y-m-d H:i:s"));
					
									if($webinar_end <= $date_now) {
										$with_session = true;
										$attendees = $gtw->getSessionAttendees($time->webinarKey, $time->sessionKey);
										foreach($attendees as $item) {
											$arr_item = array('email' => $item->email);
							
											if(!in_array($arr_item, $attended)) 
												$attended[] = $arr_item;
										}						
									}
								}
				
								if($with_session) {
									$registrants = $gtw->getRegistrants($gtw_values->media_id);
									foreach($registrants as $item) {
										
										$arr_data = array('email' => $item->email);
										$not_data = array('email' => $item->email, 'percent_viewed' => 0, 'received_at' => 0);
										if(!in_array($arr_data, $attended))
											$add_emails[] = $not_data;
									}
								}
			
								break;
							case 9:
								$registrants = $gtw->getRegistrants($gtw_values->media_id);
				
								foreach($registrants as $registered) {
					
									$arr_item = array('email' => $registered->email, 'percent_viewed' => 0, 'received_at' => 0);
				
									if(!in_array($arr_item, $add_emails)) {
										$add_emails[] = $arr_item;
									}
								}
							break;							
							}
						}
					}
				break;
			case 5:
				require_once APPPATH.'libraries/dropbox/dropbox.class.php';
				
				if($dropbox->media_id != -99) {
				
					$keys = $this->Services->getServiceUserConnection($user_id, $serv_id);
					if(count($keys) > 0) {
						$dropboxAPI = new dropboxApi($keys->access_token); 
	
						$content = $dropboxAPI->getFolderFiles($dropbox->media_id, true);
				
						foreach ($content as $item) {
							
							//print_r($item);

							$share = $dropboxAPI->getShare($item->path);

							$file = explode('/', $item->path);
							
							$filename 	= $file[count($file) - 1];
							$filename	= strlen($filename) > 30 ? substr($filename, 0, 30).'...' : $filename;
							
							$str = $file[count($file) - 1];
							
							$folder	= substr($item->path, 0, strlen($item->path) - strlen($str));
							$folder	= strlen($folder) > 50 ? substr($folder, 0, 50).'...' : $folder;
														
							$share_url = isset($share->url) ? $share->url : '';
													
							$add_emails[] = array('actual_file' => $item->path, 'filename'=> $filename, 'folder' => $folder, 'share_url' => $share_url);	
							
						} 
					}
				}
				
				break;
			case 6:
				require_once APPPATH.'libraries/google/google_conn.class.php';
				
				switch($sub_service_id) {
						case 13:
							$keys = $this->Services->getServiceUserConnection($user_id, $serv_id);
							if(count($keys) > 0) {
								$google = new google_conn(json_encode($keys));

								try {
									$events = $google->getCalendarEvents($dropbox->media_id, $fuzed_created_date.'T00:00:00Z');
								} catch (Exception $e) {
									$events = array();
									$events = $google->getCalendarEvents($dropbox->media_id);
								}
																	
								foreach ($events['items'] as $item) {

									$item_summary = isset($item['summary']) ? $item['summary'] :  '';
									$attendees = isset($item['attendees']) ? $item['attendees'] : array();
				
									//echo $item_summary.'<br />';
				
									$sat_cri = false;
									if($dropbox->condition == '**' && strpos('x'.strtolower($item_summary), strtolower($dropbox->filter_value)) !== false) {
										$sat_cri = true;
									} else if ($dropbox->condition == '*~' && !strncmp(strtolower($item_summary), strtolower($dropbox->filter_value), strlen($dropbox->filter_value))) {
										$sat_cri = true;
									}

									if(count($attendees) > 0 && $sat_cri) {
										
									/*echo $item_summary;
									echo '<pre>';
									print_r($attendees);
									echo '</pre>';*/
										
										foreach($attendees as $attend) {
											$organizer = isset($attend['organizer']) ? $attend['organizer'] : 0;
											if($attend['responseStatus'] == 'accepted' && $organizer == 0)
												$add_emails[] = array('email' => $attend['email'], 'appointment' => $item_summary);
										}
									}
								} 
							}
							break;
						case 14:
							$keys = $this->Services->getServiceUserConnection($user_id, $serv_id);
							if(count($keys) > 0) {
								$google = new google_conn(json_encode($keys));
								
								try {
									$events = $google->getCalendarEvents($dropbox->media_id, $fuzed_created_date.'T00:00:00Z');
								} catch (Exception $e) {
									$events = array();
									$events = $google->getCalendarEvents($dropbox->media_id);
								}

								foreach ($events['items'] as $item) {
									$item_summary = isset($item['summary']) ? $item['summary'] : '';
									$description = isset($item['description']) ? $item['description'] : '';  

									$sat_cri = false;
									if($dropbox->condition == '**' && strpos('x'.strtolower($item_summary), strtolower($dropbox->filter_value)) !== false) {
										$sat_cri = true;
									} else if ($dropbox->condition == '*~' && !strncmp(strtolower($item_summary), strtolower($dropbox->filter_value), strlen($dropbox->filter_value))) {
										$sat_cri = true;
									}
										
									$event_start= isset($item['start']['dateTime']) ? strtotime($item['start']['dateTime']) : 0;
									$date_now	= strtotime(date("Y-m-d H:i:s"));
					
									$regexp = "/[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}/i";		
									preg_match_all($regexp, $description, $desc_email);
					
									// get email from description
									if(count($desc_email[0]) > 0 && $sat_cri) {
					
										foreach($desc_email[0] as $email_item) {
						
											$add_emails[] = array('email' => $email_item, 'appointment' => $item_summary);
										}				
									}
								}
							}
							break;
							}

				
				break;				
			}
			
			return $add_emails;
			
		}
    
    function getMediaName($media_id = '') { 
    	
    	$media_name = '';
    	$user_id = $this->session->userdata('user_id');
    	
		$query = $this->db->query("SELECT * FROM user_media WHERE user_id = ".$user_id." AND media_id = '".$media_id."'");
		
		if ($query->num_rows() > 0) {
			$data 	= $query->row();
			$media_name = $data->name;
		}
		
		return $media_name;
    	
    }
    
    function getTagName($tag_id = '') {
    	
    	$tag_name = '';
    	$user_id = $this->session->userdata('user_id');
    	
		$query = $this->db->query("SELECT * FROM user_tags WHERE user_id = ".$user_id." AND tag_id = ".$tag_id);
		
		if ($query->num_rows() > 0) {
			$data		= $query->row();
			$tag_name 	= $data->tag_name;
		}
		
		return $tag_name;
    	
    }
    
    function addfieldOAP($field = '') {
    	
    	$id = 0;
    	$user_id = $this->session->userdata('user_id');
    	
		$query = $this->db->query("SELECT * FROM action_fields WHERE field_name = '".$field."' AND user_id = ".$user_id);
		
		if ($query->num_rows() < 1 && $field != '') {
			$data = array('field_name' => $field,
						  'user_id' => $user_id
						  );

			$query = $this->db->insert('action_fields', $data);
			$id = $this->db->insert_id();
		}
		
		return $id;
    	
    }
    
    function getNoConnection($user_id = null) {
    	
		$query = $this->db->query("SELECT * FROM user_services_connection WHERE user_id = ".$user_id);
		
		return $query->num_rows();
    	
    }
    
    function tag_color($tag = 0) {
    
    	$bcolor = '#efefef';
    	
    	if($tag == 1)
    		$bcolor = '#4DA6FF';
    	else if($tag == 2)
    		$bcolor = '#4d77cb';
    	else if($tag == 3)
    		$bcolor = '#9933cc';
    	else if($tag == 4)
    		$bcolor = '#cb4d4d';
    	else if($tag == 5)
			$bcolor = '#e09952';
		else if($tag == 6)
			$bcolor = '#dbdb57';
		else if($tag == 7)
			$bcolor = '#34b27d';
		
		return $bcolor;
    }
    
    function getUserHubspostPortalID($user_id = 0) {
    
    	require_once APPPATH.'libraries/hubspot/hubspot.class.php';
		
		$portal_id = 0;
		$keys = $this->Services->getServiceUserConnection($user_id, 7);
		if(count($keys) > 0) {
			$access_token = $keys->access_token;
			
			$hubspot = new hubspotApi($access_token);
			$content = $hubspot->getSettings();
			
			foreach ($content as $value) {
				if($value->name == 'list')
					$portal_id = $value->portalId;
			}
			
			if($portal_id == 0 && count($content) > 0) {
				$portal_id = $content[0]->portalId;
			
			}
		}
		
		return $portal_id;
    }
    
    function hubspostRequestToken($user_id = 0) {
    
    	require_once APPPATH.'libraries/hubspot/hubspot.class.php';
		
		$result = 0;
		$keys = $this->Services->getServiceUserConnection($user_id, 7);
		if(count($keys) > 0) {
			$refresh_token = $keys->refresh_token;
			$hubspot = new hubspotApi($refresh_token);
			$content = $hubspot->refreshToken($refresh_token);
			
			if(count($content) > 0 && !isset($content->error)) {	
				$this->db->query('DELETE FROM user_services_connection WHERE user_id = '.$user_id.' AND service_id = 7');			
				$result = $this->saveServiceUserConnection($user_id, 7, $content);
			}
			
		}
		
		return $result;
    }
    
    function getConnectionFuzeRaplet($user_id = null) {
    	
    	$show = array();
		$query = $this->db->query("SELECT * FROM user_fuzed_services WHERE sub_service_first = 18 AND status = 1 AND user_id = ".$user_id);

		if($query->num_rows() > 0) {
			$data = $query->row();
			$show = json_decode($data->values_first);
		}
				
		return $show;
    	
    }
    
    function getConnectionFuzeFreshdesk($user_id = null) { 
    	
    	$show = array();
		$query = $this->db->query("SELECT * FROM user_fuzed_services WHERE sub_service_first = 19 AND status = 1 AND user_id = ".$user_id);
		if($query->num_rows() > 0) {
			$data = $query->row();
			$show = json_decode($data->values_first);
		}
				
		return $show;
		
    }
    
    function getConnectionFuzeZendesk($user_id = null) { 
    	
    	$show = array();
		$query = $this->db->query("SELECT * FROM user_fuzed_services WHERE sub_service_first = 20 AND status = 1 AND user_id = ".$user_id);
		if($query->num_rows() > 0) {
			$data = $query->row();
			$show = json_decode($data->values_first);
		}
				
		return $show;
		
    }
    
    function getFuzeExistRaplet($user_id = null) {
    	
		$query = $this->db->query("SELECT * FROM user_fuzed_services WHERE sub_service_first = 18 AND user_id = ".$user_id);
		return $query->num_rows();
    	
    }
    
    function getFuzeExistFreshdesk($user_id = null) { 
    	
		$query = $this->db->query("SELECT * FROM user_fuzed_services WHERE sub_service_first = 19 AND user_id = ".$user_id);
		return $query->num_rows();
		
    }
    
    function getFuzeExistZendesk($user_id = null) { 
    	
		$query = $this->db->query("SELECT * FROM user_fuzed_services WHERE sub_service_first = 20 AND user_id = ".$user_id);
		return $query->num_rows();
		
    }
    
    function getFuzeExternal($user_id = null, $sub_service_id = null) { 
    	
    	$data = array();
    	
		$query = $this->db->query("SELECT * FROM user_fuzed_services WHERE sub_service_first = ".$sub_service_id." AND user_id = ".$user_id);
		if ($query->num_rows() > 0) {
			$data = $query->row();
		}
		
		return $data;
		
    }
    
    function hubspotRenewToken($user_id) {
    
		require_once APPPATH.'libraries/hubspot/hubspot.class.php';
	
		$keys = $this->Services->getServiceUserConnection($user_id, 7);					
		if(count($keys) > 0) {
	
			$hubspot = new hubspotApi($keys->access_token); 
			$content = $hubspot->getSettings();
			
			//check if the token is expired
			if(isset($content->status) && $content->status == 'error') {
				// do the renew token, else skip
				$do = $this->hubspostRequestToken($user_id);
				$new_keys = $this->Services->getServiceUserConnection($user_id, 7);
			}
		}
		
		return true;
    }
    
}