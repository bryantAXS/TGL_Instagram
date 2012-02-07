<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'tgl_instagram/classes/Instagram.php';

/**
 * TGL Instagram is a EE Module allowing a user to authenticate with the Instagram API, and to retrieve infromation from it using embedded template tags.
 * 
 *  For more information please see the repository here:  
 *
 * @package Tgl
 */

class Tgl_instagram 
{
	public $return_data = '';
	public $cache_name = 'tgl_instagram';
	public $refresh_cache = 60;			// in mintues
	public $cache_expired = FALSE;

	public function Tgl_instagram() 
	{
		
		$this->EE =& get_instance();
		$this->EE->load->helper('url');
		$this->EE->load->model('tgl_instagram_model');

		$settings = $this->EE->tgl_instagram_model->get_settings();

		$config = array(
      'client_id' => $settings['client_id'],
      'client_secret' => $settings['client_secret']
    );

		$this->instagram = new Instagram($config);
		$this->instagram->setAccessToken($settings['access_token']);
		
	}

	/**
	 * Spits out array nicely
	 * @param  array $data
	 * @return void
	 */
	function _dump($data){
		echo "<pre>";
		echo print_r($data);
		echo "</pre>";
	}

	function _log($message){
		$this->EE->TMPL->log_item("TGL Instagram - ".$message);
	}

	/**
	 * Returns the authenticated user's feed.
	 * @return [type]
	 */
	function feed(){
		
		//params from the template tag
		$params = array();
		$params['method'] = 'feed'; // just adding a dummy value, so we don't get duplicate cache values for other methods that use the same params
		$params['cache'] = (integer) $this->EE->TMPL->fetch_param('cache', $this->refresh_cache);
		$params['limit'] = $this->EE->TMPL->fetch_param('limit', null);

		//check to see if we have cached data to use
		if($cached_data = $this->_check_cache($params)){
			if(! $this->cache_expired){
				return $cached_data;	
			}
		}

		//no cache data to use, lets hit the api
		$feed_data = $this->instagram->getUserFeed(null,null,$params['limit']);

		//extract
		$response = json_decode($feed_data, true);

		//if there is no data, return
		if( ! isset($response['data']) || count($response['data']) < 1 || empty($response['data']))
		{
			return FALSE;
		}

		//get data between ee tags
		$tagdata = $this->EE->TMPL->tagdata;

		//variables to be used when we parse the tagdata
		$variables = array();
		$count = 0;

		foreach ($response['data'] as $data) {
		  
		  if($params['limit'] != null && $count == $params['limit']) break;

		  $row_variables = array();
		  
		  $row_variables = $this->_parse_row_data($data, $row_variables);
		  
		  $variables[] = $row_variables;
    	
    	$count++;

  	}

    $return_data = $this->EE->TMPL->parse_variables($tagdata, $variables);

    $this->_write_cache($return_data, $params);

    return $return_data;

	}


	/**
	 * Returns a specific user's own feed (only pictures they explicitly took).
	 * @return [type]
	 */
	function user_feed(){
		
		$params = array();
		$params['method'] = 'username'; // just adding a dummy value, so we don't get duplicate cache values for other methods that use the same params
		$params['user_name'] = $this->EE->TMPL->fetch_param('username');
		$params['limit'] = $this->EE->TMPL->fetch_param('limit');
		$params['cache'] = (integer) $this->EE->TMPL->fetch_param('cache', $this->refresh_cache);

		//make sure we have the required parameters
		if(! isset($params['user_name']))
		{
			$this->_log("Username not specified");
			return FALSE;
		}

		//check to see if we have cached data to use
		if($cached_data = $this->_check_cache($params)){
			if(! $this->cache_expired){
				$this->_log("Returning cached data");
				return $cached_data;	
			}
		}

		//get the user id for the api call
		$user_id = $this->_get_user_id($params['user_name']);
		$user_feed_data = $this->instagram->getUserRecent($user_id);
		
		$response = json_decode($user_feed_data, true);

		//if there is no data, return
		if( ! isset($response['data']) || count($response['data']) < 1 || empty($response['data']))
		{
			$this->_log("No response from Instagram API");
			return FALSE;
		}

		$tagdata = $this->EE->TMPL->tagdata;

		$variables = array();
		$count = 0;

		foreach ($response['data'] as $data) {
		  
		  if($params['limit'] != null && $count == $params['limit']) break;

		  $row_variables = array();
		  
		  $row_variables = $this->_parse_row_data($data, $row_variables);
		  
		  $variables[] = $row_variables;
    	
    	$count++;

  	}

    $return_data = $this->EE->TMPL->parse_variables($tagdata, $variables);

    $this->_write_cache($return_data, $params);

    $this->_log("Returning fresh data");

    return $return_data;

	}

