<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DL Manager, a plugin for Dotclear 2
# Copyright (C) 2008,2010 Moe (http://gniark.net/) and Tomtom (http://blog.zenstyle.fr)
#
# DL Manager is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# DL Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

/**
@ingroup Download manager
@brief General class
*/
class dlManager
{
	/**
	return list of subdirectories
	@return	<b>array</b> Subdirectories
	*/
	public static function listDirs($in_jail=false,$empty_value=false)
	{
		global $core;
		
		$dirs = array();
		
		# empty default value
		if ($empty_value)
		{
			$dirs = array(''=> '');
		}
		
		try
		{
			if (!is_object($core->media))
			{
				$core->media = new dcMedia($core);
			}
			# from gallery/gal.php
			foreach ($core->media->getRootDirs() as $v)
			{
				$path = $v->relname;
				if ($in_jail)
				{
					if (self::inJail($path))
					{
						$dirs[$path] = $path;
					}
				}
				else
				{
					$dirs[$path] = $path;
				}
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
		
		return($dirs);
	}

	/**
	get sort values
	@param	empty_value	<b>boolean</b>	Add an empty value in the array
	@return	<b>array</b> sort values
	*/
	public static function getSortValues($empty_value=false)
	{
		$array = (($empty_value === true) ? array('' => '') : array());
		
		# from /dotclear/admin/media.php
		return(array_merge($array,array(
			__('By names, ascendant') => 'name-asc',
			__('By names, descendant') => 'name-desc',
			__('By dates, ascendant') => 'date-asc',
			__('By dates, descendant') => 'date-desc'
		)));
	}

	/**
	return DL Manager URL
	@return	<b>string</b> URL
	*/
	public static function pageURL()
	{
		global $core;

		return ($core->blog->url.$core->url->getBase('media'));
	}
	
	/**
	make BreadCrumb
	@param	dir	<b>string</b>	path directory
	@return	<b>record</b> BreadCrumb
	*/
	public static function breadCrumb($dir)
	{
		# BreadCrumb
		$base_url = self::pageURL().'/';
		$dirs = explode('/',$dir);
		$path = '';
		
		$breadCrumb = array();
		
		foreach ($dirs as $dir)
		{
			$dir = trim($dir);
			
			if (!empty($dir))
			{
				$path = (($path == '') ? $dir : $path.'/'.$dir); 
				$breadCrumb[] = array(
					'name' => $dir,
					'url' => $base_url.$path
				);
			}
		}
		
		return(staticRecord::newFromArray($breadCrumb));
	}
	
	/**
	test if a file or a directory is in "jail"
	@param	path	<b>string</b>	path
	@return	<b>boolean</b> BreadCrumb
	*/
	public static function inJail($path)
	{
		global $core;
		
		$root = $core->blog->settings->dlManager->dlmanager_root;
		
		if (!empty($root) && (strpos($path,$root) !== 0))
		{
			return false;
		}
		
		return true;
	}
	
	/**
	get a static record for items
	@param	array	<b>array</b>	path directory
	@return	<b>record</b> Items
	*/
	public static function getItems($array)
	{
		global $core;
		
		$count_dl = unserialize($core->blog->settings->dlManager->dlmanager_count_dl);
		if (!is_array($count_dl))
		{
			$count_dl = array();
		}
		
		$items = array();
				
		foreach ($array as $k => $v)
		{
			$dl = '0';
			if ($core->blog->settings->dlManager->dlmanager_counter)
			{
				if ((isset($v->media_id))
					&& (array_key_exists($v->media_id,$count_dl)))
				{
						$dl = $count_dl[$v->media_id];
				}
			}
			$items[] = array(
				'dir_url' => (isset($v->dir_url) ? $v->dir_url : ''),
				'relname' => (isset($v->relname) ? $v->relname : ''),
				'media_type' => (isset($v->media_type) ? $v->media_type : ''),
				'media_title' => (isset($v->media_title) ? $v->media_title : ''),
				'size' => (isset($v->size) ? $v->size : ''),
				'file' => (isset($v->file) ? $v->file : ''),
				'file_url' => (isset($v->file_url) ? $v->file_url : ''),
				'media_id' => (isset($v->media_id) ? $v->media_id : ''),
				'basename' => (isset($v->basename) ? $v->basename : ''),
				'extension' => (isset($v->extension) ? $v->extension : ''),
				'type' => (isset($v->type) ? $v->type : ''),
				'media_type' => (isset($v->media_type) ? $v->media_type : ''),
				'media_dtstr' => (isset($v->media_dtstr) ? $v->media_dtstr : ''),
				'media_thumb' => (isset($v->media_thumb) ? $v->media_thumb : ''),
				'media_meta' => (isset($v->media_meta) ? $v->media_meta : ''),
				'count_dl' => $dl,
			);
		}
		
		return(staticRecord::newFromArray($items));
	}
	
	/**
	get zip content (files) of a media item
	@param	array	<b>fileItem</b>	File item
	@return	<b>record</b> Files
	*/
	public static function getZipContent($item)
	{
		global $core;
		
		$files = array();
		
		$content = $core->media->getZipContent($item);
		
		foreach ($content as $file => $v)
		{
			$files[] = array('file' => $file);
		}
		
		return(staticRecord::newFromArray($files));
	}
	
	/**
	get image metadata a media item
	@param	array	<b>fileItem</b>	File item
	@return	<b>record</b> Image metadata
	*/
	public static function getImageMeta($item)
	{
		global $core;
		
		$meta = array();
				
		foreach ($item->media_meta as $k => $v)
		{
			if (!empty($v))
			{
				$meta[] = array(
					'name' => $k ,
					'value' => $v
				);
			}
		}
		
		return(staticRecord::newFromArray($meta));
	}
	
	/**
	find entries containing this media
	@param	path	<b>string</b>	path
	@return	<b>boolean</b> BreadCrumb
	*/
	public static function findPosts($id)
	{
		global $core;
		
		$file = $core->media->getFile($id);
		
		# from /dotclear/admin/media_item.php
		$params = array(
			'post_type' => '',
			'from' => 'LEFT OUTER JOIN '.$core->prefix.'post_media PM ON P.post_id = PM.post_id ',
			'sql' => 'AND ('.
				'PM.media_id = '.(integer) $id.' '.
				"OR post_content_xhtml LIKE '%".$core->con->escape($file->relname)."%' ".
				"OR post_excerpt_xhtml LIKE '%".$core->con->escape($file->relname)."%' "
		);
		
		if ($file->media_image)
		{ # We look for thumbnails too
			$media_root = $core->blog->host.path::clean($core->blog->settings->system->public_url).'/';
			foreach ($file->media_thumb as $v) {
				$v = preg_replace('/^'.preg_quote($media_root,'/').'/','',$v);
				$params['sql'] .= "OR post_content_xhtml LIKE '%".$core->con->escape($v)."%' ";
				$params['sql'] .= "OR post_excerpt_xhtml LIKE '%".$core->con->escape($v)."%' ";
			}
		}
		
		$params['sql'] .= ') ';
		
		$rs = $core->blog->getPosts($params);
		
		return $rs;
	}
}