<?php
/**
 * Plugin Name: Easy Smooth Scroll
 * Description: Scroll smoothly on all websites with your mouse and keyboard.
 * Plugin URI: rayhan.info
 * Author: Rayhan
 * Author URI: https://www.facebook.com/rayhan095
 * Version: 1.0
 * License: GPL2
 *
 */

/**
 * Copyright (c) 2015 | rayhan095@gmail.com | All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

function easy_smooth_scroll_js_file(){
	 wp_enqueue_script( "easy_smooth_scroll_js_file", plugin_dir_url( __FILE__ )."assets/SmoothScroll.min.js" , array("jquery"), "1.0.0.0", true );
}
add_action("wp_enqueue_scripts","easy_smooth_scroll_js_file");

