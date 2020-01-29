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

		if (!empty($this->getState('row')))
		{
			$form->setFieldAttribute('key', 'readonly', true);
		}

		return $form;
	}

	public function save($data)
	{

		if (empty($data['file']))
		{
			throw new Exception('Empty file');
		}

		if (empty($data['key']))
		{
			throw new Exception('Empty constant key', 1);
		}

		$path = TranslatorHelper::getPath($data['file']);

		if (!file_exists($path))
		{
			throw new Exception('File not found');
		}

		$new_row = strtoupper($data['key']) . " = \"" . $data['value'] . "\"";

		$file_content = file_get_contents($path);

		if (empty($data['row']))
		{
			if (mb_stripos($file_content, $data['key'] . ' =', 0, 'UTF-8') === false)
			{
				if (File::append($path, "\r\n" . $new_row) === false)
				{
					throw new Exception('File not write');
				}
			}
			else
			{
				throw new Exception(Text::sprintf('COM_TRANSLATOR_CONSTANT_ALREADY_EXISTS', $data['key']), 1);
			}
		}
		else
		{
			$new_file_content = str_replace(TranslatorHelper::urlDecode($data['row']), $new_row, $file_content);
			$result           = file_put_contents($path, $new_file_content);
			if ($result === false)
			{
				throw new Exception('File not write');
			}
		}
		$this->setState('row', TranslatorHelper::urlEncode($new_row));
	}

	public function delete(array $cid, ?string $file = null)
	{

		$file = (isset($file) ? $file : $this->getState('file'));

		if (empty($file))
		{
			throw new Exception('Empty file');
		}

		$path = TranslatorHelper::getPath($file);

		if (file_exists($path) === false)
		{
			throw new Exception('File not exists');
		}

		$rows = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		$delete_rows = [];
		foreach ($cid as $val)
		{
			$delete_rows[] = urldecode($val);
		}

		$new_rows = array_diff($rows, $delete_rows);

		$result = file_put_contents($path, implode("\r\n", $new_rows));

		if ($result === false)
		{
			throw new Exception('Could not write data to file');
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
		$item = array(
			'row'  => TranslatorHelper::urlEncode($this->getState('row')),
			'file' => $this->getState('file')
		);

		if (!empty($item['row']))
		{
			$constant      = explode('=', urldecode($item['row']));
			$item['key']   = trim($constant[0]);
			$item['value'] = mb_substr(trim($constant[1]), 1, -1, 'UTF-8');
		}

		return $item;
	}

	protected function populateState()
	{
		$app = Factory::getApplication();
		$this->setState('row', $app->input->get('row', null, 'raw'));
		$this->setState('file', $app->input->get('file', null, 'raw'));
	}


	public function translateByGoogle(array $rows, array $translate, string $file)
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

		foreach ($rows AS $row)
		{

			$constant = explode('=', TranslatorHelper::urlDecode($row));

			$key  = trim($constant[0]);
			$text = mb_substr(trim($constant[1]), 1, -1, 'UTF-8');

			$result = TranslatorHelper::translateByGoogle($source, $target, $text, $attempts);

			if (empty($result))
			{
				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_TRANSLATE_CONSTANT_ERROR', $key), 'error');
				continue;
			}

			try
			{
				$this->save([
					'file'  => $file,
					'key'   => $key,
					'value' => $result,
					'row'   => $row,
				]);

				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_TRANSLATE_CONSTANT_SUCCESS', $key));
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::sprintf('COM_TRANSLATOR_CONSTANT_SAVE_ERROR', $key, $e->getMessage()), 'error');
			}

		}

	}


}