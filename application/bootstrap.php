<?php // defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------
// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(APPPATH.'classes/kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

include '/usr/share/php/Math/BigInteger.php';

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Europe/London');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_GB.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-gb');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
}
elseif (isset($_SERVER['REMOTE_ADDR']))
{
	$developmentAddrs = array();
	if (file_exists(APPPATH.'config/dev_ips'.EXT))
	{
		include APPPATH.'config/dev_ips'.EXT;
	}

	if (in_array($_SERVER['REMOTE_ADDR'], $developmentAddrs)) 
	{
		Kohana::$environment = Kohana::DEVELOPMENT;
	}
	else
	{
		Kohana::$environment = Kohana::PRODUCTION;
	}
}

/**
 * Enable xdebug parameter collection in development mode to improve fatal stack traces.
 */
if (Kohana::$environment == Kohana::DEVELOPMENT && extension_loaded('xdebug'))
{
    ini_set('xdebug.collect_params', 3);
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
	'base_url'   => '/',
	'index_file' => FALSE
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	'auth'       => MODPATH.'auth',       // Basic authentication
	'cache'         => MODPATH.'cache',      // Caching with multiple backends
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	'database'   => MODPATH.'database',   // Database access
	'doctrine2'  => MODPATH.'doctrine2',  // Doctrine2
	// 'image'      => MODPATH.'image',      // Image manipulation
	// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
#	'jelly   '      => MODPATH.'jelly',	
#	'jelly-filtered'=> MODPATH.'jelly-filtered',
	'php-ipaddress' => MODPATH.'php-ipaddress',
#	'kohana-jelly-reverse-engineer' => MODPATH.'kohana-jelly-reverse-engineer',
	));


/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
#Route::set('default', '(<controller>(/<action>(/<id>)))')
#	->defaults(array(
#		'controller' => 'welcome',
#		'action'     => 'index',
#	));

Route::set('login', '', array(
        ))
        ->defaults(array(
                'controller' => 'login',
                'action'     => 'login_page',
        ));

Route::set('logout', 'logout', array(
        ))
        ->defaults(array(
                'controller' => 'login',
                'action'     => 'logout',
        ));

Route::set('package_config_backfire', 'package/config/backfire/<package>/<version>/<request_name>', array(
		'package'	=> '[A-Za-z0-9_]+',
		'version'	=> '[0-9.]+',
		'request_name' => '[A-Za-z0-9_]+',
	))
	->defaults(array(
		'directory'  => 'package/config',
		'controller' => 'backfire',
		'action'     => 'default',
	));

Route::set('package_config_lucid', 'package/config/lucid/<package>/<version>/<request_name>', array(
		'package'	=> '[A-Za-z0-9_]+',
		'version'	=> '[0-9.]+',
		'request_name' => '[A-Za-z0-9_]+',
	))
	->defaults(array(
		'directory'  => 'package/config',
		'controller' => 'lucid',
		'action'     => 'default',
	));
	
Route::set('package_test_config', 'test/config/<hostname>/<os>/<package>/<version>/<request_name>', array(
		'package'	=> '[A-Za-z0-9_]+',
		'version'	=> '[0-9.]+',
		'request_name'  => '[A-Za-z0-9_]+',
		'hostname'	=> '[A-Za-z0-9_.-]+',
		'os'		=> '[A-Za-z0-9_]+',
	))
	->defaults(array(
		'directory'  => 'test/config',
		'controller' => 'generic',
		'action'     => 'default',
		'type'       => 'config',
	));

Route::set('package_test_status', 'test/status/<hostname>/<os>/<request_name>', array(
		'request_name'  => '[A-Za-z0-9_]+',
		'hostname'	=> '[A-Za-z0-9_.-]+',
		'os'		=> '[A-Za-z0-9_]+',
	))
	->defaults(array(
		'directory'  => 'test/config',
		'controller' => 'generic',
		'action'     => 'default',
		'type'       => 'status',
	));

