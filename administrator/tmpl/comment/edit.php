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

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form
	action="<?php echo Route::_('index.php?option=com_blogg&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="comment-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'comment')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'comment', Text::_('COM_BLOGG_TAB_COMMENT', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_BLOGG_FIELDSET_COMMENT'); ?></legend>
				<?php echo $this->form->renderField('comment_title'); ?>
				<?php echo $this->form->renderField('comment_desc'); ?>
				<?php echo $this->form->renderField('comment_gallery'); ?>
				<?php echo $this->form->renderField('comment_gallery_text'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	<input type="hidden" name="jform[post_id]" value="<?php echo $this->item->post_id; ?>" />
	<input type="hidden" name="jform[comment_date]" value="<?php echo $this->item->comment_date; ?>" />
	<input type="hidden" name="jform[comment_update]" value="<?php echo $this->item->comment_update; ?>" />
	<input type="hidden" name="jform[comment_ip]" value="<?php echo $this->item->comment_ip; ?>" />
	<input type="hidden" name="jform[comment_hit]" value="<?php echo $this->item->comment_hit; ?>" />

	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
