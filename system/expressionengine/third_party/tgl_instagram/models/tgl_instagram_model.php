<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model that handles DB communication for the TGL Twitter Module
 *
 * @author Bryant Hughes
 * @version 0.1
 * @copyright The Good Lab - http://thegoodlab.com , 18 August, 2011
 **/

class Tgl_instagram_model extends CI_Model {
  
	public $site_id;
	
	var $_ee;
	var $cache;
		
	function __construct()
	{
		parent::__construct();
		
		$this->_ee =& get_instance();
		$this->site_id = $this->_ee->config->item('site_id');
		
		//prep-cache
		if (! isset($this->_ee->session->cache['tgl_instagram']))
		{
			$this->_ee->session->cache['tgl_instagram'] = array();
		}
		$this->cache =& $this->_ee->session->cache['tgl_instagram'];
	
  }

	/**
	 * Returns all channel field settings
	 *
	 * @return array : settings for the module
	 * @author Bryant Hughes
	 */
	function get_settings()
	{

		$query = $this->db->query("SELECT * 
    FROM exp_tgl_instagram_settings
    WHERE site_id = ". $this->site_id);

    $settings = false;

    if($query->num_rows() > 0){

      $settings = array();

      foreach ($query->result_array() as $row){
				$settings[$row['var']] = $row['var_value'];
      }

    }
		
		return $settings;
			
	}
			
	/**
	 * Deletes all old settings, then loops through the post and creates new settings based on the values
	 * that are submitted.
	 *
	 * @return boolean - if the operation was successful
	 * @author Bryant Hughes
	 */
	function insert_new_settings()
	{
		
		$success = true;
		
		// get current settings out of DB
		$sql = "SELECT * FROM exp_tgl_instagram_settings WHERE site_id = $this->site_id";
		$settings_result = $this->db->query($sql);
		
		$old_settings = $settings_result->result_array();
				
		$current_settings = array();
				
		foreach ($old_settings as $csetting)
		{
			$current_settings[$csetting['var']] = $csetting['var_value'];
		}
			
		//remove all settings before we re-add them
		$this->delete_all_settings();
				
		// insert settings into DB
		foreach ($_POST as $key => $value)
		{
			if ($key !== 'submit' && $key !== 'Submit')
			{
        // $key = $DB->escape_str($key);
        if(!$this->db->query($this->db->insert_string(
         "exp_tgl_instagram_settings", 
         array(
           'var'       => $key,
           'var_value' => $value, 
           'site_id'   => $this->site_id
         )
        ))){
          $success = false;
        }
			}
		}
		
		return $success;
			
	}
	
	/**
	 * deletes any old request tokens and then re-inserts the provided tokens
	 *
	 * @param string $request_token 
	 * @param string $request_token_secret 
	 * @return boolean - if the operation was successful
	 * @author Bryant Hughes
	 */
	function insert_secret_token($request_token, $request_token_secret)
	{
		
		$success = true;
					
		$this->db->where('site_id', $this->site_id);
		$this->db->where('var', 'request_token');
		if( ! $this->db->delete('exp_tgl_instagram_settings')){
			$success = false;
		}
		
		$this->db->where('site_id', $this->site_id);
		$this->db->where('var', 'request_token_secret');
		if( ! $this->db->delete('exp_tgl_instagram_settings')){
			$success = false;
		}
		
		if(!$this->db->query($this->db->insert_string("exp_tgl_instagram_settings", 
     array(
       'var'       => 'request_token',
       'var_value' => $request_token, 
       'site_id'   => $this->site_id
     )
    ))){
      $success = false;
    }
		
		if(!$this->db->query($this->db->insert_string("exp_tgl_instagram_settings", 
     array(
       'var'       => 'request_token_secret',
       'var_value' => $request_token_secret, 
       'site_id'   => $this->site_id
     )
    ))){
      $success = false;
    }
		
		return $success;
		
	}
	
	/**
	 * deletes any old access tokens and then re-inserts the provided tokens
	 *
	 * @param string $access_token 
	 * @param string $access_token_secret 
	 * @return void
	 * @author Bryant Hughes
	 */
	function insert_access_token($access_token)
	{
		
		$success = true;
					
		$this->db->where('site_id', $this->site_id);
		$this->db->where('var', 'access_token');
		if( ! $this->db->delete('exp_tgl_instagram_settings')){
			$success = false;
		}
				
		if(!$this->db->query($this->db->insert_string("exp_tgl_instagram_settings", 
     array(
       'var'       => 'access_token',
       'var_value' => $access_token, 
       'site_id'   => $this->site_id
     )
    ))){
      $success = false;
    }
				
		return $success;
		
	}
	
	/**
	 * deletes all settings for the module
	 *
	 * @return void
	 * @author Bryant Hughes
	 */
	function delete_all_settings()
	{
		
		// clense current settings out of DB : we add the WHERE site_id = $site_id, because the only setting we want to save is the module_id 
		// setting, which is set to site_id 0 -- because its not site specific
		$sql = "DELETE FROM exp_tgl_instagram_settings WHERE site_id = $this->site_id";
		return $this->db->query($sql);
		
	}
	
	/**
	 * deletes a specific setting from the module
	 *
	 * @param string $val - name of the setting you want to delete
	 * @return void
	 * @author Bryant Hughes
	 */
	function delete_setting($val)
	{
		$this->db->where('site_id', $this->site_id);
		$this->db->where('var', $val);
		return $this->db->delete('exp_tgl_instagram_settings');
	}
		
}
	
	
	
	
	
	
	
