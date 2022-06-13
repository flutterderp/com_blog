<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Version;

$jfour_col        = 'form-group col-md-6';
$jfour_container  = 'p-3';
$jfour_row        = 'row';
$jthree_col       = 'control-group span6';
$jthree_container = 'container-fluid';
$jthree_row       = 'row-fluid';
$published        = (int) $this->state->get('filter.published');
?>

<div class="<?php echo Version::MAJOR_VERSION < 4 ? $jthree_container : $jfour_container; ?>">
	<div class="<?php echo Version::MAJOR_VERSION < 4 ? $jthree_row : $jfour_row; ?>">
		<div class="<?php echo Version::MAJOR_VERSION < 4 ? $jthree_col : $jfour_col; ?>">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
			</div>
		</div>
		<div class="<?php echo Version::MAJOR_VERSION < 4 ? $jthree_col : $jfour_col; ?>">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
			</div>
		</div>
	</div>
	<div class="<?php echo Version::MAJOR_VERSION < 4 ? $jthree_row : $jfour_row; ?>">
		<?php if ($published >= 0) : ?>
			<div class="<?php echo Version::MAJOR_VERSION < 4 ? $jthree_col : $jfour_col; ?>">
				<div class="controls">
					<?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_blog']); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="<?php echo Version::MAJOR_VERSION < 4 ? $jthree_col : $jfour_col; ?>">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.tag', []); ?>
			</div>
		</div>
	</div>
</div>
