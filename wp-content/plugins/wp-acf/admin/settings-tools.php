<?php 

class acf_settings_tools {
	
	var $view = 'settings-tools',
		$data = array();
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
	
		// actions
		add_action('admin_menu', array($this, 'admin_menu'));
		
	}
	
	
	/*
	*  admin_menu
	*
	*  This function will add the ACF menu item to the WP admin
	*
	*  @type	action (admin_menu)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_menu() {
		
		// bail early if no show_admin
		if( !acf_get_setting('show_admin') ) {
			
			return;
			
		}
		
		
		// add page
		$page = add_submenu_page('edit.php?post_type=acf-field-group', __('Tools','acf'), __('Tools','acf'), acf_get_setting('capability'),'acf-settings-tools', array($this,'html') );
		
		
		// actions
		add_action('load-' . $page, array($this,'load'));
		
	}
	
	
	/*
	*  load
	*
	*  This function will look at the $_POST data and run any functions if needed
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function load() {
		
		// all export pages should not load local fields
		acf_disable_local();
		
		
		// run import / export
		if( acf_verify_nonce('import') ) {
			
			$this->import();
		
		} elseif( acf_verify_nonce('export') ) {
			
			if( isset($_POST['generate']) ) {
				
				$this->generate();
			
			} else {
				
				$this->export();
			
			}
		
		}
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
	}
	
	
	/*
	*  html
	*
	*  This function will render the view
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function html() {
		
		// load view
		acf_get_view($this->view, $this->data);
		
	}
	
	
	/*
	*  getFields
	*
	*  Gets all acf-field-group posts from the database
	*
	*  @type	function
	*  @date	12/16/2015
	*
	*  @param	n/a
	*  @return	n/a
	*/
	function getFieldGroups() {
		$posts = get_posts(array(
			"posts_per_page" => -1,
			"post_type" => "acf-field-group",
			"post_status" => "publish"
		));
		
		$return = array();
		
		foreach ($posts as $post) {
			$return[$post->ID] = $post;
		}
		
		return $return;
	}
	
	/*
	*  getFields
	*
	*  Gets all acf-field posts from the database
	*
	*  @type	function
	*  @date	12/16/2015
	*
	*  @param	n/a
	*  @return	n/a
	*/
	function getFields() {
		$posts = get_posts(array(
			"posts_per_page" => -1,
			"post_type" => "acf-field",
			"post_status" => "publish"
		));
		
		$return = array();
		
		foreach ($posts as $post) {
			$return[$post->ID] = $post;
		}
		
		return $return;
	}
	
	
	/*
	*  export
	*
	*  This function will export field groups to a .json file
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function export() {
		
		// vars
		$json = $this->get_json();
		
		
		// validate
		if( $json === false ) {
			
			acf_add_admin_notice( __("No field groups selected", 'acf') , 'error');
			return;
		
		}
		
		
		// set headers
		$file_name = 'acf-export-' . date('Y-m-d') . '.json';
		
		header( "Content-Description: File Transfer" );
		header( "Content-Disposition: attachment; filename={$file_name}" );
		header( "Content-Type: application/json; charset=utf-8" );
		
		echo acf_json_encode( $json );
		die;
		
	}
	
	
	/*
	*  import
	*
	*  This function will import a .json file of field groups
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function import() {
		
		// validate
		if( empty($_FILES['acf_import_file']) ) {
			
			acf_add_admin_notice( __("No file selected", 'acf') , 'error');
			return;
		
		}
		
		
		// vars
		$file = $_FILES['acf_import_file'];
		
		
		// validate error
		if( $file['error'] ) {
			
			acf_add_admin_notice(__('Error uploading file. Please try again', 'acf'), 'error');
			return;
		
		}
		
		
		// validate type
		if( pathinfo($file['name'], PATHINFO_EXTENSION) !== 'json' ) {
		
			acf_add_admin_notice(__('Incorrect file type', 'acf'), 'error');
			return;
			
		}
		
		
		// read file
		$json = file_get_contents( $file['tmp_name'] );
		
		
		// decode json
		$json = json_decode($json, true);
		
		
		// validate json
    	if( empty($json) ) {
    	
    		acf_add_admin_notice(__('Import file empty', 'acf'), 'error');
	    	return;
    	
    	}
    	
    	
    	// if importing an auto-json, wrap field group in array
    	if( isset($json['key']) ) {
	    	
	    	$json = array( $json );
	    	
    	}
    	
    	
    	// vars
    	$added = array();
    	$deletedgroups = array();
    	$deletedfields = array();
    	$ref = array();
    	$order = array();
			
    	$allgroups = $this->getFieldGroups();
    	$allfields = $this->getFields();
    	
    	foreach( $json as $field_group ) {
				
				$upate = false;
    		
	    	// check if field group exists
	    	if( $post = acf_get_field_group($field_group['key'], true) ) {
					
					// add ID to trigger update instead of insert
					$field_group["ID"] = $post["ID"];
					
					$update = true;
					
	    	}
	    	
	    	
	    	// remove fields
			$fields = acf_extract_var($field_group, 'fields');
			
			
			// format fields
			$fields = acf_prepare_fields_for_import( $fields );
			
			
			// save field group
			$field_group = acf_update_field_group( $field_group );
			
			
			// remove group from $allgroups array
			if (isset($allgroups[$field_group['ID']])) {
				unset($allgroups[$field_group['ID']]);
			}
			
			
			// add to ref
			$ref[ $field_group['key'] ] = $field_group['ID'];
			
			
			// add to order
			$order[ $field_group['ID'] ] = 0;
			
			
			// add fields
			foreach( $fields as $field ) {
				
				// add parent
				if( empty($field['parent']) ) {
					
					$field['parent'] = $field_group['ID'];
					
				} elseif( isset($ref[ $field['parent'] ]) ) {
					
					$field['parent'] = $ref[ $field['parent'] ];
						
				}
				
				
				// add field menu_order
				if( !isset($order[ $field['parent'] ]) ) {
					
					$order[ $field['parent'] ] = 0;
					
				}
				
				$field['menu_order'] = $order[ $field['parent'] ];
				$order[ $field['parent'] ]++;
				
				
				// add ID if the field already exists
				if($post = acf_get_field($field['key'], true) ) {
					
					// add ID to trigger update instead of insert
					$field["ID"] = $post["ID"];
					
				}
				
				
				// save field
				$field = acf_update_field( $field );
				
				
				// remove group from allgroups array
				if (isset($allfields[$field['ID']])) {
					unset($allfields[$field['ID']]);
				}
				
				
				// add to ref
				$ref[ $field['key'] ] = $field['ID'];
				
			}
			
			if ($update) {
				// append to updated
				$updated[] = '<a href="' . admin_url("post.php?post={$field_group['ID']}&action=edit") . '" target="_blank">' . $field_group['title'] . '</a>';
			} else {
				// append to added
				$added[] = '<a href="' . admin_url("post.php?post={$field_group['ID']}&action=edit") . '" target="_blank">' . $field_group['title'] . '</a>';
			}
						
    	}
			
			if (!empty($allgroups)) {
				foreach ($allgroups as $post) {
					$deletedgroups[] = $post->post_title;
					wp_delete_post($post->ID);
				}
			}

			if (!empty($allfields)) {
				foreach ($allfields as $post) {
					$deletedfields[] = $post->post_title;
					wp_delete_post($post->ID);
				}
			}
    	
    	
    	// messages
    	if( !empty($added) ) {
    		
    		$message = __('<b>Success</b>. Import tool added %s field groups: %s', 'acf');
    		$message = sprintf( $message, count($added), implode(', ', $added) );
    		
	    	acf_add_admin_notice( $message );
    	
    	}
    	
			if( !empty($updated) ) {

    		$message = __('<b>Success</b>. Import tool updated %s field group(s): %s', 'acf');
    		$message = sprintf( $message, count($updated), implode(', ', $updated) );

	    	acf_add_admin_notice( $message );

    	}
    	
		
	}
	
	
	/*
	*  generate
	*
	*  This function will generate PHP code to include in your theme
	*
	*  @type	function
	*  @date	11/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function generate() {
		
		// vars
		$json = $this->get_json();
		
		
		// validate
		if( $json === false ) {
			
			acf_add_admin_notice( __("No field groups selected", 'acf') , 'error');
			return;
		
		}
		
				
		// update view
		$this->view = 'settings-tools-export';
		$this->data['field_groups'] = $json;
		
	}
	
	
	/*
	*  get_json
	*
	*  This function will return the JSON data for given $_POST args
	*
	*  @type	function
	*  @date	3/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function get_json() {
		
		// validate
		if( empty($_POST['acf_export_keys']) ) {
			
			return false;
				
		}
		
		
		// vars
		$json = array();
		
		
		// construct JSON
		foreach( $_POST['acf_export_keys'] as $key ) {
			
			// load field group
			$field_group = acf_get_field_group( $key );
			
			
			// validate field group
			if( empty($field_group) ) {
				
				continue;
			
			}
			
			
			// load fields
			$field_group['fields'] = acf_get_fields( $field_group );
	
	
			// prepare fields
			$field_group['fields'] = acf_prepare_fields_for_export( $field_group['fields'] );
			
			
			// extract field group ID
			$id = acf_extract_var( $field_group, 'ID' );
			
			
			// add to json array
			$json[] = $field_group;
			
		}
		
		
		// return
		return $json;
		
	}
	
}


// initialize
new acf_settings_tools();

?>
