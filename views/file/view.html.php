<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Language\Text,
	MVC\View\HtmlView,
	Toolbar\ToolbarHelper,
	Toolbar\Toolbar,
	Layout\FileLayout,
};

class TranslatorViewFile extends HtmlView
{

	protected $form;

	public function __construct(array $config = array())
	{
		parent::__construct($config);
		$this->setLayout('edit');
	}

	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->state = $this->get('State');
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$bar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_TRANSLATOR_ADD_FILE'), 'file-plus');

		$layout = new FileLayout('com_translator.toolbar.button.donate');
		$bar->appendButton('Custom', $layout->render(), 'donate');

		ToolbarHelper::save('file.save', 'JTOOLBAR_APPLY');
		ToolbarHelper::cancel('file.cancel', 'JTOOLBAR_CANCEL');
	}

}