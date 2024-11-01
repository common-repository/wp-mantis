<?php
/*
Plugin Name: WP Mantis
Plugin URI: http://niklas-rother.de/projekte/wp-mantis
Description: Extended Version of "WP Mantis Table". Allows to view Changelogs, Roadmaps and Buglists from <a href="http://mantisbt.org">MantisBT</a> in Wordpress Pages and Post.
Version: 1.2.2
Author: Niklas Rother
Author URI: http://niklas-rother.de
Text Domain: wp-mantis
  
    Copyright 2010 Niklas Rother (e-mail: info@niklas-rother.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_shortcode('Mantis', 'wpmantis_shortcode');
add_action('wp_print_styles', 'wpmantis_print_styles');
add_action('admin_menu', 'wpmantis_admin_menu');
add_action('admin_init', 'wpmantis_admin_init');
add_action('init', 'wpmantis_init');
add_action('admin_head', 'wpmantis_admin_head');

register_activation_hook(__FILE__, 'wpmantis_set_options');
register_deactivation_hook(__FILE__, 'wpmantis_unset_options');

//Load the textdomain for translation and register the pagination script
function wpmantis_init()
{
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain('wp-mantis', null, $plugin_dir);
	
	//only include the script in the front end, not in the admin area to avoid conflicts
	if(!is_admin())
	{
		wp_register_script('pagination', WP_PLUGIN_URL . '/wp-mantis/paging.js');
		wp_enqueue_script('pagination');
	}
}

//Print the Mantis CSS for the Roadmap and Changelog
function wpmantis_print_styles()
{
	wp_register_style('wp-mantis-css', WP_PLUGIN_URL . '/wp-mantis/wp-mantis.css');
	wp_enqueue_style('wp-mantis-css');
}

//Prints out JavaScript in the Admin <head> Tag
function wpmantis_admin_head()
{
	echo '<script type="text/javascript">function wpmantis_only_numbers(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;
    return true;
	}</script>';
}

//Register the color picker script. (www.jscolor.com)
function wpmantis_admin_init()
{
	wp_register_script('jscolor', WP_PLUGIN_URL . '/wp-mantis/jscolor/jscolor.js');
}

//Delete the options (while deactivation)
function wpmantis_unset_options()
{
	delete_option('wp_mantis_options');
}

//Adds the admin menu and registers the print_scripts action for the admin page.
function wpmantis_admin_menu()
{
	$plugin_id = add_options_page(__('WP Mantis', 'wp-mantis'), __('WP Mantis', 'wp-mantis'), 'manage_options', __FILE__, 'wpmantis_admin_page');
	add_action('admin_print_scripts-' . $plugin_id, 'wpmantis_admin_print_scripts');
}

//Prints the jscolor script on the admin page.
function wpmantis_admin_print_scripts()
{
	wp_enqueue_script('jscolor');
}

//Fill the options with default entrys. Also used to reset the options.
function wpmantis_set_options()
{
	$options = array(
		'mantis_soap_url' => 'http://yoursite.com/bugs/api/soap/mantisconnect.php?wsdl',
		'mantis_user' => 'wordpress',
		'mantis_password' => 'password',
		'mantis_base_url' => 'http://yoursite.com',
		'mantis_max_desc_lenght' => 25,
		'mantis_statuses' => array(
			'10' => __('New', 'wp-mantis'),
			'20' => __('Feedback', 'wp-mantis'),
			'30' => __('Acknowledged', 'wp-mantis'),
			'40' => __('Confirmed', 'wp-mantis'),
			'50' => __('Assigned', 'wp-mantis'),
			'80' => __('Resolved', 'wp-mantis'),
			'90' => __('Closed', 'wp-mantis')
		),
		'mantis_colors' => array(
			'10' => '#fcbdbd',
			'20' => '#e3b7eb',
			'30' => '#ffcd85',
			'40' => '#fff494',
			'50' => '#c2dfff',
			'80' => '#d2f5b0',
			'90' => '#c9ccc4'
		),
		'mantis_enable_pagination' => true,
		'mantis_bugs_per_page' => 15
	);

	update_option('wp_mantis_options', $options);
}

//Outputs the admin page and handles button clicks.
function wpmantis_admin_page()
{
	if (!current_user_can('manage_options'))
		wp_die( __('You do not have sufficient permissions to access this page.', 'wp-mantis') );
?>

        <div class="wrap">
        <h2>WP Mantis Configuration</h2>
        <?php
        if (isset($_POST['submit']))
            wpmantis_update_options();
		else if (isset($_POST['get_translation']))
			wpmantis_get_status_translation();
		else if (isset($_POST['defaults']))
		{
			wpmantis_set_options();
			?>
			<div id="message" class="updated fade">
			<p>Options reset.</p>
			</div>
			<?php
		}

        //Get the options
        $options = get_option('wp_mantis_options');
		
        ?>
        <form name="wp-mantis-settings" method="post" action="">
        <table class="form-table">
                <tr align="top"><th scope="row"><?php _e('URL to MantisConnect WSDL:', 'wp-mantis'); ?></th><td><input type="text" name="mantis_soap_url" value="<?php echo $options['mantis_soap_url'] ?>" size="45" /></td></tr>
                <tr align="top"><td colspan="2"><?php _e('The URL to MantisConnect should be the complete URL where you find the mantisconnect.php file.  Make sure to end this url with ?wsdl or you\'ll get errors everywhere!', 'wp-mantis'); ?></td></tr>

                <tr align="top"><th scope="row"><?php _e('Mantis Base URL:', 'wp-mantis'); ?></th><td><input type="text" name="mantis_base_url" value="<?php echo $options['mantis_base_url'] ?>" size="45" /></td></tr>
                <tr align="top"><td colspan="2"><?php _e('The URL the homepage of your Mantis installation (e.g. http://yoursite.com/bugs).', 'wp-mantis'); ?></td></tr>

                <tr align="top"><th scope="row"><?php _e('Mantis User:', 'wp-mantis'); ?></th><td><input type="text" name="mantis_user" value="<?php echo $options['mantis_user'] ?>" /></td></tr>
                <tr align="top"><td colspan="2"><?php _e('This is the user used to access Mantis and get the bug list. Must have at least reporter capabilities. (See readme for details)', 'wp-mantis'); ?></td></tr>

                <tr align="top"><th scope="row"><?php _e('Mantis Password:', 'wp-mantis'); ?></th><td><input type="password" name="mantis_password" value="<?php echo $options['mantis_password'] ?>" /></td></tr>
                <tr align="top"><td colspan="2"><?php _e('The password for the user listed above.', 'wp-mantis'); ?></td></tr>
				
				<tr align="top"><th scope="row"><?php _e('Maximum Description Lenght:', 'wp-mantis'); ?></th><td><input type="text" name="mantis_max_desc_lenght" value="<?php echo $options['mantis_max_desc_lenght'] ?>" /></td></tr>
                <tr align="top"><td colspan="2"><?php _e('Maximum characters to display for the description of a bug. Set to 0 to display no description.', 'wp-mantis'); ?></td></tr>
				
				<tr align="top"><th scope="row"><?php _e('Enable Pagination:', 'wp-mantis'); ?></th><td><input type="checkbox" name="mantis_enable_pagination" value="mantis_enable_pagination" <?php echo $options['mantis_enable_pagination'] ? 'checked="checked"' : '' ?>/></td></tr>
                <tr align="top"><td colspan="2"><?php _e('Enable pagination for bugs? If unchecked the setting below has no function.', 'wp-mantis'); ?></td></tr>
				
				<tr align="top"><th scope="row"><?php _e('Bugs per Page:', 'wp-mantis'); ?></th><td><input type="text" name="mantis_bugs_per_page" value="<?php echo $options['mantis_bugs_per_page'] ?>"/ onKeyPress="return wpmantis_only_numbers(event);"></td></tr>
                <tr align="top"><td colspan="2"><?php _e('Number of bugs shown on one site, if pagination is enabled.', 'wp-mantis'); ?></td></tr>
        </table>
        <h3><?php _e('Colors and Statuses', 'wp-mantis'); ?></h3>
		<p><?php _e('If the names of the statues are not in the right language, you can use this button to pull the correct values directly<br />from Mantis. This is also a good way to test the connection. The values will be in the language set for the user listed above</p><small>Make sure that you save the options first, or the old values will be used to make the connection!', 'wp-mantis'); ?></small><br />
		<input type="submit" class="button-secondary" name="get_translation" value="<?php _e('Get Statuses Names', 'wp-mantis'); ?>"><br />
		
        <p><?php _e('Customize the status colors below:', 'wp-mantis'); ?></p>
        <table class="widefat">
			<thead>
				<tr>
					<th><?php _e('Status', 'wp-mantis'); ?></th>
					<th><?php _e('Color', 'wp-mantis'); ?></th>
					<th><?php _e('ID #', 'wp-mantis'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e('Status', 'wp-mantis'); ?></th>
					<th><?php _e('Color', 'wp-mantis'); ?></th>
					<th><?php _e('ID #', 'wp-mantis'); ?></th>
				</tr>
			</tfoot>
			<tbody>
					<tr><td>10</td><td><?php echo $options['mantis_statuses'][10] ?></td><td><input type="text" class="color {hash:true}" name="color[10]" value="<?php echo $options['mantis_colors'][10] ?>" /></td></tr>
					<tr><td>20</td><td><?php echo $options['mantis_statuses'][20] ?></td><td><input type="text" class="color {hash:true}" name="color[20]" value="<?php echo $options['mantis_colors'][20] ?>" /></td></tr>
					<tr><td>30</td><td><?php echo $options['mantis_statuses'][30] ?></td><td><input type="text" class="color {hash:true}" name="color[30]" value="<?php echo $options['mantis_colors'][30] ?>" /></td></tr>
					<tr><td>40</td><td><?php echo $options['mantis_statuses'][40] ?></td><td><input type="text" class="color {hash:true}" name="color[40]" value="<?php echo $options['mantis_colors'][40] ?>" /></td></tr>
					<tr><td>50</td><td><?php echo $options['mantis_statuses'][50] ?></td><td><input type="text" class="color {hash:true}" name="color[50]" value="<?php echo $options['mantis_colors'][50] ?>" /></td></tr>
					<tr><td>80</td><td><?php echo $options['mantis_statuses'][80] ?></td><td><input type="text" class="color {hash:true}" name="color[80]" value="<?php echo $options['mantis_colors'][80] ?>" /></td></tr>
					<tr><td>90</td><td><?php echo $options['mantis_statuses'][90] ?></td><td><input type="text" class="color {hash:true}" name="color[90]" value="<?php echo $options['mantis_colors'][90] ?>" /></td></tr>
			</tbody>
		</table>
        <br />
		<input type="submit" class="button-secondary" name="defaults" value="<?php _e('Reset Options', 'wp-mantis'); ?>" />
        <input type="submit" class="button-primary" name="submit" value="<?php _e('Save Changes', 'wp-mantis'); ?>" />
        </form>
		</div>
<?php
}

//Connects to MantisConnect and retrievs the translation for the statuses enum.
function wpmantis_get_status_translation()
{
	$options = get_option('wp_mantis_options');
	extract($options);
	$client = new SoapClient($mantis_soap_url);
	try
	{	
		$results = $client->mc_enum_status($mantis_user, $mantis_password);
		
		foreach ($results as $result)
		{
				$id = $result->id;
				$name = $result->name;
				
				$mantis_statuses[$id] = $name;
		}
		$options['mantis_statuses'] = $mantis_statuses;
		update_option('wp_mantis_options', $options);
		
		?>
        <div id="message" class="updated fade">
        <p><?php _e('Options saved.', 'wp-mantis'); ?></p>
        </div>
        <?php
	}
	catch(SoapFault $e)
	{
		throw $e;
		?>
        <div id="message" class="error fade">
        <p><?php printf(__('Error: %s', 'wp-mantis'), $e->getMessage()); ?></p>
        </div>
        <?php
	}
}

//Reads the new options from POST and writes it to the DB.
function wpmantis_update_options()
{
	$options = get_option('wp_mantis_options');

	$options['mantis_user'] = $_REQUEST['mantis_user'];
	$options['mantis_password'] = $_REQUEST['mantis_password'];
	$options['mantis_soap_url'] = $_REQUEST['mantis_soap_url'];
	$options['mantis_base_url'] = $_REQUEST['mantis_base_url'];
	$options['mantis_max_desc_lenght'] = $_REQUEST['mantis_max_desc_lenght'];
	$options['mantis_enable_pagination'] = isset($_REQUEST['mantis_enable_pagination']);
	$options['mantis_bugs_per_page'] = $_REQUEST['mantis_bugs_per_page'];
	$options['mantis_colors'] = $_REQUEST['color'];
	
	//Check to see that the base URL ends with a trailing slash if not, add it
	if (substr($options['mantis_base_url'], -1, 1) != '/') { $options['mantis_base_url'] .= '/'; }

	update_option('wp_mantis_options', $options);

	?>
	<div id="message" class="updated fade">
	<p><?php _e('Options saved.', 'wp-mantis'); ?></p>
	</div>
	<?php
}

//The main function of this plugin. Parses the attributes and prints out the requested stuff.
function wpmantis_shortcode($atts)
{
	//Get options
	extract(get_option('wp_mantis_options'));
	
	//Select Mode
	if($atts[0] == 'bugs')
	{
		//Get Attributes
		extract(shortcode_atts(array('proj_id' => 0, 'exclude_stat' => '', 'limit' => 1000000, 'include_stat' => ''), $atts));
		
		//Handling of invalid combinations
		if($proj_id == 0)
			return __('Error: No project ID specified!', 'wp-mantis');
		
		if($exclude_stat != '' && $include_stat != '')
			return __('Error: Can not specify both include and exclude!', 'wp-mantis');
			
		$exclude = false; $include = false; //outer scope
		if($exclude_stat != '')
		{
			$exclude_stat = explode(',', $exclude_stat);
			$exclude = true;
			$include = false;
		}
		else if($include_stat != '')
		{
			$include_stat = explode(',', $include_stat);
			$exclude = false;
			$include = true;
		}
		
		$client = new SoapClient($mantis_soap_url);
		try
		{
			$results = $client->mc_project_get_issues($mantis_user, $mantis_password, $proj_id, 1, $limit);
			
			$output = '<table id="mantis_bugs" border="1" style="border-collapse:collapse"><tr><td>' . __('ID #', 'wp-mantis') . '</td><td>' . __('Status', 'wp-mantis') . '</td><td>' . __('Category', 'wp-mantis') . '</td><td>' . __('Details', 'wp-mantis') . '</td></tr>';
			
			foreach ($results as $result)
			{
					$id = $result->id;
					$title = $result->summary;
					$category = $result->category;
					$b_status = $result->status->id;
					$b_status_name = $mantis_statuses[$b_status];
					$description = $result->description;
					$description = wpmantis_shorten_text($description, $mantis_max_desc_lenght);
					
					if($exclude && in_array($b_status, $exclude_stat))
						continue;
					else if ($include && !in_array($b_status, $include_stat))
						continue;

					$output .= "<tr style=\"background: $mantis_colors[$b_status];\"><td><a href=\"{$mantis_base_url}view.php?id=$id\" target=\"_new\">$id</a></td><td>$b_status_name</td><td>$category</td><td><b>$title</b><br />$description</td></tr>";
			}

			//Close the table
			$output .= '</table>';
			
			//Add pagination stuff
			if($mantis_enable_pagination)
			{
				$output .= '<div id="mantis_navigation"></div>';
				$output .= "<script type=\"text/javascript\">
							var pager = new Pager('mantis_bugs', $mantis_bugs_per_page);
							pager.init(); 
							pager.showPageNav('pager', 'mantis_navigation', '" . __('Prev', 'wp-mantis') . "', '" . __('Next', 'wp-mantis') . "'); 
							pager.showPage(1);</script>";
			}
		}
		catch(SoapFault $e)
		{
			if(current_user_can('manage_options')) //display full error message (witch includes the password!) only to the admin
			{
				_e('Note: This message is only displayed to admins, dont worry ;)', 'wp-mantis') . '<br /><br />'; //echo since we will produce a fatal error!
				throw $e;
			}
			else
				return sprintf(__('Fatal Exception while connecting to Mantis: %s', 'wp-mantis'), $e->getMessage());
		}
		
		return $output;
	}
	else if($atts[0] == 'roadmap' || $atts[0] == 'changelog')
	{
		extract(shortcode_atts(array('ver_id' => 0, 'proj_id' => 0, 'proj_name' => '', 'ver_name' => ''), $atts));
		//Handling of invalid combinations
		if($ver_id == 0 && $proj_id == 0 && $proj_name == '') //Easy: Noting specified, error.
			return __('Error: No version/project ID or project name specified! See Readme for details.', 'wp-mantis');
		
		if($ver_id > 0 && $proj_id > 0) //Too much information: We could prefer one, but its better to throw an error.
			return __('Error: Cannot specify both version and product ID!', 'wp-mantis');
			
		if($proj_name != '')
		{
			if($ver_id > 0)
				return __('Error: Cannot use version ID with project name. See Readme for details.', 'wp-mantis');
				
			if($proj_id > 0)
				return __('Error: Cannot specify both project name and ID', 'wp-mantis');
			//Version name is optional!
		}
		
		//Encode username and password, since they could contain an & (or other 'bad' chars)
		$mantis_user = urlencode($mantis_user);
		$mantis_password = urlencode($mantis_password);
		
		//Select the correct URL
		$http_body = "username=$mantis_user&password=$mantis_password&perm_login=0&secure_session=1&return=";
		$return_url = $atts[0] . '_page.php?';
		
		//no error checking here, because the code above will trigger an error on theese.
		if($ver_id > 0)
			$return_url .= 'version_id=' . $ver_id . '&';
		
		if($proj_id > 0)
			$return_url .= 'project_id=' . $proj_id . '&';
		
		if($ver_name != '')
			$return_url .= 'version=' . $ver_name . '&';
			
		if($proj_name != '')
			$return_url .=  'project=' . $proj_name . '&';
		
		$return_url = substr($return_url, 0, -1); //remove last &
		$http_body .= urlencode($return_url); //return url contains &
		
		$fetch_url = $mantis_base_url . 'login.php'; //The url of the login.php, wich will handle the redirecting stuff
		
		//snoopy is deprecated, but the HTTP API is buggy, so we suppress the Warning with the at.
		@require_once(ABSPATH . 'wp-includes/class-snoopy.php');
		$snoopy = new Snoopy();
		
		if(!$snoopy->fetch($fetch_url . '?' . $http_body))
		{
			//Error!
			return __('Error in Snoopy: %s', $snoopy->error);
		}
		$content = $snoopy->results;
		
		$tt = strstrb(strstr($content, '<tt>'), '</tt>') . '</tt>'; //closing tag is cut of
		
		if(strlen($tt) < 5) //nothing found, </tt> is ever present!
			return __('Error while fetching the page from Mantis. The page we got seem no to be a changelog or roadmap.', 'wp-mantis');
		
		//Remove all Links
		$tt = wpmantis_strip_only($tt, 'a');
		$tt = preg_replace('#<span class="bracket-link">.*<\/span>#isU', '', $tt);
		$output = '<p class="mantis_roadmap">';
		$output .= $tt;
		$output .= '</p>';

		return $output;
	}
	else
		return __('Error: No Mode selected.', 'wp-mantis');
}

//Helper: Emulate strstr with before_needle on old PHP Versions
function strstrb($h,$n){
    return array_shift(explode($n,$h,2));
}

//Helper: Strip out HTML tags
function wpmantis_strip_only($str, $tags, $stripContent = false)
{
    $content = '';
    if(!is_array($tags)) {
        $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
        if(end($tags) == '') array_pop($tags);
    }
    foreach($tags as $tag) {
        if ($stripContent)
             $content = '(.+</'.$tag.'[^>]*>|)';
         $str = preg_replace('#</?'.$tag.'[^>]*>'.$content.'#is', '', $str);
    }
    return $str;
}

//Helper: Shortens texts and marks it with ...
function wpmantis_shorten_text($text, $chars = 25)
{
    if ($chars == 0)
		return '';
	
	if(strlen($text) > $chars)
	{
		$text = $text . ' ';
		$text = substr($text ,0 ,$chars);
		$text = substr($text, 0, strrpos($text,' '));
		$text = $text . '...';
		return $text;
	}
	else
		return $text;
}
?>
