<?php
/**
 * Created by PhpStorm.
 * User: MosinVE
 * Date: 18.04.2017
 * Time: 23:14
 */

namespace instaCheck;


class SessionStorage
{
    private $SessionState = false;

    /**
     * SessionStorage constructor.
     *
     */
    public function __construct()
    {
        $this->SessionState = session_start();
    }

    /**
     * @param $name
     * @param $value
     */
    function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    function get($name)
    {
        if (isset($_SESSION[$name])){
	        return $_SESSION[$name];
        }
    }
}