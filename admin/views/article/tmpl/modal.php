<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Version;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

if(Version::MAJOR_VERSION < 4)
{
	// @deprecated 4.0 the function parameter, the inline js and the buttons are not needed since 3.7.0.
	$function  = Factory::getApplication()->input->getCmd('function', 'jEditArticle_' . (int) $this->item->id);

	// Function to update input title when changed
	Factory::getDocument()->addScriptDeclaration('
		function jEditArticleModal() {
			if (window.parent && document.formvalidator.isValid(document.getElementById("item-form"))) {
				return window.parent.' . $this->escape($function) . '(document.getElementById("jform_title").value);
			}
		}
	');
	?>
	<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('article.apply'); jEditArticleModal();"></button>
	<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('article.save'); jEditArticleModal();"></button>
	<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('article.cancel');"></button>
	<?php
}
?>
<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
