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
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Object\CMSObject;
use \Joomla\CMS\Helper\TagsHelper;

/**
 * Blogg model.
 *
 * @since  1.0.0
 */
class PostformModel extends FormModel
{
	private $item = null;
	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_blogg');

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
	 * Method to get an ojbect.
	 *
	 * @param   integer $id The id of the object to get.
	 *
	 * @return  Object|boolean Object on success, false on failure.
	 *
	 * @throws  Exception
	 */
	public function getItem($id = null)
	{
		if ($this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('post.id');
			}

			// Get a level row instance.
			$table = $this->getTable();
			$properties = $table->getProperties();
			$this->item = ArrayHelper::toObject($properties, CMSObject::class);

			if ($table !== false && $table->load($id) && !empty($table->id))
			{
				$user = Factory::getApplication()->getIdentity();
				$id   = $table->id;
				

				$canEdit = $user->authorise('core.edit', 'com_blogg') || $user->authorise('core.create', 'com_blogg');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_blogg'))
				{
					$canEdit = $user->id == $table->created_by;
				}

				if (!$canEdit)
				{
					throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
				}

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if (isset($table->state) && $table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the Table to a clean CMSObject.
				$properties = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, CMSObject::class);
				

				
			}
		}

		return $this->item;
	}

	/**
	 * Method to get the table
	 *
	 * @param   string $type   Name of the Table class
	 * @param   string $prefix Optional prefix for the table class name
	 * @param   array  $config Optional configuration array for Table object
	 *
	 * @return  Table|boolean Table if found, boolean false on failure
	 */
	public function getTable($type = 'Post', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get an item by alias
	 *
	 * @param   string $alias Alias string
	 *
	 * @return int Element id
	 */
	public function getItemIdByAlias($alias)
	{
		$table      = $this->getTable();
		$properties = $table->getProperties();

		if (!in_array('alias', $properties))
		{
				return null;
		}

		$table->load(array('alias' => $alias));
		$id = $table->id;

		
			return $id;
		
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
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array   $data     An optional array of data for the form to interogate.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form    A Form object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_blogg.post', 'postform', array(
						'control'   => 'jform',
						'load_data' => $loadData
				)
		);

		if (empty($form))
		{
				return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 * @since   1.0.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_blogg.edit.post.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		if ($data)
		{
			

			return $data;
		}

		return array();
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function save($data)
	{
		$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('post.id');
		$state = (!empty($data['state'])) ? 1 : 0;
		$user  = Factory::getApplication()->getIdentity();

		
		if ($id)
		{
			// Check the user can edit this item
			$authorised = $user->authorise('core.edit', 'com_blogg') || $authorised = $user->authorise('core.edit.own', 'com_blogg');
		}
		else
		{
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_blogg');
		}

		if ($authorised !== true)
		{
			throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$table = $this->getTable();

		if(!empty($id))
		{
			$table->load($id);
		}

		
		
	try{
			if ($table->save($data) === true)
			{
				return $table->id;
			}
			else
			{
				return false;
			}
		}catch(\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}
			
	}

	/**
	 * Method to delete data
	 *
	 * @param   int $pk Item primary key
	 *
	 * @return  int  The id of the deleted item
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function delete($id)
	{
		$user = Factory::getApplication()->getIdentity();

		
		if (empty($id))
		{
			$id = (int) $this->getState('post.id');
		}

		if ($id == 0 || $this->getItem($id) == null)
		{
				throw new \Exception(Text::_('COM_BLOGG_ITEM_DOESNT_EXIST'), 404);
		}

		if ($user->authorise('core.delete', 'com_blogg') !== true)
		{
				throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$table = $this->getTable();

		if ($table->delete($id) !== true)
		{
				throw new \Exception(Text::_('JERROR_FAILED'), 501);
		}

		return $id;
		
	}

	/**
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		$table = $this->getTable();

		return $table !== false;
	}
	
	/**
	 * Method to Upload Image
	 *
	 **/
	function uploadlogo($id)
	{
		$mainframe = Factory::getApplication();
		$imageSize = $mainframe->getMenu()->getActive()->getParams()->get('image_size');
		$image_big = 450;
		if(!empty($imageSize)) {$image_big = $imageSize;}

		$base = JPATH_ROOT;
		$working_folder =$base."/media/com_blogg/";
		$db 	= Factory::getDbo();
		if($_FILES["jform"]["size"]["post_image"] > 0 ){

			$path = strtolower(strrchr($_FILES["jform"]["name"]["post_image"], '.'));
			if(($path!='.jpeg') && ($path!='.jpg') && ($path!='.gif') && ($path!='.png')){
				return 'invalidformat';
			}
			
			if (file_exists($working_folder."th".$_POST["image_old"])) { 
				if($_POST["image_old"]){
					@unlink ($working_folder."th".$_POST["image_old"]);
				}
			}
	
			$filename = strtolower($this->fncUid().'_'.$_FILES["jform"]["name"]["post_image"]);
			if(copy($_FILES["jform"]["tmp_name"]["post_image"], $working_folder.$filename)){
				$query = $db->getQuery(true);
				$query = "UPDATE #__blogg_posts SET post_image = '".$filename."' WHERE id = '".$id."'";
				$db->setQuery( $query );
				try{
					$db->execute();
				} catch (Exception $e) {$mainframe->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),'WARNING');return false;}

				$source=$working_folder.$filename;
				if(($path=='.jpeg') || ($path=='.jpg')){$this->correctImageOrientation($source);}
				$dest=$working_folder."th".$filename;
				$this->imageCompression($source,150,$dest);
				$dest=$working_folder."grandes/".$filename;
				$this->imageCompression($source,$image_big,$dest);
				@unlink( $source );
			}
		}
	}
	
	/**
	 * Generate Random ID
	 *
	 **/
	 function fncUid(){
		return sprintf(
				 '%08x-%04x-%04x-%02x%02x-%012x', mt_rand(), mt_rand(0, 65535),	 bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)),
				 bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)), mt_rand(0, 255), mt_rand() );
	}

	function correctImageOrientation($filename) {
	  if (function_exists('exif_read_data')) {
	    $exif = exif_read_data($filename);
	    if($exif && isset($exif['Orientation'])) {
	      $orientation = $exif['Orientation'];
	      if($orientation == 1){ return;}
	      if($orientation != 1){
	        $img = imagecreatefromjpeg($filename);
	        $deg = 0;
	        switch ($orientation) {
	          case 3:
	            $deg = 180;
	            break;
	          case 6:
	            $deg = 270;
	            break;
	          case 8:
	            $deg = 90;
	            break;
	        }
	        if ($deg) {
	          $img = imagerotate($img, $deg, 0);        
	        }
	        // then rewrite the rotated image back to the disk as $filename 
	        imagejpeg($img, $filename, 95);
	      } // if there is some rotation necessary
	    } // if there is orientation info
	  } // if function exists      
	}

	## Thumbnail Creation [With Ratio]
	function imageCompression($imgfile="",$thumbsize=0,$savePath) {
		$this->thumbnail(2, $imgfile, $savePath, $thumbsize, $thumbsize);
		/*
		if($savePath==NULL) {
				header('Content-type: image/jpeg');
		}
		list($width,$height)=getimagesize($imgfile);
		$imgratio=$width/$height; 
		if($imgratio>1) {
				$newwidth=$thumbsize;
				$newheight=$thumbsize/$imgratio;
		} else {
				$newheight=$thumbsize;       
				$newwidth=$thumbsize*$imgratio;
		}
		$thumb=imagecreatetruecolor($newwidth,$newheight); // Making a new true color image

		$source=imagecreatefromjpeg($imgfile); // Now it will create a new image from the source
		imagecopyresampled($thumb,$source,0,0,0,0,$newwidth,$newheight,$width,$height);  // Copy and resize the image
		imagejpeg($thumb,$savePath,100);
		imagedestroy($thumb);
		*/
	}

	function thumbnail ($gdver, $src, $newFile1, $maxw='' ,$maxh='') {
		$gdarr 	= 	array (1,2);
		for ($i=0; $i<count($gdarr); $i++) {
			if ($gdver != $gdarr[$i]) $test.="|";
		}
		$exp 							= 	explode ("|", $test);
		if (count ($exp) == 3) {
			$this->ErrorImage ("Incorrect GD version!");
		}
		if (!function_exists ("imagecreate") || !function_exists ("imagecreatetruecolor")) {
			$this->ErrorImage ("No image create functions!");
		}
		$size = @getimagesize ($src);
		if (!$size) {
			$this->ErrorImage ("Image File Not Found!");
		} else {
			
			$imgratio= $size[0]/$size[1]; 
			if($imgratio>1) {
					$newx=$maxw;
					$newy=$maxw/$imgratio;
			} else {
					$newy=$maxw;       
					$newx=$maxw*$imgratio;
			}
			
			/*
			if ($size[0] > $maxw) {
				#$newx = intval ($maxw);
				$newx=$maxw;
				#$newy = $maxh;
				$newy = intval ($size[1] * ($maxw / $size[0]));
			} else {
			
				#$newx = $size[0];
				$newx=$maxw;
				#$newy = $maxh;
				$newy = $size[1];
			}
			*/
			if ($gdver == 1) {
				$destimg =  imagecreate ($newx, $newy );
			} else {
				$destimg = @imagecreatetruecolor ($newx, $newy ) or die ($this->ErrorImage ("Cannot use GD2 here!"));
			}
			if ($size[2] == 1) {
				if (!function_exists ("imagecreatefromgif")) {
					ErrorImage ("Cannot Handle GIF Format!");
				} else {
					$sourceimg = imagecreatefromgif ($src);
					if ($gdver == 1)
						imagecopyresized ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
					else
						@imagecopyresampled ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]) or die (ErrorImage ("Cannot use GD2 here!"));
					imagegif ($destimg, $newFile1);
				}
			}elseif ($size[2]==2) {
				$sourceimg = imagecreatefromjpeg ($src);
				if ($gdver == 1)
					imagecopyresized ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
				else
					@imagecopyresampled ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]) or die (ErrorImage ("Cannot use GD2 here!"));
				imagejpeg ($destimg, $newFile1);
				
			}elseif ($size[2] == 3) {
				$sourceimg = imagecreatefrompng ($src);
				if ($gdver == 1)
					imagecopyresized ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]);
				else
					@imagecopyresampled ($destimg, $sourceimg, 0,0,0,0, $newx, $newy, $size[0], $size[1]) or die (ErrorImage ("Cannot use GD2 here!"));
				imagepng ($destimg, $newFile1);
			}else {
				ErrorImage ("Image Type Not Handled!");
			}
		}
		imagedestroy ($destimg);
		imagedestroy ($sourceimg);
	}
	
	function ErrorImage ($text) {
		global $maxw;
		$len 							= 	strlen($text);
		if($maxw < 100) $errw = 100;
		$errh 							= 	25;
		$chrlen 						= 	intval (4 * $len);
		$offset 						= 	intval (($errw - $chrlen) / 2);
		$im 							= 	imagecreate ($errw, $errh); /* Create a blank image */
		$bgc 							= 	imagecolorallocate ($im, 153, 63, 63);
		$tc 							= 	imagecolorallocate ($im, 255, 255, 255);
		imagefilledrectangle ($im, 0, 0, $errw, $errh, $bgc);
		imagestring ($im, 2, $offset, 7, $text, $tc);
		imagejpeg ($im);
		imagedestroy ($im);
		exit;
	}
	
}
