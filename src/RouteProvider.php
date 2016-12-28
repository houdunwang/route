<?php
/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/
namespace houdunwang\route;

use houdunwang\framework\ServiceProvider;

class RouteProvider extends ServiceProvider {
	//延迟加载
	public $defer = true;

	public function boot() {
		//解析路由
		\Route::dispatch();
	}

	public function register() {
		$this->app->single( 'Route', function ( $app ) {
			return Route::single( $app );
		} );
	}
}