Route::set('package_status', 'status/<hostname>/<os>/<request_name>', array(
		'request_name'  => '[A-Za-z0-9_]+',
		'hostname'	=> '[A-Za-z0-9_.-]+',
		'os'		=> '[A-Za-z0-9_]+',
	))
	->defaults(array(
		'directory'  => 'status/config',
		'controller' => 'generic',
		'action'     => 'default',
	));

Route::set('test', 'admin/test', array(
	))
	->defaults(array(
		'directory'  => 'test/config',
		'controller' => 'generic',
		'action'     => 'home',
	));

Route::set('test_info', 'info', array(
	))
	->defaults(array(
		'directory'  => 'test/config',
		'controller' => 'generic',
		'action'     => 'info',
	));

Route::set('package_list', 'package/list/backfire')
	->defaults(array(
		'directory'  => 'package/list',
		'controller' => 'backfire',
		'action'     => 'default',
	));


Route::set('deployments_usage', 'admin/deployments/usage')
        ->defaults(array(
		'controller' => 'deployments_usage',
                'action'     => 'default',
        ));

Route::set('deployments_usage_all', 'admin/deployments/usage/all')
        ->defaults(array(
                'controller' => 'deployments_usage',
                'action'     => 'all',
        ));

Route::set('cron_jobs', 'admin/cronjobs')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'default',
        ));

Route::set('cron_jobs_by_host', 'admin/cronjobs/host/<host>')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'default',
        ));

Route::set('cron_jobs_incoming', 'admin/cronjobs/incoming')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'incoming',
        ));

Route::set('cron_jobs_enabled', 'admin/cronjobs/enabled')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'enabled',
        ));

Route::set('cron_jobs_enabled_by_host', 'admin/cronjobs/host/<host>/enabled')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'enabled',
        ));

Route::set('create_cron_job', 'admin/cronjobs/create')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'create',
        ));

Route::set('delete_cron_job', 'admin/cronjobs/<id>/delete')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'delete',
        ));

Route::set('edit_cron_job', 'admin/cronjobs/<id>/edit')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'edit',
        ));

Route::set('view_cron_job', 'admin/cronjobs/<id>')
        ->defaults(array(
                'controller' => 'cronjobs',
                'action'     => 'view',
        ));

Route::set('certificates', 'admin/certificates')
        ->defaults(array(
                'controller' => 'certificates',
                'action'     => 'default',
        ));

Route::set('current_certificates', 'admin/certificates/current')
        ->defaults(array(
                'controller' => 'certificates',
                'action'     => 'current',
        ));

Route::set('view_certificate', 'admin/certificates/<id>')
        ->defaults(array(
                'controller' => 'certificates',
                'action'     => 'view',
        ));

Route::set('reserved_subnets', 'admin/subnets/reserved')
        ->defaults(array(
                'controller' => 'subnets_reserved',
                'action'     => 'default',
        ));

Route::set('create_reserved_subnet', 'admin/subnets/reserved/create')
        ->defaults(array(
                'controller' => 'subnets_reserved',
                'action'     => 'create',
        ));

Route::set('delete_reserved_subnet', 'admin/subnets/reserved/<id>/delete')
        ->defaults(array(
                'controller' => 'subnets_reserved',
                'action'     => 'delete',
        ));

Route::set('edit_reserved_subnet', 'admin/subnets/reserved/<id>/edit')
        ->defaults(array(
                'controller' => 'subnets_reserved',
                'action'     => 'edit',
        ));

Route::set('servers', 'admin/servers')
        ->defaults(array(
                'controller' => 'servers',
                'action'     => 'default',
        ));

Route::set('current_servers', 'admin/servers/current')
        ->defaults(array(
                'controller' => 'servers',
                'action'     => 'current',
        ));

Route::set('create_server', 'admin/servers/create')
        ->defaults(array(
                'controller' => 'servers',
                'action'     => 'create',
        ));

Route::set('delete_server', 'admin/servers/<id>/delete')
        ->defaults(array(
                'controller' => 'servers',
                'action'     => 'delete',
        ));

Route::set('edit_server', 'admin/servers/<id>/edit')
        ->defaults(array(
                'controller' => 'servers',
                'action'     => 'edit',
        ));

