<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Factory,
	Language\Text,
	MVC\View\HtmlView,
	Toolbar\ToolbarHelper,
	HTML\HTMLHelper,
	Toolbar\Toolbar,
	Layout\FileLayout,
	Router\Route,
};

class TranslatorViewConstants extends HtmlView
{
	public $activeFilters;

	/**
	 * @var JObject
	 * @since version
	 */
	public $state;
	public $filterForm;
	public $googleForm;
	public $to_file;
	public $items;

	public function display($tpl = null)
	{
		$app         = Factory::getApplication();
		$this->items = $this->get('Items');
		$this->state = $this->get('State');

		switch ($this->getLayout())
		{
			case 'default':

				$this->filterForm    = $this->get('FilterForm');
				$this->activeFilters = $this->get('ActiveFilters');
				$this->googleForm    = $this->get('GoogleForm');
				break;

			case 'import':

				$this->to_file = $app->input->get('to_file', null, 'raw');

				if (empty($this->to_file))
				{
					throw new Exception('Empty file to import');
				}

				if (!empty($this->items))
				{
					/** @var TranslatorModelConstants $model */
					$model      = $this->getModel();
					$diff_items = $model->getItems($this->to_file);
					if (!empty($diff_items))
					{
						$this->items = array_diff_key($this->items, $diff_items);
					}
				}

				break;

			default;

				throw new Exception('Unknown layout');
		}


		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$doc = Factory::getDocument();
		$app = Factory::getApplication();

		ToolbarHelper::title(Text::sprintf('COM_TRANSLATOR_LANGUAGE_FILE_S', str_replace(':', '/', $this->state->get('file'))), 'file-2');

		Factory::getDocument()->addStyleDeclaration('#toolbar-heart {float:right;}');
		ToolbarHelper::link('https://www.liqpay.ua/en/checkout/saharin88', 'Donate', 'heart');

		if ($this->getLayout() === 'default')
		{
			$bar = Toolbar::getInstance('toolbar');

			ToolbarHelper::link(JRoute::_('index.php?option=com_translator&view=files', false), Text::_('COM_TRANSLATOR_BACK_TO_LIST'), 'arrow-left-3');

			ToolbarHelper::addNew('constant.add', 'COM_TRANSLATOR_NEW_CONSTANT');


			$layout = new FileLayout('joomla.toolbar.translate.google');
			$bar->appendButton('Custom', $layout->render());


			ToolbarHelper::deleteList('', 'constants.delete');
			$files = $this->get('OtherLangs');
			if (count($files))
			{
				HTMLHelper::_('formbehavior.chosen', 'select');
				$doc->addStyleDeclaration('#toolbar-importconstants{float:right;}#toolbar-importconstants label{float:left; line-height:30px; margin-right:10px;}');
				$bar->addButtonPath(__DIR__ . '/../../helpers/button');
				$bar->appendButton('ImportConstants', 'import', 'COM_TRANSLATOR_IMPORT_CONSTANTS', $files, $this->state->get('file', $app->input->get('file', null, 'raw')));
			}
		}
		else
		{
			ToolbarHelper::custom('constant.export', 'out', '', 'COM_TRANSLATOR_EXPORT');
		}
	}
}
