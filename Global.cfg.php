<?php
/**
 * ------------------------------------------------------------------------------
 * Global Configuration for Master Controllers
 * ------------------------------------------------------------------------------
 * @copyright    Copyright (c) 2012, Auction.com, LLC
 * @version      $Revision: 496 $
 * @modifiedby   $LastChangedBy: Sunil $
 * @lastmodified $LastChangedDate: 2012-01-13 14:00:55 -0700 (Fri, 13 Jan 2012) $
 * -----------------------------------------------------------------------------
 */
/**
 * Constants
 */
date_default_timezone_set('America/Los_Angeles');

define('AFW_INIT_TIME', microtime(true));
define('SYSTEM_PATH', realpath('../../..') . '/');
define('CACHE_PATH', realpath('../../../../cache') . '/');
define('SOURCE_PATH', SYSTEM_PATH . 'source/');
define('COMPONENT_PATH', SOURCE_PATH . 'component/');
define('CONTROLLER_PATH', SOURCE_PATH . 'controller/');
define('VENDOR_PATH', SOURCE_PATH . 'vendor/');
define('DOMAIN_KEY', isset($_SERVER['AFW_DOMAIN_KEY']) ? $_SERVER['AFW_DOMAIN_KEY'] : 'default');
define('MARKET_KEY', isset($_SERVER['AFW_MARKET_KEY']) ? $_SERVER['AFW_MARKET_KEY'] : 'default');


/**
 * Required System Classes
 */
include COMPONENT_PATH . 'array/ArrayRegistry.cls.php';
include COMPONENT_PATH . 'system/Debug.cls.php';
include COMPONENT_PATH . 'system/System.cls.php';
include COMPONENT_PATH . 'system/Harness.cls.php';

Harness::init();

/**
 * Start the System object
 */
System::init(new ArrayRegistry(array(
	'path' => array(
		'system' => SYSTEM_PATH,
		'cache' => CACHE_PATH,
		'source' => SOURCE_PATH,
		'component' => COMPONENT_PATH,
		'controller' => CONTROLLER_PATH,
		'domain' => SYSTEM_PATH . 'domains/' . DOMAIN_KEY . '/',
		'vendor' => SOURCE_PATH . 'vendor/',
	),
	'namespace' => array(
		'Request' => 'component/http/Request',
		'Response' => 'component/http/Response',
		'View' => 'component/system/View',
		'Cookie' => 'component/http/Cookie',
		'Router' => 'component/system/Router',
		'Connections' => 'component/system/Connections',
		'UserAuth' => 'component/session/UserAuth',
		'Messenger' => 'component/data/Messenger',
			'webUser' => 'component/data/messenger_library/webUser',
			'systemManager' => 'component/data/messenger_library/systemManager',
		'Navigation' => 'component/system/Navigation',
		'Build' => 'component/system/Build',
		'StaticContent' => 'component/http/StaticContent',
		'Output' => 'component/system/Output',
		'SMP' => 'component/tcp/SMP',
		'Session' => 'component/session/Session',
		//'Form' => 'component/form/Form',
		'FormHandler' => 'component/form/FormHandler',
		'FormHandler1' => 'component/form/FormHandler_temp',
		'Search' => 'component/search/Search',
		'WebService' => 'component/webservice/WebService',
		'DocumentService' => 'component/document/DocumentService',
		'DataSphere' => 'component/data/DataSphere',
		'Localization' => 'component/localization/Localization',
		'PageInfo' => 'component/system/PageInfo',
		'PageMetrics' => 'component/system/PageMetrics',
		//'Html' => 'component/html/Html',
		'FormHelper' => 'component/html/FormHelper',
		'Certona' => 'component/data/Certona',
		'Payment' => 'component/payment/Payment',
		'API' => 'component/api/api',
	),
	'var' => array(
		'environment' => $_SERVER['AFW_ENVIRONMENT'],
		'device' => 'browser',
		'timezone' => 'America/Los_Angeles',
		'error_level' => E_ALL,
		'domain' => DOMAIN_KEY,
		'market' => $_SERVER['AFW_MARKET_KEY'],
		'timestamp' => microtime(true),
		'debug' => true,
		'show_widget_report' => true
	),
)));

/**
 * Initialize global components
 */

System::Request()->init();

/**
 * Include the Build Configuration
 */

include System::$path['domain'] . 'build.cfg.php';

System::Request()->setDevice();

System::$path['build'] = System::$path['domain'] . 'builds/' . System::Build()->info['build'] . '/';
System::$path['static'] = System::$path['domain'] . 'builds/' . System::Build()->info['build'] . '/static/' . System::Request()->getDevice() . '/';
System::$path['view'] = System::$path['domain'] . 'builds/' . System::Build()->info['build'] . '/view/' . System::Request()->getDevice() . '/';

// Common hash which rolls up the domain, build, and device into a single key
System::$var['site_hash'] = hash('md5', (System::$path['domain'] . System::Build()->info['build'] . System::Request()->getDevice()));
