<?php
/*
* Plugin Name: Custom Excerpt
* Description: DIT 21063 1401 Assignment - Enables the user specify length of excerpt in words or characters. Allow user set a custom read more.   
* Version: 1.0 
* Author: Mario Russo
* Author URI: http://www.russomario.com
*/

/*Actions
----------------------------------------------------------*/
add_action('admin_menu', 'register_custom_excerpt_options');

add_action('admin_menu', 'create_custom_excerpt_admin_page');

add_action('admin_menu', 'add_custom_excerpt_sections');

add_action('admin_menu', 'add_custom_excerpt_fields');




function custom_excerpt($excerpt) {
	
}
add_filter('the_excerpt', 'custom_excerpt');

/* Register Plugin Settings
----------------------------------------------------------*/ 
function register_custom_excerpt_options() {
	register_setting(
		'custom_excerpt_admin', //Option Group
		'custom_excerpt_options', //Name of the field in db
		'sanatize_custom_excerpt' //Callback function to sanatize the input
	);
}

/* Callback function to sanatize input
-------------------------------------------------------------*/
function sanatize_custom_excerpt($input) {
	return $input;
}

/*Add Admin options page
--------------------------------------------------------------*/
function create_custom_excerpt_admin_page() {
	add_menu_page('Custom Excerpt Options', //Page Title
				  'Custom Excerpt Options', //Menu Title
				  'manage_options', //Capabilities required 
			      'customexcerpt', //slug/page 
				  'custom_excerpt_options', //callback to output menu
				  '', // Icon URL set an Icon for the plugin menu 
				  '22' //Position of the menu 
				  );
}


/*Add basic content to menu page 
--------------------------------------------------------------*/
function custom_excerpt_options() {
	echo "<div class='wrap'>";
	echo "<h2>Custom Excerpt Settings</h2>";
	
	echo'<form method="post" action="options.php">';
	
	settings_fields('custom_excerpt_admin'); //option group
	do_settings_sections('customexcerpt'); //page slug
	
	submit_button();
	
	echo '</form><!--End of form-->';
	echo "</div><!--End of wrap div-->";
	
}

/*Add Custom Excerpt Sections
---------------------------------------------------------------*/
function add_custom_excerpt_sections() {
	add_settings_section(
		'general_options', //Id of the section - UNIQUE in the plugin.
		'General Options', // Section Title - Appear in the page.
		'', //callback function - output Instructions to the user. 
		'customexcerpt' //slug of menu page that should appear. 
	);
}

/*ADD Custom Excerpt Fields
---------------------------------------------------------------*/
function add_custom_excerpt_fields() {
		
	add_settings_field(
		'length_insert', //id of the field
		'Excerpt Length', //Title - Label of the field 
		'create_length_insert_field', //callback function to output the field.
		'customexcerpt', //slug of the menu page
		'general_options' //Section that the field should be in. 
	);
	
	add_settings_field(
		'length_of_field', //id of the field
		'Length of:', //Title - Label of the field 
		'create_length_of_field', //callback function to output the field.
		'customexcerpt', //slug of the menu page
		'general_options' //Section that the field should be in. 
	);
}

/*Output Length Insert Field
---------------------------------------------------------------*/
function create_length_insert_field() {
				
		echo "<input type='text' id='length_insert' name='custom_excerpt_options[length_insert]' />";
}

/*Output Length of: Field
---------------------------------------------------------------*/
function create_length_of_field() {
				
		echo "<select id='length_of_field' name='custom_excerpt_options[length_of_field]'>
			  	<option value='characters' >Characters</option>
			  	<option value='words'>Words</option>
		      </select>";
}