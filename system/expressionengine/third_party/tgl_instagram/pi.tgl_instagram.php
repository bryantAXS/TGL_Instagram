<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'tgl_instagram/Instagram.php';

/**
 * TGL Instagram Plugin
 * Copyright The Good Lab, 2012
 */

$plugin_info = array(
	'pi_name'			=> 'TGL Instagram',
	'pi_version'		=> '0.1',
	'pi_author'			=> 'Bryant Hughes',
	'pi_author_url'		=> 'http://TheGoodLab.com',
	'pi_description'	=> 'Plugin to interact with the Instagram API',
	'pi_usage'			=> Tgl_instagram::usage()
);

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

		$config = array(
        'client_id' => 'f3592a49bf254775a2cf9961ff3cdf91',
        'client_secret' => 'd4b5367cf30a4e8a9ebc63fcd5d1fb07',
        'grant_type' => 'authorization_code',
        'redirect_uri' => 'http://fultonfithouse.local',
     );

		$this->instagram = new Instagram($config);
		
		if(isset($_SESSION['InstagramAccessToken'])){
			
			$accessToken = $_SESSION['InstagramAccessToken'];
				
		}else{
			if($accessToken = $this->instagram->getAccessToken()){
				$_SESSION['InstagramAccessToken'] = $accessToken;	
			}else{
				$this->instagram->openAuthorizationUrl();		
			}
		}

		$accessToken = "4933.f3592a4.6ed55940e59c483d83633862e5092bcc";

		$this->instagram->setAccessToken($accessToken);

		//Check to see if it's a one-off tag or a pair
		$mode = ($tagdata) ? "pair" : "single";
		
		$plugin_vars = array(
			"title"         =>  "video_title",
			"html"          =>  "embed_code",
			"author_name"   =>  "video_author",
			"author_url"    =>  "video_author_url",
			"thumbnail_url" =>  "video_thumbnail"
		);
		
		foreach ($plugin_vars as $var) {
			$video_data[$var] = false;
		}

		//Deal with the parameters
		// $video_url = ($this->EE->TMPL->fetch_param('url')) ?  html_entity_decode($this->EE->TMPL->fetch_param('url')) : false;
		// $max_width = ($this->EE->TMPL->fetch_param('max_width')) ? "&maxwidth=" . $this->EE->TMPL->fetch_param('max_width') : "";
		// $max_height = ($this->EE->TMPL->fetch_param('max_height')) ? "&maxheight=" . $this->EE->TMPL->fetch_param('max_height') : "";

		// cache can be disabled by setting 0 as the cache_minutes param
		// if ($this->EE->TMPL->fetch_param('cache_minutes') !== FALSE && is_numeric($this->EE->TMPL->fetch_param('cache_minutes'))) {
		// 	$this->refresh_cache = $this->EE->TMPL->fetch_param('cache_minutes');
		// }
		
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