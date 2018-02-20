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

$__autoload['dlManager'] = dirname(__FILE__).'/inc/lib.dlManager.php';

require_once(dirname(__FILE__).'/_widget.php');

$core->blog->settings->addNameSpace('dlManager');

if ($core->blog->settings->dlManager->dlmanager_active)
{
	$core->url->register('media','media',
		'^media(/.+)?$',array('dlManagerPageDocument','page'));
	$core->url->register('mediaplayer','mediaplayer',
		'^mediaplayer/([0-9]+)$',array('dlManagerPageDocument','player'));
	$core->url->register('download','download',
		'^download/([0-9]+)$',array('dlManagerPageDocument','download'));
	$core->url->register('viewfile','viewfile',
		'^viewfile/(.+)$',array('dlManagerPageDocument','viewfile'));
}
