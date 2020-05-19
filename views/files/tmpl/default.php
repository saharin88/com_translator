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
	Session\Session,
};

Text::script('COM_TRANSLATOR_CONFIRM_IMPORT_ALL');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
HTMLHelper::_('formbehavior.chosen', 'select');

$compare = $this->state->get('filter.compare', []);

$languages = LanguageHelper::getKnownLanguages(constant('JPATH_' . strtoupper($this->state->get('filter.client', 'site'))));
unset($languages[$this->state->get('filter.language', Factory::getLanguage()->getTag())]);


$doc = Factory::getDocument();
$js  = <<< JS
jQuery(document).ready(function($) {
    
    $('.diff-constants').click(function(){
        return confirm(Joomla.Text._('COM_TRANSLATOR_CONFIRM_IMPORT_ALL'));
    });
    
    let timeout,
        tCount,
        count = 5,
        dCount = function() {
            count--;
            $('#compareCount').text(count);
            if(count > 0) {
                tCount = setTimeout(dCount, 1000);
            }
        };
    
    $('#selectForCompare').on('change', function(e) {
        let _this = this;
        clearTimeout(timeout);
        clearTimeout(tCount);
        count = 5;
        $('#compareCount').removeClass('hidden').text(count);
        tCount = setTimeout(dCount, 1000);
        timeout = setTimeout(function() {
            _this.form.submit();
        }, 5000);
    });
});
JS;
$css = <<< CSS
span.diff-constants {
    cursor: pointer;
}
#compare div.controls {
    position: relative;
}
#compareCount {
    position: absolute;
    right: 5px;
    bottom: 3px;
    z-index: 1001;
}
CSS;
$doc->addScriptDeclaration($js);
$doc->addStyleDeclaration($css);


?>
<form action="<?= Route::_('index.php?option=com_translator&view=files', false) ?>" method="post" name="adminForm" id="adminForm">


    <div id="j-main-container">

        <div class="clearfix">

            <div class="pull-left">
				<?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['filtersHidden' => false]]) ?>
            </div>

			<?php
			if (!empty($languages))
			{
				?>
                <div class="pull-right" id="compare">
                    <div class="control-group">
                        <label class="control-label" for="selectForCompare"><?= Text::_('COM_TRANSLATE_SHOW_LANGUAGES_FOR_COMPARE') ?></label>
                        <div class="controls">
                            <span id="compareCount" class="muted hidden">5</span>
                            <select name="filter[compare][]" multiple="multiple" id="selectForCompare">
								<?php
								foreach ($languages AS $language)
								{
									?>
                                    <option value="<?= $language['tag'] ?>"<?= (in_array($language['tag'], $compare) ? ' selected' : '') ?>><?= $language['nativeName'] ?></option>
									<?php
								}
								?>
                            </select>
                        </div>
                    </div>
                </div>
				<?php
			}
			?>

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
					if (!empty($languages) && count($compare))
					{
						foreach (array_intersect_key($languages, array_flip($compare)) as $language)
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
						if (!empty($languages) && count($compare))
						{
							foreach (array_intersect_key($languages, array_flip($compare)) as $language)
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

									if ($countCompareConstants === '-')
									{
										echo HTMLHelper::_('link', Route::_('index.php?option=com_translator&task=file.create&file=' . $compareFileKey . '&' . Session::getFormToken() . '=1', false), Text::_('COM_TRANSLATOR_ADD_FILE'));
									}
									else
									{
										echo $countCompareConstants;
										if ($diffConstants !== false)
										{
											$title = implode('<br/>', array_keys($diffConstants));
											?>
                                            <a href="<?= Route::_('index.php?option=com_translator&task=constants.importAll&file=' . $fileKey . '&from_file=' . $compareFileKey . '&' . Session::getFormToken() . '=1', false) ?>" class="hasTooltip diff-constants text-error" title="<?= $title ?>">(<?= count($diffConstants) ?>)</a>
											<?php
										}
									}
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
