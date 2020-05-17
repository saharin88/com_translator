<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	MVC\Model\BaseDatabaseModel,
	MVC\Model\ListModel,
	Language\LanguageHelper,
	Language\Text,
	Factory,
	Router\Route,
};

class TranslatorModelConstants extends ListModel
{

	public function getItems(?string $file = null)
	{
		$file = (isset($file) ? $file : $this->getState('file'));

		$constants = TranslatorHelper::getConstants($file);

		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			foreach ($constants as $key => $val)
			{
				if (mb_stristr($key, $search, false, 'UTF-8') === false && mb_stristr($val, $search, false, 'UTF-8') === false)
				{
					unset($constants[$key]);
				}
			}
		}

		return $constants;
	}

	public function getOtherLangs()
	{
		$result    = [];
		$file      = $this->getState('file');
		$extension = TranslatorHelper::getExtension($file);

		if ($extension === false)
		{
			list($client,) = explode(':', $this->getState('file'));
			$languages = LanguageHelper::getKnownLanguages(constant('JPATH_' . strtoupper($client)));
			foreach ($languages as $tag => $language)
			{
				$filename                          = $tag . '.ini';
				$result[$client . ':' . $filename] = Text::sprintf('COM_TRANSLATOR_VIEW_LANGUAGES_BOX_ITEM', $filename, Text::_('J' . $client));
			}
		}
		else
		{
			/** @var TranslatorModelFiles $filesModel */
			$filesModel = BaseDatabaseModel::getInstance('Files', 'TranslatorModel', array('ignore_request' => true));
			$filesModel->setState('filter.search', $extension);

			foreach (['site', 'administrator'] AS $client)
			{
				$languages = LanguageHelper::getKnownLanguages(constant('JPATH_' . strtoupper($client)));
				$filesModel->setState('filter.client', $client);
				foreach ($languages as $tag => $language)
				{
					$filesModel->setState('filter.language', $tag);
					$files = $filesModel->getFiles();
					if (!empty($files))
					{
						foreach ($files as $filename)
						{
							if (TranslatorHelper::getExtension($filename) === $extension)
							{
								$result[$client . ':' . $filename] = Text::sprintf('COM_TRANSLATOR_VIEW_LANGUAGES_BOX_ITEM', $filename, Text::_('J' . $client));
							}
						}
					}
				}

			}
		}

		if (isset($result[$file]))
		{
			unset($result[$file]);
		}

		return $result;
	}

	public function getPath(?string $file = null)
	{
		$file = (isset($file) ? $file : $this->getState('file'));

		try
		{
			$path = TranslatorHelper::getPath($file);
		}
		catch (Exception $e)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
			$app->redirect(Route::_('index.php?option=com_translator&view=files', false));
		}

		return $path;
	}

	protected function populateState($ordering = '', $direction = '')
	{

		$app = Factory::getApplication();

		$this->setState('file', $app->input->get('file', null, 'raw'));

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
		$this->setState('filter.search', $search);

		$editMode = $this->getUserStateFromRequest($this->context . '.edit_mode', 'edit_mode', 0);
		$this->setState('edit_mode', $editMode);

	}

	public function getTranslateForm()
	{
		$form = $this->loadForm($this->context . '.translate', 'translate', ['control' => '', 'load_data' => []]);

		list($client, $filename) = explode(':', $this->getState('file'));
		$language = mb_stristr($filename, '.', true, 'UTF-8');
		$form->setFieldAttribute('to', 'default', $language, 'translate');
		$form->setValue('client', 'translate', $client);

		return $form;
	}

}