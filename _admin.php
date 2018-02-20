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

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$_menu['Blog']->addItem(__('Download Manager'),
	'plugin.php?p=dlManager',
	'index.php?pf=dlManager/icon.png',
	preg_match('/plugin.php\?p=dlManager(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

#dashboard
$core->addBehavior('adminDashboardFavorites','dlManagerDashboardFavorites');

function dlManagerDashboardFavorites($core,$favs)
{
	$favs->register('dlManager', array(
		'title' => __('Download Manager'),
		'url' => 'plugin.php?p=dlManager',
		'small-icon' => 'index.php?pf=dlManager/icon.png',
		'large-icon' => 'index.php?pf=dlManager/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}
