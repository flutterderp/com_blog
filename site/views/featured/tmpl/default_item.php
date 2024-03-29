<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

// Create a shortcut for params.
$params  = &$this->item->params;
$canEdit = $this->item->params->get('access-edit');
$info    = $this->item->params->get('info_block_position', 0);

// Check if associations are implemented. If they are, define the parameter.
$assocParam = (Associations::isEnabled() && $params->get('show_associations'));

$currentDate          = Factory::getDate()->format('Y-m-d H:i:s');
$nullDate             = Factory::getDbo()->getNullDate();
$conditionUnpublished = $this->item->state == ((Version::MAJOR_VERSION === 4) ? ContentComponent::CONDITION_UNPUBLISHED : 0);
$isNotPublishedYet    = $this->item->publish_up > $currentDate;
$isExpired            = !is_null($this->item->publish_down) && $this->item->publish_down !== $nullDate && $this->item->publish_down < $currentDate;
$isUnpublished        = $this->item->state == $conditionUnpublished || $isNotPublishedYet || $isExpired;
?>

<?php if ($isUnpublished) : ?>
	<div class="system-unpublished">
<?php endif; ?>

<?php if ($params->get('show_title')) : ?>
	<h2 class="item-title" itemprop="headline">
	<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
		<a href="<?php echo Route::_(BlogHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)); ?>" itemprop="url">
			<?php echo $this->escape($this->item->title); ?>
		</a>
	<?php else : ?>
		<?php echo $this->escape($this->item->title); ?>
	<?php endif; ?>
	</h2>
<?php endif; ?>

<?php if ($this->item->state == $conditionUnpublished) : ?>
	<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
<?php endif; ?>
<?php if ($isNotPublishedYet) : ?>
	<span class="label label-warning"><?php echo Text::_('JNOTPUBLISHEDYET'); ?></span>
<?php endif; ?>
<?php if ($isExpired) : ?>
	<span class="label label-warning"><?php echo Text::_('JEXPIRED'); ?></span>
<?php endif; ?>

<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
	<?php echo LayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $this->item, 'print' => false)); ?>
<?php endif; ?>

<?php // Blog is generated by content plugin event "onContentAfterTitle" ?>
<?php echo $this->item->event->afterDisplayTitle; ?>

<?php // Todo Not that elegant would be nice to group the params ?>
<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
	|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam); ?>

<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
	<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
	<?php echo LayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
<?php endif; ?>
<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
	<?php echo LayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
<?php endif; ?>

<?php echo LayoutHelper::render('joomla.content.intro_image', $this->item); ?>

<?php // Blog is generated by content plugin event "onContentBeforeDisplay" ?>
<?php echo $this->item->event->beforeDisplayContent; ?>

<?php echo $this->item->introtext; ?>

<?php if ($info == 1 || $info == 2) : ?>
	<?php if ($useDefList) : ?>
		<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
		<?php echo LayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
	<?php endif; ?>
	<?php if ($params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
		<?php echo LayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
	<?php endif; ?>
<?php endif; ?>

<?php if ($params->get('show_readmore') && $this->item->readmore) :
	if ($params->get('access-view')) :
		$link = Route::_(BlogHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
	else :
		$menu = Factory::getApplication()->getMenu();
		$active = $menu->getActive();
		$itemId = $active->id;
		$link = new Uri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
		$link->setVar('return', base64_encode(BlogHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)));
	endif; ?>

	<?php echo LayoutHelper::render('joomla.content.readmore', array('item' => $this->item, 'params' => $params, 'link' => $link)); ?>

<?php endif; ?>

<?php if ($isUnpublished) : ?>
	</div>
<?php endif; ?>

<?php // Blog is generated by content plugin event "onContentAfterDisplay" ?>
<?php echo $this->item->event->afterDisplayContent; ?>
