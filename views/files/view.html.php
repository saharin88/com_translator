<?php
defined('_JEXEC') or die;

use Joomla\CMS\
{
	Language\Text,
	MVC\View\HtmlView,
	HTML\HTMLHelper,
	Toolbar\ToolbarHelper,
	Toolbar\Toolbar,
	Layout\FileLayout,
};

class TranslatorViewFiles extends HtmlView
{

	public $activeFilters;
	public $files;
	public $filterForm;
	/**
	 * @var Joomla\CMS\Object\CMSObject
	 * @since version
	 */
	public $state;

	public function display($tpl = null)
	{
		$this->files         = $this->get('Files');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Adds the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		$bar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_TRANSLATOR_LANGUAGE_FILES'), 'copy');

		$layout = new FileLayout('com_translator.toolbar.button.donate');
		$bar->appendButton('Custom', $layout->render(), 'donate');

		ToolbarHelper::addNew('file.add');
		ToolbarHelper::preferences('com_translator');
	}
}
