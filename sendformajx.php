<?php
// Определяем константы Joomla
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__FILE__) . DS . '..' . DS . '..' );
// Установим кодировку
header("Content-Type: text/html; charset=utf-8");
// Подключаем файлы Joomla
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
// Инициализируем Joomla
JFactory::getApplication('site')->initialise();
// Запускаем модуль
echo JModuleHelper::renderModule( JModuleHelper::getModule('mod_mailform') );