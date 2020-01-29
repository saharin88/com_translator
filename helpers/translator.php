<?php

defined('_JEXEC') or die;

require_once(__DIR__ . '/vendor/autoload.php');

use \Dejurin\GoogleTranslateForFree;
use Joomla\CMS\
{
	Factory,
	Component\ComponentHelper,
};

class TranslatorHelper
{

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

	public static function getPath(?string $file = null)
	{
		$file = (isset($file) ? $file : Factory::getApplication()->input->get('file', null, 'raw'));

		if (empty($file))
		{
			throw new Exception('Empty file');
		}

		if (isset(self::$path[$file]) === false)
		{

			list($client, $filename) = explode(':', $file);

			$language = mb_stristr($filename, '.', true, 'UTF-8');

			$path = constant('JPATH_' . strtoupper($client)) . '/language/' . $language . '/' . $filename;

			if (file_exists($path) === false)
			{

				throw new Exception('File not exists');
			}

			self::$path[$file] = $path;
		}

		return self::$path[$file];
	}

	public static function sortFileConstants(string $file)
	{

		$path = self::getPath($file);

		if (file_exists($path) === false)
		{
			throw new Exception('File not exists');
		}

		$rows = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		if ($rows === false)
		{
			throw new Exception('Error get file rows');
		}

		if (count($rows))
		{

			$constants = [];

			foreach ($rows as $row)
			{
				if (mb_substr($row, 0, 1, 'UTF-8') === ';')
				{
					continue;
				}
				list($key, $val) = explode('=', $row);
				if (!empty($key) && !empty($val))
				{
					$constants[trim($key)] = mb_substr(trim($val), 1, -1, 'UTF-8');
				}
			}

			ksort($constants);

			$sorted_rows = [];

			foreach ($constants AS $key => $val)
			{
				$sorted_rows[] = strtoupper($key) . " = \"" . $val . "\"";
			}

			if (file_put_contents($path, implode("\r\n", $sorted_rows)) === false)
			{
				throw new Exception('File put sorted constants error');
			}
			else
			{
				return $constants;
			}

		}
		else
		{
			return [];
		}

	}

}