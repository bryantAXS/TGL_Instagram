<?php if( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'tgl_instagram/classes/Instagram.php';

class Tgl_instagram_mcp
{
	private $data = array();
	
	public function __construct()
	{
		
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
		$this->base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=tgl_instagram';
		    
		// Load table lib for control panel
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		
		// Module specific styles
		$this->EE->cp->load_package_css('tgl_instagram');
		
		// Set page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('tgl_instagram_module_name'));
		
	}

	/**
	 * Module CP index function
	 *
	 * @return view code
	 * 
	 */
	public function index()
	{
		$this->EE->load->model('tgl_instagram_model');
		
		$this->data['form_action'] = AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=tgl_instagram'.AMP.'method=submit_settings';
		$this->data['settings'] = $this->EE->tgl_instagram_model->get_settings();
		
		if(isset($this->data['settings']['client_id'], $this->data['settings']['client_secret']) && ! isset($this->data['settings']['access_token'])){
			
			$config = array(
      	'client_id' => $this->data['settings']['client_id'],
      	'client_secret' => $this->data['settings']['client_secret'],
      	'grant_type' => 'authorization_code',
      	'redirect_uri' => urlencode($this->EE->config->item('cp_url').'?D=cp&C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=tgl_instagram'.AMP.'callback=true')
     	);

			$instagram = new Instagram($config);

			if($access_token = $this->EE->input->get('code')){
				
				$access_token = $instagram->getAccessToken();
				
				if(! empty($access_token)){
					$this->EE->tgl_instagram_model->insert_access_token($access_token);
					$this->data['settings']['access_token'] = $access_token;
				}else{
					//echo 'error';
				}

			}else{
				$this->data['authorized_url'] = $instagram->getAuthorizationUrl();		
			}
		
		}

		return $this->EE->load->view('index', $this->data, TRUE);

	}

	function _dump($data){
		echo "<pre>";
		echo print_r($data);
		echo "</pre>";
	}
	
	/**
	 * Called after new settings have been submitted
	 *
	 * @return void
	 * 
	 */
	public function submit_settings()
	{
		
		$this->EE->load->model('tgl_instagram_model');
		
		//loops through the post and adds all settings (deletes old settings first)
		$success = $this->EE->tgl_instagram_model->insert_new_settings();
		
		$settings = $this->EE->tgl_instagram_model->get_settings();
		
		if($success && isset($settings['pin']) && ! isset($settings['access_token'], $settings['access_token_secret'])){
			
			//if a pin has been submitted, we want to generate the access tokens for the app
			if($this->generate_access_tokens($settings)){
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('Success! You are now Authenticated.'));
			}else{
				
				//if the pin was not able to be created, delete the submitted pin and send the user back to the authenticate page.
				/*
					TODO : we could use some better UX here.  Ideally sending the user back to this page happens after they create a request token, 
								 and sending them back because of an invaid acess token authentication is somewhat confusing
				*/
				$this->EE->tgl_twitter_model->delete_setting('pin');
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('Error authenticating with Twitter. Please verify Pin and re-submit'));
				$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=tgl_twitter'.AMP.'method=register_with_twitter');
			}
			
		}else{
			
			//else : sumission before pin as been submitted, or after all settings have been submitted
			
			if(! $success){
			  $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('Error saving settings.'));
			}else{
			  $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('Success!'));
			}
			
		}
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=tgl_instagram');

	}
	
	/**
	 * function that kills all settings in the DB and starts us over at square one.
	 *
	 * @return void
	 * @author Bryant Hughes
	 */
	public function erase_settings()
	{
		$this->EE->load->model('tgl_instagram_model');
		$this->EE->tgl_instagram_model->delete_all_settings();
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('Authentication Settings Erased.'));
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=tgl_instagram');
	}
		
}

/* End of File: mcp.module.php */