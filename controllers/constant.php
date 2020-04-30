<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Language\Text,
	MVC\Controller\FormController,
	Router\Route,
	Factory,
	Response\JsonResponse,
};

class TranslatorControllerConstant extends FormController
{

	protected $new_row = null;

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

			TranslatorHelper::getPath($file);

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
		$key     = $this->input->get->get('key', null, 'raw');
		$file    = $this->input->get->get('file', null, 'raw');

		try
		{
			TranslatorHelper::getPath($file);
		}
		catch (Exception $e)
		{
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=files'), $e->getMessage(), 'error');

			return false;
		}

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

			$url = 'index.php?option=' . $this->option . '&view=' . $this->view_item . '&file=' . $file;

			if (!empty($key))
			{
				$url .= '&key=' . $validData['key'];
			}


			$this->setRedirect(Route::_($url, false));

			return false;
		}

		try
		{
			$model->save($validData, $file);
		}
		catch (Exception $e)
		{

			$url = 'index.php?option=' . $this->option . '&file=' . $file;

			if ($e->getCode() === 1)
			{
				$url .= '&view=' . $this->view_item . (empty($key) ? '' : '&key=' . $key);
			}
			else
			{
				$url .= '&view=' . $this->view_list;
			}

			$this->setRedirect(Route::_($url, false), $e->getMessage(), 'error');

			return false;
		}

		if (empty($key))
		{
			$this->setMessage(Text::_('COM_TRANSLATOR_CONSTANT_ADDED'));
		}
		else
		{
			$this->setMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_EDITED', $key));
		}

		$app->setUserState($context . '.data', null);

		$url = 'index.php?option=' . $this->option;

		if ($task === 'apply')
		{
			$url .= '&view=' . $this->view_item . '&file=' . $file . '&key=' . $key;
		}
		else
		{
			$url .= '&view=' . $this->view_list . '&file=' . $file;
		}

		$this->setRedirect(Route::_($url, false));

		return true;

	}

	public function cancel($key = null)
	{
		if ($this->checkToken() === false)
		{
			die('Error Token');
		}

		$file = $this->input->get->get('file', null, 'raw');

		if (empty($file))
		{
			$url = 'index.php?option=' . $this->option . '&view=files';
		}
		else
		{
			$url = 'index.php?option=' . $this->option . '&view=' . $this->view_list . '&file=' . $file;
		}

		$this->setRedirect(Route::_($url, false));

	}

	public function saveAjax()
	{
		$result = $this->save();
		$data   = $this->input->post->get('jform', array(), 'array');
		exit(new JsonResponse($data, $this->message, !$result, false));
	}

}