<?php
class Tgl_instagram_upd
{
	public $version = '0.1';
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
	}
	
	public function install()
	{
		$this->EE->db->insert('modules', array(
			'module_name' => 'Tgl_instagram',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		));

		$data = array(
		  'class'     => 'Tgl_instagram',
		  'method'    => 'callback'
		);

		$this->EE->db->insert('actions', $data);
		
		$this->EE->load->dbforge();
		
		//create tgl_instagram module settings table
		$fields = array(
			'id'		=>	array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'auto_increment' => TRUE),
			'site_id'	=>	array('type' => 'int', 'constraint' => '8', 'unsigned' => TRUE, 'null' => FALSE, 'default' => '1'),
			'var'		=>	array('type' => 'varchar', 'constraint' => '60', 'null' => FALSE),
			'var_value'	=>	array('type' => 'varchar', 'constraint' => '100', 'null' => FALSE)
		);
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('tgl_instagram_settings');
		
		// get the module id
		$results = $this->EE->db->query("SELECT * FROM exp_modules WHERE module_name = 'TGL Instagram'");
		$module_id = $results->row('module_id');
			
		$sql = array();
		$sql[] = 
					"INSERT IGNORE INTO exp_tgl_instagram_settings ".
					"(id, site_id, var, var_value) VALUES ".
					"('', '0', 'module_id', " . $module_id . ")";
					
		return TRUE;
	}
	
	public function update( $current = '' )
	{
		if($current == $this->version) { return FALSE; }
		return TRUE;
	}
	
	public function uninstall()
	{
		
		//$this->EE->db->where('Tgl_instagram', 'callback');
    //$this->EE->db->delete('actions');

    $this->EE->db->query("DELETE FROM exp_modules WHERE module_name = 'Tgl_Instagram'");
    
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('tgl_instagram_settings');

		return TRUE;
	}
}

/* End of File: upd.module.php */