Route::set('generate_server_wiki_page', 'admin/servers/<id>/wiki_page')
        ->defaults(array(
                'controller' => 'servers',
                'action'     => 'generate_wiki_page',
        ));

Route::set('view_server', 'admin/servers/<id>')
        ->defaults(array(
                'controller' => 'servers',
                'action'     => 'view',
        ));

Route::set('nodes', 'admin/nodes')
        ->defaults(array(
                'controller' => 'nodes',
                'action'     => 'default',
        ));

Route::set('deployable_nodes', 'admin/nodes/deployable')
        ->defaults(array(
                'controller' => 'nodes',
                'action'     => 'deployable',
        ));

Route::set('create_node', 'admin/nodes/create')
        ->defaults(array(
                'controller' => 'nodes',
                'action'     => 'create',
        ));

Route::set('delete_node', 'admin/nodes/<boxNumber>/delete')
        ->defaults(array(
                'controller' => 'nodes',
                'action'     => 'delete',
        ));

Route::set('edit_node', 'admin/nodes/<boxNumber>/edit')
        ->defaults(array(
                'controller' => 'nodes',
                'action'     => 'edit',
        ));

Route::set('submit_hash_node', 'admin/nodes/<boxNumber>/submit_hash')
        ->defaults(array(
                'controller' => 'nodes',
                'action'     => 'submit_hash',
        ));

Route::set('view_node', 'admin/nodes/<boxNumber>')
        ->defaults(array(
                'controller' => 'nodes',
                'action'     => 'view',
        ));

Route::set('deployments', 'admin/deployments')
        ->defaults(array(
                'controller' => 'deployments_main',
                'action'     => 'default',
        ));

Route::set('current_deployments', 'admin/deployments/current')
        ->defaults(array(
                'controller' => 'deployments_main',
                'action'     => 'current',
        ));
Route::set('my_deployments', 'admin/deployments/mine')
        ->defaults(array(
                'controller' => 'deployments_main',
                'action'     => 'mine',
        ));
Route::set('create_deployment', 'admin/deployments/create')
        ->defaults(array(
                'controller' => 'deployments_main',
                'action'     => 'create',
        ));

Route::set('delete_deployment', 'admin/deployments/<id>/delete')
        ->defaults(array(
                'controller' => 'deployments_main',
                'action'     => 'delete',
        ));

Route::set('end_deployment', 'admin/deployments/<id>/end')
        ->defaults(array(
                'controller' => 'deployments_main',
                'action'     => 'end',
        ));

Route::set('edit_deployment', 'admin/deployments/<id>/edit')
        ->defaults(array(
                'controller' => 'deployments_main',
                'action'     => 'edit',
        ));

Route::set('view_deployment', 'admin/deployments/<id>')
        ->defaults(array(
                'controller' => 'deployments_main',
                'action'     => 'view',
        ));

Route::set('usage_deployment', 'admin/deployments/<id>/usage')
        ->defaults(array(
                'controller' => 'deployments_usage',
                'action'     => 'default',
        ));

Route::set('node_requests', 'admin/noderequests')
	->defaults(array(
                'controller' => 'noderequests',
                'action'     => 'default',
        ));

Route::set('pending_node_requests', 'admin/noderequests/pending')
        ->defaults(array(
                'controller' => 'noderequests',
                'action'     => 'pending',
        ));

Route::set('delete_node_request', 'admin/noderequests/<id>/delete')
        ->defaults(array(
                'controller' => 'noderequests',
                'action'     => 'delete',
        ));

Route::set('edit_node_request', 'admin/noderequests/<id>/edit')
        ->defaults(array(
                'controller' => 'noderequests',
                'action'     => 'edit',
        ));

Route::set('view_node_request', 'admin/noderequests/<id>')
        ->defaults(array(
                'controller' => 'noderequests',
                'action'     => 'view',
        ));

Route::set('enquiries', 'admin/enquiries')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'default',
        ));

Route::set('unresponded_enquiries', 'admin/enquiries/unresponded')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'unresponded',
        ));

