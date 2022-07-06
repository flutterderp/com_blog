<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

if(Version::MAJOR_VERSION === 4)
{
	/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
	/** @link https://docs.joomla.org/J4.x:Web_Assets */
	$wa = $this->document->getWebAssetManager();
	$wa->registerScript('com_blog.admin-articles-batch', 'com_blog/admin-articles-default-batch-footer.min.js');
	$wa->useScript('com_blog.admin-articles-batch');
}
?>
<button type="button" class="btn" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value='';document.getElementById('batch-user-id').value='';document.getElementById('batch-tag-id').value=''" data-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('article.batch');return false;">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
