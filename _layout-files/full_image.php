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
use Joomla\CMS\Uri\Uri;

$app    = Factory::getApplication();
$params = $displayData->params;
$images = json_decode($displayData->images);
?>

<?php if (isset($images->image_fulltext) && !empty($images->image_fulltext) && file_exists(JPATH_BASE . '/' . $images->image_fulltext) === true) : ?>
	<?php
	$alt_text = $images->image_fulltext_alt ? $images->image_fulltext_alt : $displayData->title;
	$imgfloat = empty($images->float_fulltext) ? $params->get('float_fulltext') : $images->float_fulltext;
	$img_path = pathinfo($images->image_fulltext, PATHINFO_DIRNAME);
	$img_base = pathinfo($images->image_fulltext, PATHINFO_BASENAME);
	$img_url  = Uri::root() . $img_path . '/' . $img_base;
	$class    = '';
	$title    = '';
	list($img_width, $img_height)	= getimagesize($images->image_fulltext);

	if ($images->image_fulltext_caption)
	{
		$class = 'class="caption"';
		$title = 'title="' . htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'utf-8') . '"';
	}

	$app->set('ogImage', $img_url);
	$app->set('ogImageWidth', $img_width);
	$app->set('ogImageHeight', $img_height);
	?>
	<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image">
		<meta name="twitter:image" content="<?php echo $img_url; ?>">

		<img <?php echo $class; ?> src="<?php echo htmlspecialchars($images->image_fulltext, ENT_COMPAT, 'utf-8'); ?>"
			alt="<?php echo htmlspecialchars($alt_text, ENT_COMPAT, 'utf-8'); ?>" <?php echo $title; ?>
			width="<?php echo $img_width; ?>" height="<?php echo $img_height; ?>" itemprop="image">
	</div>
<?php endif; ?>
