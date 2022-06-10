<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Version;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

if(Version::MAJOR_VERSION < 4)
{
	HTMLHelper::_('behavior.caption');
	HTMLHelper::_('behavior.core');
}

// Add strings for translations in Javascript.
Text::script('JGLOBAL_EXPAND_CATEGORIES');
Text::script('JGLOBAL_COLLAPSE_CATEGORIES');

Factory::getDocument()->addScriptDeclaration("
jQuery(function($) {
	$('.categories-list').find('[id^=category-btn-]').each(function(index, btn) {
		var btn = $(btn);
		btn.on('click', function() {
			btn.find('span').toggleClass('icon-plus');
			btn.find('span').toggleClass('icon-minus');
			if (btn.attr('aria-label') === Joomla.Text._('JGLOBAL_EXPAND_CATEGORIES'))
			{
				btn.attr('aria-label', Joomla.Text._('JGLOBAL_COLLAPSE_CATEGORIES'));
			} else {
				btn.attr('aria-label', Joomla.Text._('JGLOBAL_EXPAND_CATEGORIES'));
			}
		});
	});
});");
?>
<div class="categories-list<?php echo $this->pageclass_sfx; ?>">
	<?php
		echo LayoutHelper::render('joomla.content.categories_default', $this);
		echo $this->loadTemplate('items');
	?>
</div>
