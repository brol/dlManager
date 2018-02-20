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

if (!$core->blog->settings->dlManager->dlmanager_active) {return;}

/**
@ingroup Download manager
@brief Document
*/
class dlManagerPageDocument extends dcUrlHandlers
{
	private static function check()
	{
		
		global $core;
		
		# if the plugin is disabled
		if (!$core->blog->settings->dlManager->dlmanager_active)
		{
			self::p404();
			return;
		}
		
		# exit if the public_path (and Media root) doesn't exist
		if (!is_dir($core->blog->public_path))
		{
			self::p404();
			return;
		}
	}
	
	/**
	serve the document
	@param	args	<b>string</b>	Argument
	*/
	public static function page($args)
	{
		global $core;

		self::check();
		
		# start session
		$session_id = session_id();
		if (empty($session_id)) {session_start();}
		
		$_ctx =& $GLOBALS['_ctx'];
		
		try
		{
			# define root of DL Manager
			$page_root = $core->blog->settings->dlManager->dlmanager_root;

			# used to remove root from path
			$page_root_len = strlen($page_root);
			
			# remove slash at the beginning of the string
			if ($page_root_len > 0) {$page_root_len += 1;}
			
			$page_dir = $page_root;

			$dir = '/';
			$_ctx->dlManager_currentDir = __('Home');
			
			$root = true;
			
			# if the visitor request a directory
			if ((!empty($args)) && (substr($args,0,1) == '/'))
			{
				$_ctx->dlManager_currentDir = substr($args,1);
				$dir = substr($args,1);
				$page_dir = $page_root.'/'.$dir;
				$root = false;
			}
			
			# BreadCrumb
			$_ctx->dlManager_BreadCrumb = dlManager::breadCrumb($dir);
			# /BreadCrumb
			
			# file sort
			# if visitor can choose how to sort files
			if ($core->blog->settings->dlManager->dlmanager_enable_sort === true)
			{
				# from /dotclear/admin/media.php
				if ((!empty($_POST['media_file_sort']))
					&& (in_array($_POST['media_file_sort'],dlManager::getSortValues())))
				{
					$_SESSION['media_file_sort'] = $_POST['media_file_sort'];
				}
				if (!empty($_SESSION['media_file_sort']))
				{
					$core->media->setFileSort($_SESSION['media_file_sort']);
					$_ctx->dlManager_fileSort = $_SESSION['media_file_sort'];
				}
				# /from /dotclear/admin/media.php
			}
			else
			{
				# default value
				$_ctx->dlManager_fileSort = $core->blog->settings->dlManager->dlmanager_file_sort;
			}

			# exit if the directory doesn't exist
			$dir_full_path = $core->media->root.'/'.$page_dir;
			if (!is_dir($dir_full_path))
			{
				self::p404();
				return;
			}
			
			# used to remove link to root directory
			$parent_dir_full_path = path::real(dirname($dir_full_path));
			
			# get the content of the directory
			$core->media->setFileSort($_ctx->dlManager_fileSort);
			
			$core->media->chdir($page_dir);
			$core->media->getDir();			
			
			# get relative paths from root of DL Manager
			foreach ($core->media->dir['dirs'] as $k => $v)
			{
				$item =& $core->media->dir['dirs'][$k];
				$item->media_type = 'folder';
				
				# if the current page is the root
				if ($root && ($item->file == $parent_dir_full_path))
				{
					# remove link to root directory
					unset($core->media->dir['dirs'][$k]);
				}
				else
				{
					$item->relname = substr($item->relname,$page_root_len);
					
					# rename link to parent directory
					if ($item->file == $parent_dir_full_path)
					{
						$item->basename = __('parent directory');
					}
				}
			}
			
			$_ctx->dlManager_dirs = dlManager::getItems($core->media->dir['dirs']);
			unset($core->media->dir['dirs']);
			
			# pager
			$files =& $core->media->dir['files'];
			
			$_ctx->dlManager_multiple_pages = (boolean) (count($files) >
				$core->blog->settings->dlManager->dlmanager_nb_per_page);
			
			$_ctx->dlManager_pager = new pager(
				# current page
				((isset($_GET['page'])) ? $_GET['page'] : 1),count($files),
				$core->blog->settings->dlManager->dlmanager_nb_per_page,10);
			
			$_ctx->dlManager_pager->html_prev = '&#171; '.__('previous');
			$_ctx->dlManager_pager->html_next = __('next').' &#187;';
			$_ctx->dlManager_pager->var_page = 'page';
			$_ctx->dlManager_pager->html_link_sep = ' ';
			$_ctx->dlManager_pager->html_prev_grp = '&#8230;';
			$_ctx->dlManager_pager->html_next_grp = '&#8230;';
			
			$files_array = array();
			
			for ($i=$_ctx->dlManager_pager->index_start, $j=0;
				$i<=$_ctx->dlManager_pager->index_end; $i++, $j++)
			{
				$item =& $files[$i];

				$item->relname = substr($item->relname,$page_root_len);
				
				$files_array[] = $item;
			}
			$_ctx->dlManager_files = dlManager::getItems($files_array);
			unset($core->media->dir['files'],$files_array);
			# /pager
			
			unset($files_array);
		}
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}

