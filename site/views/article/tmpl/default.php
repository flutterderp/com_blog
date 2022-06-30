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
// use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

// Create shortcuts to some parameters.
$app        = Factory::getApplication();
$doc        = Factory::getDocument();
$user       = Factory::getUser();
$tpl        = $app->getTemplate($tpl_params = true);
$tpl_params = $tpl->params;
$params     = $this->item->params;
$urls       = json_decode($this->item->urls);
$canEdit    = $params->get('access-edit');
$info       = $params->get('info_block_position', 0);

// Check if associations are implemented. If they are, define the parameter.
$assocParam           = (Associations::isEnabled() && $params->get('show_associations'));
$currentDate          = Factory::getDate()->format('Y-m-d H:i:s');
$conditionUnpublished = (Version::MAJOR_VERSION === 4) ? ContentComponent::CONDITION_UNPUBLISHED : 0;
$isNotPublishedYet    = $this->item->publish_up > $currentDate;
$isExpired            = !is_null($this->item->publish_down) && $this->item->publish_down < $currentDate && $this->item->publish_down !== Factory::getDbo()->getNullDate();

if(Version::MAJOR_VERSION < 4)
{
	HTMLHelper::_('behavior.caption');
}

foreach($this->item->jcfields as $key => $field)
{
	$jcfields[$field->name] = $field;
}
?>
<div class="item-page<?php echo $this->pageclass_sfx; ?>" itemscope itemtype="https://schema.org/Article">
	<meta itemprop="inLanguage" content="<?php echo ($this->item->language === '*') ? Factory::getConfig()->get('language') : $this->item->language; ?>" />
	<link itemprop="mainEntityOfPage" href="<?php echo Route::_(BlogHelperRoute::getArticleRoute($this->item->slug, $this->item->catid)); ?>">
	<meta itemprop="author" content="<?php echo $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>">
	<span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
		<meta itemprop="name" content="<?php echo $this->escape($tpl_params->get('publisher_name', $this->item->author)); ?>">
		<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
			<meta itemprop="url" content="<?php echo $tpl_params->get('logo', 'https://via.placeholder.com/150x150/ccc/ccc.png?text=logo'); ?>">
		</span>
	</span>

	<?php if(!$params->get('show_modify_date')) : ?>
		<time datetime="<?php echo HTMLHelper::_('date', ($this->item->modified ? $this->item->modified : $this->item->publish_up), 'c'); ?>" itemprop="dateModified"></time>
	<?php endif; ?>

	<?php if(!$params->get('show_publish_date')) : ?>
		<time datetime="<?php echo HTMLHelper::_('date', $this->item->publish_up, 'c'); ?>" itemprop="datePublished"></time>
	<?php endif; ?>

	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
	</div>
	<?php endif;
	if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && $this->item->paginationrelative)
	{
		echo $this->item->pagination;
	}
	?>

	<?php // Todo Not that elegant would be nice to group the params ?>
	<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
	|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam); ?>

	<?php if (!$useDefList && $this->print) : ?>
		<div id="pop-print" class="btn hidden-print">
			<?php echo HTMLHelper::_('icon.print_screen', $this->item, $params); ?>
		</div>
		<div class="clearfix"> </div>
	<?php endif; ?>
	<?php if ($params->get('show_title')) : ?>
	<div class="page-header">
		<h2 itemprop="headline">
			<?php echo $this->escape($this->item->title); ?>
		</h2>

		<?php if ($this->item->state == $conditionUnpublished) : ?>
			<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
		<?php endif; ?>
		<?php if ($isNotPublishedYet) : ?>
			<span class="label label-warning"><?php echo Text::_('JNOTPUBLISHEDYET'); ?></span>
		<?php endif; ?>
		<?php if ($isExpired) : ?>
			<span class="label label-warning"><?php echo Text::_('JEXPIRED'); ?></span>
		<?php endif; ?>
	</div>
	<?php else : ?>
		<meta itemprop="headline" content="<?php echo $this->escape($this->item->title); ?>">
	<?php endif; ?>
	<?php if (!$this->print) : ?>
		<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
			<?php echo LayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $this->item, 'print' => false)); ?>
		<?php endif; ?>
	<?php else : ?>
		<?php if ($useDefList) : ?>
			<div id="pop-print" class="btn hidden-print">
				<?php echo HTMLHelper::_('icon.print_screen', $this->item, $params); ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php // Blog is generated by content plugin event "onContentAfterTitle" ?>
	<?php echo $this->item->event->afterDisplayTitle; ?>

	<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
		<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
		<?php echo LayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
	<?php endif; ?>

	<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
		<?php $this->item->tagLayout = new FileLayout('joomla.content.tags'); ?>

		<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
	<?php endif; ?>

	<?php // Blog is generated by content plugin event "onContentBeforeDisplay" ?>
	<?php echo $this->item->event->beforeDisplayContent; ?>

	<?php if (isset($urls) && ((!empty($urls->urls_position) && ($urls->urls_position == '0')) || ($params->get('urls_position') == '0' && empty($urls->urls_position)))
		|| (empty($urls->urls_position) && (!$params->get('urls_position')))) : ?>
	<?php echo $this->loadTemplate('links'); ?>
	<?php endif; ?>

	<?php if ($params->get('access-view')) : ?>
	<?php echo LayoutHelper::render('joomla.content.full_image', $this->item); ?>
	<?php
	if (!empty($this->item->pagination) && $this->item->pagination && !$this->item->paginationposition && !$this->item->paginationrelative) :
		echo $this->item->pagination;
	endif;
	?>
	<?php if (isset ($this->item->toc)) :
		echo $this->item->toc;
	endif; ?>
	<div itemprop="articleBody">
		<?php if(!empty($this->item->video->uri)) : ?>
			<div class="videowrapper">
				<iframe src="<?php echo $this->item->video->uri; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
		<?php endif; ?>

		<?php echo $this->item->text; ?>

		<?php if(!empty($this->item->sources) || $this->item->sources_blob) : ?>
			<h2 class="source-toggle" id="sourceToggle"><?php echo Text::_('COM_BLOG_HEADING_SOURCES'); ?> <span class="fa fa-angle-right"></span></h2>

			<div class="sources" id="articleSources">
				<?php if((int) $this->item->toggle_sources_type === 1) : ?>
					<ol>
						<?php foreach($this->item->sources as $source) : ?>
							<?php
							$source_text   = array();

							if($source['source_title'])
							{
								$source_text[] = nl2br($this->escape($source['source_title']));
							}

							if($source['source_publish_date'])
							{
								$source_text[] = $this->escape($source['source_publish_date']);
							}

							if($source['source_url'])
							{
								$source_text[] = '<a href="' . $source['source_url'] . '" target="_blank" rel="noopener noreferrer">' . $this->escape($source['source_url']) . '</a>';
							}

							$source_text = implode(' ', $source_text);
							?>
							<li><?php echo $source_text; ?></li>
						<?php endforeach; ?>
					</ol>
				<?php else : ?>
					<?php echo $this->item->sources_blob; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ($params->get('show_image_gallery_frontend') && !empty($this->item->gallery)) : ?>
			<div class="row small-up-2 medium-up-3 large-up-4" data-equalizer>
				<?php foreach($this->item->gallery as $img) : ?>
					<div class="column" data-equalizer-watch>
						<a href="<?php echo $img['gallery_image']; ?>" class="jcepopup" target="_blank"
							rel="caption['<?php echo json_encode($img['gallery_caption']); ?>'];group['gallery']">
							<img src="<?php echo Uri::root() . $img['gallery_image']; ?>" alt="<?php echo pathinfo($img['gallery_image'], PATHINFO_FILENAME); ?>">
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ($info == 1 || $info == 2) : ?>
		<?php if ($useDefList) : ?>
			<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
			<?php echo LayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
		<?php endif; ?>

		<?php if ($params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
			<?php $this->item->tagLayout = new FileLayout('joomla.content.tags'); ?>
			<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	if (!empty($this->item->pagination) && $this->item->pagination && $this->item->paginationposition && !$this->item->paginationrelative) :
		echo $this->item->pagination;
	?>
	<?php endif; ?>

	<?php if (isset($urls) && ((!empty($urls->urls_position) && ($urls->urls_position == '1')) || ($params->get('urls_position') == '1'))) : ?>
	<?php echo $this->loadTemplate('links'); ?>
	<?php endif; ?>
	<?php // Optional teaser intro text for guests ?>
	<?php elseif ($params->get('show_noauth') == true && $user->get('guest')) : ?>
	<?php echo LayoutHelper::render('joomla.content.intro_image', $this->item); ?>
	<?php echo HTMLHelper::_('content.prepare', $this->item->introtext); ?>
	<?php // Optional link to let them register to see the whole article. ?>
	<?php if ($params->get('show_readmore') && $this->item->fulltext != null) : ?>
	<?php $menu = Factory::getApplication()->getMenu(); ?>
	<?php $active = $menu->getActive(); ?>
	<?php $itemId = $active->id; ?>
	<?php $link = new Uri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false)); ?>
	<?php $link->setVar('return', base64_encode(BlogHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language))); ?>

	<?php echo LayoutHelper::render('joomla.content.readmore', array('item' => $this->item, 'params' => $params, 'link' => $link)); ?>
	<?php endif; ?>
	<?php endif; ?>
	<?php
	if (!empty($this->item->pagination) && $this->item->pagination && $this->item->paginationposition && $this->item->paginationrelative) :
		echo $this->item->pagination;
	?>
	<?php endif; ?>
	<?php // Blog is generated by content plugin event "onContentAfterDisplay" ?>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
