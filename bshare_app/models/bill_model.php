<?php

class Bill_model extends CI_Model {
	public function __construct() {
		$this->load->database();
	}

	function get_bills_by_userid($userid, &$time) {
		// before getting updated bills, get the current time of the mysql server
		$time = $this->db->query("SELECT NOW()");
		
		// retrieve bills
		$this->db->where('creatorid', $userid);
		$query = $this->db->get('bills');
		
		if ($query != false && $query->row() > 0) {
			return get_object_vars($query->result()[0]);
		} else
			return false;
	}
	
	function get_bill_by_billid($billid) {
		$this->db->where('billid', $billid);
		$query = $this->db->get('bills');
		
		if ($query != false && $query->num_rows() > 0) {
			return get_object_vars($query->result()[0]);
		} else 
			return false;
	}
	
	function get_updated_bills($userid, $last_sync_time, &$time) {
		// before getting updated bills, get the current time of the mysql server
		$time = $this->db->query("SELECT NOW()");
		
		// get updated bills
		$this->db->select('*');
		$this->db->from('bills');
		$this->db->where('creatorid', $userid);
		$this->db->where('lastupdatetime > ', $last_sync_time);
		
		$result = $this->db->get();
		
		if ($result == false)
			return false;
		else		
			return $result->result();
	}
	
	function create_bill($creatorid, $billdesc, $amount, $tip) {
		// Prepare the data to insert
		$bill_data = array(
			'creatorid' => $creatorid,
			'billdesc' => $billdesc,
			'amount' => $amount,
			'tip' => $tip,
			'isdone' => false,
			'isdeleted' => false
		);
		
		// Transaction
		$this->db->trans_start();
		$this->db->insert('bills', $bill_data);
		$id = $this->db->insert_id();
		if ($id != 0) {
			$this->db->where('billid', $id);
			$query = $this->db->get('bills');
			
			if ($query == true && $query->num_rows() > 0) {
				$bill_data = get_object_vars($query->result()[0]);
			} else {
				log_message('info', 'In bill_model.php createbill() mysql error.');
			}
		}
		$this->db->trans_complete();
		
		
		if ($this->db->trans_status() === false) {
			log_message('info', 'In bill_model.php createbill() transaction failed.'.$id);
			return false;
		} else {		
			return $bill_data;
		}		
	}
	
	function update_bill($billid, $billdesc, $amount, $tip) {
		$update_data = array(
			'billdesc' => $billdesc,
			'amount' => $amount,
			'tip' => $tip
		);
		
		$this->db->trans_start();
		
		// update bill
		$this->db->where('billid', $billid);		
		$this->db->update('bills', $update_data);
		
		// get latest state of the bill item
		$this->db->where('billid', $billid);
		$query = $this->db->get('bills');
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() == false || $query == false || $query->num_rows() == 0) {
			return false;
		} else {
			return get_object_vars($query->result()[0]);
		}
		
		return result;
	}
	
	function delete_bill($billid) {
		$this->db->where('billid', $billid);
				
		if ($this->db->delete('bills'))
			return true;
		else 
			return false;
	}
	
	function complete_bill($billid) {
		$this->db->where('billid', $billid);
		$this->db->where('isdone', false);
		
		$this->db->update('bills', array('isdone' => true));
		
		if ($this->db->affected_rows() > 0)
			return true;
		else 
			return false;
	}
	
	function request_money($requesteeid, $billid) {
		$bill_request = array(
			'billid' => $billid,
			'requesteeid' => $requesteeid,
			'isconfirmed' => false,
			'isdeleted' => false
		);

		$this->db->trans_start();		// start transaction
		
		// check whether a row exist
		$this->db->select('*');
		$this->db->from('billrequests');
		$this->db->where('billid', $billid);
		$this->db->where('requesteeid', $requesteeid);
		$this->db->where('isdeleted', true);
		
		$result = $this->db->get();
		
		if ($result == false) {		
			$this->db->insert('billrequests', $bill_request);
			
			if ($this->db->affected_rows() > 0) {
				// get the id of the recent inserted-row
				$id = $this->db->insert_id();
				
				return $id;
			} else {
				return false;
			}
		} else {
			//$this->db->
		}
	}
}