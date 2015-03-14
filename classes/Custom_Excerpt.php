<?php

class Custom_Excerpt {
	public function __construct() {
		
		/*Actions
----------------------------------------------------------*/
		add_action('admin_menu', [$this,  'register_custom_excerpt_options'] );
		
		add_action('admin_menu', [$this, 'create_custom_excerpt_admin_page'] );
		
		add_action('admin_menu', [$this, 'add_custom_excerpt_sections'] );
		
		add_action('admin_menu', [$this, 'add_custom_excerpt_fields'] );
		
		add_filter('the_excerpt', [$this, 'custom_excerpt']);
	}
		
	/*Function to create the custom Excerpt.
	 * accepts one parameter the excerpt from worpress.
	 * get the length that user set on admin from DB.
	 * check if the user select words or characters count. 
	 * check the read more set by the user. 
	 * call the determined function needed which return the output.  
	 */
	public function custom_excerpt($excerpt) {
		
		$words = FALSE; //Define words variable as false.
		
		$char = FALSE; //Define char variable as false.
		
		$options = get_option('custom_excerpt_options');// get options from DB.
		
		//If length is set.
		if(isset($options['length_insert'])){
			
			//If is empty or bigger than 365 set the length to 365.
			//ELSE length is equal what user set in admin.
			if(empty($options['length_insert']) || $options['length_insert'] > 365){
			
				$length = 365;
			
			}else{
		
				$length = $options['length_insert'];
			}
		}
		
		//If the length of field is set.
		if(isset($options['length_of_field'])){
				
			//If length of field is equal to words set words variable to true.
			if($options['length_of_field'] == 'words'){
				$words = TRUE;
			}
			
			//If length of field is equal to chars set char variable to true.
			if($options['length_of_field'] == 'chars'){
				$char = TRUE;
			}		
		}
		
		//IF read more field is set.
		if(isset($options['read_more_field'])){
			
			//IF read more field is not empty, set readmore variable the have the field value.
			//ELSE set the default value. 
			if(!empty($options['read_more_field'])){
				
				$readmore = $options['read_more_field'];
				
			}else{
				
				$readmore = '[...]';
			}
			
		}
		
		//if words selected, call the function to count the words.
		if($words){
			
			return $this->word_count($excerpt, $length, $readmore);		
		}
		
		//if characters selected, call the function to count the characters.
		if($char){
			
			return $this->char_count($excerpt, $length, $readmore);		
		}
		
	}
	
	/*Function to count the characters. 
	 * accepts 3 parameters the excerpt, length and readmore selected.
	 * replace the special characters from excerpt, 
	 * strip tags from the excerpt,
	 * cut the excerpt using the passed length,
	 * cut the excerpt on the last space so not cut words in the middle.
	 * * Check IF Permalink is set, define the permalink and return the new excerpt with the permalink.
	 * ELSE returns the new excerpt without the permalink between paragraph tags.  
	 */
	public function char_count($excerpt, $length, $readmore){
			$newexcerpt = preg_replace(" (\[.*?\])", '', $excerpt);//replace the spacial characters form [&hellip;] in the end.
			
			$newexcerpt = strip_tags($newexcerpt);//Strip paragraph and other possible tags from the excerpt.  
					
			$newexcerpt = substr($newexcerpt, 0, $length+1);//Cut the excerpt using the passed length.
			
			$newexcerpt = substr($newexcerpt, 0, strripos($newexcerpt, " "));//Cut the excerpt on the last space so not cut words in the middle.
			
			//Check the permalink if checked and return the new excerpt with the permalink.
			//Else return the without the permalink.		
			$options = get_option('custom_excerpt_options'); //Check the DB
		
			if(isset($options['permalink_field']) && $options['permalink_field'] == 'yes'){
				
				$permalink = get_permalink($post->ID); // Assign the permalink variable to the get permalink function with the $post->ID
				
				$permalink = "<a href='$permalink'>"; // Assign the permalink variable to the a tag with the premlink.
				
				return '<p>' . $newexcerpt . ' ' . $permalink . $readmore . '</a> </p>'; // Return the new excerpt with the permalink and a tag.
					
			}else{
				
				return '<p>' . $newexcerpt . ' ' . $readmore . '</p>';//Return the new excerpt without permalink.
			}	 
	}
	
