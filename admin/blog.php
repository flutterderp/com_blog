<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

if(Version::MAJOR_VERSION < 4)
{
	HTMLHelper::_('behavior.tabstate');
}

if (!Factory::getUser()->authorise('core.manage', 'com_blog'))
{
	throw new Access\Exception\NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

JLoader::register('BlogHelper', __DIR__ . '/helpers/blog.php');

$controller = JControllerLegacy::getInstance('Blog');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
