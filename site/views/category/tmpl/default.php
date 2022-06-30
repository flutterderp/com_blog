<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Version;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

if(Version::MAJOR_VERSION < 4)
{
	HTMLHelper::_('behavior.caption');
}
?>
<div class="category-list<?php echo $this->pageclass_sfx; ?>">

<?php
$this->subtemplatename = 'articles';
echo LayoutHelper::render('joomla.content.category_default', $this);
?>

</div>
