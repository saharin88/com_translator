<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\
{
	Factory,
	Router\Route,
	Language\Text,
	Layout\LayoutHelper,
	HTML\HTMLHelper,
	Language\LanguageHelper,
};

if ($this->state->get('filter.compare'))
{
	$languages = LanguageHelper::getKnownLanguages(constant('JPATH_' . strtoupper($this->state->get('filter.client', 'site'))));
	unset($languages[$this->state->get('filter.language', Factory::getLanguage()->getTag())]);
}
else
{
	$languages = [];
}

$doc = Factory::getDocument();
$js  = <<< JS
jQuery(document).ready(function($) {
    $('#compareCheckbox').on('click', function(e) {
        if($(this).is(':checked')) {
            $('#compare').val('1');
        } else {
            $('#compare').val('0');
        }
        this.form.submit();
    });
});
JS;
$css = <<< CSS
span.diff-constants {
    cursor: pointer;
}
CSS;
$doc->addScriptDeclaration($js);
$doc->addStyleDeclaration($css);


?>
<form action="<?= Route::_('index.php?option=com_translator&view=files', false) ?>" method="post" name="adminForm" id="adminForm">


    <div id="j-main-container">

        <div>

            <div class="pull-left">
				<?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['filtersHidden' => false]]) ?>
            </div>

            <div class="pull-right">
                <label class="checkbox">
                    <input type="hidden" name="filter[compare]" value="0" id="compare">
                    <input id="compareCheckbox" type="checkbox"<?= ($this->state->get('filter.compare') ? ' checked' : '') ?>> <?= Text::_('COM_TRANSLATE_SHOW_LANGUAGES_FOR_COMPARE') ?>
                </label>
            </div>

        </div>

		<?php if (empty($this->files))
		{
			?>
            <div class="alert alert-no-items">
				<?= Text::_('JGLOBAL_NO_MATCHING_RESULTS') ?>
            </div>
			<?php
		}
		else
		{
			?>
            <table class="table table-striped" id="overrideList">
                <thead>
                <tr>
                    <th class="center" width="1%">
						<?= Text::_('COM_TRANSLATOR_NUMBER') ?>
                    </th>
                    <th>
						<?= Text::_('COM_TRANSLATOR_FILE_NAME') ?>
                    </th>
                    <th class="center">
						<?= Text::_('COM_TRANSLATOR_NUMBER_OF_CONSTANTS') ?>
                    </th>
					<?php
					if (!empty($languages))
					{
						foreach ($languages as $language)
						{
							?>
                            <th class="center">
								<?= $language['nativeName'] ?>
                            </th>
							<?php
						}
					}
					?>

                </tr>
                </thead>
                <tbody>
				<?php
				$i = 0;
				foreach ($this->files AS $file)
				{
					$i++;
					$fileKey        = $this->state->get('filter.client', 'site') . ':' . $file;
					$fileWithoutTag = stristr($file, '.');
					$constants      = TranslatorHelper::getConstants($fileKey);
					$countConstants = count($constants);
					?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
							<?= $i ?>
                        </td>
                        <td>
							<?= HTMLHelper::link(Route::_('index.php?option=com_translator&view=constants&file=' . $fileKey, false), $file) ?>
                        </td>
                        <td class="center">
							<?= $countConstants ?>
                        </td>
						<?php
						if (!empty($languages))
						{
							foreach ($languages as $language)
							{
								$compareFile      = $this->state->get('filter.client', 'site') . ':' . $file;
								$compareFileKey   = $this->state->get('filter.client', 'site') . ':' . $language['tag'] . $fileWithoutTag;
								$compareConstants = [];
								try
								{
									$compareConstants      = TranslatorHelper::getConstants($compareFileKey);
									$countCompareConstants = count($compareConstants);
								}
								catch (Exception $e)
								{
									$countCompareConstants = '-';
								}

								$diffConstants = false;

								if ($countCompareConstants !== '-' && $countCompareConstants > $countConstants)
								{
									$diffConstants = array_diff_key($compareConstants, $constants);
								}

								?>
                                <td class="center">
									<?php
									echo $countCompareConstants . ($diffConstants === false ? '' : ' <span class="hasTooltip diff-constants" title="' . implode('<br/>', array_keys($diffConstants)) . '">(' . count($diffConstants) . ')</span>');
									?>
                                </td>
								<?php
							}
						}
						?>
                    </tr>
					<?php
				}
				?>
                </tbody>
            </table>
			<?php
		}
		?>

        <input type="hidden" name="form_submited" value="1">
        <input type="hidden" name="task" value="">
		<?= HTMLHelper::_('form.token') ?>

    </div>

</form>
