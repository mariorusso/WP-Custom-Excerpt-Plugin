<?php
/*
* Plugin Name: Custom Excerpt
* Description: DIT 21063 1401 Assignment - Enables the user specify length of excerpt in words or characters. Allow user set a custom read more.   
* Version: 1.0 
* Author: Mario Russo
* Author URI: http://www.russomario.com
*/

 $MY_PATH = plugin_dir_path(__FILE__);
 
 require($MY_PATH . 'classes/Custom_Excerpt.php');
 
 new Custom_Excerpt;