	/*Function to count the words. 
	 * accepts 3 parameters the excerpt, length and readmore selected.
	 * strip tags from the excerpt passed.
	 * explode the excerpt into an Array
	 * Pop the last Array item. 
	 * Implode the Array into a string
	 * Check IF Permalink is set, define the permalink and return the new excerpt with the permalink.
	 * ELSE returns the new excerpt without the permalink  
	 */
	public function word_count($excerpt, $length, $readmore){
		
		$newexcerpt = strip_tags($excerpt); // Strip Tags from excerpt.
		
		$newexcerpt = explode(' ', $newexcerpt, $length+1); // Explode the excerpt into an Array with the set length.
		
		array_pop($newexcerpt); // Pop the last occurrence of the array because is a big string with more than one word.
		
		$newexcerpt = implode(' ', $newexcerpt); // Implode the Array into a string using spaces between words. 
		
		//Set the permalink if checked and return the new excerpt with the permalink.
		//Else return the without the permalink.
		$options = get_option('custom_excerpt_options'); //Check the DB
		
			if(isset($options['permalink_field']) && $options['permalink_field'] == 'yes'){
				
				$permalink = get_permalink($post->ID); // Assign the permalink variable to the get permalink function with the $post->ID
				
				$permalink = "<a href='$permalink'>"; // Assign the permalink variable to the a tag with the premlink.
				
				return '<p>' . $newexcerpt . ' ' . $permalink . $readmore . '</a> </p>'; // Return the new excerpt with the permalink and a tag.
					
			}else{
				
				return '<p>' . $newexcerpt . ' ' . $readmore . '</p>';//Return the new excerpt without permalink.
			}	
		
	} 
	
	/* Register Plugin Settings
	----------------------------------------------------------*/ 
	public function register_custom_excerpt_options() {
		register_setting(
			'custom_excerpt_admin', //Option Group
			'custom_excerpt_options', //Name of the field in db
			[$this, 'sanatize_custom_excerpt'] //Callback function to sanatize the input
		);
	}
	
	/* Callback function to sanatize input
	 * clean the input array.
	 * return the clean array. 
	-------------------------------------------------------------*/
	public function sanatize_custom_excerpt($input) {
		
		$clean['length_insert'] = intval($input['length_insert']); //clean the length insert input using intval. 
		
		$clean['read_more_field'] = strip_tags($input['read_more_field']); //strip tags from read more input.
		$clean['read_more_field'] = esc_attr($clean['read_more_field'] ); //escape HTML attributes using esc_attr from worpress.
		
		$clean['length_of_field'] = $input['length_of_field']; //assing the length of input to clean to return. 
		
		$clean['permalink_field'] = $input['permalink_field']; //assing the permalink of input to clean to return.
		
		return $clean; //return clean array. 
	}
	
	/*Add Admin options page
	--------------------------------------------------------------*/
	public function create_custom_excerpt_admin_page() {
		add_menu_page('Custom Excerpt Options', //Page Title
					  'Custom Excerpt Options', //Menu Title
					  'manage_options', //Capabilities required 
				      'customexcerpt', //slug/page 
					  [$this, 'custom_excerpt_options'], //callback to output menu
					  '', // Icon URL set an Icon for the plugin menu 
					  '22' //Position of the menu 
					  );
	}
	
	
	/*Add basic content to menu page 
	--------------------------------------------------------------*/
	public function custom_excerpt_options() {
		echo "<div class='wrap'>";
		echo "<h2>Custom Excerpt Settings</h2>";
		
		echo'<form method="post" action="options.php">';
		
		settings_fields('custom_excerpt_admin'); //option group
		do_settings_sections('customexcerpt'); //page slug
		
		submit_button();// Add the save changes button wordpress function. 
		
		echo '</form><!--End of form-->';
		echo "</div><!--End of wrap div-->";
		
	}
	
	/*Add Custom Excerpt Sections
	---------------------------------------------------------------*/
	public function add_custom_excerpt_sections() {
		add_settings_section(
			'general_options', //Id of the section - UNIQUE in the plugin.
			'General Options', // Section Title - Appear in the page.
			'', //callback function - output Instructions to the user. 
			'customexcerpt' //slug of menu page that should appear. 
		);
	}
	
