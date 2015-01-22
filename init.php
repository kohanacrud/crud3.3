<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: Vitalik
 * Date: 07.05.14
 * Time: 23:13
 * To change this template use File | Settings | File Templates.
 */


Route::set('test', Kohana::$config->load('crudconfig.base_url'))
    ->defaults(array(
    'controller' => 'test',
    'action'     => 'index',
));





//route systems not delete
include ('config/route_config.php');
