<?php

defined('JPATH_BASE') or die;

use Joomla\CMS\
{
	Language\Text,
};

Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');

?>
<button id="translateByGoogle" class="btn"><?= Text::_('COM_TRANSLATOR_TOOLBAR_GOOGLE_LABEL') ?></button>

<script>
    jQuery(document).ready(function ($) {
        $('#translateByGoogle').on('click', function () {

            console.log(typeof document.adminForm.boxchecked.value + ' ' +document.adminForm.boxchecked.value);

            if (document.adminForm.boxchecked.value === '0') {
                alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
            } else {
                $('#translateByGoogleModal').modal('show');
            }
        });
    });
</script>