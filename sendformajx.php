<?php
// Определяем константы Joomla
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__) . DS . '..' . DS . '..' );
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
// Инициализируем Joomla
JFactory::getApplication('site')->initialise();
// Запускаем модуль
JModuleHelper::renderModule( JModuleHelper::getModule('mod_mailform') );