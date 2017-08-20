<?php
error_reporting(E_ALL | E_STRICT);

/**
 * Test bootstrap, for setting up auto-loading
 */
class Bootstrap
{
    public static function init()
    {
        include('');
    }
}

Bootstrap::init();