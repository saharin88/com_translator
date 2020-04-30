<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\
{
	Language\Text,
	Router\Route,
	HTML\HTMLHelper,
	Layout\LayoutHelper,
	Factory,
	Session\Session
};

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.modal', 'a.modal', ['size' => ['x' => '730', 'y' => '180']]);
Text::script('COM_TRANSLATOR_REMOVE_CONFIRM');
Text::script('JERROR_AN_ERROR_HAS_OCCURRED');

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

.imported-color {
    background-color: rgb(223, 240, 216);
    width: 14px;
    height: 14px;
    display: block;
    float: left;
    margin: 2px 5px 0 0;
}

.clearImported {
    margin-left: 5px;
    text-transform: lowercase;
}

.clearImported:after {
    content: ')';
}

.clearImported:before {
    content: '(';
}

input[name="checkAllImported"] {
    float: left;
    margin: 2px 10px 0 0;
    background-color: rgb(223, 240, 216);
}
CSS;


$js = <<< JS

jQuery(document).ready(function($) {
    
    $('input[name="checkAllImported"]').on('click', function(e) {
        
        let boxchecked = $('table#constantList').siblings('input[name="boxchecked"]'),
            totalChecked = parseInt(boxchecked.val()),
            imported = $('tr.success', 'table#constantList').find('input[type="checkbox"]'),
            checked = $(this).is(':checked');
        
        imported.prop('checked', checked);
        
        imported.each(function() {
            this.checked = checked;
        });
        
        if(checked)  {
            totalChecked +=imported.length;
        } else {
            totalChecked -=imported.length;
        }
        
        boxchecked.val(totalChecked)
        
    });
    
    $('body').on('submit', 'form#constantForm', function(e) {
        
        e.preventDefault();
        e.stopPropagation();
        
        let _form = $(this);
        
        $.ajax({
            url: _form.attr('action'),
            data: _form.serializeArray(),
            method: 'POST',
            dataType: 'json',
            success: function(resp) {
                if(resp.success) {
                    $('span.' + resp.data.key.toLowerCase()).html(resp.data.value);
                    Joomla.renderMessages({"message" : [resp.message]});
                } else {
                    Joomla.renderMessages({"error" : [resp.message]});
                }
                
                if(resp.messages) {
                    Joomla.renderMessages(resp.messages);
                }
                jModalClose();
            },
            error: function() {
                Joomla.renderMessages({"error" : [Joomla.Text._('JERROR_AN_ERROR_HAS_OCCURRED')]});
                jModalClose();
            }
        });
        
    });
    
});

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

$app      = Factory::getApplication();
$file     = $this->state->get('file', $app->input->get('file', null, 'raw'));
$imported = Factory::getSession()->get($file, [], 'com_translator.imported');

$search = $this->state->get('filter.search');

?>

<form action="<?= Route::_('index.php?option=com_translator&view=constants&file=' . $file, false) ?>" method="post" name="adminForm" id="adminForm">

    <div id="j-main-container">

		<?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this]) ?>

		<?php
		if (empty($this->items))
		{
			?>
            <div class="alert alert-no-items"><?= Text::_(empty($search) ? 'COM_TRANSLATOR_NO_CONSTANTS_IN_FILE' : 'COM_TRANSLATOR_NO_CONSTANTS_FOUND') ?></div>
			<?php
		}
		else
		{
			?>

            <div>
                <div class="pull-right"><span class="imported-color"></span> - <?= Text::_('COM_TRANSLATOR_IMPORTED') . HTMLHelper::_('link', Route::_('index.php?option=com_translator&task=constants.clearImported&file=' . $file . '&' . Session::getFormToken() . '=1', false), Text::_('JCLEAR'), ['class' => 'clearImported']) ?></div>
                <div class="pull-right">
                    <input type="checkbox" name="checkAllImported" title="<?= Text::_('COM_TRANSLATOR_CHECK_ALL_IMPORTED') ?>" class="hasTooltip">
                </div>
            </div>

            <table class="table table-striped" id="constantList">
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
					if (in_array($key, $imported))
					{
						$markImported = true;
					}
					else
					{
						$markImported = false;
					}
					?>

                    <tr class="row<?= ($i % 2) . ($markImported ? ' success' : '') ?>">

                        <td class="center">
							<?= HTMLHelper::_('grid.id', $i, $key) ?>
                        </td>

                        <td class="center">
							<?= $i ?>
                        </td>

                        <td>
							<?= HTMLHelper::link(Route::_('index.php?option=com_translator&view=constant&key=' . $key . '&file=' . $file, false), $key, ['class' => strtolower($key)]) ?>
                        </td>

                        <td class="center">
                            <span class="<?= strtolower($key) ?>"><?= $val ?></span> <a class="modal" href="<?= Route::_('index.php?option=com_translator&view=constant&tmpl=component&key=' . $key . '&file=' . $file . '&ajax=1', false) ?>"><span class="icon-pencil small"> </span></a>
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
