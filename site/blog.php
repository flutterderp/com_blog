<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/helpers/route.php';
require_once JPATH_COMPONENT . '/helpers/query.php';

$input	= JFactory::getApplication()->input;
$user		= JFactory::getUser();

if ($input->get('view') === 'article' && $input->get('layout') === 'pagebreak')
{
	if (!$user->authorise('core.edit', 'com_blog'))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		
		return;
	}
}
elseif ($input->get('view') === 'articles' && $input->get('layout') === 'modal')
{
	if (!$user->authorise('core.edit', 'com_blog'))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		
		return;
	}
}

$controller = JControllerLegacy::getInstance('Blog');
$controller->execute($input->get('task'));
$controller->redirect();
