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
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\User\UserFactoryInterface;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user       = Factory::getApplication()->getIdentity();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_blogg') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'postform.xml');
$canEdit    = $user->authorise('core.edit', 'com_blogg') && file_exists(JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'postform.xml');
$canCheckin = $user->authorise('core.manage', 'com_blogg');
$canChange  = $user->authorise('core.edit.state', 'com_blogg');
$canDelete  = $user->authorise('core.delete', 'com_blogg');

// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_blogg.list');
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
	  name="adminForm" id="adminForm">
	<?php if(!empty($this->filterForm)) { echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); } ?>
	<div class="table-responsive">
		<table class="table table-striped" id="postList">
			<thead>
			<tr>
				<div class="clsLinkedBlog">
					<div class="clsLinkedBlog_title"><?php echo Text::_($this->blogTitle); ?></div>
				</div>
										
				<div id="clsTopMenuBg">
					<?php if($userId > 0){ ?>
					<div class="clsFloatRight"><img src="<?php echo $this->baseurl; ?>/media/com_blogg/Images/icons/add_post.png"  border="0" width="16px" align="bottom" alt="Add New Post" />
						<a href="<?php echo Route::_( 'index.php?option=com_blogg&task=postform.edit&id=0', false ); ?>"><?php echo Text::_('COM_BLOG_ADD_NEW_POST');?></a>
					</div>
					<?php } else {?>
					<div class="clsFloatRight"><a href="<?php echo Route::_('index.php?option=com_users&view=login'. '&return='. base64_encode(JURI::getInstance()->toString()), false);?>"><?php echo Text::_('COM_BLOG_LOGIN_TO_POST');?> </a>
					</div>
					<?php } ?>
					<div class="clsClearBoth"></div>
				</div>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<div class="pagination">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php  foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_blogg'); ?>
				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_blogg')): ?>
				<?php $canEdit = Factory::getApplication()->getIdentity()->id == $item->created_by; ?>
				<?php endif; ?>
			<?php $author_link = Route::_( 'index.php?view=posts',false);
				  if ($userId > 0) 
					{$author_link = Route::_( 'index.php?option=com_comprofiler&task=userProfile&user='.$item->created_by, false);}
			 ?>

			<tr class="row<?php echo $i % 2; ?>">
					
				<?php $canCheckin = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_blogg.' . $item->id) || $item->checked_out == Factory::getApplication()->getIdentity()->id; ?>
				<?php if($canCheckin && $item->checked_out > 0) : ?>
					<a href="<?php echo Route::_('index.php?option=com_blogg&task=post.checkin&id=' . $item->id .'&'. Session::getFormToken() .'=1'); ?>">
					<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'post.', false); ?></a>
				<?php endif; ?>
				<div class="clsPostTitle"><a href="<?php echo Route::_('index.php?option=com_blogg&view=post&id='.(int) $item->id, false); ?>"><?php echo $this->escape($item->post_title); ?></a></div>
				<div class="clsTDBorderTop"></div>
				<?php if($item->post_image){?>
					<img src="<?php echo $this->baseurl.'/media/com_blogg/th'.$item->post_image;?>"  border="0" alt="Blog Image" align="left" class="clsImgPad" />
				<?php } ?>		
				<div class="clsMyText"><?php print( substr( Text::_( $item->post_desc),0,500));?>...
				<a href="<?php echo Route::_( 'index.php?option=com_blogg&view=post&id='.$item->id, false); ?>">
					<?php echo Text::_('COM_BLOG_READ_MORE');?>
				</a>
				</div>
			</tr>
			<tr></tr>
				
		<div id="divBlogDetails">
			<div align="right">
				<?php echo Text::_('COM_BLOG_AUTHOR');?>
 				<a href="<?php echo $author_link;?>"><?php echo Text::_($item->created_by);?></a>
				<?php echo  ' - '.Text::_('COM_BLOG_DATE').' '.JHTML::_('date',  $item->post_date, Text::_('DATE_FORMAT_LC1')); ?>
				<img src="<?php echo $this->baseurl; ?>/media/com_blogg/Images/icons/comments.gif"  border="0" alt="Comments" />
				<a href="<?php echo Route::_( 'index.php?view=post&id='.$item->id, false); ?>">
					<?php	//echo Text::_('COM_BLOG_COMMENTS').' ('.max(0, $item->nb_comments).')';?>
					<?php	echo Text::_('COM_BLOG_COMMENTS').' ('.max(0, 0).')';?>
				</a>
				<img src="<?php echo $this->baseurl; ?>/media/com_blogg/Images/icons/readmore.png"  border="0" title="Read More..." />
				<a href="<?php echo Route::_( 'index.php?view=post&id='.$item->id, false); ?>">
					<?php echo Text::_('COM_BLOG_READ_MORE');?>
				</a>
				
			</div>
		</div>

				
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_blogg&task=postform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo Text::_('COM_BLOGG_ADD_ITEM'); ?></a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value=""/>
	<input type="hidden" name="filter_order_Dir" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
	if($canDelete) {
		$wa->addInlineScript("
			jQuery(document).ready(function () {
				jQuery('.delete-button').click(deleteItem);
			});

			function deleteItem() {

				if (!confirm(\"" . Text::_('COM_BLOGG_DELETE_MESSAGE') . "\")) {
					return false;
				}
			}
		", [], [], ["jquery"]);
	}
?>
