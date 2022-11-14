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
use \Blogg\Component\Blogg\Site\Helper\BloggHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_blogg', JPATH_SITE);

$user    = Factory::getApplication()->getIdentity();
$canEdit = BloggHelper::canUserEdit($this->item, $user);


?>

<div class="comment-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
		<?php throw new \Exception(Text::_('COM_BLOGG_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_BLOGG_EDIT_ITEM_TITLE', $this->item->id); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_BLOGG_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-comment"
			  action="<?php echo Route::_('index.php?option=com_blogg&task=commentform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo isset($this->item->state) ? $this->item->state : ''; ?>" />

	<input type="hidden" name="jform[ordering]" value="<?php echo isset($this->item->ordering) ? $this->item->ordering : ''; ?>" />

	<input type="hidden" name="jform[checked_out]" value="<?php echo isset($this->item->checked_out) ? $this->item->checked_out : ''; ?>" />

	<input type="hidden" name="jform[checked_out_time]" value="<?php echo isset($this->item->checked_out_time) ? $this->item->checked_out_time : ''; ?>" />

				<?php echo $this->form->getInput('created_by'); ?>
				<?php echo $this->form->getInput('modified_by'); ?>
	<input type="hidden" name="jform[post_id]" value="<?php echo isset($this->item->post_id) ? $this->item->post_id : ''; ?>" />

	<input type="hidden" name="jform[comment_date]" value="<?php echo isset($this->item->comment_date) ? $this->item->comment_date : ''; ?>" />

	<input type="hidden" name="jform[comment_update]" value="<?php echo isset($this->item->comment_update) ? $this->item->comment_update : ''; ?>" />

	<input type="hidden" name="jform[comment_ip]" value="<?php echo isset($this->item->comment_ip) ? $this->item->comment_ip : ''; ?>" />

	<input type="hidden" name="jform[comment_hit]" value="<?php echo isset($this->item->comment_hit) ? $this->item->comment_hit : ''; ?>" />

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'comment')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'comment', Text::_('COM_BLOGG_TAB_COMMENT', true)); ?>
	<?php echo $this->form->renderField('comment_title'); ?>

	<?php echo $this->form->renderField('comment_desc'); ?>

	<?php echo $this->form->renderField('comment_gallery'); ?>

	<?php echo $this->form->renderField('comment_gallery_text'); ?>

	<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger"
					   href="<?php echo Route::_('index.php?option=com_blogg&task=commentform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_blogg"/>
			<input type="hidden" name="task"
				   value="commentform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
