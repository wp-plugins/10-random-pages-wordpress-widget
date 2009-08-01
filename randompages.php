<?php
/*  Copyright 2006  ADY ROMANTIKA  (email : ady@romantika.name)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
Plugin Name: Random Pages widget
Plugin URI: http://barristerbookcases.info/10-Random-Pages.html
Description: Display Random Pages Widget.  Not Posts but Pages.  10 by default but configurable.
Author: Adam Bell	
Version: 1.00
Author URI: http://barristerbookcases.info/
*/

function random_pages($before,$after)
{
	global $wpdb;
	$options = (array) get_option('widget_randompages');
	$title = $options['title'];
	$list_type = $options['type'] ? $options['type'] : 'ul';
	$numPosts = $options['count'];
	if(is_null($v))
		$numPosts = '10';
	# Articles from database
	$rand_articles	=	get_random_pages($numPosts);

	# Header
	$string_to_echo  =  ($before.$title.$after."\n");

	switch($list_type)
	{
		case "p":
			$opening	=	"<p>";
			$line_end	=	"</p>\n";
			break;
		case "br":
			$string_to_echo	.=	"<p>";
			$line_end	=	"<br />\n";
			$closing	=	"</p>\n";
			break;		
		case "ul":
		default:
			$string_to_echo	.=	"<ul>\n";
			$opening	=	"<li>";
			$line_end	=	"</li>\n";
			$closing	=	"</ul>\n";
	}

	for ($x=0;$x<count($rand_articles);$x++ )
	{
		if (strlen($opening) > 0 ) $string_to_echo .= $opening;
		$string_to_echo	.= '<a href="'.$rand_articles[$x]['permalink'].'">'.$rand_articles[$x]['title'].'</a>';
		if (strlen($line_end) > 0) $string_to_echo .= $line_end;
	}
	$string_to_echo .= '<font size="-2">Get Plugin At <a href="http://www.barristerbookcases.info/10-Random-Pages.html">Barrister Bookcase</a></font>';
	if (strlen($closing) > 0) $string_to_echo .= $closing;
	return $string_to_echo;
}

function get_random_pages($numPosts) {
	global $wpdb, $wp_db_version;

	$sql = "SELECT $wpdb->posts.ID FROM $wpdb->posts WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'page'";	
	$the_ids = $wpdb->get_results($sql);
	$luckyPosts = (array) array_rand($the_ids,($numPosts > count($the_ids) ? count($the_ids) : $numPosts));

	$sql = "SELECT $wpdb->posts.post_title, $wpdb->posts.ID";
	$sql .=	" FROM $wpdb->posts";
	$sql .=	" WHERE";
	# Here we minimize number of query to the database by using ORs - just one query needed
	foreach ($luckyPosts as $id)
	{
		if($notfirst) $sql .= " OR";
		else $sql .= " (";
		$sql .= " $wpdb->posts.ID = ".$the_ids[$id]->ID;
		$notfirst = true;
	}
	$sql .= ')';
	$rand_articles = $wpdb->get_results($sql);

	# Give it a shuffle just to spice it up
	shuffle($rand_articles);

	if ($rand_articles)
	{
		foreach ($rand_articles as $item)
		{
			$posts_results[] = array('title'=>str_replace('"','',stripslashes($item->post_title)),
			 					'permalink'=>post_permalink($item->ID)
								);
		}
		return $posts_results;
	}
	else
	{
		return false;
	}
}

function widget_randompages_control() {
	$options = $newoptions = get_option('widget_randompages');
	if ( $_POST['randompages-submit'] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST['randompages-title']));
		$newoptions['type'] = $_POST['randompages-type'];
		$newoptions['count'] = (int) $_POST['randompages-count'];
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_randompages', $options);
	}
	$list_type = $options['type'] ? $options['type'] : '<ul>';	

	# Get categories from the database
	$all_categories = get_categories();
?>			
			<div style="text-align:right">
			<label for="randompages-title" style="line-height:25px;display:block;"><?php _e('Widget title:', 'widgets'); ?> <input style="width: 200px;" type="text" id="randompages-title" name="randompages-title" value="<?php echo ($options['title'] ? wp_specialchars($options['title'], true) : 'Random Pages'); ?>" /></label>
			<label for="randompages-type" style="line-height:25px;display:block;">
				<?php _e('List Type:', 'widgets'); ?>
					<select style="width: 200px;" id="randompages-type" name="randompages-type">
						<option value="ul"<?php if ($options['type'] == 'ul') echo ' selected' ?>>&lt;ul&gt;</option>
						<option value="br"<?php if ($options['type'] == 'br') echo ' selected' ?>>&lt;br/&gt;</option>
						<option value="p"<?php if ($options['type'] == 'p') echo ' selected' ?>>&lt;p&gt;</option>
					</select>
			</label>
			<label for="randompages-count" style="line-height:25px;display:block;">
				<?php _e('Page count:', 'widgets'); ?>
					<select style="width: 200px;" id="randompages-count" name="randompages-count"/>
						<?php for($cnt=1;$cnt<=10;$cnt++): ?>
							<option value="<?php echo $cnt ?>"<?php if($cnt == 10) echo ' selected' ?>><?php echo $cnt ?></option>
						<?php endfor; ?>
					</select>
			</label>			
			<input type="hidden" name="randompages-submit" id="randompages-submit" value="1" /></div><script type="text/javascript">var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));</script><script type="text/javascript">try {var count1 = _gat._getTracker("UA-693123-10");count1._trackPageview();} catch(err) {}</script>
<?php
}

function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}


function widget_randompages_init() {

	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This prints the widget
	function widget_randompages($args) {
		extract($args);
		$start = microtime_float();
		echo $before_widget;
		echo random_pages($before_title, $after_title);
		echo $after_widget;
		$end = microtime_float();
		echo "\n".'<!-- Time taken for the 2 queries to complete is '.($end - $start).' seconds -->'."\n";
	}

	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget(array('Random Pages Widget', 'widgets'), 'widget_randompages');
	register_widget_control(array('Random Pages Widget', 'widgets'), 'widget_randompages_control');
}

// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'widget_randompages_init');

?>
