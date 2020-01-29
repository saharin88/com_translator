<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\
{
	Language\Text,
	Router\Route,
	HTML\HTMLHelper,
	Layout\LayoutHelper,
	Factory,
};

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.modal', 'a.modal', ['size' => ['x' => '730', 'y' => '180']]);
Text::script('COM_TRANSLATOR_REMOVE_CONFIRM');

$doc = Factory::getDocument();

$css = <<< CSS
.btns-row {
    min-height: 30px;
    float: right;
    min-width: 140px;
    margin-top: -40px;
    margin-right: 60px;
}

.btn-import {
    position: fixed;
    z-index: 1000;
}
CSS;


$js = <<< JS

Joomla.submitbutton = function (task) {

    if (task === "constant.delete") {
        if (!confirm(Joomla.JText._('COM_TRANSLATOR_REMOVE_CONFIRM'))) {
            return;
        }
    }

    Joomla.submitform(task, document.getElementById("adminForm"));

    Joomla.isChecked = function (isitchecked, form) {
        if (typeof form === 'undefined') {
            var forms = document.getElementsByName('adminForm');
            if (forms.length > 1) {
                form = forms[forms.length - 1];
            } else {
                form = forms[0];
            }
        }

        form.boxchecked.value = isitchecked ? parseInt(form.boxchecked.value) + 1 : parseInt(form.boxchecked.value) - 1;

        // If we don't have a checkall-toggle, done.
        if (!form.elements['checkall-toggle']) return;

        // Toggle main toggle checkbox depending on checkbox selection
        var c = true,
            i, e, n;

        for (i = 0, n = form.elements.length; i < n; i++) {
            e = form.elements[i];

            if (e.type === 'checkbox' && e.name !== 'checkall-toggle' && !e.checked) {
                c = false;
                break;
            }
        }

        form.elements['checkall-toggle'].checked = c;

    };
};

JS;

$doc->addStyleDeclaration($css);
$doc->addScriptDeclaration($js);

$app  = Factory::getApplication();
$file = $this->state->get('file', $app->input->get('file', null, 'raw'));

?>

<form action="<?= Route::_('index.php?option=com_translator&view=constants&file=' . $file, false) ?>" method="post" name="adminForm" id="adminForm">

    <div id="j-main-container">

		<?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]) ?>

		<?php
		if (empty($this->items))
		{
			?>
            <div class="alert alert-no-items"><?= Text::_('COM_TRANSLATOR_NO_CONSTANTS_IN_FILE') ?></div>
			<?php
		}
		else
		{
			?>

            <table class="table table-striped" id="overrideList">
                <thead>
                <tr>

                    <th width="1%" class="center">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>

                    <th class="center" width="1%">
						<?= Text::_('COM_TRANSLATOR_NUMBER') ?>
                    </th>

                    <th>
						<?= Text::_('COM_TRANSLATOR_CONSTANT_KEY') ?>
                    </th>

                    <th class="center">
						<?= Text::_('COM_TRANSLATOR_CONSTANT_VALUE') ?>
                    </th>

                </tr>
                </thead>
                <tbody>
				<?php
				$i = 0;
				foreach ($this->items AS $key => $val)
				{
					$i++;
					$row = urlencode(strtoupper($key) . " = \"" . $val . "\"");
					?>

                    <tr class="row<?php echo $i % 2; ?>">

                        <td class="center">
							<?= HTMLHelper::_('grid.id', $i, $row) ?>
                        </td>

                        <td class="center">
							<?= $i ?>
                        </td>

                        <td>
							<?= HTMLHelper::link(Route::_('index.php?option=com_translator&view=constant&row=' . $row . '&file=' . $file, false), $key) ?>
                        </td>

                        <td class="center">
							<?= $val ?> <a class="modal" href="<?= Route::_('index.php?option=com_translator&view=constant&tmpl=component&row=' . $row . '&file=' . $file, false) ?>"><span class="icon-pencil small"> </span></a>
                        </td>

                    </tr>

					<?php
				}
				?>
                </tbody>
            </table>

			<?php
		}

		echo HTMLHelper::_('bootstrap.renderModal', 'translateByGoogleModal', [
			'title'      => Text::_('COM_TRANSLATOR_TOOLBAR_GOOGLE_LABEL'),
			'modalWidth' => '30',
			'footer'     => '<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton(\'constants.translateByGoogle\');">' . Text::_('COM_TRANSLATOR_TRANSLATE') . '</button>'
		], '<div class="container-popup form-horizontal">' . $this->googleForm->renderFieldset('default') . '</div>');

		?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
		<?= HTMLHelper::_('form.token') ?>

    </div>

</form>
