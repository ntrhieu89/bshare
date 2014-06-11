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
	
	public function set_avatar($userid, $avatar) {
		$data = array(
			'avatar' => $avatar	
		);
		
		$this->db->where('userid', $userid);
		$this->db->update('users', $data);

		if ($this->db->affected_rows() == 0 || $this->db->affected_rows() == 1) {
			return true;
		} else 
			return false;
	}
	
	public function set_alias($userid, $alias) {
		$data = array(
			'alias' => $alias
		);
		
		$this->db->where('userid', $userid);
		$this->db->update('users', $data);
		
		if ($this->db->affected_rows() == 1)
			return true;
		else 
			return false;
	}
	
	public function change_password($userid, $oldpass, $newpass) {
		$this->db->where('userid', $userid);
		$result = $this->db->get('users');
		
		if ($result == true && $result->num_rows() == 1) {
			$user = get_object_vars($result->result()[0]);
			
			$pw = hash('sha512', $oldpass . $user['salt']);
			
			if ($pw === $user['password']) {
				$new_pw = hash('sha512', $newpass . $user['salt']);
				$data = array('password' => $new_pw);
				
				$this->db->where('userid', $userid);
				$this->db->update('users', $data);
				
				if ($this->db->affected_rows() == 1)
					return true;
			}
		}
		
		return false;
	}
	
	public function get_user_by_id($userid) {
		$query = $this->db->query('select * from users where userid='.$userid.'');
		
		if ($query == true && $query->num_rows() == 1) {
			return get_object_vars($query->result()[0]);
		} else 
			return false;
	}
	
	/**
	 * Checks friendship of user 2 with the view of user 1.
	 * For example, if the return status = 1, it means the user 1 has sent friendship request
	 * but still does not have confirmation from user 2.
	 * @param unknown $user1
	 * @param unknown $user2
	 */
	public function check_friendship($user1id, $user2id) {
		if ($user1id === $user2id)
			return 'owner';
		
		$query = $this->db->query('select * from friendship where inviterid='.$user1id.' and inviteeid='.$user2id.'');
		
		if ($query != null && $query->num_rows() == 1) {
			$friendship = get_object_vars($query->result()[0]);		
			
			if ($friendship['status'] == 1)
				return 'invitee';
			else if ($friendship['status'] == 2)
				return 'friend';			
		}
		
		$query = $this->db->query('select * from friendship where inviterid='.$user2id.' and inviteeid='.$user1id.'');
		
		if ($query != null && $query->num_rows() == 1) {
			$friendship = get_object_vars($query->result()[0]);
				
			if ($friendship['status'] == 1)
				return 'inviter';
			else if ($friendship['status'] == 2)
				return 'friend';
		}		
		
		return 'stranger';
	}
	
	/** functions supported for social network **/
	
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
	
	/**
	 * Invites friend.
	 * Only return true if the two users exist and their relationship status is not set.
	 * @param unknown $inviterid
	 * @param unknown $inviteeid
	 * @return boolean
	 */
	public function invite_friend($inviterid, $inviteeid) {
		// user invites himself, which is bad
		if ($inviterid == $inviteeid)
			$this->response(null, 400);
		
		$this->db->trans_start();
		$this->db->query("select * from friendship where (inviterid=".$inviterid." and inviteeid=".$inviteeid.") or (inviterid=".$inviteeid." and inviteeid=".$inviterid.")");
		$result = $this->db->get();
		
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

	/**
	 * Accepts friend.
	 * Only return true if the two users exist and an invitation for friend request was sent before. 
	 * @param unknown $inviterid
	 * @param unknown $inviteeid
	 * @return boolean
	 */
	public function accept_friend($inviterid, $inviteeid) {
		$this->db->where('inviterid', $inviterid);
		$this->db->where('inviteeid', $inviteeid);
		$this->db->where('status', 1);
		
		$accept = array(
			'status' => 2
		);
		
		$this->db->update('friendship', $accept);
		
		if ($this->db->affected_rows() == 1)
			return true;
		else
			return false;
	}
	
	public function reject_friend($inviterid, $inviteeid) {
		$this->db->where('inviterid', $inviterid);
		$this->db->where('inviteeid', $inviteeid);
		$this->db->where('status', 1);
		
		$this->db->delete('friendship');
		
		if ($this->db->affected_rows() == 1)
			return true;
		else
			return false;		
	}
	
	public function unfriend($friendid1, $friendid2) {
		$this->db->where('inviterid', $friendid1);
		$this->db->where('inviteeid', $friendid2);
		$this->db->or_where('inviterid', $friendid2);
		$this->db->where('inviteeid', $friendid1);
		$this->db->where('status', 2);
		
		$this->db->delete('friendship');
		
		if ($this->db->affected_rows() == 1)
			return true;
		else
			return false;		
	}
}