<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\
{
	HTML\HTMLHelper,
	Factory,
	Router\Route,
	Layout\LayoutHelper,
	Language\Text,
};

$app = Factory::getApplication();
$doc = Factory::getDocument();
$doc->addScriptDeclaration('
	Joomla.submitbutton = function(task) {
		if (task == "constant.delete") {
			if(!confirm("' . Text::_('COM_TRANSLATOR_REMOVE_CONFIRM') . '")) {
			    return;
			}
		}
		Joomla.submitform(task, document.getElementById("adminForm"));
	};
');
?>
<form action="<?php echo Route::_('index.php?option=com_translator&view=file&path=' . $app->input->getString('path')); ?>"
      method="post" name="adminForm"
      id="adminForm">

	<?php
	if (!empty($this->sidebar))
	{
	?>
    <div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
    </div>

    <div id="j-main-container" class="span10">
		<?php
		}
		else
		{
		?>

        <div id="j-main-container">

			<?php
			}
			?>

			<?= LayoutHelper::render('joomla.searchtools.default', array('view' => $this)) ?>

			<?php if (empty($this->rows))
			{
				?>
                <div class="alert alert-no-items">
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </div>
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
							<?= Text::_('COM_TRANSLATOR_CONSTANT') ?>
                        </th>

                        <th class="center">
							<?= Text::_('COM_TRANSLATOR_CONSTANT_VALUE') ?>
                        </th>

                    </tr>
                    </thead>
                    <tbody>
					<?php
					$i = 0;
					foreach ($this->rows AS $k => $row)
					{
						if (empty(trim($row)))
						{
							continue;
						}
						$i++;
						$constant = explode('=', $row);
						?>

                        <tr class="row<?php echo $i % 2; ?>">


                            <td class="center">
								<?php echo HTMLHelper::_('grid.id', $k, urlencode($row)); ?>
                            </td>

                            <td class="center">
								<?= $i ?>
                            </td>

                            <td>
								<?= HTMLHelper::link(Route::_('index.php?option=com_translator&view=constant&row=' . urlencode($row) . '&file=' . $this->state->get('file.path'), false), $constant[0]) ?>
                            </td>

                            <td class="center">
								<?= str_replace('"', '', $constant[1]) ?>
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

            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="boxchecked" value="0"/>
			<?= HTMLHelper::_('form.token') ?>

        </div>

</form>