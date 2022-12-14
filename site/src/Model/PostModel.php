<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Blogg
 * @author     Pastre <claude.pastre@free.fr>
 * @copyright  2022 Pastre
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Blogg\Component\Blogg\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\User\UserFactoryInterface;
use \Blogg\Component\Blogg\Site\Helper\BloggHelper;

/**
 * Blogg model.
 *
 * @since  1.0.0
 */
class PostModel extends ItemModel
{
	public $_item;

	

	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws Exception
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_blogg');
		$user = $app->getIdentity();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_blogg')) && (!$user->authorise('core.edit', 'com_blogg')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_blogg.edit.post.id');
		}
		else
		{
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_blogg.edit.post.id', $id);
		}

		$this->setState('post.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('post.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer $id The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function getItem($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id))
			{
				$id = $this->getState('post.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table && $table->load($id))
			{
				

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if (isset($table->state) && $table->state != $published)
					{
						throw new \Exception(Text::_('COM_BLOGG_ITEM_NOT_LOADED'), 403);
					}
				}

				// Convert the Table to a clean CMSObject.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, CMSObject::class);

			}

			if (empty($this->_item))
			{
				throw new \Exception(Text::_('COM_BLOGG_ITEM_NOT_LOADED'), 404);
			}
		}
		
// get the comments associated to this post		
		$db 	= Factory::getDbo();
		$query  = $db->getQuery(true);
		$query
			->select('comment.*, user.name AS commentedby')
			->from('#__blogg_comments AS comment')
			->join('LEFT', '#__users AS user ON comment.created_by = user.id')
			->where('comment.state = "1" AND post_id='. (int) $id)
			->order('comment.comment_date ASC');
		
		if ($this->_getList( $query )) {
			$this->_item->comments = $this->_getList( $query ); }
		

		 $container = \Joomla\CMS\Factory::getContainer();

		 $userFactory = $container->get(UserFactoryInterface::class);

		if (isset($this->_item->created_by))
		{
			$user = $userFactory->loadUserById($this->_item->created_by);
			$this->_item->created_by_name = $user->name;
		}

		 $container = \Joomla\CMS\Factory::getContainer();

		 $userFactory = $container->get(UserFactoryInterface::class);

		if (isset($this->_item->modified_by))
		{
			$user = $userFactory->loadUserById($this->_item->modified_by);
			$this->_item->modified_by_name = $user->name;
		}

		return $this->_item;
	}
	


	/**
	 * Get an instance of Table class
	 *
	 * @param   string $type   Name of the Table class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Post', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 *
	 * @param   string $alias Item alias
	 *
	 * @return  mixed
	 */
	public function getItemIdByAlias($alias)
	{
		$table      = $this->getTable();
		$properties = $table->getProperties();
		$result     = null;
		$aliasKey   = null;
		if (method_exists($this, 'getAliasFieldNameByView'))
		{
			$aliasKey   = $this->getAliasFieldNameByView('post');
		}
		

		if (key_exists('alias', $properties))
		{
			$table->load(array('alias' => $alias));
			$result = $table->id;
		}
		elseif (isset($aliasKey) && key_exists($aliasKey, $properties))
		{
			$table->load(array($aliasKey => $alias));
			$result = $table->id;
		}
		
			return $result;
		
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer $id The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('post.id');
				
		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
		
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer $id The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('post.id');

				
		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getApplication()->getIdentity();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
				
	}

	/**
	 * Publish the element
	 *
	 * @param   int $id    Item id
	 * @param   int $state Publish state
	 *
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
				
		$table->load($id);
		$table->state = $state;

		return $table->store();
				
	}

	/**
	 * Method to delete an item
	 *
	 * @param   int $id Element id
	 *
	 * @return  bool
	 */
	public function delete($id)
	{
		$table = $this->getTable();

		
			return $table->delete($id);
		
	}

	/**
	 * function to Delete Comment
	 **/
	function delete_comment($id)
	{
		$result = false;
		$mainframe = Factory::getApplication();
		$input = $mainframe->input;
		$user	= Factory::getUser();
		if(trim($user->id) == '' || $user->id <= 0){
			$msg = Text::_( 'Please login to manage blog post' );
			$link = Route::_('index.php?option=com_blogg&view=posts', false);
			$this->setRedirect( $link, $msg );  return;
		}
 		$Itemid = $input->get( 'Itemid', '', 'INT' );
		$db = Factory::getDbo();
		$query  = $db->getQuery(true);
		$query = 'SELECT id, created_by, post_id FROM #__blogg_comments WHERE id = ' .$id. '  AND created_by = '.$user->id ;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		if($rows[0]->id){
			$query  = $db->getQuery(true);
			$query = 'DELETE FROM #__blogg_comments WHERE id = '.$id.' AND created_by = '.$user->id ;
			$db->setQuery( $query );
			$result = $db->execute();
			}
		return $result;	
	}
			
	/**
	 * function to delete the image in my post
	 **/
	function delete_mypost_image()
	{
		$result = false;
		$mainframe = Factory::getApplication();
		$input = $mainframe->input;
		$user = Factory::getUser();
		if(trim($user->id) == '' || $user->id <= 0){
			$msg = Text::_( 'Please login to manage blog post' );
			$link = Route::_('index.php?option=com_blog&view=blog', false);
			$this->setRedirect( $link, $msg );  return;
		}
		$base = JPATH_ROOT;
		$working_folder =$base."/media/com_blogg/";
		
		$id = $input->get( 'id', '', 'INT' );
		if ($input->get( 'author', '', 'INT' )) {
			$user_id = $input->get( 'author', '', 'INT' ); 
		} else {
			$user_id = $user->id; 
		}

		$Itemid = $input->get( 'Itemid', '', 'INT' );

		$return_view = 'post';

		$db = Factory::getDbo();
		$query  = $db->getQuery(true);
		$query = 'SELECT id, created_by, post_image FROM #__blogg_posts WHERE id = '. $id . ' AND created_by = '.$user_id;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		if($rows[0]->id){
			$rowId = $rows[0]->id;
			if (file_exists($working_folder."grandes/".$rows[0]->post_image)) 
			{ 
				@unlink ($working_folder."grandes/".$rows[0]->post_image);
			}
			if (file_exists($working_folder."th".$rows[0]->post_image)) 
			{ 
				@unlink ($working_folder."th".$rows[0]->post_image);
			}
			$query  = $db->getQuery(true);
			$query = "UPDATE #__blogg_posts SET post_image='' WHERE id = $rowId AND created_by = " .$user_id;
			$db->setQuery( $query );
			$result = $db->execute();
			return $result;
		}
	}
	
}
