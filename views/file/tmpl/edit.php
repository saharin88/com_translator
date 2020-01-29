<?php
defined('_JEXEC') or die;
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');
$doc = JFactory::getDocument();
$doc->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "file.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};
');
?>
<script>
    jQuery(document).ready(function ($) {

        var extension = $('#jform_extension'),
            language = $('#jform_language'),
            langfn = function () {
                var val = $(':selected', extension).text(),
                    val_split = val.split(' - ');
                if (typeof val_split[1] !== 'undefined') {
                    $('option', language).each(function (index, value) {
                        var option_split = $(this).text().split(' - ');
                        if (typeof option_split[1] !== 'undefined') {
                            if (option_split[1] === val_split[1]) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        } else {
                            $(this).show();
                        }
                    });
                } else {
                    $('option', language).show();
                }
                language.val('').trigger('liszt:updated');
            };
        langfn();
        extension.on('change', function () {
            langfn();
        });
    });
</script>
<form action="<?php echo JRoute::_('index.php?option=com_translator&layout=edit'); ?>" method="post" name="adminForm"
      id="adminForm" class="form-validate">

    <div class="form-horizontal">

		<?= $this->form->renderFieldset('general') ?>

        <input type="hidden" name="task" value=""/>

		<?php echo JHtml::_('form.token'); ?>

    </div>

</form>