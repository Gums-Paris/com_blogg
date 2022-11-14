<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Blogg
 * @author     Pastre <claude.pastre@free.fr>
 * @copyright  2022 Pastre
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Blogg\Component\Blogg\Administrator\View\Comments;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Blogg\Component\Blogg\Administrator\Helper\BloggHelper;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
/**
 * View class for a list of Comments.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		$this->sidebar = Sidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = BloggHelper::getActions();

		ToolbarHelper::title(Text::_('COM_BLOGG_TITLE_COMMENTS'), "generic");

		$toolbar = Toolbar::getInstance('toolbar');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Comments';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				$toolbar->addNew('comment.add');
			}
		}

		if ($canDo->get('core.edit.state')  || count($this->transitions))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (isset($this->items[0]->state))
			{
				$childBar->publish('comments.publish')->listCheck(true);
				$childBar->unpublish('comments.unpublish')->listCheck(true);
				$childBar->archive('comments.archive')->listCheck(true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete('comments.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
			}

			$childBar->standardButton('duplicate')
				->text('JTOOLBAR_DUPLICATE')
				->icon('fas fa-copy')
				->task('comments.duplicate')
				->listCheck(true);

			if (isset($this->items[0]->checked_out))
			{
				$childBar->checkin('comments.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state))
			{
				$childBar->trash('comments.trash')->listCheck(true);
			}
		}

		

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
			{
				$toolbar->delete('comments.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin'))
		{
			$toolbar->preferences('com_blogg');
		}

		// Set sidebar action
		Sidebar::setAction('index.php?option=com_blogg&view=comments');
	}
	
	/**
	 * Method to order fields 
	 *
	 * @return void 
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => Text::_('JGRID_HEADING_ID'),
			'a.`state`' => Text::_('JSTATUS'),
			'a.`ordering`' => Text::_('JGRID_HEADING_ORDERING'),
			'a.`created_by`' => Text::_('COM_BLOGG_COMMENTS_CREATED_BY'),
			'a.`modified_by`' => Text::_('COM_BLOGG_COMMENTS_MODIFIED_BY'),
			'a.`post_id`' => Text::_('COM_BLOGG_COMMENTS_POST_ID'),
			'a.`comment_date`' => Text::_('COM_BLOGG_COMMENTS_COMMENT_DATE'),
			'a.`comment_update`' => Text::_('COM_BLOGG_COMMENTS_COMMENT_UPDATE'),
			'a.`comment_ip`' => Text::_('COM_BLOGG_COMMENTS_COMMENT_IP'),
			'a.`comment_hit`' => Text::_('COM_BLOGG_COMMENTS_COMMENT_HIT'),
			'a.`comment_title`' => Text::_('COM_BLOGG_COMMENTS_COMMENT_TITLE'),
			'a.`comment_desc`' => Text::_('COM_BLOGG_COMMENTS_COMMENT_DESC'),
			'a.`comment_gallery`' => Text::_('COM_BLOGG_COMMENTS_COMMENT_GALLERY'),
			'a.`comment_gallery_text`' => Text::_('COM_BLOGG_COMMENTS_COMMENT_GALLERY_TEXT'),
		);
	}

	/**
	 * Check if state is set
	 *
	 * @param   mixed  $state  State
	 *
	 * @return bool
	 */
	public function getState($state)
	{
		return isset($this->state->{$state}) ? $this->state->{$state} : false;
	}
}