	function popular(){
		
		$params = array();
		$params['method'] = 'popular'; // just adding a dummy value, so we don't get duplicate cache values for other methods that use the same params
		$params['limit'] = $this->EE->TMPL->fetch_param('limit');
		$params['cache'] = (integer) $this->EE->TMPL->fetch_param('cache', $this->refresh_cache);

		//check to see if we have cached data to use
		if($cached_data = $this->_check_cache($params)){
			if(! $this->cache_expired){
				return $cached_data;	
			}
		}

		$popular_feed_data = $this->instagram->getPopularMedia();
	
		$response = json_decode($popular_feed_data, true);

		//if there is no data, return
		if( ! isset($response['data']) || count($response['data']) < 1 || empty($response['data']))
		{
			return FALSE;
		}

		$tagdata = $this->EE->TMPL->tagdata;

		$variables = array();
		$count = 0;

		foreach ($response['data'] as $data) {
		 	
		  if($params['limit'] != null && $count == $params['limit']) break;

		  $row_variables = array();
		  
		  $row_variables = $this->_parse_row_data($data, $row_variables);
		  
		  $variables[] = $row_variables;
    	
    	$count++;

  	}

    $return_data = $this->EE->TMPL->parse_variables($tagdata, $variables);

    $this->_write_cache($return_data, $params);

    return $return_data;

	}

	/**
	 * Extracts data from a single row returned from the API and assigns it to our row_variables array which eventually gets used to parse our template
	 * @param  array $data          a single row's data from the Instagram API
	 * @param  array $row_variables An array of data for a row will be parsing, something this is blank and sometimes it might hold other data that has already been parsed.
	 * @return array
	 */
	function _parse_row_data($data, $row_variables = null)
	{
		
		if(! $row_variables) $row_variables = array();

	  //static data
	  $row_variables['filter'] = $data['filter'];
	  $row_variables['created_at'] = $data['created_time'];
	  $row_variables['link'] = $data['link'];

	  $row_variables['tag_count'] = count($data['tags']);
	  if(count($data['tags']) > 0){
	  	$row_variables['tags'] = $this->_parse_tags($data['tags']);
	  }else{
	  	$row_variables['tags'] = array();
	  }

	  //caption
	  if(count($data['caption']) > 0){
	  	$row_variables['caption'] = $data['caption']['text'];
	  }else{
	  	$row_variables['caption'] = "";
	  }

	  //comments
	  if(count($data['comments']) > 0){
	  	$row_variables['comment_count'] = $data['comments']['count'];
	  	$row_variables['comments'] = $this->_parse_comments($data['comments']['data']);
	  }

	  //likes
	  if(count($data['likes']) > 0){
	  	$row_variables['likes_count'] = $data['likes']['count'];
	  	$row_variables['likes'] = $this->_parse_likes($data['likes']['data']);
	  }

	  //user
	  $row_variables['username'] = $data['user']['username'];
	  $row_variables['website'] = $data['user']['website'];
	  $row_variables['bio'] = $data['user']['bio'];
	  $row_variables['profile_picture'] = $data['user']['profile_picture'];
	  $row_variables['full_name'] = $data['user']['full_name'];


	  //images
	  $row_variables['thumbnail_url'] = $data['images']['thumbnail']['url'];
	  $row_variables['thumbnail'] = "<img src='".$data['images']['thumbnail']['url']."' width='".$data['images']['thumbnail']['width']."' height='".$data['images']['thumbnail']['height']."'/>";

	  $row_variables['low_resolution_url'] = $data['images']['low_resolution']['url'];
	  $row_variables['low_resolution'] = "<img src='".$data['images']['low_resolution']['url']."' width='".$data['images']['low_resolution']['width']."' height='".$data['images']['low_resolution']['height']."'/>";

	  $row_variables['standard_resolution_url'] = $data['images']['standard_resolution']['url'];
	  $row_variables['standard_resolution'] = "<img src='".$data['images']['standard_resolution']['url']."' width='".$data['images']['standard_resolution']['width']."' height='".$data['images']['standard_resolution']['height']."'/>";

	  return $row_variables;

	}

