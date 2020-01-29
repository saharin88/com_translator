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
		if ($this->checkToken() === false)
		{
			die('Error Token');
		}

		/** @var TranslatorModelConstant $model */
		$model = $this->getModel('Constant');
		$cid   = $this->input->get('cid', [], 'array');
		$file  = $this->input->get('file', null, 'raw');

		try
		{
			$model->delete($cid, $file);
			$this->setMessage(Text::plural('COM_TRANSLATOR_N_ITEMS_DELETED', count($cid)));
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
		if ($this->checkToken() === false)
		{
			die('Error Token');
		}

		$app = Factory::getApplication();

		/** @var TranslatorModelConstant $model */
		$model = $this->getModel('Constant');
		$rows  = $this->input->get('cid', array(), 'array');
		$file  = $this->input->get('file', null, 'raw');

		if (empty($file))
		{
			$this->setRedirect(Route::_('index.php?option=com_translator&view=files', false), Text::_('COM_TRANSLATOR_UNKNOWN_FILE_FOR_EXPORT'))->redirect();

		}

		if (empty($rows))
		{
			$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false), Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'))->redirect();
		}

		$data = array(
			'file' => $file
		);

		$success_count = 0;
		$error_count   = 0;

		foreach ($rows AS $row)
		{
			$constant      = explode('=', urldecode($row));
			$data['key']   = trim($constant[0]);
			$data['value'] = mb_substr(trim($constant[1]), 1, -1, 'UTF-8');

			try
			{
				$model->save($data);
				$success_count++;
				if ($success_count < 11)
				{
					$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_IMPORT_SUCCESS', $data['key']));
				}
			}
			catch (Exception $e)
			{
				$error_count++;
				if ($error_count < 11)
				{
					if ($e->getCode() === 0)
					{
						$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_IMPORT_ERROR', $data['key'], $e->getMessage()), 'error');
					}
					else
					{
						$app->enqueueMessage($e->getMessage(), 'error');
					}
				}
			}
		}

		if ($success_count > 0)
		{
			try
			{
				TranslatorHelper::sortFileConstants($file);
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		if ($success_count > 10)
		{
			$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_MANY_MESSAGES', $success_count));
		}

		if ($error_count > 10)
		{
			$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_MANY_MESSAGES', $error_count), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false))->redirect();

	}

	public function translate()
	{

		$this->checkToken() or die('Error Token');

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

			if (empty($file))
			{
				throw new Exception('Empty file');
			}

			$model->$task($cid, $translate, $file);

			try
			{
				TranslatorHelper::sortFileConstants($file);
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_translator&view=constants&file=' . $file, false));

	}

	public function getModel($name = 'Constants', $prefix = 'TranslatorModel', $config = [])
	{
		return parent::getModel($name, $prefix, $config);
	}

}