Route::set('unresponded_type_enquiries', 'admin/enquiries/unresponded/types/<type>')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'unresponded',
        ));

Route::set('type_enquiries', 'admin/enquiries/types/<type>/list')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'default',
        ));

Route::set('enquiry_types', 'admin/enquiries/types')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'types',
        ));

Route::set('create_enquiry_type', 'admin/enquiries/types/create')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'create_type',
        ));

Route::set('delete_enquiry_type', 'admin/enquiries/types/<id>/delete')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'delete_type',
        ));

Route::set('edit_enquiry_type', 'admin/enquiries/types/<id>/edit')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'edit_type',
        ));

Route::set('reply_enquiry', 'admin/enquiries/<id>/reply')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'reply',
        ));

Route::set('view_enquiry', 'admin/enquiries/<id>')
        ->defaults(array(
                'controller' => 'enquiries',
                'action'     => 'view',
        ));


Route::set('inventory', 'admin/inventory')
        ->defaults(array(
                'controller' => 'inventory',
                'action'     => 'default',
        ));

Route::set('create_inventory_item', 'admin/inventory/item/create')
        ->defaults(array(
                'controller' => 'inventory',
                'action'     => 'create',
        ));

Route::set('delete_inventory_item', 'admin/inventory/item/<id>/delete')
        ->defaults(array(
                'controller' => 'inventory',
                'action'     => 'delete',
        ));

Route::set('edit_inventory_item', 'admin/inventory/item/<id>/edit')
        ->defaults(array(
                'controller' => 'inventory',
                'action'     => 'edit',
        ));

Route::set('view_inventory_item', 'admin/inventory/item/<id>')
        ->defaults(array(
                'controller' => 'inventory',
                'action'     => 'view',
	));

Route::set('view_photo_inventory_item', 'admin/inventory/item/<id>/photo')
        ->defaults(array(
                'controller' => 'inventory',
                'action'     => 'view_photo',
        ));
Route::set('view_photo_size_inventory_item', 'admin/inventory/item/<id>/photo/<size>')
        ->defaults(array(
                'controller' => 'inventory',
                'action'     => 'view_photo',
        ));

Route::set('view_radacct', 'admin/radaccts/<radacctid>')
        ->defaults(array(
                'controller' => 'radaccts',
                'action'     => 'view',
        ));

Route::set('radaccts', 'admin/radaccts')
        ->defaults(array(
                'controller' => 'radaccts',
                'action'     => 'default',
        ));

Route::set('radaccts_page', 'admin/radaccts/page/<page>')
        ->defaults(array(
                'controller' => 'radaccts',
                'action'     => 'default',
        ));

Route::set('view_radpostauth', 'admin/radpostauths/<id>')
        ->defaults(array(
                'controller' => 'radpostauths',
                'action'     => 'view',
        ));

Route::set('users', 'admin/users')
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'default',
        ));
Route::set('test2', 'admin/test2')
        ->defaults(array(
                'controller' => 'test',
                'action'     => 'default',
        ));
Route::set('user_autocomplete', 'admin/users/autocomplete')
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'autocomplete',
        ));
Route::set('user_autocomplete_where', 'admin/users/autocomplete/<where>', array('path' => '.*'))
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'autocomplete',
        ));

Route::set('create_user', 'admin/users/create')
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'create',
        ));

Route::set('create_external_user', 'admin/users/create/external')
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'create_external',
        ));

Route::set('delete_user', 'admin/users/<id>/delete')
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'delete',
        ));

Route::set('details_user', 'admin/users/<username>/details', array('username' => '[a-zA-Z0-9_\-\.]+'))
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'view_details_json',
        ));

Route::set('edit_user', 'admin/users/<id>/edit')
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'edit',
        ));

Route::set('view_user', 'admin/users/<id>')
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'view',
        ));

Route::set('forgot_password', 'forgot_password', array(
        ))
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'forgot_password',
        ));

Route::set('change_password', 'admin/change_password', array(
        ))
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'change_password',
        ));

Route::set('reset_password', 'reset_password/<hash>', array(
        ))
        ->defaults(array(
                'controller' => 'users',
                'action'     => 'reset_password',
        ));

