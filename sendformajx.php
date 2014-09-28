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
$joomla_app = JFactory::getApplication('site');
$joomla_app->initialise();
// Запускаем модуль
$module_title = htmlspecialchars( $joomla_app->input->post->get('title', '', 'string') );

if ( $module_title  != '' ) {
	$module = JModuleHelper::getModule('mod_mailform', $module_title);
	
	if ( $module->id > 0 ) {
		echo JModuleHelper::renderModule( $module );
	} else {
		die( JText::_('MOD_MAILFORM_MODULE_NOT_FOUND') );
	}
} else {
	die( JText::_('MOD_MAILFORM_MODULE_NOT_FOUND') );
}
