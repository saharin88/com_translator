<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\
{
	Factory,
	Language\Text,
	Layout\FileLayout,
	Toolbar\ToolbarButton,
};

class JToolbarButtonImportConstants extends ToolbarButton
{

	public function fetchButton($type = 'ImportConstants', $name = '', $label = '', $files = [], $to_file = null)
	{
		if (empty($to_file) || file_exists(TranslatorHelper::getPath($to_file)) === false)
		{
			return null;
		}

		$options = [
			'name'    => $name,
			'label'   => Text::_($label),
			'files'   => $files,
			'to_file' => $to_file
		];

		$layout = new FileLayout('com_translator.toolbar.import');

		return $layout->render($options);
	}

	public function fetchId($type, $name)
	{
		return $this->_parent->getName() . '-importconstants';
	}

}
