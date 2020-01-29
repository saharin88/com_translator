<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Language\Text,
	MVC\View\HtmlView,
	Toolbar\ToolbarHelper,
};

class TranslatorViewConstant extends HtmlView
{

	protected $form;
	protected $item;

	/**
	 * @var Joomla\CMS\Object\CMSObject
	 * @since version
	 */
	protected $state;

	public function __construct(array $config = array())
	{
		parent::__construct($config);
		$this->setLayout('edit');
	}

	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->state = $this->get('State');
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{

		ToolbarHelper::title(Text::_((empty($this->state->get('row')) ? 'COM_TRANSLATOR_CONSTANT_ADD' : 'COM_TRANSLATOR_CONSTANT_EDIT')), 'edit');

		ToolbarHelper::apply('constant.apply');
		ToolbarHelper::save('constant.save');
		JToolBarHelper::cancel('constant.cancel', 'JTOOLBAR_CANCEL');

		$this->sidebar = JHtmlSidebar::render();
	}

}