<?php
/**
 * Created by PhpStorm.
 * User: Vitalik
 * Date: 19.01.2015
 * Time: 16:15
 */

abstract class Datagert extends Kohana_Database {

    public static $arr_config;

    public static function name_database(){
        self::$arr_config = parent::instance();

        return self::$arr_config->_config['connection']['database'];
    }
}