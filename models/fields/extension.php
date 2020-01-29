<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\
{
	Factory,
	Form\FormHelper,
	HTML\HTMLHelper,
	Language\Text,
};

FormHelper::loadFieldClass('list');

class JFormFieldExtension extends JFormFieldList
{

	protected function getOptions()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select("`extension_id`, `type`, `client_id`, CASE WHEN `type` = 'template' THEN CONCAT('tpl_', `name`) ELSE `name` END AS ext")
			->from('#__extensions')
			->where('`enabled` = 1')
			->where("`type` IN ('component', 'module', 'plugin', 'template')")
			->order('ext ASC ');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		$options = parent::getOptions();

		foreach ($result AS $ext)
		{
			$options[] = HTMLHelper::_('select.option', $ext->extension_id, $ext->ext . (in_array($ext->type, array('module', 'template')) ? ' - ' . ($ext->client_id === '1' ? Text::_('JADMINISTRATOR') : Text::_('JSITE')) : ''));
		}

		return $options;

	}

}