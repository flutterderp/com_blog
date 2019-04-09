<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$app = JFactory::getApplication();
$option = $app->input->get('option', 'com_content', 'string');
$component = ucwords(str_ireplace('com_', '', $option));
$helperRoute = $component . 'HelperRoute';
$params = $displayData->params;
?>
<?php $images = json_decode($displayData->images); ?>
<?php if (isset($images->image_intro) && !empty($images->image_intro) && file_exists(JPATH_BASE . '/' . $images->image_intro) === true) : ?>
	<?php
	$imgfloat = empty($images->float_intro) ? $params->get('float_intro') : $images->float_intro;
	$img_path = pathinfo($images->image_intro, PATHINFO_DIRNAME);
	$img_base = pathinfo($images->image_intro, PATHINFO_BASENAME);
	$img_url = JUri::root() . $img_path . '/' . $img_base;
	list($img_width, $img_height)	= getimagesize($images->image_intro);
	$class = '';
	$title = '';

	if ($images->image_intro_caption)
	{
		$class = 'class="caption"';
		$title = 'title="' . htmlspecialchars($images->image_intro_caption, ENT_COMPAT, 'utf-8') . '"';
	}
	?>
	<div class="pull-<?php echo htmlspecialchars($imgfloat, ENT_COMPAT, 'utf-8'); ?> item-image">
		<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
			<a href="<?php echo JRoute::_($helperRoute::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)); ?>">
				<img <?php echo $class; ?> src="<?php echo htmlspecialchars($images->image_intro, ENT_COMPAT, 'utf-8'); ?>"
					alt="<?php echo htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'utf-8'); ?>" <?php echo $title; ?>
					height="<?php echo $img_height; ?>" width="<?php echo $img_width; ?>" itemprop="thumbnailUrl">
			</a>
		<?php else : ?>
			<img <?php echo $class; ?> src="<?php echo htmlspecialchars($images->image_intro, ENT_COMPAT, 'utf-8'); ?>"
				alt="<?php echo htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'utf-8'); ?>" <?php echo $title; ?>
				height="<?php echo $img_height; ?>" width="<?php echo $img_width; ?>" itemprop="thumbnailUrl">
		<?php endif; ?>
	</div>
<?php endif; ?>
