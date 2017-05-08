<?php
/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/

namespace houdunwang\route\build;

use Exception;
use houdunwang\config\Config;
use ReflectionMethod;
use houdunwang\container\Container;
use houdunwang\middleware\Middleware;

/**
 * 控制器处理类
 * Class Controller
 *
 * @package houdunwang\route\build
 */
trait Controller
{
    protected $module;
    protected $controller;
    protected $action;
    //路由参数
    protected $routeArgs = [];

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }


    /**
     * 获取默认控制器动作
     */
    public function getDefaultControllerAction()
    {
        //URL结构处理
        $param = array_filter(
            explode('/', Request::get(Config::get('http.url_var')))
        );

        switch (count($param)) {
            case 2:
                array_unshift($param, Config::get('http.default_module'));
                break;
            case 1:
                array_unshift($param, Config::get('http.default_controller'));
                array_unshift($param, Config::get('http.default_module'));
                break;
            case 0:
                array_unshift($param, Config::get('http.default_action'));
                array_unshift($param, Config::get('http.default_controller'));
                array_unshift($param, Config::get('http.default_module'));
                break;
        }
        //替换方法名称
        $param[1] = preg_replace_callback(
            '/_([a-z])/',
            function ($matches) {
                return ucfirst($matches[1]);
            },
            $param[1]
        );

        return implode('/', $param);
    }

    /**
     * 执行控制器
     *
     * @param $action
     *
     * @return mixed
     * @throws \Exception
     */
    public function controllerRun($action, $args = [])
    {
        if (count(explode('/', $action)) != 3) {
            throw new Exception('控制器参数错误');
        }
        $this->compileControllerAction($action);
        //控制器开始运行中间件
        \Middleware::system('controller_start');

        return $this->action($args);
    }

    /**
     * 根据动作地址设置常量
     *
     * @param $action
     */
    public function compileControllerAction($action)
    {
        $param = explode('/', $action);
        $this->setModule($param[0]);
        $this->setController($param[1]);
        $this->setAction($param[2]);

        defined('MODULE') or define('MODULE', $param[0]);
        defined('CONTROLLER') or define('CONTROLLER', ucfirst($param[1]));
        defined('ACTION') or define('ACTION', $param[2]);
        defined('MODULE_PATH') or define(
            'MODULE_PATH',
            ROOT_PATH.'/'.Config::get('controller.app').'/'.MODULE
        );
        defined('VIEW_PATH') or define('VIEW_PATH', MODULE_PATH.'/view');
        defined('__VIEW__') or define(
            '__VIEW__',
            __ROOT__.'/'.Config::get('app.path').'/'.MODULE.'/view'
        );
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param mixed $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param mixed $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * 执行控制器
     *
     * @param       $action
     * @param array $args
     *
     * @return mixed
     * @throws \Exception
     */
    public function executeControllerAction($action, $args = [])
    {
        $info   = explode('@', $action);
        $class  = Config::get('http.app').'\\'.$info[0];
        $method = $info[1];
        //控制器不存在执行中间件
        if ( ! class_exists($class)) {
            throw new Exception('控制器不存在');
        }
        //方法不存在时执行中间件
        if ( ! method_exists($class, $method)) {
            throw new Exception('控制器的方法不存在');
        }

        //控制器开始运行中间件
        Middleware::web('controller_start');
        $controller = Container::make($class, true);
        try {
            /**
             * 参数处理
             * 控制器路由方式访问时解析路由参数并注入到控制器方法参数中
             */
            $reflectionMethod = new \ReflectionMethod($class, $method);
            foreach ($reflectionMethod->getParameters() as $k => $p) {
                if (isset($this->args[$p->name])) {
                    //如果为路由参数时使用路由参数赋值
                    $args[$p->name] = $this->args[$p->name];
                } else {
                    //如果类型为类时分析类
                    if ($dependency = $p->getClass()) {
                        $args[$p->name] = Container::build($dependency->name);
                    } else {
                        //普通参数时获取默认值
                        $args[$p->name] = Container::resolveNonClass($p);
                    }
                }
            }

            //执行控制器方法
            return $reflectionMethod->invokeArgs($controller, $args);
        } catch (ReflectionException $e) {
            $action = new ReflectionMethod($controller, '__call');

            return $action->invokeArgs($controller, [$method, '']);
        }
    }
}