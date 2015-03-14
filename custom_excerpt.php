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

add_filter('the_excerpt', 'custom_excerpt');

function custom_excerpt($excerpt) {
	
	$words = FALSE;
	
	$char = FALSE;
	
	$options = get_option('custom_excerpt_options');
	
	if(isset($options['length_insert'])){
		if(empty($options['length_insert']) || $options['length_insert'] > 365){
			$length = 365;
		}else{
			$length = $options['length_insert'];
		}
	}
	
	
	if(isset($options['length_of_field'])){
		if($options['length_of_field'] == 'words'){
			$words = TRUE;
		}
		
		if($options['length_of_field'] == 'chars'){
			$char = TRUE;
		}		
	}
	
	if(isset($options['read_more_field'])){
		if($options['read_more_field']){
			$readmore = $options['read_more_field'];
		}else{
			$readmore = '[...]';
		}
		
	}
	
	//if words selected, call the function to count the words.
	if($words){
		
		return word_count($excerpt, $length, $readmore);		
	}
	
	//if characters selected, call the function to count the characters.
	if($char){
		
		return char_count($excerpt, $length, $readmore);		
	}
	
}

/*Function to count the characters. 
 * accepts 2 parameters the excerpt and the length selected.
 * replace the special characters from excerpt, 
 * strip tags from the excerpt,
 * cut the excerpt using the passed length,
 * cut the excerpt on the last space so not cut words in the middle.
 * Return the new excerpt between paragraph tags.
 */
function char_count($excerpt, $length, $readmore){
		$newexcerpt = preg_replace(" (\[.*?\])", '', $excerpt);//replace the spacial characters form [&hellip;] in the end.
		
		$newexcerpt = strip_tags($newexcerpt);//Strip paragraph and other possible tags from the excerpt.  
				
		$newexcerpt = substr($newexcerpt, 0, $length+1);//Cut the excerpt using the passed length.
		
		$newexcerpt = substr($newexcerpt, 0, strripos($newexcerpt, " "));//Cut the excerpt on the last space so not cut words in the middle.
		
		//Set the permalink if checked and return the new excerpt with the permalink.
		//Else return the without the permalink.		
		$options = get_option('custom_excerpt_options');
		if(isset($options['permalink_field']) && $options['permalink_field'] == 'yes'){
			$permalink = get_permalink($post->ID);
			$permalink = "<a href='$permalink'>";
				return '<p>' . $newexcerpt . ' ' . $permalink . $readmore . '</a> </p>';
		}else{
			return '<p>' . $newexcerpt . ' ' . $readmore . '</p>';//Return the new excerpt.
		}	 
}

/*Function to count the words. 
 * accepts 2 parameters the excerpt and the length selected.
 */
function word_count($excerpt, $length, $readmore){
	
	$newexcerpt = strip_tags($excerpt);
	
	$newexcerpt = explode(' ', $newexcerpt, $length+1);
	
	array_pop($newexcerpt);
	
	$newexcerpt = implode(' ', $newexcerpt);
	
	//Set the permalink if checked and return the new excerpt with the permalink.
	//Else return the without the permalink.
	$options = get_option('custom_excerpt_options');
		if(isset($options['permalink_field']) && $options['permalink_field'] == 'yes'){
			$permalink = get_permalink($post->ID);
			$permalink = "<a href='$permalink'>";
				return '<p>' . $newexcerpt . ' ' . $permalink . $readmore . '</a> </p>';
		}else{
			return '<p>' . $newexcerpt . ' ' . $readmore . '</p>';//Return the new excerpt.
		}	
	
	
	
	
} 



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
		'Excerpt Length:', //Title - Label of the field 
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
	
	add_settings_field(
		'read_more_field', //id of the field
		'Read more text:', //Title - Label of the field 
		'create_read_more_field', //callback function to output the field.
		'customexcerpt', //slug of the menu page
		'general_options' //Section that the field should be in. 
	);
	
	add_settings_field(
		'permalink_field', //id of the field
		'Enable permalink?', //Title - Label of the field 
		'create_permalink_checkbox', //callback function to output the field.
		'customexcerpt', //slug of the menu page
		'general_options' //Section that the field should be in. 
	);
}

/*Output Length Insert Field
---------------------------------------------------------------*/
function create_length_insert_field() {
	
	$options = get_option('custom_excerpt_options');
		if(isset($options['length_insert'])){
			$value = $options['length_insert'];
		}else {
			$value = '';
		}
				
		echo "<input type='text' id='length_insert' name='custom_excerpt_options[length_insert]' value='$value' />
			  <br/><i>Defaults and max length 55(Words) and 365(Characters).</i>";
}

/*Output Length of: Field
---------------------------------------------------------------*/
function create_length_of_field() {
	
	$options = get_option('custom_excerpt_options');
	if(!isset($options['length_of_field'])){
	  	$value = '';
		$words_selected = 'selected';
		$char_selected = '';
	}else{
		$value = esc_attr($options['length_of_field']);
		$words_selected = ($value == 'words') ? 'selected' : '';
		$char_selected = ($value == 'chars') ? 'selected' : '';
	}
				
		echo "<select id='length_of_field' name='custom_excerpt_options[length_of_field]'>
			  	<option value='chars' $char_selected>Characters</option>
			  	<option value='words' $words_selected >Words</option>
		      </select>";
}

/*Output Read More Field
---------------------------------------------------------------*/
function create_read_more_field() {
	
	$options = get_option('custom_excerpt_options');
	
		if(isset($options['read_more_field']) && !empty($options['read_more_field'])){
			$value = $options['read_more_field'];
		}else {
			$value = '[...]';
		}
				
		echo "<input type='text' id='read_more_field' name='custom_excerpt_options[read_more_field]' value='$value' />
			  <br/><i>Defaults to [...].</i>";
}

/*Output Read More Field
---------------------------------------------------------------*/
function create_permalink_checkbox() {
	$options = get_option('custom_excerpt_options');
	if(isset($options['permalink_field'])){
		$checked = 'checked';
	}else{
		$checked = '';
	}	
	
	echo "<input type='checkbox' id='permalink_field' name=custom_excerpt_options[permalink_field]' value='yes' $checked />
			<i>Add a link to the post in your readmore text.</i>";
}