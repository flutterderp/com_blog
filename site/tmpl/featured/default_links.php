<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\Component\Blog\Site\Helper\RouteHelper;

/** @var \Joomla\Component\Blog\Site\View\Featured\HtmlView $this */
?>
<ul class="com-blog-blog__links">
    <?php foreach ($this->link_items as $item) : ?>
        <li class="com-blog-blog__link">
            <a href="<?php echo Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
                <?php echo $item->title; ?></a>
        </li>
    <?php endforeach; ?>
</ul>
