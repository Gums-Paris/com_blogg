<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Blogg
 * @author     Pastre <claude.pastre@free.fr>
 * @copyright  2022 Pastre
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Blogg\Component\Blogg\Site\Service;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Class BloggRouter
 *
 */
class Router extends RouterView
{
	private $noIDs;
	/**
	 * The category factory
	 *
	 * @var    CategoryFactoryInterface
	 *
	 * @since  1.0.0
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 *
	 * @var    array
	 *
	 * @since  1.0.0
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = Factory::getApplication()->getParams('com_blogg');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		
			$posts = new RouterViewConfiguration('posts');
			$this->registerView($posts);
			$ccPost = new RouterViewConfiguration('post');
			$ccPost->setKey('id')->setParent($posts);
			$this->registerView($ccPost);
			$postform = new RouterViewConfiguration('postform');
			$postform->setKey('id');
			$this->registerView($postform);
			$ccComment = new RouterViewConfiguration('comment');
			$ccComment->setKey('id');
			$this->registerView($ccComment);
			$commentform = new RouterViewConfiguration('commentform');
			$commentform->setKey('id');
			$this->registerView($commentform);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}


	
		/**
		 * Method to get the segment(s) for an post
		 *
		 * @param   string  $id     ID of the post to retrieve the segments for
		 * @param   array   $query  The request that is built right now
		 *
		 * @return  array|string  The segments of this item
		 */
		public function getPostSegment($id, $query)
		{
			return array((int) $id => $id);
		}
			/**
			 * Method to get the segment(s) for an postform
			 *
			 * @param   string  $id     ID of the postform to retrieve the segments for
			 * @param   array   $query  The request that is built right now
			 *
			 * @return  array|string  The segments of this item
			 */
			public function getPostformSegment($id, $query)
			{
				return $this->getPostSegment($id, $query);
			}
		/**
		 * Method to get the segment(s) for an comment
		 *
		 * @param   string  $id     ID of the comment to retrieve the segments for
		 * @param   array   $query  The request that is built right now
		 *
		 * @return  array|string  The segments of this item
		 */
		public function getCommentSegment($id, $query)
		{
			return array((int) $id => $id);
		}
			/**
			 * Method to get the segment(s) for an commentform
			 *
			 * @param   string  $id     ID of the commentform to retrieve the segments for
			 * @param   array   $query  The request that is built right now
			 *
			 * @return  array|string  The segments of this item
			 */
			public function getCommentformSegment($id, $query)
			{
				return $this->getCommentSegment($id, $query);
			}

	
		/**
		 * Method to get the segment(s) for an post
		 *
		 * @param   string  $segment  Segment of the post to retrieve the ID for
		 * @param   array   $query    The request that is parsed right now
		 *
		 * @return  mixed   The id of this item or false
		 */
		public function getPostId($segment, $query)
		{
			return (int) $segment;
		}
			/**
			 * Method to get the segment(s) for an postform
			 *
			 * @param   string  $segment  Segment of the postform to retrieve the ID for
			 * @param   array   $query    The request that is parsed right now
			 *
			 * @return  mixed   The id of this item or false
			 */
			public function getPostformId($segment, $query)
			{
				return $this->getPostId($segment, $query);
			}
		/**
		 * Method to get the segment(s) for an comment
		 *
		 * @param   string  $segment  Segment of the comment to retrieve the ID for
		 * @param   array   $query    The request that is parsed right now
		 *
		 * @return  mixed   The id of this item or false
		 */
		public function getCommentId($segment, $query)
		{
			return (int) $segment;
		}
			/**
			 * Method to get the segment(s) for an commentform
			 *
			 * @param   string  $segment  Segment of the commentform to retrieve the ID for
			 * @param   array   $query    The request that is parsed right now
			 *
			 * @return  mixed   The id of this item or false
			 */
			public function getCommentformId($segment, $query)
			{
				return $this->getCommentId($segment, $query);
			}

	/**
	 * Method to get categories from cache
	 *
	 * @param   array  $options   The options for retrieving categories
	 *
	 * @return  CategoryInterface  The object containing categories
	 *
	 * @since   1.0.0
	 */
	private function getCategories(array $options = []): CategoryInterface
	{
		$key = serialize($options);

		if (!isset($this->categoryCache[$key]))
		{
			$this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
		}

		return $this->categoryCache[$key];
	}
}
