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

<div class="post-edit front-end-edit">
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

		<form id="form-post"
			  action="<?php echo Route::_('index.php?option=com_blogg&task=postform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
	<input type="hidden" name="jform[id]" value="<?php echo isset($this->item->id) ? $this->item->id : ''; ?>" />

	<input type="hidden" name="jform[state]" value="<?php echo isset($this->item->state) ? $this->item->state : ''; ?>" />

	<input type="hidden" name="jform[ordering]" value="<?php echo isset($this->item->ordering) ? $this->item->ordering : ''; ?>" />

	<input type="hidden" name="jform[checked_out]" value="<?php echo isset($this->item->checked_out) ? $this->item->checked_out : ''; ?>" />

	<input type="hidden" name="jform[checked_out_time]" value="<?php echo isset($this->item->checked_out_time) ? $this->item->checked_out_time : ''; ?>" />

				<?php echo $this->form->getInput('created_by'); ?>
				<?php echo $this->form->getInput('modified_by'); ?>
	<input type="hidden" name="jform[post_date]" value="<?php echo isset($this->item->post_date) ? $this->item->post_date : ''; ?>" />

	<input type="hidden" name="jform[post_update]" value="<?php echo isset($this->item->post_update) ? $this->item->post_update : ''; ?>" />

	<input type="hidden" name="jform[post_ip]" value="<?php echo isset($this->item->post_ip) ? $this->item->post_ip : ''; ?>" />

	<input type="hidden" name="jform[post_hits]" value="<?php echo isset($this->item->post_hits) ? $this->item->post_hits : ''; ?>" />

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'post')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'post', Text::_('COM_BLOGG_TAB_POST', true)); ?>
	<?php echo $this->form->renderField('post_title'); ?>

	<?php echo $this->form->renderField('post_desc'); ?>

	<?php echo $this->form->renderField('post_image'); ?>

	<?php echo $this->form->renderField('ext_gallery'); ?>

	<?php echo $this->form->renderField('ext_gallery_text'); ?>

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
					   href="<?php echo Route::_('index.php?option=com_blogg&task=postform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_blogg"/>
			<input type="hidden" name="task"
				   value="postform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
