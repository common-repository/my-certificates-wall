<?php
/*
Plugin Name: My Certificates
Plugin URI: http://certificateswall.com/
Description: Shows certificates wall icons for specified user of certificateswall.com
Version: 0.1
Author: Roman Gelembjuk
Author URI: http://www.gelembjuk.com/
*/

/*
License: GPL
Compatibility: WordPress 2.0 with Widget-plugin.

Installation:
Place the widget_certificateswall.php file in your /wp-content/plugins/ directory
and activate through the administration panel, and then go to the widget panel and
drag it to where you would like to have it!
*/

/*  Copyright Roman Gelembjuk

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


/* Changelog

*/

function widget_certificateswall_init() {
	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_certificateswall( $args ) {
		extract($args);
		
		$options = get_option('widget_certificateswall');
		$path=ABSPATH.'wp-content/cache/';
		$cachefile=$path.'certificateswall.htm';
		
		if($options['certificateswall_src_cache']=='1' && (!file_exists($path) || file_exists($path) && !is_writable($path))){
			$options['certificateswall_src_cache']='0';
		}
		if($options['certificateswall_src_cache']=='1'){
			$cachetime=intval($options['certificateswall_src_cachetime']);
			if($cachetime==0) $cachetime=5;
			if(file_exists($cachefile)){
	      	      	      if(time()-filectime($cachefile)<$cachetime*60){
	      	      	      	      echo file_get_contents($cachefile);
	      	      	      	      return true;
	      	      	      }
	      	      }
		}
		
		
		$title = $options['certificateswall_src_title'];
		$class = $options['certificateswall_src_class'];

		$userid=$options['certificateswall_src_userid'];
		if(intval($userid)==0){
			echo '<p>-- user ID is not provided --</p>';
			return true;
		}
		$json=widget_certificateswall_get_page('http://certificateswall.com/index.php?page=public&id='.$userid.'&format=json');		
		$profile=json_decode($json);
		if(!isset($profile->userid)){
			echo '<p>-- problems of data loading --</p>';
			return true;
		}
		
		$showthumb=intval($options['certificateswall_src_showmythumb']);
		$showname=intval($options['certificateswall_src_showmyname']);
		$middleicons=intval($options['certificateswall_src_middlesize']);
		$columns=$options['certificateswall_src_countofcolumns'];
		$columns=intval($columns);
		if($columns<1 || $columns>10) $columns=1;

		$html='<div id="certificateswall" class="widget '.$class.'">'."\n";
		if($title!=''){
			$html.='<h3  class="widget-title">'.$title.'</h3>'."\n";
		}
		if($showthumb==1 || $showname==1){
			$html.='<div id="certificateswall_profile" style="margin-bottom:5px;">'."\n";
			if($showthumb==1 && $profile->profile->profile->thumbnail!=''){
				$html.='<img src="'.$profile->profile->profile->thumbnail.'">'."\n";
			}
			if($showname==1){
				$html.='<span ><a href="'.$profile->profile->profile->publiclink.'" target="_blank" >'.$profile->profile->profile->name.'</a></span>'."\n";
			}
			$html.='</div>'."\n";
		}
		//list of certificates
		if(count($profile->profile->certificates)>0){
			$html.='<table border="0" cellpadding="0" cellspacing="0">'."\n";
			$html.='<tr>'."\n";
			$i=0;
			foreach($profile->profile->certificates as $c){				
				$img=$c->smallimage;
				if($middleicons==1) $img=$c->middleimage;
				$html.='<td><a href="'.$c->publiclink.'" tagret="_blank"><img src="'.$img.'" title="'.$c->title.'" border="0"></a></td>'."\n";
				$i++;
				if($i==$columns){
					$html.='</tr><tr>'."\n";
					$i=0;
				}
			}
			$html.='</tr>'."\n";
			$html.='</table>'."\n";
		}
		$html.='</div>';

		echo $html;
		
		if($options['certificateswall_src_cache']=='1'){
	      	      file_put_contents($cachefile,$html);
	      	}
	} /* widget_certificateswall() */

	function widget_certificateswall_control() {
		$options = $newoptions = get_option('widget_certificateswall');
		$message='';
		if ( $_POST["certificateswall_src_submit"] ) {
			$newoptions['certificateswall_src_title'] = strip_tags(stripslashes($_POST["certificateswall_src_title"]));
			$newoptions['certificateswall_src_class'] = strip_tags(stripslashes($_POST["certificateswall_src_class"]));
			$newoptions['certificateswall_src_userid'] = strval(intval($_POST["certificateswall_src_userid"]));
			if($newoptions['certificateswall_src_userid']=='0') $newoptions['certificateswall_src_userid']='';
			$newoptions['certificateswall_src_countofcolumns'] = strval(intval($_POST["certificateswall_src_countofcolumns"]));
			if($newoptions['certificateswall_src_countofcolumns']<'1' || $newoptions['certificateswall_src_countofcolumns']>'10') 
				$newoptions['certificateswall_src_countofcolumns']='3';
			$newoptions['certificateswall_src_middlesize'] = $_POST["certificateswall_src_middlesize"];
			if($newoptions['certificateswall_src_middlesize']!='1') $newoptions['certificateswall_src_middlesize']='0';
			$newoptions['certificateswall_src_showmyname'] = $_POST["certificateswall_src_showmyname"];
			if($newoptions['certificateswall_src_showmyname']!='1') $newoptions['certificateswall_src_showmyname']='0';
			$newoptions['certificateswall_src_showmythumb'] = $_POST["certificateswall_src_showmythumb"];
			if($newoptions['certificateswall_src_showmythumb']!='1') $newoptions['certificateswall_src_showmythumb']='0';
			
			$newoptions['certificateswall_src_cachetime']=strval(intval($_POST["certificateswall_src_cachetime"]));;
			$newoptions['certificateswall_src_cache']=strval(intval($_POST["certificateswall_src_cache"]));;
			if($newoptions['certificateswall_src_cache']=='1'){
				//check if folder exists and is writable
				$path=ABSPATH.'wp-content/cache/';
				if(!file_exists($path)){
					$newoptions['certificateswall_src_cache']='0';
					$message='Cache folder does not exist';
				}elseif(!is_writable($path)){
					$newoptions['certificateswall_src_cache']='0';
					$message='Cache folder is not writable';
				}
			}
			
		} /* if */
		
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_certificateswall', $options);
		} /* if */
		if(!isset($options['certificateswall_src_title'])){
			//set default options
			$options['certificateswall_src_title']='My Certificates Wall';
			$options['certificateswall_src_class']='';
			$options['certificateswall_src_cache']='0';
			$options['certificateswall_src_cachetime']='60';
			$options['certificateswall_src_userid']='';
			$options['certificateswall_src_countofcolumns']='3';
			$options['certificateswall_src_middlesize']='0';
			$options['certificateswall_src_showmyname']='1';
			$options['certificateswall_src_showmythumb']='1';
		}

		$title = htmlspecialchars($options['certificateswall_src_title'], ENT_QUOTES);
		$class = $options['certificateswall_src_class'];
		
		if($message!=''){
			?>
			<div style="border:1px solid red;color:red;padding:6px;"><?php echo $message; ?></div>
			<?
		}
		
		?>
		
		<?php _e('Title:'); ?> <input style="width: 170px;" id="certificateswall_src_title" name="certificateswall_src_title" type="text" value="<?php echo $title; ?>" /><br />
		<?php _e('CSS class:'); ?> <input style="width: 170px;" id="certificateswall_src_class" name="certificateswall_src_class" type="text" value="<?php echo $class; ?>" size="10"/><br />
		<?php _e('Use cache:'); ?> 
			<select id="certificateswall_src_cache" name="certificateswall_src_cache">
			<option value="0" <?php if($options['certificateswall_src_cache']=='0') echo 'selected' ?>>No
			<option value="1" <?php if($options['certificateswall_src_cache']=='1') echo 'selected' ?>>Yes
			</select>
			<a title="<?php _e('If this is Yes data loaded from certificates wall.com will be saved on your wordpress site for specified time (Cache Time).
			When this time expire then data will be loaded from certificateswall.com again. This option is recommended to be Yes.
			To use cache you must to create folder /wp-content/cache and set \'Writable for all\' permissions to it. The plugin will store cache in this folder.'); ?>">[?]</a>
		<br>
		<?php _e('Cache time (minutes):'); ?> 
			<input id="certificateswall_src_cachetime" name="certificateswall_src_cachetime" type="text" value="<?php echo $options['certificateswall_src_cachetime']; ?>" size="5"/><br />
		<hr>
		<?php _e('User ID on certificateswall.com:'); ?> <br>
			<input id="certificateswall_src_userid" name="certificateswall_src_userid" type="text" value="<?php echo $options['certificateswall_src_userid']; ?>" size="10"/>
			<a title="<?php _e('User ID on certificateswall.com. You can copy your ID on "Share the Wall" section of certificateswall.com site.'); ?>">[?]</a>
		<br />
		<br>
		
		<?php _e('Count of icons per row:'); ?> 
			<input id="certificateswall_src_countofcolumns" name="certificateswall_src_countofcolumns" type="text" value="<?php echo $options['certificateswall_src_countofcolumns']; ?>" size="3"/>
			<a title="<?php _e('Choose count of icons to display in 1 row. If you display small icons then optimal is 3. If middle size then choose 1.'); ?>">[?]</a>
		<br><br>

		<?php _e('Middle size icons:'); ?> 
			<select id="certificateswall_src_middlesize" name="certificateswall_src_middlesize">
			<option value="0" <?php if($options['certificateswall_src_middlesize']=='0') echo 'selected' ?>>No
			<option value="1" <?php if($options['certificateswall_src_middlesize']=='1') echo 'selected' ?>>Yes
			</select>
			<a title="<?php _e('If this is Yes then middle size images for certificates will be displayed'); ?>">[?]</a>
		<br><br>

		<?php _e('Show my name:'); ?> 
			<select id="certificateswall_src_showmyname" name="certificateswall_src_showmyname">
			<option value="0" <?php if($options['certificateswall_src_showmyname']=='0') echo 'selected' ?>>No
			<option value="1" <?php if($options['certificateswall_src_showmyname']=='1') echo 'selected' ?>>Yes
			</select>
			<a title="<?php _e('If this is Yes then name of certificatesWall profile will be visible'); ?>">[?]</a>
		<br><br>

		<?php _e('Show my image:'); ?> 
			<select id="certificateswall_src_showmythumb" name="certificateswall_src_showmythumb">
			<option value="0" <?php if($options['certificateswall_src_showmythumb']=='0') echo 'selected' ?>>No
			<option value="1" <?php if($options['certificateswall_src_showmythumb']=='1') echo 'selected' ?>>Yes
			</select>
			<a title="<?php _e('If this is Yes then thumbnail of certificatesWall profile will be visible'); ?>">[?]</a>
		<br><br>
		

		<input type="hidden" id="certificateswall_src_submit" name="certificateswall_src_submit" value="1" />

		<?php
	} /* widget_certificateswall_control() */
	
	function widget_certificateswall_get_page($url,$lim=15) {
		$agent = "My Certificates WP from ".$_SERVER["SERVER_NAME"];

		if(function_exists('curl_init')){
			  $ch = curl_init();
			  curl_setopt($ch, CURLOPT_URL, $url);
			  curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			  curl_setopt($ch, CURLOPT_TIMEOUT, $lim);
			  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    	
			  $result = curl_exec ($ch);

			  if (curl_error($ch)) { 
				      $result="";
			  } 

			curl_close ($ch);
		}else{
			  $result=file_get_contents($url);
		}
		return $result;
	}
	
	register_sidebar_widget('My certificates', 'widget_certificateswall');
	register_widget_control('My certificates', 'widget_certificateswall_control' );
} /* widget_certificateswall_init() */

add_action('plugins_loaded', 'widget_certificateswall_init');

?>