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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * Supports a modal article picker.
 *
 * @since  1.6
 */
class JFormFieldModal_Article extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Modal_Article';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$allowNew       = ((string) $this->element['new'] == 'true');
		$allowEdit      = ((string) $this->element['edit'] == 'true');
		$allowClear     = ((string) $this->element['clear'] != 'false');
		$allowSelect    = ((string) $this->element['select'] != 'false');
		$allowPropagate = ((string) $this->element['propagate'] == 'true');

		$languages = LanguageHelper::getContentLanguages(array(0, 1));

		// Load language
		Factory::getLanguage()->load('com_blog', JPATH_ADMINISTRATOR);

		// The active article id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'Article_' . $this->id;

		// Add the modal field script to the document head.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				Factory::getDocument()->addScriptDeclaration("
				function jSelectArticle_" . $this->id . "(id, title, catid, object, url, language) {
					window.processModalSelect('Article', '" . $this->id . "', id, title, catid, object, url, language);
				}
				");

				Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkArticles = 'index.php?option=com_blog&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
		$linkArticle  = 'index.php?option=com_blog&amp;view=article&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
		$modalTitle   = Text::_('COM_BLOG_CHANGE_ARTICLE');

		if (isset($this->element['language']))
		{
			$linkArticles .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkArticle  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle   .= ' &#8212; ' . $this->element['label'];
		}

		$urlSelect = $linkArticles . '&amp;function=jSelectArticle_' . $this->id;
		$urlEdit   = $linkArticle . '&amp;task=article.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
		$urlNew    = $linkArticle . '&amp;task=article.add';

		if ($value)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__blog'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}

		$title = empty($title) ? Text::_('COM_BLOG_SELECT_AN_ARTICLE') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current article display field.
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select article button
		if ($allowSelect)
		{
			$html .= '<button'
				. ' type="button"'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-toggle="modal"'
				. ' data-target="#ModalSelect' . $modalId . '"'
				. ' title="' . HTMLHelper::tooltipText('COM_BLOG_CHANGE_ARTICLE') . '">'
				. '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
				. '</button>';
		}

		// New article button
		if ($allowNew)
		{
			$html .= '<button'
				. ' type="button"'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-toggle="modal"'
				. ' data-target="#ModalNew' . $modalId . '"'
				. ' title="' . HTMLHelper::tooltipText('COM_BLOG_NEW_ARTICLE') . '">'
				. '<span class="icon-new" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
				. '</button>';
		}

		// Edit article button
		if ($allowEdit)
		{
			$html .= '<button'
				. ' type="button"'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' data-target="#ModalEdit' . $modalId . '"'
				. ' title="' . HTMLHelper::tooltipText('COM_BLOG_EDIT_ARTICLE') . '">'
				. '<span class="icon-edit" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
				. '</button>';
		}

		// Clear article button
		if ($allowClear)
		{
			$html .= '<button'
				. ' type="button"'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-remove" aria-hidden="true"></span>' . Text::_('JCLEAR')
				. '</button>';
		}

		// Propagate article button
		if ($allowPropagate && count($languages) > 2)
		{
			// Strip off language tag at the end
			$tagLength = (int) strlen($this->element['language']);
			$callbackFunctionStem = substr("jSelectArticle_" . $this->id, 0, -$tagLength);

			$html .= '<a'
			. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
			. ' id="' . $this->id . '_propagate"'
			. ' href="#"'
			. ' title="' . HTMLHelper::tooltipText('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP') . '"'
			. ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
			. '<span class="icon-refresh" aria-hidden="true"></span>' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON')
			. '</a>';
		}

		$html .= '</span>';

		// Select article modal
		if ($allowSelect)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'       => $modalTitle,
					'url'         => $urlSelect,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn" data-dismiss="modal">' . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
				)
			);
		}

		// New article modal
		if ($allowNew)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalNew' . $modalId,
				array(
					'title'       => Text::_('COM_BLOG_NEW_ARTICLE'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn"'
							. ' onclick="window.processModalEdit(this, \''
							. $this->id . '\', \'add\', \'article\', \'cancel\', \'item-form\', \'jform_id\', \'jform_name\'); return false;">'
							. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
							. '<button type="button" class="btn btn-primary"'
							. ' onclick="window.processModalEdit(this, \''
							. $this->id . '\', \'add\', \'article\', \'save\', \'item-form\', \'jform_id\', \'jform_name\'); return false;">'
							. Text::_('JSAVE') . '</button>'
							. '<button type="button" class="btn btn-success"'
							. ' onclick="window.processModalEdit(this, \''
							. $this->id . '\', \'add\', \'article\', \'apply\', \'item-form\', \'jform_id\', \'jform_name\'); return false;">'
							. Text::_('JAPPLY') . '</button>',
				)
			);
		}

		// Edit article modal
		if ($allowEdit)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalEdit' . $modalId,
				array(
					'title'       => Text::_('COM_BLOG_EDIT_ARTICLE'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlEdit,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id
							. '\', \'edit\', \'article\', \'cancel\', \'item-form\', \'jform_id\', \'jform_name\'); return false;">'
							. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
							. '<button type="button" class="btn btn-primary"'
							. ' onclick="window.processModalEdit(this, \''
							. $this->id . '\', \'edit\', \'article\', \'save\', \'item-form\', \'jform_id\', \'jform_name\'); return false;">'
							. Text::_('JSAVE') . '</button>'
							. '<button type="button" class="btn btn-success"'
							. ' onclick="window.processModalEdit(this, \''
							. $this->id . '\', \'edit\', \'article\', \'apply\', \'item-form\', \'jform_id\', \'jform_name\'); return false;">'
							. Text::_('JAPPLY') . '</button>',
				)
			);
		}

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" id="' . $this->id . '_id"' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(Text::_('COM_BLOG_SELECT_AN_ARTICLE'), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '" />';

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.4
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
