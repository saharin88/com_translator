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

class TranslatorHelper
{

	protected static $constants = [];

	protected static $path = [];

	public static function translateByGoogle(string $source, string $target, string $text, ?int $attempts = 5)
	{
		$tr = new GoogleTranslateForFree();

		return $tr->translate($source, $target, $text, $attempts);
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

	public static function getExtByPath($path)
	{
		if (self::checkUrlEncode($path))
		{
			$path = urldecode($path);
		}
		if (!file_exists($path))
		{
			throw new Exception('File does not exist by this path');
		}
		$path_segments     = explode('/', $path);
		$filename          = end($path_segments);
		$filename_segments = explode('.', $filename);

		return $filename_segments[1];
	}

	public static function getLangByPath($path)
	{
		if (self::checkUrlEncode($path))
		{
			$path = urldecode($path);
		}
		if (!file_exists($path))
		{
			throw new Exception('File does not exist by this path');
		}
		$path_segments     = explode('/', $path);
		$filename          = end($path_segments);
		$filename_segments = explode('.', $filename);

		return $filename_segments[0];
	}


	/**
	 * @param string $str
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public static function checkUrlEncode(string $str)
	{
		if (urlencode(urldecode($str)) === $str)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function urlEncode(string $str)
	{
		if (self::checkUrlEncode($str))
		{
			return $str;
		}
		else
		{
			return urlencode($str);
		}
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function urlDecode(string $str)
	{
		if (self::checkUrlEncode($str))
		{
			return urldecode($str);
		}
		else
		{
			return $str;
		}
	}

	/**
	 * @param string $str
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function parseConstant(string $str)
	{
		$constant = explode('=', self::urlDecode($str));

		return [
			'key'   => trim($constant[0]),
			'value' => (empty($constant[1]) ? '' : mb_substr(trim($constant[1]), 1, -1, 'UTF-8'))
		];
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
			Factory::getApplication()->enqueueMessage(Text::_('COM_TRANSLATOR_ERROR_SAVE_CONSTANTS_TO_LANGUAGE_FILE'), 'error');

			return false;
		}

		self::$constants[$file] = $constants;

		return true;
	}


}