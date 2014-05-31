<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class Bills extends REST_Controller {
	function __construct() {
		parent::__construct();
		
		$this->load->model('bill_model');
		
		$this->load->library('session');
	}
	
	function create_bill_get() {
		if ($this->_check_authorization() == false) {
			log_message('info', 'Create bill: Unauthorized.');			
			return $this->response(null, 401);	
		}
		
		$billdesc = $this->get('billdesc');
		$amount = $this->get('amount');
		$tip = $this->get('tip');
		
		if ($billdesc == false || $amount == false || $tip == false) {
			log_message('info', 'Create bill: Bad request.');
			$this->response(null, 400);
		}
		
		// get user id from session
		$user_data = $this->session->userdata('user_data');
		$creatorid = $user_data['userid'];
		
		$bill_data = $this->bill_model->create_bill($creatorid, $billdesc, $amount, $tip);
		
		if ($bill_data == false) {
			$this->response(null, 409);
		} else {
			$this->response($bill_data, 200);
		}
	}
	
	function update_bill_get() {
		if (!$this->_check_authorization())
			$this->response(null, 401);
		
		// get userid
		$user_data = $this->session->userdata('user_data');
		$userid = $user_data['userid'];
		
		$billid = $this->get('billid');
		$billdesc = $this->get('billdesc');
		$amount = $this->get('amount');
		$tip = $this->get('tip');
		
		if ($billid == false)
			$this->response(null, 400);
		
		if ($userid == false)
			$this->response(null, 400);
		
		if ($billdesc == false || $amount == false || $tip == false) {
			$this->response(null, 400);
		}

		$bill = $this->bill_model->get_bill_by_billid($billid);
		
		if ($bill == false)
			$this->response(null, 404);

		// bill exists. only update when the request user is the creator of the bill
		if ($userid != $bill['creatorid']) {
			$this->response(null, 403);
		} else {			
			$result = $this->bill_model->update_bill($billid, $billdesc, $amount, $tip);
			
			if ($result == false)
				$this->response(null, 409);
			else 
				$this->response($result, 200);
		}
	}
	
	function delete_bill_get() {
		$billid = $this->get('billid');
		$userid = $this->get('userid');
		
		if ($billid == false)
			$this->response(null, 400);
		
		if ($userid == false)
			$this->response(null, 400);
		
		$bill = $this->bill_model->get_bill_by_billid($billid);
		
		if ($bill == false)
			$this->response(null, 404);

		if ($userid != $bill['creatorid']) {
			$this->response(null, 403);
		} else {
			$result = $this->bill_model->delete_bill($billid);
			
			if ($result == false)
				$this->response(null, 409);
			else
				$this->response(null, 200);
		}
	}
	
	/** functions to support synchronization **/
	
	function fetch_bills_get() {
		$last_sync_time = $this->get('last_sync_time');
		$userid = $this->get('userid');
		
		if ($userid == false)
			$this->response(null, 400);
		
		if ($last_sync_time == false) {		// last sync time is not known, get all the bills
			$bills = $this->bill_model->get_bills_by_userid($userid, $time);
		} else {
			$bills = $this->bill_model->get_updated_bills($userid, $last_sync_time, $time);
		}		
		
		if ($bills == false)
			$this->response(null, 404);
		else {
			$reply = array(
				'sync_time' => $time,
				'updates' => $bills
			);
			$this->response($reply, 200);
		}
	}
	
	private function _check_authorization() {
		$user_data = $this->session->userdata('user_data');
	
		if ($user_data == false)
			return false;
		else {
			$sess_user_id = $user_data['userid'];
				
			if ($sess_user_id == false) {				
				return false;
			} else {
				return true;
			}
		}
	}
}
