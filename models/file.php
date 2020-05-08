<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Factory,
	Language\Text,
	MVC\Model\FormModel,
	Language\LanguageHelper,
};

class TranslatorModelFile extends FormModel
{

	protected $cache = array();

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_translator.file', 'file', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}


	public function save($data)
	{
		if (empty($data['extension']))
		{
			throw new exception('Empty extension id');
		}

		if (empty($data['language']))
		{
			throw new Exception('Empty language');
		}

		$extension = $this->getextension($data['extension']);

		if (empty($extension))
		{
			throw new Exception('Empty extension');
		}

		$client_id = mb_substr($data['language'], -1, 1, 'UTF-8');
		$language  = mb_substr($data['language'], 0, -1, 'UTF-8');

		if ($client_id === '1')
		{
			$client_path = JPATH_SITE;
		}
		else if ($client_id === '0')
		{
			$client_path = JPATH_ADMINISTRATOR;
		}
		else
		{
			throw new Exception('Bad client id');
		}

		$path = $client_path . '/language/';

		$client_languages = LanguageHelper::getKnownLanguages($client_path);
		$languages_tag    = array_keys($client_languages);

		if (!in_array($language, $languages_tag))
		{
			throw new Exception(Text::sprintf('COM_TRANSLATOR_NOT_FOUND_LANGUAGES_FOR_SELECTED_CLIENT', $language));
		}

		$path .= $language;

		$filename = $language . '.';

		switch ($extension->type)
		{
			case 'component':
			case 'module':
				$filename .= $extension->element;
				break;
			case 'plugin':
				$filename .= 'plg_' . $extension->folder . '_' . $extension->element;
				break;
			case 'template':
				$filename .= 'tpl_' . $extension->element;
				break;

			default:
				throw new Exception('For this type of extension it is forbidden to create a language file');
		}

		if (!empty($data['sys']))
		{
			$filename .= '.sys';
		}

		$filename .= '.ini';

		if (file_exists($path . '/' . $filename))
		{
			$this->setState('file_exists', true);
		}
		else
		{
			if (fopen($path . '/' . $filename, 'a') === false)
			{
				throw new Exception(Text::_('COM_TRANSLATOR_COULD_NOT_CREATE_FILE'));
			}
		}

		return ($client_id === '0' ? 'administrator' : 'site') . ':' . $filename;

	}


	private function getExtension($extension_id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('`type`, `element`, `folder`');
		$query->from('#__extensions');
		if (is_numeric($extension_id))
		{
			$query->where('extension_id = ' . (int) $extension_id);
		}
		else
		{
			$query->where('name = ' . $db->q($extension_id));
		}
		$db->setQuery($query);

		return $db->loadObject();
	}

	public function reSave(array $files)
	{
		$app = Factory::getApplication();

		foreach ($files as $file)
		{
			try
			{
				$constants = TranslatorHelper::getConstants($file);
				TranslatorHelper::saveToIniFile($constants, $file);
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');
			}
		}

	}

}
