<?php
/*
Plugin Name: Child Menu
Description: Prints parent title, children and siblings titles as a cached menu on parent and child pages.
Version: 1.7
Author: Erik
Author URI: http://www.fohlin.net/getsimple-child-menu-plugin
*/

// get correct id for plugin
$thisfile = basename(__FILE__, '.php'); // This gets the correct ID for the plugin.

// register plugin
register_plugin(
	$thisfile,	// ID of plugin, should be filename minus php
	'Child Menu',	# Title of plugin
	'1.7',	// Version of plugin
	'Erik',	// Author of plugin
	'http://www.fohlin.net/getsimple-child-menu-plugin',	// Author URL
	'Prints parent title, children and siblings titles as a cached menu on parent and child pages.',	// Plugin Description
	'template',	// Page type of plugin
	'go_child_menu'	// Function that displays content
);

// activate actions
//add_action('content-top','go_child_menu'); //Can be used if you need an action.
add_action('changedata-save','clear_child_menu_cache'); 

// functions
function clear_child_menu_cache() 
{
	$cachepath = GSDATAOTHERPATH.'child_menu_cache/';
	if (is_dir($cachepath))
	{
		$dir_handle = @opendir($cachepath) or exit('Unable to open ...getsimple/data/other/child_menu_cache folder');
		$filenames = array();
		
		while ($filename = readdir($dir_handle))
		{
			$filenames[] = $filename;
		}
		
		if (count($filenames) != 0)
		{
			foreach ($filenames as $file) 
			{
				if (!($file == '.' || $file == '..' || is_dir($cachepath.$file) || $file == '.htaccess'))
				{
					unlink($cachepath.$file) or exit('Unable to clean up ...getsimple/data/other/child_menu_cache folder');
				}
			}
		}
	}
}


function go_child_menu() 
{
	$active_page=return_page_slug();
	$cashepath = GSDATAOTHERPATH.'child_menu_cache/'.$active_page.'.cache';
	
	if (is_file($cashepath)) //We have a cashed file, use it.
	{
		echo file_get_contents($cashepath);
	}
	else //We do not have a cached file, create a new one.
	{
		global $PRETTYURLS;
		$dir_handle = @opendir(GSDATAPAGESPATH) or exit('Unable to open ...getsimple/data/pages folder');
		
		$active_parent=return_parent();
		if (strlen($active_parent)==0)
		{
			$active_parent=return_page_slug();
		}
	
		$filenames = array();
		
		while ($filename = readdir($dir_handle))
		{
			$filenames[] = $filename;
		}
		
		if (count($filenames) != 0)
		{
			sort($filenames); //Sort according to page Slug/URL
			$childmenuarray = array();
			$childmenusortarray = array();
			$slugortorder = 21;
			
			//Loop through all pages
			foreach ($filenames as $file) 
			{
				if (!($file == '.' || $file == '..' || is_dir(GSDATAPAGESPATH.$file) || $file == '.htaccess'))
				{
					$thisfile = file_get_contents(GSDATAPAGESPATH.$file);
					$XMLdata = simplexml_load_string($thisfile);
					
					//If parent.
					if ($XMLdata->private != 'Y' and strcmp($XMLdata->url,$active_parent)==0)
					{
						//Check if current page
						if (strcmp($XMLdata->url,$active_page)==0)
						{
							$current=' class="current"';
						}
						else
						{
							$current='';
						}
						
						//Store the parent page data
						if ($PRETTYURLS==1)
						{
							if (strlen($XMLdata->menu)>0)
							{
								$childmenuparent='<p id="parent"'.$current.'><a href="'.$XMLdata->url.'">'.$XMLdata->menu.'</a></p>';
							}
							else
							{
								$childmenuparent='<p id="parent"'.$current.'><a href="'.$XMLdata->url.'">'.$XMLdata->title.'</a></p>';
							}
						}
						else
						{
							if (strlen($XMLdata->menu)>0)
							{
								$childmenuparent='<p id="parent"'.$current.'><a href="index.php?id='.$XMLdata->url.'">'.$XMLdata->menu.'</a></p>';
							}
							else
							{
								$childmenuparent='<p id="parent"'.$current.'><a href="index.php?id='.$XMLdata->url.'">'.$XMLdata->title.'</a></p>';
							}
						}
					}
					elseif ($XMLdata->private != 'Y' and strcmp($XMLdata->parent,$active_parent)==0) //If child.
					{
						//Build the menuOrder sorting array
						if ($XMLdata->menuOrder>0)
						{
							$childmenusortarray[]=$XMLdata->menuOrder;
						}
						else
						{
							$childmenusortarray[]=$slugortorder; //Default to top
						}
						$slugortorder++;
						
						//Check if current page
						if (strcmp($XMLdata->url,$active_page)==0)
						{
							$current=' class="current"';
						}
						else
						{
							$current='';
						}
						
						//Build the childmenuarray
						if ($PRETTYURLS==1)
						{
							if (strlen($XMLdata->menu)>0)
							{
								$childmenuarray[]='<p'.$current.'><a href="'.$XMLdata->url.'">'.$XMLdata->menu.'</a></p>';
							}
							else
							{
								$childmenuarray[]='<p'.$current.'><a href="'.$XMLdata->url.'">'.$XMLdata->title.'</a></p>';
							}
						}
						else
						{
							if (strlen($XMLdata->menu)>0)
							{
								$childmenuarray[]='<p'.$current.'><a href="index.php?id='.$XMLdata->url.'">'.$XMLdata->menu.'</a></p>';
							}
							else
							{
								$childmenuarray[]='<p'.$current.'><a href="index.php?id='.$XMLdata->url.'">'.$XMLdata->title.'</a></p>';
							}
						}
					}
				}
			}
			
			//Sort the child menu according to menuOrder
			array_multisort($childmenusortarray,SORT_ASC, SORT_STRING,$childmenuarray);
			$childmenu = "";
			foreach ($childmenuarray as $childmenuitem) 
			{
				$childmenu=$childmenu.$childmenuitem;
			}
			
			
			if (strlen($childmenu)>0)
			{
				$thismenu='<div id="child_menu">'.$childmenu.'</div>'; //.$childmenuparent
				echo '<!-- un-cached -->'.$thismenu;
			}
			else
			{
				$thismenu='';
			}
			
			//Check if cache folder exists.
			if (is_dir(GSDATAOTHERPATH.'child_menu_cache')==false)
			{
				mkdir(GSDATAOTHERPATH.'child_menu_cache', 0755) or exit('Unable to create ...getsimple/data/other/child_menu_cache folder');
			}
			
			//Save cached child menu file.
			$fp = @fopen($cashepath, 'w') or exit('Unable to save ...getsimple/data/other/child_menu_cache/'.$active_page);
			fwrite($fp, $thismenu);
			fclose($fp);
		}
	}
}

?>