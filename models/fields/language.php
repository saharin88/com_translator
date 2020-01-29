<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\
{
	Form\FormHelper,
	Language\Text,
	Language\LanguageHelper,
};

FormHelper::loadFieldClass('language');

class TranslatorFormFieldLanguage extends JFormFieldLanguage
{

	protected function getOptions()
	{
		$languages = [];

		$site_languages  = LanguageHelper::getKnownLanguages(JPATH_SITE);
		$admin_languages = LanguageHelper::getKnownLanguages(JPATH_ADMINISTRATOR);

		// Create a single array of them.
		foreach ($site_languages as $tag => $language)
		{
			$languages[$tag . '1'] = Text::sprintf('COM_TRANSLATOR_VIEW_LANGUAGES_BOX_ITEM', $language['name'], Text::_('JSITE'));
		}

		foreach ($admin_languages as $tag => $language)
		{
			$languages[$tag . '0'] = Text::sprintf('COM_TRANSLATOR_VIEW_LANGUAGES_BOX_ITEM', $language['name'], Text::_('JADMINISTRATOR'));
		}

		// Sort it by language tag and by client after that.
		ksort($languages);

		return $languages;

	}


}