<?php
defined('_JEXEC') or die;

use Joomla\CMS\
{
	Language\Text,
	MVC\View\HtmlView,
	HTML\HTMLHelper,
	Toolbar\ToolbarHelper,
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
		ToolbarHelper::title(Text::_('COM_TRANSLATOR_LANGUAGE_FILES'), 'copy');

		\Joomla\CMS\Factory::getDocument()->addStyleDeclaration('#toolbar-heart {float:right;}');
		ToolbarHelper::link('https://www.liqpay.ua/en/checkout/saharin88', 'Donate', 'heart');

		ToolbarHelper::addNew('file.add');
		ToolbarHelper::custom('file.reSafe', 'loop', '', Text::_('COM_TRANSLATOR_RE_SAVE'));
		ToolbarHelper::preferences('com_translator');
	}
}
