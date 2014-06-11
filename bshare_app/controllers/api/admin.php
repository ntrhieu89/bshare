<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Hieu Nguyen
 */

require APPPATH.'/libraries/REST_Controller.php';

class Admin extends REST_Controller {
	function __construct() {
		parent::__construct();
	
		// loading the database model
		$this->load->model('admin_model');
	}
	
	public function resetdb_post() {
		$salt = '26bfabe84c4963c96e1150e6bcfb41675d3dc6862545f896a387eea4dae7ab4b340a875c19f416dea9938e1e7178dd6775fec0d8a358b0921dea267bc803408b';
		$code = 'f98b5157ffdfbfe454447cbadc18d7a0d6a33f9771ac2701f0039a4b038e189865b9e02f8d2de99c3a7470d551d2f6acc17e3231a4361b72f7018b38e29ce4ef';
		
		$ccode = $this->post('code');
		
		// hash client code
		$ccode_hash = hash('sha512', $ccode . $salt);
		
		if ($ccode_hash === $code) {
			$var = $this->admin_model->reset_database();
			
			if ($var['success'] === $var['total'])
				$this->response($var, 200);
			else 
				$this->response($var, 400);
		} else 
			$this->response(null, 400);
	}
}