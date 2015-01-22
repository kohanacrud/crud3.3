<?php
/**
 * Created by PhpStorm.
 * User: Vitalik
 * Date: 02.01.2015
 * Time: 20:16
 */

Class ReallyCoolTest extends Unittest_TestCase
{

    private $metod_crud;
    private $response;




    public function __construct () {

        $this->response = Request::factory('/admin')->execute();
//        //$this->metod_crud = new Cruds();
        $this->metod_crud = Controller_Test::$testing_static_crud;
    }

//    function providerStrLen()
//    {
//
//        return array(
//            array('One set of testcase data', 24),
//            array('This is a different one', 23),
//        );
//    }

    /**
     * @dataProvider providerStrLen
     */
//    function testStrLen($string, $length)
//    {
//        $this->assertSame(
//            $length,
//            strlen($string)
//        );
//    }



    //model all

    public function test_select_table(){
        //$select_table  = $this->metod_crud->select_table();
        //print_r($select_table);
    }

    public function test_show_name_column () {

        foreach ($this->metod_crud->show_name_column() as $rows) {

            $this->assertNotEmpty($rows['COLUMN_NAME']);
        }
    }

    public function test_select_all_where () {
        $this->assertNotEmpty($this->metod_crud->table, 'table name');
        $this->assertNotEmpty(Model::factory('All')->select_all_where($this->metod_crud->table), 'metod model not empty');

    }

    public function test_load_table () {

        $this->assertNotEmpty($this->metod_crud->table, 'name table not empty');
        $this->assertNotEmpty($this->metod_crud->class_metod['function'], 'function not empty');
        $this->assertNotEmpty($this->metod_crud->class_metod['class'], 'class not empty');
        $this->assertNotEmpty($this->metod_crud->class_metod['callback_function_name'], 'callback_function_name not empty');

    }



}

