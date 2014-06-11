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
		
		// form validation
		$this->load->library('form_validation');
	}
	
	/**
	 * Create a new account
	 */
	public function signup_post() {
		// check validation	
		if ($this->form_validation->run('signup') == false) {
			$this->response(validation_errors(), 400);
		}
		
		// get data
		$username = $this->post('username');
		$email = $this->post('email');
		$password = $this->post('password');
		
		if ($this->user_model->create_user($username, $email, $password) == TRUE)
			$this->response('OK', 200);
		else {
			$this->response(null, 500);		// HTTP code 500 for server error
		}
	}
	
	/**
	 * Login to the system
	 */
	public function login_post() {	
		// get data
		if ($this->form_validation->run('login_username') == true) {
			$username_or_email = $this->post('username');
		} else if ($this->form_validation->run('login_email') == true) {
			$email = $this->post('email');
		} else {
			$this->response(validation_erros(), 400);
		}
		
		$password = $this->post('password');
		
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
	 * Log out.
	 */
	public function logout_get() {
		if (!$this->_check_authorization())
			$this->response(null, 404);
	
		// performs log out
		// remove session
		$array_items = array('userid' => '','username' => '','email' => '');
		$this->session->unset_userdata($array_items);
	
		$this->response(null, 200);
	}
	
	/**
	 * Sets avatar
	 */
	public function setavatar_post() {
		if (!$this->_check_authorization()) {
			$this->response(null, 404);
		}
		
		// get user id from session
		$userid = $this->session->userdata('user_data')['userid'];
		
		// change the uploaded file name
		$data = $_FILES['avatar'];		
		
		if ($data == false || $data['name'] == false)
			$this->response($data, 400);
		
		$file_name = $data['name'];
		$ext = pathinfo($file_name, PATHINFO_EXTENSION);
		
		if (preg_match("\.(?i)(jpg|png|gif|bmp)", $ext, $match) == false)
			$this->response($data, 400);

		$new_file_name = $userid.'_avt.'.pathinfo($file_name, PATHINFO_EXTENSION);
		
		// set upload directory
		$upload_dir = 'bshare_app/files/avatars/';
		$upload_file = $upload_dir.$new_file_name;
		
		// move upload file
		if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_file)) {
			// update database
			if ($this->user_model->set_avatar($userid, $new_file_name)) {
				$this->response($this->config->base_url().$upload_file, 200);
			}
		}
		
		$this->response(null, 400);
	}
	
	/**
	 * Sets alias. If new alias is identical with the old alias, 
	 * this function will return false.
	 */
	public function setalias_post() {
		if (!$this->_check_authorization()) {
			$this->response(null, 404);
		}
		
		// get user id from session
		$userid = $this->session->userdata('user_data')['userid'];
				
		if ($this->form_validation->run('set_alias') == false)
			$this->response(null, 400);
		
		$alias = $this->post('alias');
		
		if ($this->user_model->set_alias($userid, $alias) == true)
			$this->response(null, 200);
		
		$this->response(null, 400);
	}
	
	public function changepass_post() {
		if (!$this->_check_authorization()) {
			$this->response(null, 404);
		}

		// get user id from session
		$userid = $this->session->userdata('user_data')['userid'];

		if ($this->form_validation->run('change_password') == false)
			$this->response(null, 400);	

		$oldpass = $this->post('oldpass');
		$newpass = $this->post('newpass');
		
		if ($oldpass === $newpass)
			$this->response(null, 200);
		
		if ($this->user_model->change_password($userid, $oldpass, $newpass) == true)
			$this->response(null, 200);
		else
			$this->response(null, 400);
	}
	
	/**
	 * Gets information of a user.
	 * Currently required user to log in in order to use this function
	 */
	public function getuser_post() {
		if (!$this->_check_authorization()) {
			$this->response(null, 404);
		}		
		
		// runs validation to validate the user input
		if ($this->form_validation->run('get_user') == false)
			$this->response(null, 400);
		
		$requesterid = $this->session->userdata('user_data')['userid'];
		$userid = $this->post('userid');
		
		$user = $this->user_model->get_user_by_id($userid);
		
		if ($user == null)
			return $this->response(null, 400);
		
		// gets the friendship status between these two user
		$status = $this->user_model->check_friendship($requesterid, $user['userid']);

		$data = array(
			'friendship' => $status,
			'userid' => $user['userid'],
			'username' => $user['username'],
			'email' => $user['email'],
			'alias' => $user['alias'],
			'avatar' => $user['avatar'],
			'jointime' => $user['jointime'],
			'lastupdatetime' => $user['lastupdatetime'],
			'lastlogintime' => $user['lastlogintime'],			
		);
		
		return $this->response($data, 200);
	}
	
	/**
	 * Invites another member to become friend
	 */
	public function invitefriend_post() {
		if (!$this->_check_authorization()) {
			$this->response(null, 404);
		}
		
		if ($this->form_validation->run('invite_friend') == false) {
			$this->response(null, 400);
		}
				
		$inviterid = $this->session->userdata('user_data')['userid'];
		$inviteeid = $this->post('inviteeid');
		
		if ($this->user_model->invite_friend($inviterid, $inviteeid) == false) {
			$this->response(null, 409);
		} else {
			$this->response(null, 200);
		}
	}
	
	/**
	 * The invitee accepts friend invitation.
	 */
	public function accept_friend_get() {
		if (!$this->_check_authorization()) {
			$this->response(null, 404);
		}
		
		if ($this->form_validation->run('accept_reject_friend') == false) {
			$this->response(null, 400);
		}
		
		$inviterid = $this->post('inviterid');
		$inviteeid = $this->session->userdata('user_data')['userid'];

		if ($this->user_model->accept_friend($inviterid, $inviteeid) == false) {
			$this->response(null, 409);
		} else {
			$this->response(null, 200);
		}		
	}
	
	/**
	 * The invitee rejects the friend invitation.
	 */
	public function reject_friend_get() {
		if (!$this->_check_authorization()) {
			$this->response(null, 404);
		}
		
		if ($this->form_validation->run('accept_reject_friend') == false) {
			$this->response(null, 400);
		}
		
		$inviterid = $this->post('inviterid');
		$inviteeid = $this->session->userdata('user_data')['userid'];
		
		if ($this->user_model->reject_friend($inviterid, $inviteeid) == false) {
			$this->response(null, 409);
		} else {
			$this->response(null, 200);
		}		
	}
	
	/**
	 * A user thaws the friendship with one of his friends.
	 */
	public function unfriend_get() {
		if (!$this->_check_authorization()) {
			$this->response(null, 404);
		}
		
		if ($this->form_validation->run('unfriend') == false) {
			$this->response(null, 400);
		}
		
		$friendid2 = $this->post('inviterid');
		$friendid1 = $this->session->userdata('user_data')['userid'];
		
		if ($this->user_model->unfriend($friendid1, $friendid2) == false) {
			$this->response(null, 409);
		} else {
			$this->response(null, 200);
		}		
	}
	
	private function _check_authorization() {
		$user_data = $this->session->userdata('user_data');
		
		if ($user_data == false)
			return false;
		else {
			$sess_user_id = $user_data['userid'];
			
			if ($sess_user_id == false || preg_match('/^[1-9][0-9]*$/D', $sess_user_id) == false)
				return false;
			else
				return true;
		}
	}
}