		$tplset = $core->themes->moduleInfo($core->blog->settings->system->theme,'tplset');
        if (!empty($tplset) && is_dir(dirname(__FILE__).'/default-templates/'.$tplset)) {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates/'.$tplset);
        } else {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates/'.DC_DEFAULT_TPLSET);
        }

		self::serveDocument('media.html','text/html',true,false);
	}

	/**
	serve the media player document
	@param	args	<b>string</b>	Argument
	*/
	public static function player($args)
	{
		global $core;

		self::check();

		$_ctx =& $GLOBALS['_ctx'];
		
		try
		{			
			$file = $core->media->getFile($args);
			
			if ((empty($file->file)) || (!is_readable($file->file)))
			{
				self::p404();
				return;
			}
			
			# file_url for mp3, flv, mp4 and m4v players
			if ($core->blog->settings->dlManager->dlmanager_hide_urls)
			{
				$_ctx->file_url = $core->blog->url.$core->url->getBase('viewfile').
				'/'.$file->media_id;
			}
			else
			{
				$_ctx->file_url = $file->file_url;
			}
			
			# define root of DL Manager
			$page_root = $core->blog->settings->dlManager->dlmanager_root;
			
			# used to remove root from path
			$page_root_len = strlen($page_root);
			
			# remove slash at the beginning of the string
			if ($page_root_len > 0) {$page_root_len += 1;}
			
			if (!dlManager::inJail($file->relname))
			{
				self::p404();
				return;
			}
		  
		  $file->relname =
				dirname(substr($file->relname,$page_root_len));
			if ($file->relname == '.')
			{
				$file->relname = '';
			}
			
			# BreadCrumb
			$_ctx->dlManager_BreadCrumb = dlManager::breadCrumb($file->relname);
			# /BreadCrumb
			
			# get static record
			$files = array();
			$files[] = $file;		
			$_ctx->items = dlManager::getItems($files);
			unset($files);
			# /get static record
			
			$tplset = $core->themes->moduleInfo($core->blog->settings->system->theme,'tplset');
        if (!empty($tplset) && is_dir(dirname(__FILE__).'/default-templates/'.$tplset)) {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates/'.$tplset);
        } else {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates/'.DC_DEFAULT_TPLSET);
        }
			
			self::serveDocument('media_player.html','text/html',true,false);
		}
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}
	}
	
	/**
	serve file
	@param	args	<b>string</b>	Argument
	@param	count	<b>boolean</b>	Count download
	*/
	public static function download($args)
	{
		global $core;
		
		self::check();
		
		if (!preg_match('/^[0-9]+$/',$args))
		{
			self::p404();
			return;
		}
		
		try
		{
			$file = $core->media->getFile($args);
			
			if (empty($file->file))
			{
				self::p404();
				return;
			}
			
			if (!dlManager::inJail($file->relname))
			{
				self::p404();
				return;
			}
		  
			if (is_readable($file->file))
			{
				if ($core->blog->settings->dlManager->dlmanager_counter)
				{
					$count = unserialize($core->blog->settings->dlManager->dlmanager_count_dl);
					if (!is_array($count)) {$count = array();}
					$count[$file->media_id] = array_key_exists($file->media_id,$count)
						? $count[$file->media_id]+1 : 1;
					
					$settings =& $core->blog->settings;
					
					$settings->addNamespace('dlManager');
					$settings->dlManager->put('dlmanager_count_dl',serialize($count),'string',
						'Download counter');
					//$core->callBehavior('publicDownloadedFile',(integer)$args);
				}
				http::$cache_max_age = 36000;
				http::cache(array_merge(array($file->file),get_included_files()));
				header('Content-type: '.$file->type);
				header('Content-Length: '.$file->size);
				header('Content-Disposition: attachment; filename="'.$file->basename.'"');
				readfile($file->file);
				exit;
				# header('Location:'.$file->file_url);
				exit;
			}
			else
			{
				self::p404();
				return;
			}
		}
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}
	}
	
	/**
	serve a file without incrementing the download counter
	@param	args	<b>string</b>	Argument
	*/
	public static function viewfile($args)
	{
		global $core;
		
		if (!$GLOBALS['core']->blog->settings->dlManager->dlmanager_hide_urls
		|| empty($args) || !$core->blog->settings->dlManager->dlmanager_active)
		{
			self::p404();
			return;
		}
		
		try
		{			
			# standard file
			if (preg_match('/^[0-9]+$/',$args,$matches))
			{
				$file = $core->media->getFile($matches[0]);
				
				if (empty($file->file) || ($file->type != 'audio/mpeg3'
					&& $file->type != 'video/x-flv' && $file->type != 'video/mp4'
					&& $file->type != 'video/x-m4v' && $file->media_type != 'image'))
				{
					self::p404();
					return;
				}
				
				$file_path = $file->file;
			}
			# image thumbnail
			# \see http://fr.php.net/preg_match
			elseif (preg_match('@^([0-9]+)/(m|s|t|sq)$@',$args,$matches))
			{
				$file_id = $matches[1];
				$size = $matches[2];
				
				$file = $core->media->getFile($file_id);
				
				# check that the file is an image and the requested size is valid
				if ((empty($file->file)) || ($file->media_type != 'image')
					|| !array_key_exists($size,$core->media->thumb_sizes))
				{
					self::p404();
					return;
				}
				
				if (isset($file->media_thumb[$size]))
				{
					# get the directory of the file and the filename of the thumbnail
					$file_path = dirname($file->file).'/'.basename($file->media_thumb[$size]);
				} else
				{
					$file_path = $file->file;
				}
			}
			else
			{
				self::p404();
				return;
			}
			
			if ((!dlManager::inJail($file->relname)) || (!is_readable($file_path)))
			{
				self::p404();
				return;
			}
			
			http::$cache_max_age = 36000;
			http::cache(array_merge(array($file_path),get_included_files()));
			header('Content-type: '.$file->type);
			header('Content-Length: '.filesize($file_path));
			readfile($file_path);
			exit;
		}
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}
	}
}