	/**
	 * set comment returned from the api
	 * @param  array $data comment data from api
	 * @return array
	 */
	function _parse_comments($data)
	{
		
		$comments = array();
		
		foreach ($data as $row)
		{
			$comment = array();
			$comment['comment_text'] = $row['text']; 
			$comment['comment_username'] = $row['from']['username']; 
			$comment['comment_profile_picture'] = $row['from']['profile_picture']; 
			$comment['comment_full_name'] = $row['from']['full_name'];
			
			$comments[] = $comment; 
		}

		return $comments;

	}

	/**
	 * set likes data from the api
	 * @param  array $data likes data from the api
	 * @return array
	 */
	function _parse_likes($data)
	{

		$likes = array();
		
		foreach ($data as $row)
		{
			$like = array();
			$like['like_username'] = $row['username']; 
			$like['like_full_name'] = $row['full_name']; 
			$like['like_profile_picture'] = $row['profile_picture']; 
			$likes[] = $like; 
		}

		return $likes;

	}

	/**
	 * set tags data from the api
	 * @param  array $data tags data from the api
	 * @return array
	 */
	function _parse_tags($data)
	{

		$tags = array();
		
		foreach ($data as $tag_name)
		{
			$tags[] = array('tag' => $tag_name); 
		}

		return $tags;

	}

	/**
	 * Given the user's user name, it will return the user id
	 * @param  string $username user's instagram hand;e
	 * @return string/int
	 */
	function _get_user_id($username)
	{
		
		$user_data = $this->instagram->searchUser($username);
		$response = json_decode($user_data, true);

		$user_id = false;

		if(empty($response['data'])){
			return FALSE;
		}

		foreach($response['data'] as $data){
			if($data['username'] == $username){
				$user_id = $data['id'];
			}
		}

		return $user_id;

	}

	/**
	 * Returns a dynamic name of a cache file, based on parameters that are specified
	 * @param  array $cache_params a key/value array of parameters to build the cache file name
	 * @return string
	 */
	function _get_cache_file_name($cache_params){
		
		if(! is_array($cache_params) || count($cache_params) < 1)
		{
			return FALSE;
		}

		$filename = "tgl-instagram" . '-' . current_url();

		foreach($cache_params as $key => $value){
			
			$filename .= '-'.$key.$value;
				
		}

		return $filename;
		
	}

	/**
	 * Checks to see if a cache with the respective cache_params exists, and returns the data
	 * @param  array $cache_params key/value array of cache params
	 * @return string/bollean
	 */
	function _check_cache($cache_params)
	{	
		
		// Check for cache directory
		$dir = APPPATH.'cache/'.$this->cache_name.'/';
		
		if ( ! @is_dir($dir))
		{
			return FALSE;
		}
		
		// Check for cache file
    $file = $dir.md5($this->_get_cache_file_name($cache_params));
		
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
		
		if((time() > ($timestamp + ($cache_params['cache'] * 60))) || $cache_params['cache'] == 0)
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
	function _write_cache($data, $cache_params)
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
		
		$file = $dir.md5($this->_get_cache_file_name($cache_params));
	
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