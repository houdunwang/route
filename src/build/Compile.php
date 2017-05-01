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

use Closure;
use houdunwang\container\Container;
use houdunwang\controller\Controller;
use houdunwang\request\Request;

trait Compile
{
    //匹配到路由
    protected $found = false;
    //路由参数
    public $args = [];
    //匹配成功的路由规则
    protected $matchRoute;
    //解析结果
    protected $result;

    /**
     * 获取匹配成功的路由
     *
     * @return mixed
     */
    public function getMatchRoute()
    {
        return $this->matchRoute;
    }

    /**
     * 设置匹配成功的路由
     *
     * @param mixed $matchRoute
     */
    private function setMatchRoute($matchRoute)
    {
        $this->matchRoute = $matchRoute;
    }

    /**
     * 获取路由解析结果
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * 设置路由解析结果
     *
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    //匹配路由
    protected function isMatch($key)
    {
        if (preg_match($this->route[$key]['regexp'], $this->requestUri)) {
            //获取参数
            $this->route[$key]['get'] = $this->getArgs($key);
            //验证参数
            if ( ! $this->checkArgs($key)) {
                return false;
            }
            //设置GET参数
            $this->args = $this->route[$key]['get'];
            //匹配成功的路由规则
            $this->setMatchRoute($this->route[$key]);

            return $this->found = true;
        }
    }

    //获取请求参数
    protected function getArgs($key)
    {
        $args = [];
        if (preg_match_all(
            $this->route[$key]['regexp'],
            $this->requestUri,
            $matched,
            PREG_SET_ORDER
        )) {
            //参数列表
            foreach ($this->route[$key]['args'] as $n => $value) {
                if (isset($matched[0][$n + 1])) {
                    $args[$value[1]] = $matched[0][$n + 1];
                }
            }
        }

        return $args;
    }

    //验证路由参数
    protected function checkArgs($key)
    {
        $route = $this->route[$key];
        if ( ! empty($route['where'])) {
            foreach ($route['where'] as $name => $regexp) {
                if (isset($route['get'][$name])
                    && ! preg_match($regexp, $route['get'][$name])
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    //执行路由事件
    public function exec($key)
    {
        //匿名函数
        if ($this->route[$key]['callback'] instanceof Closure) {
            //反射分析闭包
            $reflectionFunction
                  = new \ReflectionFunction($this->route[$key]['callback']);
            $gets = $this->route[$key]['get'];
            $args = [];
            foreach ($reflectionFunction->getParameters() as $k => $p) {
                if (isset($gets[$p->name])) {
                    //如果GET变量中存在则将GET变量值赋予,也就是说GET优先级高
                    $args[$p->name] = $gets[$p->name];
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
            $this->setResult($reflectionFunction->invokeArgs($args));
        } else if ($this->route[$key]['method'] == 'controller') {
            //执行控制器
            $result = Controller::run($this->route[$key]['callback']);
            $this->setResult($result);
        }
    }

    //URL事件处理
    protected function _alias($key)
    {
        if ($this->isMatch($key)) {
            //替换GET参数
            $url = $this->route[$key]['callback'];
            foreach ($this->route[$key]['get'] as $k => $v) {
                $url = str_replace('{'.$k.'}', $v, $url);
            }
            //解析后的GET参数设置到全局$_GET中
            parse_str($url, $gets);
            foreach ((array)$gets as $k => $v) {
                Request::set('get.'.$k, $v);
            }
            Controller::run($gets);
        }
    }

    //GET事件处理
    protected function _get($key)
    {
        return IS_GET && $this->isMatch($key) && $this->exec($key);
    }

    //POST事件处理
    protected function _post($key)
    {
        return IS_POST && $this->isMatch($key) && $this->exec($key);
    }

    //PUT事件处理
    protected function _put($key)
    {
        return IS_PUT && $this->isMatch($key) && $this->exec($key);
    }

    //DELETE事件
    protected function _delete($key)
    {
        return IS_DELETE && $this->isMatch($key) && $this->exec($key);
    }

    //任意提交模式
    protected function _any($key)
    {
        return $this->isMatch($key) && $this->exec($key);
    }

    //控制器路由
    protected function _controller($key)
    {
        if ($this->route[$key]['method'] == 'controller'
            && $this->isMatch($key)
        ) {
            //控制器方法
            $method = $this->getRequestAction()
                .ucfirst($this->route[$key]['get']['method']);
            //从容器提取控制器对象
            $info = explode('/', $this->route[$key]['callback']);
            define('MODULE', array_shift($info));
            define('CONTROLLER', array_shift($info));
            define('ACTION', $method);
            Controller::run();
        }
    }

    //获取请求方法
    public function getRequestAction()
    {
        switch (true) {
            case IS_GET:
                return 'get';
            case IS_POST:
                return 'post';
            case IS_PUT:
                return 'put';
            case IS_DELETE:
                return 'delete';
        }
    }

    //获取解析后的参数
    public function getArg()
    {
        return $this->args;
    }
}