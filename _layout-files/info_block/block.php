<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;

Log::add('The layout joomla.content.info_block.block is deprecated, use joomla.content.info_block instead.', Log::WARNING, 'deprecated');

echo LayoutHelper::render('joomla.content.info_block', $displayData);
