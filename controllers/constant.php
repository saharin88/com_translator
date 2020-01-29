<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Language\Text,
	MVC\Controller\FormController,
	Router\Route,
	Factory,
};

class TranslatorControllerConstant extends FormController
{

	protected $view_list = 'constants';

	public function __construct(array $config = array())
	{
		parent::__construct($config);

		$this->text_prefix .= '_ROW';

	}

	/**
	 * @param string $name
	 * @param string $prefix
	 * @param array  $config
	 *
	 * @return JModelLegacy | TranslatorModelConstant
	 *
	 * @since version
	 */
	public function getModel($name = 'Constant', $prefix = 'TranslatorModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function add()
	{

		$file = $this->input->getString('file');

		try
		{
			if (!$this->allowAdd())
			{
				throw new Exception(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			}

			if (empty($file))
			{
				throw new Exception('Empty file');
			}

			if (!file_exists(TranslatorHelper::getPath($file)))
			{
				throw new Exception('File not exists');
			}

		}
		catch (Exception $e)
		{

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				),
				$e->getMessage(),
				'error'
			);

			return false;

		}

		$context = "$this->option.edit.$this->context";

		// Clear the record edit information from the session.
		Factory::getApplication()->setUserState($context . '.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($file, 'file'), false
			)
		);

		return true;
	}

	public function save($key = null, $urlVar = null)
	{

		if ($this->checkToken() === false)
		{
			die('Error Token');
		}

		$app     = Factory::getApplication();
		$model   = $this->getModel();
		$task    = $this->getTask();
		$data    = $this->input->post->get('jform', array(), 'array');
		$context = "$this->option.edit.$this->context";
		$form    = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			if (empty($validData['file']))
			{
				$url = 'index.php?option=' . $this->option . '&view=files';
			}
			else
			{
				if (empty($validData['row']))
				{

					$url = 'index.php?option=' . $this->option . '&view=' . $this->view_list . '&file=' . $validData['file'];
				}
				else
				{
					$url = 'index.php?option=' . $this->option . '&view=' . $this->view_item . '&file=' . $validData['file'] . '&row=' . $validData['row'];
				}
			}

			$this->setRedirect(Route::_($url, false));

			return false;
		}

		try
		{
			$model->save($validData);
		}
		catch (Exception $e)
		{

			$viewRedirect = ($e->getCode() ? 'view_item' : 'view_list');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->$viewRedirect . '&file=' . $validData['file'] . ($viewRedirect === 'view_item' ? (empty($validData['row']) ? '' : '&row=' . $validData['row']) : ''), false
				), $e->getMessage()
			);

		}

		if (empty($validData['row']))
		{
			$this->setMessage(Text::_('COM_TRANSLATOR_CONSTANT_ADDED'));
		}
		else
		{
			$this->setMessage(Text::_('COM_TRANSLATOR_CONSTANT_EDITED'));
		}

		$new_row = $model->getState('row');

		$app->setUserState($context . '.data', null);

		$url = 'index.php?option=' . $this->option;

		if ($task === 'apply')
		{
			$url .= '&view=' . $this->view_item . '&file=' . $validData['file'] . '&row=' . $new_row;
		}
		else
		{
			$url .= '&view=' . $this->view_list . '&file=' . $validData['file'];
		}

		$this->setRedirect(
			Route::_($url, false)
		);

		try
		{
			TranslatorHelper::sortFileConstants($validData['file']);
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		return true;

	}

	public function cancel($key = null)
	{
		if ($this->checkToken() === false)
		{
			die('Error Token');
		}

		$data = $this->input->get('jform', array(), 'array');

		if (empty($data['file']))
		{
			$url = 'index.php?option=' . $this->option . '&view=files';
		}
		else
		{
			$url = 'index.php?option=' . $this->option . '&view=' . $this->view_list . '&file=' . $data['file'];
		}

		$this->setRedirect(Route::_($url, false));

	}

}