	/*ADD Custom Excerpt Fields
	---------------------------------------------------------------*/
	public function add_custom_excerpt_fields() {
			
		add_settings_field(
			'length_insert', //id of the field
			'Excerpt Length:', //Title - Label of the field 
			[$this, 'create_length_insert_field'], //callback function to output the field.
			'customexcerpt', //slug of the menu page
			'general_options' //Section that the field should be in. 
		);
		
		add_settings_field(
			'length_of_field', //id of the field
			'Length of:', //Title - Label of the field 
			[$this, 'create_length_of_field'], //callback function to output the field.
			'customexcerpt', //slug of the menu page
			'general_options' //Section that the field should be in. 
		);
		
		add_settings_field(
			'read_more_field', //id of the field
			'Read more text:', //Title - Label of the field 
			[$this, 'create_read_more_field'], //callback function to output the field.
			'customexcerpt', //slug of the menu page
			'general_options' //Section that the field should be in. 
		);
		
		add_settings_field(
			'permalink_field', //id of the field
			'Enable permalink?', //Title - Label of the field 
			[$this, 'create_permalink_checkbox'], //callback function to output the field.
			'customexcerpt', //slug of the menu page
			'general_options' //Section that the field should be in. 
		);
	}
	
	/*Output Length Insert Field
	 * Get options from DB 
	 * check is is set length insert field 
	 * echo the input.
	---------------------------------------------------------------*/
	public function create_length_insert_field() {
		
		$options = get_option('custom_excerpt_options'); // Get options from DB field.
		
			//check is set length insert field.
			if(isset($options['length_insert'])){
				
				//IF is set assign it to value variable.
				//ELSE value is empty string.
				$value = $options['length_insert'];
			}else {
				$value = '';
			}
			
			//Echo the input to the admin page. 		
			echo "<input type='text' id='length_insert' name='custom_excerpt_options[length_insert]' value='$value' />
				  <br/><i>Defaults and max length 55(Words) and 365(Characters).</i>";
	}
	
	/*Output Length of: Field
	 * Get options from DB 
	 * check is is set length of field 
	 * echo the select box.
	---------------------------------------------------------------*/
	public function create_length_of_field() {
		
		$options = get_option('custom_excerpt_options'); // Get options from DB field.
		
		//check IF is not set length of field. Assign default values to variables.
		//ELSE assign the values to the variables and display selected. 
		if(!isset($options['length_of_field'])){
		  	$value = '';
			$words_selected = 'selected';
			$char_selected = '';
		}else{
			$value = esc_attr($options['length_of_field']); 
			$words_selected = ($value == 'words') ? 'selected' : '';
			$char_selected = ($value == 'chars') ? 'selected' : '';
		}
		
		//Echo the select box. 			
		echo "<select id='length_of_field' name='custom_excerpt_options[length_of_field]'>
		            <option value='chars' $char_selected>Characters</option>
				 	<option value='words' $words_selected>Words</option>
			  </select>";
	}
	
	/*Output Read More Field
	 * Get options from DB 
	 * check is is set Read More field 
	 * echo the input.
	---------------------------------------------------------------*/
	public function create_read_more_field() {
		
		$options = get_option('custom_excerpt_options'); // Get options from DB field.
			
			//IF is set options Read More, and Read More is not empty. 
			if(isset($options['read_more_field']) && !empty($options['read_more_field'])){
				
				//Assign inserted value to the value variable. 
				$value = $options['read_more_field'];
			}else {
				
				//ELSE set the default value.
				$value = '[...]';
			}
			
			//Echo the input.		
			echo "<input type='text' id='read_more_field' name='custom_excerpt_options[read_more_field]' value='$value' />
				  <br/><i>Defaults to [...].</i>";
	}
	
	/*Output Read More Field
	 * Get options from DB 
	 * check is is set permalink field 
	 * echo the checkbox.
	---------------------------------------------------------------*/
	public function create_permalink_checkbox() {
		
		$options = get_option('custom_excerpt_options');// Get options from DB field. 
		
		//IF is set options permalink assign the checke value to the checked variable
		//ELSE checked variable is empty.
		if(isset($options['permalink_field'])){
			
			$checked = 'checked';
			
		}else{
			
			$checked = '';
		}	
		
		//Echo the chckbox. 
		echo "<input type='checkbox' id='permalink_field' name=custom_excerpt_options[permalink_field]' value='yes' $checked />
				<i>Add a link to the post in your readmore text.</i>";
	}

}