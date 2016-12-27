<?php
require 'vendor/autoload.php';
$config = [
	//引擎:file,mysql,memcache,redis
	'driver'    => 'file',
	//session_name
	'name'      => 'hdcmsid',
	//cookie加密密钥
	'secureKey' => 'houdunwang88',
	//有效域名
	'domain'    => '',
	//过期时间 0 会话时间 3600 为一小时
	'expire'    => 0,
	#File
	'file'      => [
		'path' => 'storage/session',
	]
];
\houdunwang\config\Config::set( 'session', $config );
$config = [
	//密钥
	'key'    => '405305c793179059f8fd52436876750c587d19ccfbbe2a643743d021dbdcd79c',
	//前缀
	'prefix' => 'HOUDUNWANG##'
];
\houdunwang\config\Config::set( 'cookie', $config );
$config = [
	'driver' => 'file',
	'file'   => [
		'dir' => 'storage/cache'
	]
];
\houdunwang\config\Config::set( 'cache', $config );
$config = [
	'cache' => false
];
\houdunwang\config\Config::set( 'route', $config );
\houdunwang\route\Route::get( '/', function () {
	echo 33;
} );
\houdunwang\route\Route::any( '/user', function () {
	return '你好 后盾网';
} );
\houdunwang\route\Route::get('/foo', 'home/index/add');
\houdunwang\route\Route::dispatch();