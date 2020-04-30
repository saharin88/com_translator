<?php

use Joomla\CMS\
{
	Router\Route,
	MVC\Controller\BaseController,
	Language\Text,
	Factory
};

class TranslatorControllerConstants extends BaseController
{

	public function __construct(array $config = [])
	{
		parent::__construct($config);

		$this->registerTask('translateByGoogle', 'translate');
	}


	public function delete()
	{
		$this->checkToken('post', false) or die('Error Token');

		/** @var TranslatorModelConstant $model */
		$model = $this->getModel('Constant');
		$cid   = $this->input->get('cid', [], 'array');
		$file  = $this->input->get('file', null, 'raw');

		try
		{
			$model->delete($cid, $file);
			//$this->setMessage(Text::plural('COM_TRANSLATOR_N_ITEMS_DELETED', count($cid)));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false));

		return true;

	}

	public function import()
	{
		$this->checkToken('post', false) or die('Error Token');

		$app = Factory::getApplication();

		/** @var TranslatorModelConstant $model */
		$model     = $this->getModel('Constant');
		$keys      = $this->input->get('cid', array(), 'array');
		$file      = $this->input->get('file', null, 'raw');
		$from_file = $this->input->get('from_file', null, 'raw');

		if (empty($file))
		{
			$this->setRedirect(Route::_('index.php?option=com_translator&view=files', false), Text::_('COM_TRANSLATOR_IMPORT_EMPTY_FILE'))->redirect();
		}

		if (empty($from_file))
		{
			$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false), Text::_('COM_TRANSLATOR_IMPORT_EMPTY_FROM_FILE'))->redirect();
		}

		if (empty($keys))
		{
			$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false), Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'))->redirect();
		}

		try
		{
			$model->import($keys, $file, $from_file);
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false))->redirect();

	}

	public function translate()
	{

		$this->checkToken('post', false) or die('Error Token');

		/** @var TranslatorModelConstant $model */
		$model     = $this->getModel('Constant');
		$task      = $this->getTask();
		$cid       = $this->input->get('cid', [], 'array');
		$translate = $this->input->get('translate', [], 'array');
		$file      = $this->input->get('file', null, 'raw');

		try
		{
			if (empty($cid))
			{
				throw new Exception(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
			}

			$model->$task($cid, $translate, $file);

		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false))->redirect();

	}

	public function clearImported()
	{
		$this->checkToken('get', false) or die('Error Token');

		$file = $this->input->get('file', null, 'raw');

		Factory::getSession()->set($file, null, 'com_translator.imported');

		$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false), Text::_('COM_TRANSLATOR_CLEAR_IMPORTED_SUCCESS'))->redirect();
	}

	public function getModel($name = 'Constants', $prefix = 'TranslatorModel', $config = [])
	{
		return parent::getModel($name, $prefix, $config);
	}

}