$core->tpl->addValue('DLMCurrentDir',array('dlManagerPageTpl','currentDir'));

# sort files
$core->tpl->addBlock('DLMIfSortIsEnabled',array('dlManagerPageTpl',
	'ifSortIsEnabled'));

$core->tpl->addValue('DLMFileSortOptions',array('dlManagerPageTpl',
	'fileSortOptions'));

# Bread Crumb
$core->tpl->addValue('DLMBaseURL',array('dlManagerPageTpl','baseURL'));

$core->tpl->addBlock('DLMBreadCrumb',array('dlManagerPageTpl','breadCrumb'));
$core->tpl->addValue('DLMBreadCrumbDirName',array('dlManagerPageTpl',
	'breadCrumbDirName'));
$core->tpl->addValue('DLMBreadCrumbDirURL',array('dlManagerPageTpl',
	'breadCrumbDirURL'));

# items
$core->tpl->addBlock('DLMItems',array('dlManagerPageTpl','items'));

$core->tpl->addBlock('DLMIfNoItem',array('dlManagerPageTpl','ifNoItem'));

$core->tpl->addBlock('DLMIfPages',array('dlManagerPageTpl','ifPages'));

# item
$core->tpl->addBlock('DLMItemIf',array('dlManagerPageTpl','itemIf'));
$core->tpl->addValue('DLMItemDirURL',array('dlManagerPageTpl','itemDirURL'));
$core->tpl->addValue('DLMItemDirPath',array('dlManagerPageTpl','itemDirPath'));

$core->tpl->addValue('DLMItemTitle',array('dlManagerPageTpl','itemTitle'));
$core->tpl->addValue('DLMItemSize',array('dlManagerPageTpl','itemSize'));
$core->tpl->addValue('DLMItemFileURL',array('dlManagerPageTpl','itemFileURL'));
$core->tpl->addValue('DLMItemDlURL',array('dlManagerPageTpl','itemDlURL'));
$core->tpl->addValue('DLMItemPlayerURL',array('dlManagerPageTpl','itemPlayerURL'));

