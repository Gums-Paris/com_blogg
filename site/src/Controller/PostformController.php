<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Blogg
 * @author     Pastre <claude.pastre@free.fr>
 * @copyright  2022 Pastre
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Blogg\Component\Blogg\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Post class.
 *
 * @since  1.0.0
 */
class PostformController extends FormController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $this->app->getUserState('com_blogg.edit.post.id');
		$editId     = $this->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$this->app->setUserState('com_blogg.edit.post.id', $editId);

		// Get the model.
		$model = $this->getModel('Postform', 'Site');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_blogg&view=postform&layout=edit', false));
	}

	/**
	 * Method to save data.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$model = $this->getModel('Postform', 'Site');

		// Get the user data.
		$data = $this->input->get('jform', array(), 'array');
	
		// Check valid file format for Upload
		if($_FILES["jform"]["size"]["post_image"] > 0 ){
			$path = strtolower(strrchr($_FILES["jform"]["name"]["post_image"], '.'));
			if(($path!='.jpeg') && ($path!='.jpg') && ($path!='.gif') && ($path!='.png')){
				$msg = Text::_( 'COM_BLOG_AUTHORISED_FORMATS' );
				$link = Route::_('index.php?option=com_blogg&view=postform&layout=edit&Itemid='.$Itemid, false);
				$this->setRedirect( $link, $msg );return; exit(0);
			}
		}
			
		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$this->app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$jform = $this->input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$this->app->setUserState('com_blogg.edit.post.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $this->app->getUserState('com_blogg.edit.post.id');
			$this->setRedirect(Route::_('index.php?option=com_blogg&view=postform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{ 
			// Save the data in the session.
			$this->app->setUserState('com_blogg.edit.post.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $this->app->getUserState('com_blogg.edit.post.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_blogg&view=postform&layout=edit&id=' . $id, false));
			$this->redirect();
		}
// après un successful save, l'id du post n'est pas dans $data['id'] on va l'y mettre mais c'est un pis-aller
		$data['id'] = $return; 
		
		// Upload Logo -Start Here
		
		$upload = $model->uploadlogo($data['id']);
		if($upload == 'invalidformat'){
			$msg = Text::_( 'COM_BLOG_AUTHORISED_FORMATS' );
			$link = Route::_('index.php?option=com_blogg&view=postform&layout=edit&Itemid='.$Itemid.'&id='.$data['id'], false);
			$this->setRedirect( $link, $msg );return; exit(0);
		}
		// Upload Logo -End Here
			 
		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$this->app->setUserState('com_blogg.edit.post.id', null);

		// Redirect to the list screen.
		if (!empty($return))
		{
			$this->setMessage(Text::_('COM_BLOGG_ITEM_SAVED_SUCCESSFULLY'));
		}
		
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_blogg&view=posts' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$this->app->setUserState('com_blogg.edit.post.data', null);
	}

	/**
	 * Method to abort current operation
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{

		// Get the current edit id.
		$editId = (int) $this->app->getUserState('com_blogg.edit.post.id');

		// Get the model.
		$model = $this->getModel('Postform', 'Site');

		// Check in the item
		if ($editId)
		{
			$model->checkin($editId);
		}

		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_blogg&view=posts' : $item->link);
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Method to remove data
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function remove()
	{
		$model = $this->getModel('Postform', 'Site');
		$pk    = $this->input->getInt('id');

		// Attempt to save the data
		try
		{
			// Check in before delete
			$return = $model->checkin($return);
			// Clear id from the session.
			$this->app->setUserState('com_blogg.edit.post.id', null);

			$menu = $this->app->getMenu();
			$item = $menu->getActive();
			$url = (empty($item->link) ? 'index.php?option=com_blogg&view=posts' : $item->link);

			if($return)
			{
				$model->delete($pk);
				$this->setMessage(Text::_('COM_BLOGG_ITEM_DELETED_SUCCESSFULLY'));
			}
			else
			{
				$this->setMessage(Text::_('COM_BLOGG_ITEM_DELETED_UNSUCCESSFULLY'), 'warning');
			}
			

			$this->setRedirect(Route::_($url, false));
			// Flush the data from the session.
			$this->app->setUserState('com_blogg.edit.post.data', null);
		}
		catch (\Exception $e)
		{
			$errorType = ($e->getCode() == '404') ? 'error' : 'warning';
			$this->setMessage($e->getMessage(), $errorType);
			$this->setRedirect('index.php?option=com_blogg&view=posts');
		}
	}
}
