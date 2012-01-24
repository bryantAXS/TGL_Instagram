<?php

	/*
  This view displays all of the module wide settings that can be 
  edited by the admin.
	*/

	echo form_open($form_action, '', '');

	$this->table->set_template($cp_table_template);
	$this->table->set_heading('TGL Instagram Settings', '');
			
	//client keys
	$client_id = isset($settings['client_id']) ? $settings['client_id'] : '';
	$client_secret = isset($settings['client_secret']) ? $settings['client_secret'] : '';
	$access_token = isset($settings['access_token']) ? $settings['access_token'] : FALSE;
	
	$client_key_input_data = array('name' => 'client_id','value' => $client_id ,'maxlength' => '100' ,'style' => 'width:50%');
	$client_secret_input_data = array('name' => 'client_secret','value' => $client_secret ,'maxlength' => '100' ,'style' => 'width:50%');
	
	$this->table->add_row('<strong>Client ID</strong>', form_input($client_key_input_data));
	$this->table->add_row('<strong>Client Secret</strong>', form_input($client_secret_input_data));

	if($access_token != FALSE)
	{
		$access_token_input_data = array('name' => 'access_token','value' => $access_token ,'maxlength' => '100' ,'style' => 'width:50%');
		$this->table->add_row('<strong>Access Token</strong>', form_input($access_token_input_data));	
	}
		
	if(isset($settings['client_id'], $settings['client_secret'], $settings['access_token']))
	{
		
		//successfully authenticated
		echo '<h3>You have successfully authenticated.</h3>';
		$this->table->add_row("","<p><a class='link-button' href='".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=tgl_instagram'.AMP."method=erase_settings'>Erase Authentication Settings</a></p>");
		echo $this->table->generate();
		
	}else if(isset($settings['client_id'], $settings['client_secret'], $authorized_url) && ! isset($settings['access_token'])){
		
		//step 2
		echo "<div id='steps-container'>";
		echo "<h3>Step 2</h3>";
		echo "<ul>";
		echo "<li>Click <small>Generate Access Token</small></li>";
		echo "</ul>";
		echo "</div>";

		$this->table->add_row("","<p><a class='link-button' href='".$authorized_url."'>Generate Access Token</a></p>");
		$this->table->add_row("","<p><a class='link-button' href='".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=tgl_instagram'.AMP."method=erase_settings'>Erase Authentication Settings</a></p>");
		echo $this->table->generate();

	}else{
		
		//step 1
		echo "<div id='steps-container'>";
		echo "<h3>Step 1</h3>";
		echo "<ul>";
		echo "<li>Visit <a target='_blank' href='http://instagram.com/developer/manage/'>http://instagram.com/developer/manage/</a> to register your website/application with Instagram's API</li>";
		echo '<li>Login or Register a new account.</li>';
		echo '<li>Click "Register a New Client"</li>';
		echo "<li>Fillout Registration Form.  When entering a URL for the field <small>OAuth redirect_uri</small>, the value should be a url to your site. ie: http://mysite.com  </li>";
		echo "<li>After registering, copy the Client ID and Client Secret values into the two fields below. </li>";
		echo "</ul>";
		echo "</div>";

		echo $this->table->generate();
		echo form_submit(array('name' => 'Submit', 'id' => 'submit', 'value' => 'Update', 'class' => 'submit'));
	
	}
	
	

	
	
	
	
	
	
	