<?php
class Functions extends CI_Model {


	function __construct()
    {
        parent::__construct();
        
        date_default_timezone_set('Australia/Canberra'); // set standard time for canberra australia
    
    }
    
    function get_max_viewed($data = array(), $filter = array()) { //$filter has array('filter', 'filter value', 'return column');
    	
    	$percent_viewed = 0;
    	if(count($data) > 0) {
			foreach($data as $item) {
				
				$event_id = explode('/', $item->iframe_heatmap_url);
				
				if($filter[0] == $event_id[6] && $filter[1] == $item->email) {
					
					if($percent_viewed < $item->percent_viewed)
						$percent_viewed = $item->percent_viewed;
				
				}

			}
		}
    	
    	return $percent_viewed;
    
    }
    
    function get_gtw_max_viewed($data = array(), $email = '') {
    	
    	$total_viewed = 0;
    	$received_at = '';
    	if(count($data) > 0) {
			foreach($data as $item) {
				if($item->email == $email) {					
					$total_viewed = $total_viewed + $item->attendanceTimeInSeconds;
					$received_at = isset($item->attendance[0]->joinTime) ? $item->attendance[0]->joinTime : '';

					/*echo '<pre>';
					print_r($item);
					echo '</pre>';*/
				}
			}
		}
    	
    	return array($total_viewed, $received_at);
    }
    
	function subval_sort($a, $subkey) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		asort($b, SORT_STRING);
		foreach($b as $k=>$v) { 
			$c[] = $a[$k];
		}
		
		return $c;
	}
    
}