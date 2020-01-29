<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\
{
	Factory,
	Router\Route,
	Language\Text,
	Layout\LayoutHelper,
	HTML\HTMLHelper
};

?>
<form action="<?= Route::_('index.php?option=com_translator&view=files', false) ?>" method="post" name="adminForm" id="adminForm">


    <div id="j-main-container">

		<?= LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['filtersHidden' => false]]) ?>

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
                </tr>
                </thead>
                <tbody>
				<?php
				$i = 0;
				foreach ($this->files AS $file)
				{
					$i++;
					?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
							<?= $i ?>
                        </td>
                        <td>
							<?= HTMLHelper::link(Route::_('index.php?option=com_translator&view=constants&file=' . $this->state->get('filter.client', 'site') . ':' . $file, false), $file) ?>
                        </td>
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
