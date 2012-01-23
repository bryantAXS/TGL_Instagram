<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'tgl_instagram/classes/Instagram.php';

/**
 * The Antenna plugin will generate the YouTube or Vimeo embed
 * code for a single YouTube or Vimeo clip. It will also give
 * you back the Author, their URL, the video title,
 * and a thumbnail.
 *
 * @package Antenna
 */

class Tgl_instagram 
{
	public $return_data = '';
	public $cache_name = 'tgl_instagram';
	public $refresh_cache = 120;			// in mintues
	public $cache_expired = FALSE;

	public function Tgl_instagram() 
	{
		
		$this->EE =& get_instance();
		$this->EE->load->model('tgl_instagram_model');
		$settings = $this->EE->tgl_instagram_model->get_settings();

		$config = array(
      'client_id' => $settings['client_id'],
      'client_secret' => $settings['client_secret']
    );

		$this->instagram = new Instagram($config);
		$this->instagram->setAccessToken($settings['access_token']);
		
	}

	function user_feed(){
		
		$user_name = $this->EE->TMPL->fetch_param('username');
		$limit = $this->EE->TMPL->fetch_param('limit');

		$user_id = $this->_get_user_id($user_name);

		$user_feed_data = $this->instagram->getUserRecent($user_id);
		$response = json_decode($user_feed_data, true);

		$tagdata = $this->EE->TMPL->tagdata;

		//$this->_dump($response);

		$variables = array();
		$count = 0;

		foreach ($response['data'] as $data) {
		  
		  if($count == $limit) break;

		  $row_variables = array();
		  $row_variables['tags'] = $data['tags'];
		  $row_variables['filter'] = $data['filter'];
		  $row_variables['created_at'] = $data['created_time'];
		  $row_variables['link'] = $data['link'];

		  $row_variables['thumbnail_url'] = $data['images']['thumbnail']['url'];
		  $row_variables['thumbnail'] = "<img src='".$data['images']['thumbnail']['url']."' width='".$data['images']['thumbnail']['width']."' height='".$data['images']['thumbnail']['height']."'/>";

		  $row_variables['low_url'] = $data['images']['thumbnail']['url'];
		  $row_variables['low'] = "<img src='".$data['images']['thumbnail']['url']."' width='".$data['images']['thumbnail']['width']."' height='".$data['images']['thumbnail']['height']."'/>";

		  $row_variables['standard_url'] = $data['images']['thumbnail']['url'];
		  $row_variables['standard'] = "<img src='".$data['images']['thumbnail']['url']."' width='".$data['images']['thumbnail']['width']."' height='".$data['images']['thumbnail']['height']."'/>";

		  $variables[] = $row_variables;
    	
    	$count++;

  	}

    $return_data = $this->EE->TMPL->parse_variables($tagdata, $variables);
    
    return $return_data;

	}

	function _dump($data){
		echo "<pre>";
		echo print_r($data);
		echo "</pre>";
	}

	function _get_user_id($username){
		
		$user_data = $this->instagram->searchUser($username);
		$response = json_decode($user_data, true);

		$user_id = false;

		foreach($response['data'] as $data){
			if($data['username'] == $username){
				$user_id = $data['id'];
			}
		}

		return $user_id;

	}

	/**
	 * Check Cache
	 *
	 * Check for cached data
	 *
	 * @access	public
	 * @param	string
	 * @param	bool	Allow pulling of stale cache file
	 * @return	mixed - string if pulling from cache, FALSE if not
	 */
	function _check_cache($url)
	{	
		// Check for cache directory
		
		$dir = APPPATH.'cache/'.$this->cache_name.'/';
		
		if ( ! @is_dir($dir))
		{
			return FALSE;
		}
		
		// Check for cache file
		
        $file = $dir.md5($url);
		
		if ( ! file_exists($file) OR ! ($fp = @fopen($file, 'rb')))
		{
			return FALSE;
		}
		       
		flock($fp, LOCK_SH);
                    
		$cache = @fread($fp, filesize($file));
                    
		flock($fp, LOCK_UN);
        
		fclose($fp);

        // Grab the timestamp from the first line

		$eol = strpos($cache, "\n");
		
		$timestamp = substr($cache, 0, $eol);
		$cache = trim((substr($cache, $eol)));
		
		if ( time() > ($timestamp + ($this->refresh_cache * 60)) )
		{
			$this->cache_expired = TRUE;
		}
		
        return $cache;
	}
	
	/**
	 * Write Cache
	 *
	 * Write the cached data
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function _write_cache($data, $url)
	{
		// Check for cache directory
		
		$dir = APPPATH.'cache/'.$this->cache_name.'/';

		if ( ! @is_dir($dir))
		{
			if ( ! @mkdir($dir, 0777))
			{
				return FALSE;
			}
			
			@chmod($dir, 0777);            
		}
		
		// add a timestamp to the top of the file
		$data = time()."\n".$data;
		
		
		// Write the cached data
		
		$file = $dir.md5($url);
	
		if ( ! $fp = @fopen($file, 'wb'))
		{
			return FALSE;
		}

		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);
        
		@chmod($file, 0777);
	}
	
	/**
	 * ExpressionEngine plugins require this for displaying
	 * usage in the control panel
	 * @access public
	 * @return string 
	 */
    public function usage() 
	{
		ob_start();
?>
TGL Instagram

<?php
		$buffer = ob_get_contents();
		
		ob_end_clean(); 
	
		return $buffer;
	}
	// END
}