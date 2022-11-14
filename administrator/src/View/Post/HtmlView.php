<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Blogg
 * @author     Pastre <claude.pastre@free.fr>
 * @copyright  2022 Pastre
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Blogg\Component\Blogg\Administrator\View\Post;
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \Blogg\Component\Blogg\Administrator\Helper\BloggHelper;
use \Joomla\CMS\Language\Text;

/**
 * View class for a single Post.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

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
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user  = Factory::getApplication()->getIdentity();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = BloggHelper::getActions();

		ToolbarHelper::title(Text::_('COM_BLOGG_TITLE_POST'), "generic");

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			ToolbarHelper::apply('post.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('post.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			ToolbarHelper::custom('post.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			ToolbarHelper::custom('post.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('post.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('post.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
