<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class Controller_Test
 * Пример тестового контроллера
 */
class Controller_Test extends Controller
{


    public function action_index()
    {

        $this->response->body(self::adminpanel()->render());

    }

    /**
     * @return Cruds
     * Метод в котором вызывается crud обязательно статический
     */
    public static function adminpanel()
    {
        $crud = new Cruds;
        $crud->load_table('city');

        return $crud;

    }



}