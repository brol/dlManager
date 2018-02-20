<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DL Manager.
# Copyright 2008,2010 Moe (http://gniark.net/) and Tomtom (http://blog.zenstyle.fr)
#
# DL Manager is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# DL Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$page_title = __('Download Manager');

$settings =& $core->blog->settings;

try
{
	if (!empty($_POST['saveconfig']))
	{
		$settings->addNameSpace('dlManager');
		$settings->dlManager->put('dlmanager_active',!empty($_POST['dlmanager_active']),
			'boolean','Enable DL Manager');
		$settings->dlManager->put('dlmanager_hide_urls',!empty($_POST['dlmanager_hide_urls']),
			'boolean','Hide files URLs');
		$settings->dlManager->put('dlmanager_counter',!empty($_POST['dlmanager_counter']),
			'boolean','Enable download counter');
		$settings->dlManager->put('dlmanager_attachment_url',!empty($_POST['dlmanager_attachment_url']),
			'boolean','Redirect attachments links to DL Manager');
		
		if ((isset($_POST['dlmanager_nb_per_page']))
			&& (abs((integer) $_POST['dlmanager_nb_per_page']) > 0))
		{
			$nb_per_page = abs((integer) $_POST['dlmanager_nb_per_page']);
		}
		else
		{
			$nb_per_page = 20;
		}
		$settings->dlManager->put('dlmanager_nb_per_page',$nb_per_page,
		'integer','Files per page');
		$settings->dlManager->put('dlmanager_enable_sort',!empty($_POST['dlmanager_enable_sort']),
			'boolean','Allow visitors to choose how to sort files');
		$settings->dlManager->put('dlmanager_file_sort',
			(!empty($_POST['dlmanager_file_sort']) ? $_POST['dlmanager_file_sort'] : ''),
			'string','file sort');
		$settings->dlManager->put('dlmanager_root',
			(!empty($_POST['dlmanager_root']) ? $_POST['dlmanager_root'] : ''),
			'string', 'root directory');

		# empty the cache
		$core->blog->triggerBlog();
		
		http::redirect($p_url.'&saveconfig=1');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
}

?>
<html>
<head>
	<title><?php echo $page_title; ?></title>
</head>
<body>
<?php
	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));
if (!empty($msg)) {
  dcPage::success($msg);
}
?>
	<form method="post" action="<?php echo http::getSelfURI(); ?>">
		<div class="fieldset">

<?php
if ($core->blog->settings->dlManager->dlmanager_active) {
	echo '<p><a class="onblog_link outgoing" href="'.$core->blog->url.$core->url->getBase('media').'" title="'.__('View the Download Manager public page').'">'.__('View the Download Manager public page').' <img src="images/outgoing-blue.png" alt="" /></a></p>';
}
?>
		<h4><?php echo __('Plugin activation'); ?></h4>
      <p>
				<?php echo form::checkbox('dlmanager_active',1,
				$core->blog->settings->dlManager->dlmanager_active); ?>
				<label class="classic" for="dlmanager_active">
					<?php printf(__('Enable the %s'),__('Download manager')); ?>
				</label>
			</p>
			<p class="form-note">
				<?php printf(__('The %s display media on a public page.'),
					__('Download manager')); ?>
			</p>
	</div>
	<div class="fieldset">
		<h4><?php echo __('Advanced options'); ?></h4>
			<p>
				<?php echo form::checkbox('dlmanager_hide_urls',1,
					$core->blog->settings->dlManager->dlmanager_hide_urls); ?>
				<label class="classic" for="dlmanager_hide_urls">
					<?php echo __('Hide URLs of images, mp3, flv, mp4 and m4v files'); ?>
				</label>
			</p>
			<p class="form-note">
				<?php printf(__('The images, mp3, flv, mp4 and m4v files will be served without revealing their URLs in %1$s and %2$s tags.'),
				'<code>'.'&lt;img /&gt;'.'</code>','<code>'.'&lt;object&gt;&lt;/object&gt;'.'</code>');
				echo ' ';
				printf(__('The public directory (or its subdirectories) can be in a restricted area or protected by a %1$s file containing %2$s.'),
				'<strong>.htaccess</strong>','<strong>Deny from all</strong>'); ?>
			</p>
			<p>
				<?php echo form::checkbox('dlmanager_counter',1,
					$core->blog->settings->dlManager->dlmanager_counter); ?>
				<label class="classic" for="dlmanager_counter">
					<?php echo __('Enable the download counter'); ?>
				</label>
			</p>
			<p>
				<?php echo form::checkbox('dlmanager_attachment_url',1,
				$core->blog->settings->dlManager->dlmanager_attachment_url); ?>
				<label class="classic" for="dlmanager_attachment_url">
					<?php printf(__('Redirect attachments links to %s'),
				__('Download manager')); ?>
				</label>
			</p>
			<p class="form-note">
				<?php echo __('When downloading an attachment, the download counter will be increased.');
					echo ' ';
					printf(__('This will redefine the %s tag.'),
						'<strong>{{tpl:AttachmentURL}}</strong>');
					echo ' ';
					printf(__('The attachments must be located in the root of %s'),
						__('Download manager'));
				?>
			</p>
			<p>
				<label class="classic" for="dlmanager_nb_per_page">
				<?php echo __('Files per page:'); ?>
				</label> 
				<?php echo form::field('dlmanager_nb_per_page',7,7,
				(($core->blog->settings->dlManager->dlmanager_nb_per_page)
					? $core->blog->settings->dlManager->dlmanager_nb_per_page : 20)); ?>
			</p>
			<p>
				<?php echo form::checkbox('dlmanager_enable_sort',1,
					$core->blog->settings->dlManager->dlmanager_enable_sort); ?>
				<label class="classic" for="dlmanager_enable_sort">
					<?php echo __('Allow visitors to choose how to sort files'); ?>
				</label> 
			</p>
			<p>
			<label for="dlmanager_file_sort">
				<?php echo __('Sort files:').
					form::combo('dlmanager_file_sort',dlManager::getSortValues(true),
						$core->blog->settings->dlManager->dlmanager_file_sort); ?>
			</label> 
			</p>
			<p class="form-note">
				<?php echo __('Leave blank to disable this feature.'); ?>
			</p>
			<p>
				<label for="dlmanager_root">
				<?php printf(__('Define root of %s:'),__('Download manager'));
					echo form::combo('dlmanager_root',dlManager::listDirs(false,true),
						$core->blog->settings->dlManager->dlmanager_root); ?>
				</label> 
			</p>
			<p class="form-note">
				<?php echo __('Leave blank to disable this feature.').' ';
					printf(__('This will change the root of the %s page and of the widget.'),
					__('Download manager'));
					echo ' ';
					printf(__('If you change this setting, reconfigure the %s widget.'),
					__('Download manager')); ?>
			</p>
			<p>
				<!-- filemanager->$exclude_list is protected -->
				<?php printf(
				__('Files can be excluded from %1$s by editing %2$s in %3$s.'),
				__('Download manager'),'<strong>media_exclusion</strong>','<strong>about:config (system)</strong>');
				echo ' ';
				printf(__('For example, to exclude %1$s and %2$s files: %3$s'),
				'PNG','JPG','<code>/\.(png|jpg)/i</code>'); ?>
			</p>
			<p>
				<?php printf(__('URL of the %s page:'),__('Download manager')); ?>
			<br />
			<code><?php echo dlManager::pageURL(); ?></code>
			</p>
		</div>
		
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>

<?php dcPage::helpBlock('dlManager');?>

</body>
</html>