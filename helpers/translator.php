<?php

defined('_JEXEC') or die;

require_once(__DIR__ . '/vendor/autoload.php');

use \Dejurin\GoogleTranslateForFree;
use Joomla\CMS\
{
	Factory,
	Component\ComponentHelper,
	Language\LanguageHelper,
	Language\Text,
};
use Buzz\Browser;
use MatthiasNoback\MicrosoftOAuth\AzureTokenProvider;
use MatthiasNoback\MicrosoftTranslator\MicrosoftTranslator;
use MatthiasNoback\MicrosoftTranslator\ApiCall\Translate;

class TranslatorHelper
{

	protected static $constants = [];

	protected static $path = [];

	protected static $translator = [];

	public static function translateByGoogle(string $text, string $to, ?string $from = null)
	{
		if (isset(self::$translator['google']) === false || !self::$translator['google'] instanceof GoogleTranslateForFree)
		{
			self::$translator['google'] = new GoogleTranslateForFree();
		}

		if (empty($from))
		{
			$from = 'auto';
		}

		return self::$translator['google']->translate($from, $to, $text, TranslatorHelper::getParam('google_attempts', 5));
	}

	public static function translateByMicrosoft(string $text, string $to, ?string $from = null)
	{
		if (isset(self::$translator['microsoft']) === false || !self::$translator['microsoft'] instanceof MicrosoftTranslator)
		{
			$browser                       = new Browser();
			$accessTokenProvider           = new AzureTokenProvider($browser, self::getParam('microsoft_api_key'), self::getParam('microsoft_end_point', 'https://api.cognitive.microsoft.com/') . 'sts/v1.0/issueToken');
			self::$translator['microsoft'] = new MicrosoftTranslator($browser, $accessTokenProvider);
		}

		return self::$translator['microsoft']->translate($text, $to, $from, null, Translate::CONTENT_TYPE_HTML);
	}

	public static function getParam($name, $default = null)
	{
		$params = self::getParams();
		if (is_array($name))
		{
			$arrparams = array();
			foreach ($name as $n)
			{
				$arrparams[$n] = $params->get($n);
			}

			return $arrparams;
		}
		else
		{
			return $params->get($name, $default);
		}

	}

	public static function getParams()
	{
		return ComponentHelper::getParams('com_translator');
	}

	public static function getExtension(string $file)
	{
		$fileExpl = explode(':', $file);

		if (!empty($fileExpl[1]))
		{
			$filename = $fileExpl[1];
		}
		else
		{
			$filename = $fileExpl[0];
		}

		$filename = str_replace('.sys', '', $filename);
		$filename = str_replace('.ini', '', $filename);

		$fileExpl = explode('.', $filename);

		if (empty($fileExpl[1]))
		{
			return false;
		}
		else
		{
			return $fileExpl[1];
		}
	}

	public static function getPath(?string $file = null)
	{
		$file = (isset($file) ? $file : Factory::getApplication()->input->get('file', null, 'raw'));

		if (empty($file))
		{
			throw new Exception(Text::sprintf('COM_TRANSLATOR_ERROR_GET_PATH', Text::_('COM_TRANSLATOR_MISSING_FILE')));
		}

		if (isset(self::$path[$file]) === false)
		{
			$fileExpl = explode(':', $file);

			if ($fileExpl[0] !== 'administrator' && $fileExpl[0] !== 'site')
			{
				throw new Exception(Text::sprintf('COM_TRANSLATOR_ERROR_GET_PATH', Text::_('COM_TRANSLATOR_UNKNOWN_CLIENT')));
			}

			if (empty($fileExpl[1]))
			{
				throw new Exception(Text::sprintf('COM_TRANSLATOR_ERROR_GET_PATH', Text::_('COM_TRANSLATOR_FILENAME_EMPTY')));
			}

			$client   = $fileExpl[0];
			$filename = $fileExpl[1];

			$language = mb_stristr($filename, '.', true, 'UTF-8');

			$path = constant('JPATH_' . strtoupper($client)) . '/language/' . $language . '/' . $filename;

			if (file_exists($path) === false)
			{
				throw new Exception(Text::sprintf('COM_TRANSLATOR_ERROR_GET_PATH', Text::_('COM_TRANSLATOR_FILE_NOT_EXISTS')));
			}

			self::$path[$file] = $path;
		}

		return self::$path[$file];
	}

	/**
	 * Get language constants by file
	 *
	 * @param string $file
	 * @param int    $save
	 *
	 * @return array
	 *
	 * @throws Exception
	 * @since version
	 */
	public static function getConstants(string $file)
	{
		if (isset(self::$constants[$file]) === false)
		{
			$path      = self::getPath($file);
			$constants = LanguageHelper::parseIniFile($path);
			ksort($constants);
			self::$constants[$file] = $constants;
		}

		return self::$constants[$file];
	}

	/**
	 * Save constants to a language file.
	 *
	 * @param array  $constants
	 * @param string $file
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since version
	 */
	public static function saveToIniFile(array $constants, string $file)
	{
		if (LanguageHelper::saveToIniFile(self::getPath($file), $constants) === false)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_TRANSLATOR_ERROR_SAVE_CONSTANTS_TO_LANGUAGE_FILE', $file), 'error');

			return false;
		}

		self::$constants[$file] = $constants;

		return true;
	}


}