$core->tpl->addValue('DLMItemBasename',array('dlManagerPageTpl',
	'itemBasename'));
$core->tpl->addValue('DLMItemExtension',array('dlManagerPageTpl',
	'itemExtension'));
$core->tpl->addValue('DLMItemType',array('dlManagerPageTpl','itemType'));
$core->tpl->addValue('DLMItemMediaType',array('dlManagerPageTpl',
	'itemMediaType'));
$core->tpl->addValue('DLMItemMTime',array('dlManagerPageTpl','itemMTime'));
$core->tpl->addValue('DLMItemDlCount',array('dlManagerPageTpl','itemDlCount'));
$core->tpl->addValue('DLMItemImageThumbPath',array('dlManagerPageTpl',
	'itemImageThumbPath'));

$core->tpl->addBlock('DLMIfDownloadCounter',array('dlManagerPageTpl','ifDownloadCounter'));

# image meta
$core->tpl->addBlock('DLMItemImageMeta',array('dlManagerPageTpl',
	'itemImageMeta'));
$core->tpl->addValue('DLMItemImageMetaName',array('dlManagerPageTpl',
	'itemImageMetaName'));
$core->tpl->addValue('DLMItemImageMetaValue',array('dlManagerPageTpl',
	'itemImageMetaValue'));

# zip content
$core->tpl->addBlock('DLMItemZipContent',array('dlManagerPageTpl',
	'itemZipContent'));
$core->tpl->addValue('DLMItemZipContentFile',array('dlManagerPageTpl',
	'itemZipContentFile'));

# text file content
$core->tpl->addValue('DLMItemFileContent',array('dlManagerPageTpl',
	'itemFileContent'));

# find entries containing a media
$core->tpl->addBlock('DLMItemEntries',array('dlManagerPageTpl',
	'itemEntries'));

# 
$core->tpl->addValue('DLMPageLinks',array('dlManagerPageTpl',
	'pageLinks'));

if ($core->blog->settings->dlManager->dlmanager_attachment_url)
{
	# redefine {{tpl:AttachmentURL}}
	$core->tpl->addValue('AttachmentURL',array('dlManagerPageTpl',
		'AttachmentURL'));
}

/**
@ingroup Download manager
@brief Template
*/
class dlManagerPageTpl
{
	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function currentDir()
	{
		return("<?php echo(\$_ctx->dlManager_currentDir); ?>");
	}

	/**
	if sort is enabled
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifSortIsEnabled($attr,$content)
	{
		return
		'<?php if ($core->blog->settings->dlManager->dlmanager_enable_sort === true) : ?>'."\n".
		$content.
		'<?php endif; ?>';
	}

	/**
	display file sort <select ...><option ...>
	@return	<b>string</b> PHP block
	*/
	public static function fileSortOptions()
	{
		return('<?php echo form::combo(\'media_file_sort\',
			dlManager::getSortValues(),$_ctx->dlManager_fileSort); ?>');
	}
	
