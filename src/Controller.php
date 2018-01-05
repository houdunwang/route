<?php
/** .-------------------------------------------------------------------
 * |  Software: [HDPHP framework]
 * |      Site: www.hdphp.com  www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/

namespace houdunwang\route;

use houdunwang\route\controller\Message;
use Code;

/**
 * 控制器基础类
 * Class Controller
 *
 * @package houdunwang\route
 */
abstract class Controller
{
    use Message;

    /**
     * 验证码
     */
    final public function captcha()
    {
        Code::make();
    }

    /**
     * 404 NotFound
     *
     * @return mixed
     */
    final public function _404($return = false)
    {
        return Response::_404($return);
    }
}