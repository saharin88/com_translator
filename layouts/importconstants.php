<?php

defined('JPATH_BASE') or die;

use Joomla\CMS\
{
	Uri\Uri,
	Factory,
	HTML\HTMLHelper,
	Language\Text,
};

/**
 * Layout variables
 * ---------------------
 * @var array $displayData
 */


$url = Uri::base() . 'index.php?option=com_translator&view=constants&layout=import&tmpl=component&to_file=' . $displayData['to_file'] . '&file=';
$doc = Factory::getDocument();
$doc->addStyleDeclaration('#modal-' . $displayData['name'] . ' .modal-body{max-height:80vh;overflow:auto;}');
?>
<script>
    jQuery(document).ready(function ($) {
        $('#<?=$displayData['name']?>').on('change', function () {
            var file = $(this).val();
            $(this).prop('selectedIndex', 0).trigger("liszt:updated");
            if (file) {
                $('#modal-<?=$displayData['name']?>').remove();
                $('<div id="modal-<?=$displayData['name']?>" class="modal hide"><div class="modal-header"><button type="button" class="close novalidate" data-dismiss="modal">×</button><h3><?=$displayData['label']?></h3></div><div class="modal-body"></div></div>').appendTo('body').children('.modal-body').load('<?= $url ?>' + file, function () {
                    $('#modal-<?=$displayData['name']?>').modal("show");
                    // todo if close? set value null
                });
            }
        });
    });
</script>

<div class="form-inline pull-right">
    <label for="<?= $displayData['name'] ?>"><?= Text::_('COM_TRANSLATOR_IMPORT_FROM_LABEL') ?></label>
    <select id="<?= $displayData['name'] ?>">
        <option value=""><?= Text::_('JSELECT') ?></option>
		<?= HTMLHelper::_('select.options', $displayData['files']) ?>
    </select>
</div>