Route::set('create_note', 'admin/notes/create')
        ->defaults(array(
                'controller' => 'notes',
                'action'     => 'create',
        ));

Route::set('delete_note', 'admin/notes/<id>/delete')
        ->defaults(array(
                'controller' => 'notes',
                'action'     => 'delete',
        ));

Route::set('notes_table', 'admin/notes/table')
        ->defaults(array(
                'controller' => 'notes',
                'action'     => 'table',
        ));

Route::set('error', 'error/<action>(/<message>)', array(
		'action' => '[0-9]++',
		'message' => '.+'))
	->defaults(array(
		'controller' => 'error'
));

Route::set('foo', 'foo/<action>')
	->defaults(array(
		'controller' => 'JellyReverseEngineer'
	));

Route::set('home', 'admin')
        ->defaults(array(
                'controller' => 'home',
                'action'     => 'default',
        ));

Route::set('deployments_usage_monthly_graph', 'admin/deployments/usage/graphs/monthly/<deployment_id>')
        ->defaults(array(
                'controller' => 'deployments_usage',
                'action'     => 'monthly_graph',
        ));

Route::set('deployments_usage_daily_graph', 'admin/deployments/usage/graphs/daily/<deployment_id>')
        ->defaults(array(
                'controller' => 'deployments_usage',
                'action'     => 'daily_graph',
        ));

Route::set('update_dnz_zones', 'scripts/update_dns_zones/<tmpdir>')
        ->defaults(array(
                'controller' => 'scripts',
                'action'     => 'update_dns_zones',
        ));

Route::set('update_nass', 'scripts/update_nass')
	->defaults(array(
                'controller' => 'scripts',
                'action'     => 'update_nass',
        ));

Route::set('update_stas', 'scripts/update_stas')
        ->defaults(array(
                'controller' => 'scripts',
                'action'     => 'update_stas',
        ));

Route::set('update_stas_all', 'scripts/update_stas/<all>')
        ->defaults(array(
                'controller' => 'scripts',
                'action'     => 'update_stas',
        ));

Route::set('data_list', 'data')
	->defaults(array(
                'controller' => 'data',
                'action'     => 'default',
        ));

Route::set('data_current_radius_users', 'data/current_radius_users')
	->defaults(array(
                'controller' => 'data',
                'action'     => 'current_radius_users',
        ));

Route::set('data_radius_users_day', 'data/radius_users_day/<year>/<month>/<day>')
	 ->defaults(array(
                'controller' => 'data',
                'action'     => 'radius_users_day',
        ));

Route::set('data_graph_uc_day', 'data/graph/<type>/day')
         ->defaults(array(
                'controller' => 'data',
                'action'     => 'day_graph',
        ));

Route::set('data_graph_uc_month', 'data/graph/<type>/month')
         ->defaults(array(
                'controller' => 'data',
                'action'     => 'month_graph',
        ));

Route::set('data_graph_uc_hour', 'data/graph/<type>/hour')
         ->defaults(array(
                'controller' => 'data',
                'action'     => 'hour_graph',
        ));

Route::set('data_graph_uc_node', 'data/graph/<type>/node')
         ->defaults(array(
                'controller' => 'data',
                'action'     => 'node_graph',
        ));

require_once(APPPATH.'/classes/mysql-dbo.php');
Doctrine\DBAL\Types\Type::addType('ipv4address', 'Model_Type_IPv4Address');
Doctrine\DBAL\Types\Type::addType('ipv6address', 'Model_Type_IPv6Address');
Doctrine\DBAL\Types\Type::addType('ipv4networkaddress', 'Model_Type_IPv4NetworkAddress');
Doctrine\DBAL\Types\Type::addType('ipv6networkaddress', 'Model_Type_IPv6NetworkAddress');
Doctrine\DBAL\Types\Type::addType('deploymenttype', 'Model_Type_DeploymentType');
Doctrine\DBAL\Types\Type::addType('tunnelprotocol', 'Model_Type_TunnelProtocol');
