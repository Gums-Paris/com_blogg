<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Blogg
 * @author     Pastre <claude.pastre@free.fr>
 * @copyright  2022 Pastre
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_blogg.list');

$userId  = Factory::getApplication()->getIdentity()->get('id');
$userGroups  = Factory::getApplication()->getIdentity()->get('groups');
$canEdit = Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_blogg');
if (!$canEdit && Factory::getApplication()->getIdentity()->authorise('core.edit.own', 'com_blogg'))
{
	$canEdit = Factory::getApplication()->getIdentity()->id == $this->item->created_by;
}
$canComment = Factory::getApplication()->getIdentity()->authorise('core.create', 'com_blogg') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'commentform.xml');
$images_path = $this->baseurl . '/media/com_blogg/Images/icons/'; 
?>

<!--	<div class="table-responsive">  -->
<div>
	<div class="clsLinkedBlog">
		<div class="clsLinkedBlog_title"><?php echo Text::_($this->blogTitle); ?></div>
	</div>

	<div id="clsTopMenuBg">
		<?php if($userId > 0){ ?>
		<div class="clsFloatRight">
			<img src="<?php echo $this->baseurl; ?>/media/com_blogg/Images/icons/add_post.png"  border="0" width="16px" align="bottom" alt="Add New Post" />
			<a href="<?php echo Route::_( 'index.php?option=com_blogg&task=postform.edit&id=0', false ); ?>"><?php echo Text::_('COM_BLOG_ADD_NEW_POST');?></a>
		</div>
		<?php } else {?>
		<div class="clsFloatRight">
			<a href="<?php echo Route::_('index.php?option=com_users&view=login'. '&return='. base64_encode(JURI::getInstance()->toString()), false);?>"><?php echo Text::_('COM_BLOG_LOGIN_TO_POST');?> </a>
	    </div>
		<?php } ?>
		<div class="clsClearBoth"></div>
	</div>

	<table class="table">
		<tbody>
			<tr>
				<div class="clsPostTitle"><?php print($this->item->post_title);?></div>
				<div class="clsTDBorderTop"></div>
			</tr>
			
			<tr>
			<?php 
				if($this->item->post_image){
					 $grande_image= JPATH_ROOT."/media/com_blogg/grandes/".$this->item->post_image;
					 if(!JFile::exists($grande_image)) {
			?>
					<img src="<?php echo $this->baseurl; ?>/media/com_blogg/<?php echo "th".$this->item->post_image;?>"  
					border="0" alt="Blog Imagette" align="left" class="clsImgPad" />
			<?php 
					} else { 
			?>
					<img src="<?php echo $this->baseurl; ?>/media/com_blogg/<?php echo "grandes/".$this->item->post_image;?>"  
					border="0" alt="Blog Image" align="left" class="clsImgPad"	/>
			<?php 
					} 
				}		
			?>
			<div class="clsMyText"><?php echo($this->item->post_desc);   ?></div>
			</tr>
			
			<tr>
			  <?php if ($this->item->ext_gallery) {
				  echo '<br />'.Text::_('COM_BLOG_INVITE');
				  if ($this->item->ext_gallery_text) { ?>
					  <a href="<?php echo($this->item->ext_gallery) ?>" target="_blank"><?php echo($this->item->ext_gallery_text) ?></a>
				  <?php } else { ?>
					  <a href="<?php echo($this->item->ext_gallery) ?>" target="_blank"><?php echo($this->item->ext_gallery) ?></a>
				  <?php } } ?>						  
			</tr>
			
			<tr>
				<div id="divBlogDetails">
  				<?php echo Text::_('COM_BLOG_AUTHOR');
    					$author_link = Route::_( 'index.php?option=com_blogg&view=post&id='.$this->item->id,false);
    					if ($userId > 0) 
    						{$author_link = Route::_( 'index.php?option=com_comprofiler&task=userProfile&user='.$this->item->created_by, false);}
  				?>
					<a href="<?php echo $author_link;?>">
				<?php echo Text::_($this->item->created_by_name);?>
					</a>
  				<?php 
					echo  ' - '.Text::_('COM_BLOG_DATE').' '.JHTML::_('date',  $this->item->post_date, Text::_('DATE_FORMAT_LC1')) .' - ';   				  				   				
					if($this->item->created_by == $userId
						or in_array(8, $userGroups)) {  
				?>        
						<?php if($this->item->post_image) : ?>
							<?php $delLink = Route::_( 'index.php?option=com_blogg&task=post.delete_mypost_image&id=' 
								.$this->item->id.'&author='.$this->item->created_by, false);?>
								<img src="<?php echo  $images_path; ?>delete.gif"  border="0" alt="Delete" />
								<a href="javascript:void(0);"  onClick="javascript:__fncDeletePostImage('<?php echo $delLink;?>');return false;">
							<?php echo Text::_('COM_BLOG_DELETE_IMAGE');?>
								</a>
						<?php endif; ?>
					<?php
					}
					?>
				</div>
			</tr>

