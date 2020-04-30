<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Factory,
	HTML\HTMLHelper,
	Router\Route,
	Language\Text,
};

HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tooltip');

$doc = Factory::getDocument();
$doc->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "constant.cancel" || document.formvalidator.isValid(document.getElementById("constantForm")))
		{
			Joomla.submitform(task, document.getElementById("constantForm"));
		}
	};
');

?>


<form action="<?= Route::_('index.php?option=com_translator&file=' . $this->state->get('file') . (empty($this->state->get('key')) ? '' : '&key=' . $this->state->get('key')), 'false') ?>" method="post" name="constantForm" id="constantForm" class="form-validate">

    <div class="form-horizontal">

        <div class="row-fluid">

            <div class=" span12">
				<?= $this->form->renderFieldset('general') ?>
            </div>

        </div>

		<?= HTMLHelper::_('form.token') ?>

    </div>

	<?php
	if (Factory::getApplication()->input->get->getString('tmpl') === 'component')
	{
		?>
        <input type="hidden" name="task" value="constant.save<?= (empty(Factory::getApplication()->input->get->get('ajax')) ? '' : 'Ajax') ?>">
        <button type="submit" class="btn button-new btn-success"><?= Text::_('JSAVE') ?></button>
		<?php
	}
	else
	{
		?>
        <input type="hidden" name="task" value="">
		<?php
	}
	?>

</form>
