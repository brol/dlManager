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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('dlManager','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('dlManager');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# change namespace of settings
$cur = $core->con->openCursor($core->prefix.'setting');
$cur->setting_ns = 'publicmedia';
$cur->update('WHERE (setting_id LIKE \'publicmedia_%\') '.
	'AND setting_ns = \'system\';');

# DL Manager
# rename Public Media settings
$core->con->execute('UPDATE '.$core->prefix.'setting SET '.
'setting_id = replace(setting_id,\'publicmedia_page\',\'dlmanager\') '.
'WHERE (setting_id LIKE \'publicmedia_%\');');

# change namespace of settings
$cur = $core->con->openCursor($core->prefix.'setting');
$cur->setting_ns = 'dlmanager';
$cur->update('WHERE setting_ns = \'publicmedia\';');

# La procédure d'installation commence vraiment là
$core->setVersion('dlManager',$m_version);
return true;