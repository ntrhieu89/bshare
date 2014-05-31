<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Hieu Nguyen
 */

require APPPATH.'/libraries/REST_Controller.php';

class Users extends REST_Controller {
	function __construct() {
		parent::__construct();
		
		// loading the database
		$this->load->model('user_model');
		
		// using session
		$this->load->library('session');
	}
	
	/**
	 * Create a new account
	 */
	public function signup_get() {
		// get data
		$username = $this->get('username');
		$email = $this->get('email');
		$password = $this->get('password');
		
		if ($username == false || $password == false || $email == false)
			$this->response(null, 400);
			
		if ($this->user_model->user_exists($username) ||
				$this->user_model->user_exists($email)) {
			$this->response(null, 409);		// HTTP code 409 for conflict
		}
		
		if ($this->user_model->create_user($username, $email, $password) == TRUE)
			$this->response(null, 200);
		else {
			$this->response(null, 500);		// HTTP code 500 for server error
		}
	}
	
	/**
	 * Login to the system
	 */
	public function login_get() {		
		// get data
		if ($this->get('username') != false) {
			$username_or_email = $this->get('username');
		} elseif ($this->get('email') != false) {
			$username_or_email = $this->get('email');
		}
		
		$password = $this->get('password');
		
		if ($username_or_email == false || $password == false) {
			$this->response(null, 400);
		}
		
		// get user
		$user = $this->user_model->get_user($username_or_email);
		
		// user not found
		if ($user === null) {
			$this->response(null, 404);
			return;
		}
		
		// check if password matches
		$pw = hash('sha512', $password . $user['salt']);
		
		if ($pw === $user['password']) {
			// prepare session data			
			$user_data = array(
				'userid' => $user['userid'],
				'username' => $user['username'],
				'email' => $user['email']
			);
			
			$this->session->set_userdata('user_data', $user_data);
			
			// return HTTP OK
			$this->response(null, 200);
		} else {
			$this->response(null, 404);	
		}		
	}
	
	/**
	 * Invites another member to become friend
	 */
	public function invite_friend_get() {
		$inviterid = $this->get('inviterid');
		$inviteeid = $this->get('inviteeid');
		
		if ($inviterid == false || inviteeid == false)
			$this->response(null, 400);
		
		if ($this->user_model->invite_friend($inviterid, $inviteeid) == false) {
			$this->response(null, 409);
		} else {
			$this->response(null, 200);
		}
	}
	
	public function accept_friend_get() {
		$inviterid = $this->get('inviterid');
		$inviteeid = $this->get('inviteeid');
		
		if ($inviterid == false || inviteeid == false)
			$this->response(null, 400);

		if ($this->user_model->accept_friend($inviterid, $inviteeid) == false) {
			$this->response(null, 409);
		} else {
			$this->response(null, 200);
		}		
	}
	
	public function reject_friend_get() {
		$inviterid = $this->get('inviterid');
		$inviteeid = $this->get('inviteeid');
		
		if ($inviterid == false || inviteeid == false)
			$this->response(null, 400);
		
		if ($this->user_model->reject_friend($inviterid, $inviteeid) == false) {
			$this->response(null, 409);
		} else {
			$this->response(null, 200);
		}		
	}
	
	public function unfriend_get() {
		$inviterid = $this->get('inviterid');
		$inviteeid = $this->get('inviteeid');
		
		if ($inviterid == false || inviteeid == false)
			$this->response(null, 400);
		
		if ($this->user_model->unfriend($inviterid, $inviteeid) == false) {
			$this->response(null, 409);
		} else {
			$this->response(null, 200);
		}		
	}
	
	/**
	 * Log out.
	 */
	public function logout_get() {		
		if (!$this->_check_authorization())
			$this->response(null, 404);

		// performs log out
		// remove session
		$this->session->sess_destroy();
		
		$this->response(null, 200);
	}
	
	private function _check_authorization() {
		$user_data = $this->session->userdata('user_data');
		
		if ($user_data == false)
			return false;
		else {
			$sess_user_id = $user_data['userid'];
			
			if ($sess_user_id == false)
				return false;
			else
				return true;
		}
	}
}