<?php

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;

$app = Factory::getApplication();
$app->getDocument()->addStyleDeclaration('#toolbar-donate {float:right;}');

?>
<a class="btn btn-small" target="_blank" href="https://www.liqpay.ua/en/checkout/saharin88"><span class="icon-heart"></span>Donate</a>
