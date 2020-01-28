<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('BlogHelperRoute', JPATH_SITE . '/components/com_blog/helpers/route.php');
JLoader::register('BlogHelperQuery', JPATH_SITE . '/components/com_blog/helpers/query.php');
JLoader::register('BlogHelperAssociation', JPATH_SITE . '/components/com_blog/helpers/association.php');

$input = JFactory::getApplication()->input;
$user  = JFactory::getUser();

$checkCreateEdit = ($input->get('view') === 'articles' && $input->get('layout') === 'modal')
	|| ($input->get('view') === 'article' && $input->get('layout') === 'pagebreak');

if ($checkCreateEdit)
{
	// Can create in any category (component permission) or at least in one category
	$canCreateRecords = $user->authorise('core.create', 'com_blog')
		|| count($user->getAuthorisedCategories('com_blog', 'core.create')) > 0;

	// Instead of checking edit on all records, we can use **same** check as the form editing view
	$values = (array) JFactory::getApplication()->getUserState('com_blog.edit.article.id');
	$isEditingRecords = count($values);

	$hasAccess = $canCreateRecords || $isEditingRecords;

	if (!$hasAccess)
	{
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

		return;
	}
}

$controller = JControllerLegacy::getInstance('Blog');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
