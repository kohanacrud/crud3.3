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

        $crud->disable_editor('name');
        $crud->disable_editor('name_en');
        $crud->disable_editor('region_en');
        $crud->disable_editor('region');


        $crud->show_name_column(array('id' => 'ID',
            'name' => 'Название',
            'name_en' => 'Название (анг.)',
            'region' => 'Регион',
            'region_en' => 'Регион (анг.)'));

        $crud->set_lang('ru');
        //$crud->icon_edit('glyphicon-pencil');
        //$crud->icon_delete('glyphicon-remove-circle');
        $crud->set_field_type('name', 'text');
        $crud->set_field_type('name_en', 'text');
        $crud->set_field_type('region', 'text');
        $crud->set_field_type('region_en', 'text');



        return $crud;

    }





}