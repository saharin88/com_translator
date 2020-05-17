<?php

defined('JPATH_BASE') or die;

use Joomla\CMS\
{
	Language\Text,
};

Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');

?>
<button id="translateByMicrosoft" class="btn"><?= Text::_('COM_TRANSLATOR_TOOLBAR_MICROSOFT_LABEL') ?></button>

<script>
    jQuery(document).ready(function ($) {
        $('#translateByMicrosoft').on('click', function () {
            if (document.adminForm.boxchecked.value === '0') {
                alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
            } else {
                $('#adminForm').find('input[name="task"]').val('constants.translateByMicrosoft');
                $('#translateModal').modal('show').find('h3').text('<?= Text::_('COM_TRANSLATOR_TOOLBAR_MICROSOFT_LABEL') ?>');
            }
        });
    });
</script>