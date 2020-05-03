<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Language\Text,
	Filesystem\File,
	Factory,
	MVC\Model\FormModel,
	Language\LanguageHelper,
};

class TranslatorModelConstant extends FormModel
{

	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm('com_translator.constant', 'constant', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		if (!empty($this->getState('key')))
		{
			$form->setFieldAttribute('key', 'readonly', true);
		}

		if (Factory::getApplication()->input->get->getBool('ajax', false))
		{
			$form->setFieldAttribute('value', 'type', 'textarea');
		}

		return $form;
	}

	public function save($data, ?string $file = null)
	{
		if (empty($data['key']))
		{
			throw new Exception('Empty constant key', 1);
		}

		if (preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $data['key']) !== 1)
		{
			throw new Exception(Text::_('COM_TRANSLATOR_BAD_CONSTANT_NAME'), 1);
		}

		$data['key'] = strtoupper($data['key']);

		$file = empty($file) ? $this->getState('file') : $file;
		$key  = $this->getState('key');

		$constants = TranslatorHelper::getConstants($file);

		if (!empty($key))
		{
			if ($key !== $data['key'])
			{
				throw new Exception(Text::_('COM_TRANSLATOR_YOU_CANNOT_CHANGE_CONSTANT_NAME'));
			}

			if (isset($constants[$data['key']]) === false)
			{
				throw new Exception(Text::_('COM_TRANSLATOR_CONSTANT_NOT_FOUND'));
			}
		}

		$constants[$data['key']] = preg_replace("/\\n/m", "<br />", $data['value']);

		TranslatorHelper::saveToIniFile($constants, $file);
	}

	public function delete(array $keys, ?string $file = null)
	{
		$app       = Factory::getApplication();
		$file      = (isset($file) ? $file : $this->getState('file'));
		$constants = TranslatorHelper::getConstants($file);
		$success   = [];

		foreach ($keys as $key)
		{
			if (isset($constants[$key]))
			{
				unset($constants[$key]);
				$success[] = $key;
			}
			else
			{
				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_DELETE_ERROR', $key, Text::_('COM_TRANSLATOR_CONSTANT_NOT_FOUND')), 'error');
			}
		}

		if (count($success))
		{
			if (TranslatorHelper::saveToIniFile($constants, $file))
			{
				foreach ($success as $key)
				{
					$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_DELETE_SUCCESS', $key));
				}
			}
		}
	}

	protected function loadFormData()
	{
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_translator.edit.constant.data', []);
		if (empty($data))
		{
			$data = $this->getItem();
		}
		$this->preprocessData('com_translator.constant', $data);

		return $data;
	}

	public function getItem()
	{
		$item = [
			'key'   => null,
			'value' => null,
		];

		$file = $this->getState('file');
		$key  = $this->getState('key');

		if (!empty($key))
		{
			$constants = TranslatorHelper::getConstants($file);
			if (isset($constants[$key]))
			{
				$item['key']   = $key;
				$item['value'] = $constants[$key];
			}
			else
			{
				throw new Exception(Text::sprintf('COM_TRANSLATOR_CONSTANT_NOT_FOUND', $key));
			}
		}

		return $item;
	}

	protected function populateState()
	{
		$app = Factory::getApplication();

		// set state key
		$key = $app->input->get->get('key', null, 'raw');
		if (!empty($key))
		{
			$key = strtoupper($key);
		}
		$this->setState('key', $key);

		// set state file
		$this->setState('file', $app->input->get('file', null, 'raw'));
	}


	public function translateByGoogle(array $keys, array $translate, string $file)
	{

		$languages = LanguageHelper::getKnownLanguages(constant('JPATH_' . strtoupper($translate['google']['client'])));

		if (empty($translate['google']['source']))
		{
			$source = 'auto';
		}
		else
		{
			if (isset($languages[$translate['google']['source']]))
			{
				$source = $translate['google']['source'];
			}
			else
			{
				throw new Exception('Bad source param for google translate');
			}
		}

		if (empty($translate['google']['target']))
		{
			throw new Exception('Empty target param for google translate');
		}
		else
		{
			if (isset($languages[$translate['google']['target']]))
			{
				$target = $translate['google']['target'];
			}
			else
			{
				throw new Exception('Bad target param for google translate');
			}
		}

		if ($source === $target)
		{
			throw new Exception(Text::_('COM_TRANSLATOR_GOOGLE_SOURCE_TARGET_EQUAL'));
		}

		$attempts = (int) (empty($translate['google']['attempts']) ? 5 : $translate['google']['attempts']);

		$app = Factory::getApplication();

		$constants = TranslatorHelper::getConstants($file);

		foreach ($keys AS $key)
		{

			if (isset($constants[$key]) === false)
			{
				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_NOT_FOUND', $key), 'error');
				continue;
			}

			$result = TranslatorHelper::translateByGoogle($source, $target, $constants[$key], $attempts);

			if (empty($result))
			{
				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_TRANSLATE_CONSTANT_ERROR', $key), 'error');
				continue;
			}

			try
			{
				$this->save([
					'key'   => $key,
					'value' => $result
				]);

				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_TRANSLATE_CONSTANT_SUCCESS', $key));
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_SAVE_ERROR', $key, $e->getMessage()), 'error');
			}

		}

	}

	/**
	 * Import constants
	 *
	 * @param array  $keys
	 * @param string $file
	 * @param string $from_file
	 *
	 *
	 * @throws Exception
	 * @since version
	 */
	public function import(array $keys, string $file, string $from_file)
	{
		$app       = Factory::getApplication();
		$constants = TranslatorHelper::getConstants($file);
		$imported  = TranslatorHelper::getConstants($from_file);
		$success   = [];

		foreach ($keys as $key)
		{
			if (isset($imported[$key]))
			{
				if (isset($constants[$key]))
				{
					$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANTA_ALREADY_EXIST', $key), 'error');
				}
				else
				{
					$constants[$key] = $imported[$key];
					$success[]       = $key;
				}
			}
			else
			{
				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_NOT_FOUND', $key), 'error');
			}
		}

		$success_count = count($success);

		if ($success_count)
		{
			if (TranslatorHelper::saveToIniFile($constants, $file))
			{

				$session  = Factory::getSession();
				$imported = array_merge($session->get($file, [], 'com_translator.imported'), $success);
				$session->set($file, $imported, 'com_translator.imported');

				foreach ($success as $i => $key)
				{
					if ($i === 10)
					{
						break;
					}
					$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_IMPORT_SUCCESS', $key));
				}

				if ($success_count > 10)
				{
					$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_MANY_MESSAGES', $success_count));
				}
			}

		}
	}


}