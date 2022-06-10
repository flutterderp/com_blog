<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;

// Include the component HTML helpers.
// HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
// $wa->getRegistry()->addExtensionRegistryFile('com_contenthistory');
$wa->useScript('keepalive')
	->useScript('form.validate');
	// ->useScript('com_contenthistory.admin-history-versions');

$this->useCoreUI = true;

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$this->ignore_fieldsets = array('jmetadata', 'item_associations');

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$app   = Factory::getApplication();
$doc   = Factory::getDocument();
$input = $app->input;
$assoc = Associations::isEnabled();

if (!$assoc)
{
	$this->ignore_fieldsets[] = 'frontendassociations';
}

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_blog&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', empty($this->item->id) ? Text::_('COM_BLOG_NEW_ARTICLE', true) : Text::sprintf('COM_BLOG_EDIT_ARTICLE', $this->item->id, true)); ?>
		<div class="row">
			<div class="col-lg-9">
				<div class="control-group"><?php echo $this->form->getInput('articletext'); ?></div>

				<div class="form-inline form-inline-header"><?php echo $this->form->renderField('video_uri'); ?></div>

				<?php echo $this->form->renderField('gallery'); ?>

				<?php echo $this->form->renderField('toggle_sources_type'); ?>

				<?php echo $this->form->renderField('sources'); ?>

				<?php echo $this->form->renderField('sources_blob'); ?>
			</div>

			<div class="col-lg-3">
				<?php echo LayoutHelper::render('joomla.edit.global', $this, null, array('client' => 1)); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php // Do not show the images and links options if the edit form is configured not to. ?>
		<?php if ($params->get('show_urls_images_backend') == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('COM_BLOG_FIELDSET_URLS_AND_IMAGES')); ?>
			<div class="row">
				<div class="col-12 col-lg-6">
					<?php echo $this->form->renderField('images'); ?>
					<?php foreach ($this->form->getGroup('images') as $field) : ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</div>
				<div class="col-12 col-lg-6">
					<?php foreach ($this->form->getGroup('urls') as $field) : ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php $this->show_options = $params->get('show_article_options', 1); ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

		<?php // Do not show the publishing options if the edit form is configured not to. ?>
		<?php if ($params->get('show_publishing_options', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
			<div class="row">
				<div class="col-12 col-lg-6">
					<fieldset id="fieldset-publishingdata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
						<div>
							<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
						</div>
					</fieldset>
				</div>
				<div class="col-12 col-lg-6">
					<fieldset id="fieldset-metadata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
						<div>
							<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
						</div>
					</fieldset>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if ( ! $isModal && $assoc) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
			<?php echo $this->loadTemplate('associations'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php elseif ($isModal && $assoc) : ?>
			<div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'editor', Text::_('COM_BLOG_SLIDER_EDITOR_CONFIG')); ?>
			<?php echo $this->form->renderFieldset('editorConfig'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_BLOG_FIELDSET_RULES')); ?>
				<?php echo $this->form->getInput('rules'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->get('return', null, 'BASE64'); ?>" />
		<input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