	/**
	display base URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function baseURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo('.sprintf($f,'dlManager::pageURL()').'); ?>');
	}
	
	/**
	BreadCrumb
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumb($attr,$content)
	{
		return('<?php while ($_ctx->dlManager_BreadCrumb->fetch()) : ?>'.
			$content.
		'<?php endwhile; ?>');
	}
	
	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumbDirURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo('.sprintf($f,'$_ctx->dlManager_BreadCrumb->url').'); ?>');
	}

	/**
	display current directory
	@return	<b>string</b> PHP block
	*/
	public static function breadCrumbDirName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo('.sprintf($f,'$_ctx->dlManager_BreadCrumb->name').'); ?>');
	}
	
	/**
	No item
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifNoItem($attr,$content)
	{
		$type = ($attr['type'] == 'dirs') ? 'dirs' : 'files';

		return('<?php if ($_ctx->{\'dlManager_'.$type.'\'}->isEmpty()) : ?>'.
		$content.
		'<?php endif; ?>');
	}
	
	/**
	If there is more than one page
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifPages($attr,$content)
	{
		return('<?php if ($_ctx->dlManager_multiple_pages) : ?>'.
		$content.
		'<?php endif; ?>');
	}
	
	/**
	loop on items
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function items($attr,$content)
	{
		$type = (($attr['type'] == 'dirs') ? 'dirs' : 'files');
		
		return
		'<?php '.
		'$_ctx->items = $_ctx->{\'dlManager_'.$type.'\'}; '.
		'while ($_ctx->items->fetch()) : ?>'."\n".
		$content.
		'<?php endwhile; unset($_ctx->items); ?>';
	}
	
	/**
	Item directory URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemDirURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return('<?php echo '.sprintf($f,'$_ctx->items->dir_url').'; ?>');
	}
	
	/**
	Item directory path
	@return	<b>string</b> PHP block
	*/
	public static function itemDirPath()
	{
		global $core;
		return('<?php echo '.
			# empty can't be used with $_ctx->items->relname, use strlen() instead
			'dlManager::pageURL().'.'((strlen($_ctx->items->relname) > 0) ?'.
			'\'/\'.$_ctx->items->relname : \'\'); ?>');
	}
	
	/**
	Item if
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	\see /dotclear/inc/public/class.dc.template.php > EntryIf()
	*/
	public static function itemIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';

		if (isset($attr['type'])) {
			$type = trim($attr['type']);
			$sign = '=';
			if (substr($type,0,1) == '!')
			{
				$sign = '!';
				$type = substr($type,1);
			}
			$types = explode(',',$type);
			foreach ($types as $type)
			{
				$if[] = '$_ctx->items->type '.$sign.'= "'.$type.'"';
			}
		}
		
		if (isset($attr['media_type'])) {
			$type = trim($attr['media_type']);
			$sign = '=';
			if (substr($type,0,1) == '!')
			{
				$sign = '!';
				$type = substr($type,1);
			}
			$types = explode(',',$type);
			foreach ($types as $type)
			{
				$if[] = '$_ctx->items->media_type '.$sign.'= "'.$type.'"';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.
				$content.
				'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/**
	Get operator
	@param	op	<b>string</b>	Operator
	@return	<b>string</b> Operator
	\see /dotclear/inc/public/class.dc.template.php > getOperator()
	*/
	protected static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
	
	/**
	Item title
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function itemTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->media_title').'; ?>');
	}
	
	/**
	Item size
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemSize($attr)
	{
		$format_open = $format_close = '';
		if (isset($attr['format']) && $attr['format'] == '1')
		{
			$format_open =  'files::size(';
			$format_close = ')';
		}
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo '.sprintf($f,
			$format_open.'$_ctx->items->size'.$format_close).'; ?>');
	}
	
	/**
	Item file URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemFileURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if ($GLOBALS['core']->blog->settings->dlManager->dlmanager_hide_urls)
		{
			return('<?php echo($core->blog->url.'.
			'$core->url->getBase(\'viewfile\').\'/\'.'.
			sprintf($f,'$_ctx->items->media_id').'); ?>');
		}
		return('<?php echo '.sprintf($f,'$_ctx->items->file_url').'; ?>');
	}
	
	/**
	Item download URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemDlURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo($core->blog->url.$core->url->getBase(\'download\').'.
			'\'/\'.'.sprintf($f,'$_ctx->items->media_id').'); ?>');
	}
	
	/**
	Item player URL
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemPlayerURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return('<?php echo($core->blog->url.$core->url->getBase(\'mediaplayer\').'.
			'\'/\'.'.sprintf($f,'$_ctx->items->media_id').'); ?>');
	}
	
	/**
	Item basename
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemBasename($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->basename').'; ?>');
	}
	
	/**
	Item extension
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemExtension($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->extension').'; ?>');
	}
	
	/**
	Item type : text/plain
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->type').'; ?>');
	}

	/**
	Item media type : text
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemMediaType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->items->media_type').'; ?>');
	}

	/**
	Item mtime
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemMTime($attr)
	{
		global $core;
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$str = '$_ctx->items->media_dtstr';
		
		if (isset($attr['format']))
		{
			if ($attr['format'] == 'date_format')
			{
				$format = $GLOBALS['core']->blog->settings->system->date_format;
			}
			elseif ($attr['format'] == 'time_format')
			{
				$format = $GLOBALS['core']->blog->settings->system->time_format;
			}
			else
			{
				$format = $attr['format'];
			}
			
			$str = 'dt::dt2str(\''.$format.'\','.$str.')';
		}
		
		return('<?php echo '.sprintf($f,$str).'; ?>');
	}
	
	/**
	Item download counter
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemDlCount()
	{
		return('<?php echo $_ctx->items->count_dl; ?>');
	}

	/**
	Test if the download counter is active
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content of the loop
	@return	<b>string</b> PHP block
	*/
	public static function ifDownloadCounter($attr,$content)
	{
		return('<?php if ($core->blog->settings->dlManager->dlmanager_counter) : ?>'.
		$content.
		'<?php endif; ?>');
	}
	
	/**
	Item image thumbnail
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemImageThumbPath($attr)
	{
		global $core;

		$size = 'sq';

		if ((isset($attr['size']))
			&& array_key_exists($attr['size'],$core->media->thumb_sizes))
		{$size = $attr['size'];}
		
		if ($GLOBALS['core']->blog->settings->dlManager->dlmanager_hide_urls)
		{
			return('<?php '.
			'echo($core->blog->url.$core->url->getBase(\'viewfile\').\'/\'.'.
			'$_ctx->items->media_id.\'/'.$size.'\'); ?>');
		}
		return('<?php if (isset($_ctx->items->media_thumb[\''.$size.'\'])) :'.
		'echo($_ctx->items->media_thumb[\''.$size.'\']);'.
		'else :'.
		'echo($_ctx->items->file_url);'.
		'endif; ?>');
	}
	
	/**
	Loop on image meta
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content of the loop
	@return	<b>string</b> PHP block
	*/
	public static function itemImageMeta($attr,$content)
	{
		return
		'<?php '.
		'$_ctx->imagemeta = dlManager::getImageMeta($_ctx->items); '.
		'while ($_ctx->imagemeta->fetch()) : ?>'."\n".
		$content.
		'<?php endwhile; unset($_ctx->imagemeta); ?>';
	}
	
	/**
	Image meta name
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemImageMetaName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->imagemeta->name').'; ?>');
	}
	
	/**
	Image meta value
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemImageMetaValue($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->imagemeta->value').'; ?>');
	}
	
	/**
	Loop on zip content
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemZipContent($attr,$content)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return
		'<?php '.
		'$_ctx->files = dlManager::getZipContent($_ctx->items); '.
		'while ($_ctx->files->fetch()) : ?>'."\n".
		$content.
		'<?php endwhile; unset($_ctx->files); ?>';
	}
	
	/**
	Zip content file
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function itemZipContentFile($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo '.sprintf($f,'$_ctx->files->file').'; ?>');
	}
	
	/**
	Text file content
	@return	<b>string</b> PHP block
	*/
	public static function itemFileContent($attr)
	{	
		return('<?php if ((is_readable($_ctx->items->file)) '.
		'&& ($_ctx->items->size < 1000000)) : '.
		'echo html::escapeHTML(file_get_contents($_ctx->items->file));'.
		'endif; ?>');
	}
	
	/**
	loop on posts which contain this item
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function itemEntries($attr,$content)
	{
		return("<?php ".
		'$_ctx->posts = dlManager::findPosts($_ctx->items->media_id);'.
		"while (\$_ctx->posts->fetch()) : ?>"."\n".
		$content.
		"<?php endwhile; unset(\$_ctx->posts); ?>");
	}
	
	/**
	redefine {{tpl:AttachmentURL}} to point to download/id
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function AttachmentURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return('<?php echo($core->blog->url.$core->url->getBase(\'download\').'.
			'\'/\'.'.sprintf($f,'$attach_f->media_id').'); ?>');
	}
	
	/**
	get page links
	@return	<b>string</b> PHP block
	*/
	public static function pageLinks()
	{
		return('<?php echo($_ctx->dlManager_pager->getLinks()); ?>');
	}
}

$core->addBehavior('publicBreadcrumb',array('extdlManager','publicBreadcrumb'));

class extdlManager
{
    public static function publicBreadcrumb($context,$separator)
    {
        if ($context == 'media') {
            return __('Download manager');
        }
    }
}

$core->addBehavior('publicBreadcrumb',array('extdlManagerpreview','publicBreadcrumb'));

class extdlManagerpreview
{
    public static function publicBreadcrumb($context,$separator)
    {
        if ($context == 'mediaplayer') {
            return __('Download manager - Preview');
        }
    }
}