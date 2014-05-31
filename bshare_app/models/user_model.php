<?php

/**
 * User model
 * @author Nguyen
 *
 */
class User_model extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	/**
	 * Checks if a username is in DB
	 *
	 * @access	public
	 * @param	string $username_or_email
	 * @return	TRUE if user already exists, otherwise FALSE
	 */	
	public function user_exists($username_or_email) {
		// find database for user with specified username or email
		$this->db->where('username', $username_or_email);
		$this->db->or_where('email', $username_or_email);
		$query = $this->db->get('users');
		
		if ($query != false && $query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Creates a new user
	 *
	 * @access	public
	 * @param	string $username the user's username
	 * @param	string $password the user's password
	 * @param	string $email the user's email
	 * @return	TRUE if user creation was successfull, otherwise FALSE
	 */	
	public function create_user($username, $email, $password) {
		// Create a random salt
		$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), TRUE));
		
		// Create salted password (Careful not to over season)
		$pw = hash('sha512', $password . $random_salt);
		
		// Get the date
		$date_now = date("Y-m-d H:i:s");
		
		$user_data = array(
			'username' => $username,
			'email' => $email,
			'password' => $pw,
			'alias' => $username,
			'salt' => $random_salt,
			'isactive' => TRUE,					// active user immediately
			'jointime' => $date_now,
			'lastlogintime' => $date_now,
			'lastupdatetime' => $date_now,
		);		
		
		$success = TRUE;
		
		// Transaction
		if (!$this->db->insert('users', $user_data))
			$success = FALSE;
		
		return $success;	
	}
	
	public function get_user($username_or_email) {
		$this->db->select('userid, username, avatar, email, password, salt');
		$this->db->from('users');
		$this->db->where('username', $username_or_email);
		$this->db->or_where('email', $username_or_email);
		$this->db->limit(1);
		
		$query = $this->db->get();
		
		if ($query == true && $query->num_rows() == 1) {
			// Return the row
			return get_object_vars($query->result()[0]);
		} else {
			return false;
		}
	}
	
	public function invite_friend($inviterid, $inviteeid) {
		// user invites himself, which is bad
		if ($inviterid == $inviteeid)
			$this->response(null, 400);
		
		$friendship_data = array(
			'inviterid' => $inviterid,
			'inviteeid' => $inviteeid,
			'status' => 1
		);
		
		if (!$this->db->insert('friendship', $friendship_data))
			return false;
		else
			return true;
	}

	public function accept_friend($inviterid, $inviteeid) {
		$this->db->where('inviterid', $inviterid);
		$this->db->where('inviteeid', $inviteeid);
		$this->db->where('status', 1);
		
		$accept = array(
			'status' => 2
		);
		
		$this->db->update('friendship', $accept);
		
		if ($this->db->affected_rows() > 0)
			return true;
		else
			return false;
	}
	
	public function reject_friend($inviterid, $inviteeid) {
		$this->db->where('inviterid', $inviterid);
		$this->db->where('inviteeid', $inviteeid);
		$this->db->where('status', 2);
		
		$this->db->delete('friendship');
		
		if ($this->db->affected_rows() > 0)
			return true;
		else
			return false;		
	}
	
	public function unfriend($inviterid, $inviteeid) {
		$this->db->where('inviterid', $inviterid);
		$this->db->where('inviteeid', $inviteeid);
		$this->db->where('status', 1);
		
		$this->db->delete('friendship');
		
		if ($this->db->affected_rows() > 0)
			return true;
		else
			return false;		
	}
}