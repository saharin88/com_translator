<?php

defined('_JEXEC') or die;

use Joomla\CMS\
{
	Factory,
	MVC\Controller\BaseController
};

JLoader::register('TranslatorHelper', __DIR__ . '/helpers/translator.php');

$controller = BaseController::getInstance('Translator');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();