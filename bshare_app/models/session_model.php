<?php

/**
 * Session model
 * @author Nguyen
 *
 */
class Session_model extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->helper('uuid');
	}
	
	// get session from the database
	private function _get_session($sessid) {
		if (!isset($sessid))
			return null;
		
		$session = $this->db->get_where('sessions', array('sessid' => $sessid), 1);
		
		return $session;
	}
	
	// check whether the session provided is opened or close
	private function _is_closed($session) {
		if (!isset($session['starttime']))
			return false;
		
		return isset($session['endtime']);
	} 
	
	/**
	 * Creates a session for a user
	 * A new session is created in independence with existing sessions of the same user.
	 * A session may never expire or end appropriately because the user may never logout.
	 * @param unknown $userid
	 * @return Ambigous <unknown, NULL, string>|string
	 */
	public function create_session($userid = -1) {
		if ($userid == -1)
			return '';
		
		// generate a unique session id
		$sesid = gen_uuid();
		$time = date("Y-m-d H:i:s");
		
		// define max number of tries if the sessid generated currently exists
		$max_tries = 5;
		
		// prepare the session data
		$session_data = array(
				'sessid' => gen_uuid(),
				'userid' => $userid,
				'starttime' => $time,
				'endtime' => ''
		);
		
		// insert the session to the database
		$try = 0;
		$success = false;
		while ($try < max_tries) {
			if ($this->db->insert('sessions', $session_data)) {
				$success = true;
				break;
			}			
			
			$session_data['sessid'] = gen_uuid();
			$try++;
		}
		
		if ($success)
			return $session_data['sessid'];
		else
			return '';
	}
	
	/**
	 * Get a session with provided session id.
	 * @param string $sessid
	 * @return NULL|unknown
	 */
	public function get_active_session($sessid) {
		// find session from the database
		$session = _get_session($sessid);
		
		if (!isset($session))
			return null;
		
		// return session only if it has not closed yet
		if (!_is_closed($session))
			return $session;
				
		return null;
	}
	
	/**
	 * Closes the session.
	 * @param unknown $sessid
	 */
	public function close_session($sessid = '') {
		$session = _get_session($sessid);
		
		if (!_is_closed($session))
			$session['endtime'] = date("Y-m-d H:i:s");
		
		$this->db->update('sessions', $session); 
	}
}