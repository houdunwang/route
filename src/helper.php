<?php
if ( ! function_exists('view')) {
    /**
     * 显示模板
     *
     * @param string $tpl
     * @param int    $expire
     *
     * @return mixed
     */
    function view($tpl = '', $expire = 0)
    {
        return \houdunwang\view\View::make($tpl, $expire);
    }
}
if ( ! function_exists('widget')) {
    //解析页面组件
    function widget()
    {
        $vars = func_get_args();
        $info = preg_split('@[\./]@', array_shift($vars));
        //方法名
        $method = array_pop($info);
        //类名
        $className = array_pop($info);
        $class     = implode('\\', $info).'\\'.ucfirst($className);

        return call_user_func_array([new $class, $method], $vars);
    }
}

if ( ! function_exists('truncate')) {
    /**
     * 截取文字内容
     *
     * @param string $content 内容
     * @param int    $len     长度
     *
     * @return string
     */
    function truncate($content, $len = 30)
    {
        return mb_substr($content, 0, $len, 'utf8');
    }
}

if ( ! function_exists('action')) {
    /**
     * 执行控制器方法
     *
     * @param       $controller
     * @param       $action
     * @param array $args
     *
     * @return mixed
     */
    function action($controller, $action, $args = [])
    {

        return call_user_func_array([new $controller, $action], $args);
    }
}