<?php

/**
 * Admin model
 * @author Nguyen
 *
 */
class Admin_model extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	private function _startsWith($haystack, $needle)
	{
		return $needle === "" || strpos($haystack, $needle) === 0;
	}
	
	private function _endsWith($haystack, $needle)
	{
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}
	
	/**
	 * Resets the whole database.
	 * All records are removed. Auto-increment atribute values start from 1.  
	 * @return multitype:number
	 */
	public function reset_database() {
	    //load file
	    $commands = file_get_contents("bshare_app/bshare.sql");
	
	    //delete comments
	    $lines = explode("\n",$commands);
	    $commands = '';
	    foreach($lines as $line){
	        $line = trim($line);
	        if( $line && ! $this->_startsWith($line,'--') ){
	            $commands .= $line . "\n";
	        }
	    }
	
	    //convert to array
	    $commands = explode(";", $commands);
	
	    //run commands
	    $total = $success = 0;
	    foreach($commands as $command){
	        if(trim($command)){
	            $success += (@mysql_query($command)==false ? 0 : 1);
	            $total += 1;
	        }
	    }
	
	    //return number of successful queries and total number of queries found
	    return array(
	        "success" => $success,
	        "total" => $total
	    );
	}
	
	/**
	 * Backup databases (download directly to the client)
	 */
	public function backup_database() {
		// TODO
	}
}