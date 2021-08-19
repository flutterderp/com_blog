<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$app         = Factory::getApplication();
$option      = $app->input->get('option', 'com_content', 'string');
$component   = ucwords(str_ireplace('com_', '', $option));
$helperRoute = $component . 'HelperRoute';
?>
<ol class="nav nav-tabs nav-stacked">
<?php foreach ($displayData->get('link_items') as $item) : ?>
	<li>
		<?php echo HTMLHelper::_('link', Route::_($helperRoute::getArticleRoute($item->slug, $item->catid, $item->language)), $item->title); ?>
	</li>
<?php endforeach; ?>
</ol>