<!-- end post -->
<!-- begin comments -->

	  <tr>
	  	<td align="left" valign="top">
			<div class="clsBGCommentTop">
				<?php echo Text::_('COM_BLOG_COMMENTS').' ('.$this->BlogCommentCount.')';?>
			</div>
		</td>
	  </tr>
	  <?php 

	  $count=1;
	  if (isset($this->item->comments)) {
	  foreach( $this->item->comments as $BlogComment) {
		  $class = ($count%2 != 0) ? 'table_row_first' : 'table_row_second'; 

	  ?>
	  <tr class="<?php echo $class;?>">
		  <td align="left" valign="top" style="padding:3px;">
			 <div class="clsCommentTitle">
			   <img src="<?php echo $images_path; ?>comments.jpg"  border="0" alt="Comments" />
				    <?php echo Text::_($BlogComment->comment_title);?>
			 </div>
			   <div class="clsMyText" style="padding-left:20px;">
			   <?php echo Text::_($BlogComment->comment_desc);?>
               </div>
			   <div class="clsMyText" style="padding-left:20px;">
			   <?php if ($BlogComment->comment_gallery) {
				  echo '<br />'.Text::_('COM_BLOG_INVITE');
				  if ($BlogComment->comment_gallery_text) { ?>
					  <a href="<?php echo($BlogComment->comment_gallery) ?>" target="_blank"><?php echo($BlogComment->comment_gallery_text) ?></a>
				  <?php } else { ?>
					  <a href="<?php echo($BlogComment->comment_gallery) ?>" target="_blank"><?php echo($BlogComment->comment_gallery) ?></a>
				  <?php } } ?>						  
               </div>              			
			   <div id="divBlogDetails">
				  <div align="right">
				    <?php 
					echo Text::_('COM_BLOG_AUTHOR');
  					  $author_link = Route::_( 'index.php?option=com_blogg&view=post&id='.$BlogComment->id,false);
  					  if ($userId > 0) {
					  $author_link = Route::_( 'index.php?option=com_comprofiler&task=userProfile&user='.$BlogComment->created_by, false);
              }
				    ?>
					 <a href="<?php echo $author_link;?>">
						<?php echo Text::_($BlogComment->commentedby);?>
					 </a>
					 <?php 
                     echo  ' - '.Text::_('COM_BLOG_DATE').' '.JHTML::_('date',  $BlogComment->comment_date, Text::_('DATE_FORMAT_LC1')); 
					
					 if($BlogComment->created_by == $userId
						or in_array(8, $userGroups)) {
 							  $delLink = Route::_( 'index.php?option=com_blogg&task=post.delete_comment&id='.$BlogComment->id, false);
					 ?>
  					 <img src="<?php echo $images_path; ?>delete.gif"  border="0" alt="Delete" />
					 <a href="javascript:void(0);"  onClick="javascript:__fncDeleteComment('<?php echo $delLink;?>');return false;">
					 <?php echo Text::_('JACTION_DELETE');?>
					 </a>
					 <?php

					 }
          ?>				
				</div>
 			</div>
		</td>
	  </tr>
	  <tr>
      <td>
        <div class="clsTDBorderTop"></div>
      </td>
    </tr>
	  <?php
		  $count++;
	   }    // end foreach comment
   }
    ?>
	  <tr>
		  <td>&nbsp;</td>
	  </tr>
<!-- end comments -->
 </tbody> 
	</table>

</div>

	<?php if ($canComment) : ?>
		<a href="<?php echo Route::_('index.php?option=com_blogg&task=commentform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo Text::_('COM_BLOG_WRITE_COMMENT'); ?></a>
	<?php endif; ?>

	<?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_blogg.' . $this->item->id) || $this->item->checked_out == Factory::getApplication()->getIdentity()->id; ?>
	<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_blogg&task=post.edit&id='.$this->item->id); ?>"><?php echo Text::_("COM_BLOGG_EDIT_ITEM"); ?></a>
	<?php elseif($canCheckin && $this->item->checked_out > 0) : ?>
	<a class="btn btn-outline-primary" href="<?php echo Route::_('index.php?option=com_blogg&task=post.checkin&id=' . $this->item->id .'&'. Session::getFormToken() .'=1'); ?>"><?php echo Text::_("JLIB_HTML_CHECKIN"); ?></a>

	<?php endif; ?>

	<?php if (Factory::getApplication()->getIdentity()->authorise('core.delete','com_blogg.post.'.$this->item->id)) : ?>

	<a class="btn btn-danger" rel="noopener noreferrer" href="#deleteModal" role="button" data-bs-toggle="modal">
		<?php echo Text::_("COM_BLOGG_DELETE_ITEM"); ?>
	</a>


	<?php echo HTMLHelper::_(
                                    'bootstrap.renderModal',
                                    'deleteModal',
                                    array(
                                        'title'  => Text::_('COM_BLOGG_DELETE_ITEM'),
                                        'height' => '50%',
                                        'width'  => '20%',
                                        
                                        'modalWidth'  => '50',
                                        'bodyHeight'  => '100',
                                        'footer' => '<button class="btn btn-outline-primary" data-bs-dismiss="modal">Close</button><a href="' . Route::_('index.php?option=com_blogg&task=post.remove&id=' . $this->item->id, false, 2) .'" class="btn btn-danger">' . Text::_('COM_BLOGG_DELETE_ITEM') .'</a>'
                                    ),
                                    Text::sprintf('COM_BLOGG_DELETE_CONFIRM', $this->item->id)
                                ); ?>

	<?php endif; ?>
	
	<script language="javascript" type="text/javascript">
 		function  __fncDeletePosts( strLink ){
			if( !confirm("<?php echo Text::_("COM_BLOG_QUESTION_POST");?>")){
				return false;
			}
			window.location = strLink;
		}
 		function  __fncDeletePostImage( strLink ){
			if( !confirm("<?php echo Text::_("COM_BLOG_QUESTION_IMAGE");?>")){
				return false;
			}
			window.location = strLink;
		}
		function  __fncDeleteComment( strLink ){
			if( !confirm("<?php echo Text::_("COM_BLOG_QUESTION_COMMENT");?>")){
				return false;
			}
			window.location = strLink;
		}
		
	</script>
