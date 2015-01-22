<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: Vitalik
 * Date: 07.05.14
 * Time: 23:43
 * To change this template use File | Settings | File Templates.
 */

class Controller_Core_Crud extends Controller_Core_Main {

    protected $table;
    protected $id;
    protected $del_arr;
    protected $id_del_array;


    public function  action_delete () {

        $re = unserialize(base64_decode($_POST['obj']));
        //запускается статический метод Controller_Test::asd
        $retw = call_user_func(array($re['callback_functions_array']['class'],
            $re['callback_functions_array']['function']));

        $this->id = Arr::get($_POST, 'id');

        //флаг для групового удаления
        $this->del_arr = Arr::get($_POST, 'del_arr');

        $this->id_del_array =  Arr::get($_POST, 'id_del_array');


        //делаем запрос если хоть один хук обявлен
        if ($retw->callback_before_delete != null or $retw->callback_after_delete != null) {
            //получаем масив строку таблицы которая должна быть удалена
            //переиницыализация хуков
            $retw->callback_before_delete($retw->callback_before_delete['name_function']);
            $retw->callback_after_delete($retw->callback_after_delete['name_function']);

            $query_array_del = Model::factory('All')->select_all_where($retw->table, $this->id);
            //получаем данные из таблиц
            $query_array_del = $query_array_del[0];

            if ($retw->set_one_to_many) {
                $query_array_del = Model::factory('All')->get_other_table($retw->set_one_to_many, $query_array_del, $this->id, false);
            }
            //die(print_r($query_array_del));
        }


        //если хук определен возврящаем данные удаления
        if ($retw->callback_before_delete != null) {
            //die(print_r($re['callback_functions_array']['function']));
            //переиницыализация статического метода обработчика
            $callbackStatic = call_user_func(array($re['callback_functions_array']['class'],
                $retw->callback_before_delete['name_function']), $query_array_del);

            //если хук ничего не возвращает то присваивание нового параметра не будет
            if ($callbackStatic != '') {
                $this->id = $callbackStatic;
            }

        }



        if (!isset($callbackStatic) or $callbackStatic !== false) {
            //удаляем

            if ($retw->set_one_to_many) {
                $fields = Model::factory('All')->delete_other_table($retw->set_one_to_many, $this->id);
            }

            if ($this->del_arr == 1) {
                $query =  Model::factory('All')->group_delete($retw->table, $this->id_del_array);
            } else {
                $query = Model::factory('All')->delete($retw->table, $this->id);
            }
        }

        //проверяем обявлен ли хук
        if ($retw->callback_after_delete != null) {
            call_user_func(array($re['callback_functions_array']['class'],
                $retw->callback_after_delete['name_function']), $query_array_del);
        }


        Request::initial()->redirect(Kohana::$config->load('crudconfig.base_url')); //редирект на главную после операции

    }

    //
    private function uploads_dir_absolut($dir_path)
    {
        //пути
        return array('absolut' => DOCROOT . $dir_path . DIRECTORY_SEPARATOR,
                    'relative' => '/'. $dir_path . DIRECTORY_SEPARATOR
        );

    }


    private function file_force_download($file) {

        $absolute = URL::site(substr($file, 1), 'http');
        $headers = get_headers($absolute, 1);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); // нужен для некоторых браузеров
        header("Content-type: ".Arr::get($headers, 'Content-Type'));
        header("Content-Disposition: attachment; filename=".basename($file));
        header("Content-Transfer-Encoding: binary");

    }










    public function action_edit () {

        //получаем масив $_GET
        $get = $this->request->query();

        if (isset($_POST['edit'])) {
             //die(print_r($_FILES));
            $re = unserialize(base64_decode($_POST['obj']));
        } else {
            $re = unserialize(base64_decode($get['obj']));
        }


        $retw = call_user_func(array($re['callback_functions_array']['class'],
            $re['callback_functions_array']['function']));

        //установка язика
        I18n::lang($retw->set_lang);

        //получаем первичный ключ
        $key_primary = Model::factory('All')->information_table($retw->table, true);
        $key_primary = $key_primary[0]->COLUMN_NAME;

        if (isset($_POST['edit'])) {
            $this->id = Arr::get($_POST, 'id');
        } else {
            $this->id = $get['id'];
        }

        Cruds::$id = $this->id;

        if (isset($_POST['edit'])) {

            $this->id = Arr::get($_POST, $key_primary);


            $name_count = Model::factory('All')->name_count($retw->table);
            //перебори формирования массива для передачи в модель для обновления записей
            //ищем в масиве $_GET поля которые вернула модель name_count
            foreach ($name_count as $name_count_rows) {



                if (isset($_POST[$name_count_rows['COLUMN_NAME']])) {
                    //если это масив то сериализуем
                    if (is_array($_POST[$name_count_rows['COLUMN_NAME']])) {
                        //удаляем пустые элементы масива

                        $befor_serialise = array_filter($_POST[$name_count_rows['COLUMN_NAME']]);

                        //если обявлен метод 1-n
                        if ($retw->set_one_to_many) {
                            $update[$name_count_rows['COLUMN_NAME']] = $befor_serialise;
                        } else {
                            $update[$name_count_rows['COLUMN_NAME']] = serialize($befor_serialise);
                        }


                    } else {
                        $update[$name_count_rows['COLUMN_NAME']] = $_POST[$name_count_rows['COLUMN_NAME']];
                    }
                    //если поля нету то проверяем масив $_FILES
                    $new_array = $update;
                } else {

                    //если поле определено как file
                    if (!empty($retw->type_field_upload)) {
                        //die(print_r($retw->type_field_upload));
                        //получаем масив с типом поля и путь к дирикктории хранения файла
                        $dir_path = $retw->type_field_upload[$name_count_rows['COLUMN_NAME']];


                       //проверяем является ли масивом
                       if (is_array($_FILES[$name_count_rows['COLUMN_NAME']]['name'])) {


                           foreach ($_FILES[$name_count_rows['COLUMN_NAME']]['name'] as $key => $rows_name) {
                               //проверка на наличие отправляемого файла

                               if ($rows_name != '') {
                                   //расширение файла
                                   $type_file = '.'. strtolower(pathinfo($rows_name, PATHINFO_EXTENSION));
                                   //генерируем имя файла с префиксом
                                   $name_file =  $dir_path[2].uniqid().$type_file;
                                   //получаем абсолютный путь к директории храния файла
                                   $file_path = $this->uploads_dir_absolut($dir_path[1]);
                                    //сохранение файла
                                   $uploaded = Upload::save(array('tmp_name' => $_FILES[$name_count_rows['COLUMN_NAME']]['tmp_name'][$key]), $name_file, $file_path['absolut']);
                                   //создание масива относительных путей
                                   $file_update[] = $file_path['relative'].$name_file;
                               }
                           }

                           //hide поля с относительными путями к файлам
                           $colum_file = 'editfile-'.$name_count_rows['COLUMN_NAME'];
                           //проверяем не является ли масив с новыми файлами пустым
                           if (!empty($_POST[$colum_file]) and !empty($file_update)) {

                               $file_update = array_merge($file_update, $_POST[$colum_file]);

                           } elseif (empty($file_update) and !empty($_POST[$colum_file])) { //если поле file пустое то присваиваем только скрытые поля

                                $file_update = $_POST[$colum_file];

                           } elseif (empty($file_update) and empty($_POST[$colum_file])) { //если поле file  и скрытые поля пусты
                               $file_update = array();
                           }

                           //если определен метод 1-n
                           if ($retw->set_one_to_many) {
                               $update[$name_count_rows['COLUMN_NAME']] = $file_update;
                           } else {
                               $update[$name_count_rows['COLUMN_NAME']] = serialize($file_update);
                           }


                       } else {
                            //если не multiple

                            //расширение файла
                            $type_file = '.'. strtolower(pathinfo($_FILES[$name_count_rows['COLUMN_NAME']]['name'], PATHINFO_EXTENSION));

                            //генерация имени файла $dir_path[2] - префикс
                            $name_file =  $dir_path[2].uniqid().$type_file;
                            //получаем масив путей абсолютный и относительный
                            $file_path = $this->uploads_dir_absolut($dir_path[1]);

                            $uploaded = Upload::save($_FILES[$name_count_rows['COLUMN_NAME']], $name_file, $file_path['absolut']);

                            if ($uploaded)
                            {
                                //относительный путь к файлу запись в базу
                                $update[$name_count_rows['COLUMN_NAME']] = $file_path['relative'].$name_file;
                                // set file type

                            }
                       }


                    }

                }

            }


            //если хук определен
            if ($retw->callback_before_edit !== null){

                $retw->callback_before_edit($retw->callback_before_edit['name_function']);
                //получаем масив строку таблицы которая должна быть редактирована
                $query_array_edit = Model::factory('All')->select_all_where($retw->table,$this->id);
                //получаем данные из других таблиц для хука
                if ($retw->set_one_to_many) {
                    $query_array_edit = Model::factory('All')->get_other_table($retw->set_one_to_many, $query_array_edit[0], $this->id, false);
                }

                $callbackStatic = call_user_func_array(array($re['callback_functions_array']['class'],
                    $retw->callback_before_edit['name_function']), array($update, $query_array_edit));

                //если в хуке returm false
                if ($callbackStatic !== false) {

                    if ($callbackStatic != '') {
                        $update = $callbackStatic;
                    }

                    //если обявлен метод 1-n
                    if ($retw->set_one_to_many) {
                        //делаем копию массива
                        $other_update = $update;

                        $update = $this->clear_field_insert($retw->set_one_to_many, $update);

                        Model::factory('All')->update_other_table($retw->set_one_to_many, $other_update, $_POST[$key_primary]);

                    }

                    $query = Model::factory('All')->update($retw->table, $update,  $_POST[$key_primary]);
                }
            } else {

                //если обявлен метод 1-n
                if ($retw->set_one_to_many) {
                    //делаем копию массива
                    $other_update = $update;

                    $update = $this->clear_field_insert($retw->set_one_to_many, $update);

                    Model::factory('All')->update_other_table($retw->set_one_to_many, $other_update, $_POST[$key_primary]);

                }

                $query = Model::factory('All')->update($retw->table, $update,  $_POST[$key_primary]);
            }


            Request::initial()->redirect(Kohana::$config->load('crudconfig.base_url'));
        }

        //вид edit
        $viev_edit = View::factory('page/edit');

        //валидация полей
        if ($retw->validation != null) {
            $retw->validation_views();
            $viev_edit->script_validate = $retw->validation;
        }

        $fields = Model::factory('All')->select_all_where($retw->table,$this->id);
        $fields = $fields[0];

        //если обявлен метод один ко многим то данные для поля берем с другой таблицы
        if ($retw->set_one_to_many) {
            $fields = Model::factory('All')->get_other_table($retw->set_one_to_many, $fields, $this->id);
        }


        //какие будут отображатся при редактировании
        if ($retw->edit_fields != null) {
            //вычисляяем пересечение масивов по ключам
            $field =  array_intersect_key($fields, array_flip($retw->edit_fields));
            $field[$key_primary] = $fields[$key_primary];
        } else {
            $field = $fields;
        }

        //типы полей на основе типов mysql
        $information_shem = Model::factory('All')->information_table($retw->table);
        $type_field = $retw->shows_type_input_default($information_shem);

        //полечаем значения для переопределения типов полей
        if (!empty($retw->set_field_type)) {

            //переопределяем масив $retw->set_field_type если передан параметр  $relation_one
            $retw->reload_field_type($retw->set_field_type);

            $new_type_field = $retw->set_field_type;
        } else {
            $new_type_field = null;
        }

        //масив параметров для поля file
        if (!empty($retw->type_field_upload)) {
            $type_field_upload = $retw->type_field_upload;
        } else {
            $type_field_upload = null;
        }

        //отключение редактора
        if (!empty($retw->disable_editor)) {
            $disable_editor = $retw->disable_editor;
        } else {
            $disable_editor = null;
        }

        //добавляет к полю select атрибут multiple
        if (!empty($retw->select_multiselect)) {
            $select_multiselect = $retw->select_multiselect;
        } else {
            $select_multiselect = null;
        }


        $viev_edit->edit_property = array('field' => $field,
                                            'select_muliselect' => $select_multiselect,
                                            'disable_editor' => $disable_editor, //отключение редактора
                                            'new_type_field' => $new_type_field, //типы полей для переопределения дефолтных
                                            'type_field_upload' => $type_field_upload, //масив параметров для поля file
                                            'type_field' => $type_field, //типы полей по дефолту
                                            'key_primary' => $key_primary, //id первичный ключ
                                            'obj' => $get['obj'],
                                            'name_colums_table_show' => $retw->new_name_column); //передаем названия полей новые

        $this->template->render = $viev_edit;


        $crud_style = $retw->static_style();

        $this->template->scripts = $crud_style['scripts'];
        $this->template->styles = $crud_style['styles'];

    }













    //для новых экшенов
    public function action_newAction () {

        $re = unserialize(base64_decode($_POST['obj']));

        $retw = call_user_func(array($re['callback_functions_array']['class'],
            $re['callback_functions_array']['function']));

        $this->id = Arr::get($_POST, 'id');
        $retw->render = true;

        //die(print_r($retw->add_action));

        if ($retw->add_action != null) {

            $query_array_del = Model::factory('All')->select_all_where($retw->table,$this->id);

            if ($retw->set_one_to_many) {
                $query_array_del = Model::factory('All')->get_other_table($retw->set_one_to_many, $query_array_del[0], $this->id, false);
            }

            call_user_func(array($re['callback_functions_array']['class'],
                Arr::get($_POST, 'func')), $query_array_del);
        }


        Request::initial()->redirect(Kohana::$config->load('crudconfig.base_url'));

    }




    public function action_show_views () {

        $re = unserialize(base64_decode($_GET['obj']));

        $retw = call_user_func(array($re['callback_functions_array']['class'],
        $re['callback_functions_array']['function']));

            //установка язика
        I18n::lang($retw->set_lang);

        $key_primary = Model::factory('All')->information_table($retw->table, true);
        $key_primary = $key_primary[0]->COLUMN_NAME;

        $this->id = Arr::get($_GET, 'id');

        //вид edit
        $show_views = View::factory('page/show_views');


        $fields = Model::factory('All')->select_all_where($retw->table,$this->id);
        $fields = $fields[0];

        //если обявлен метод один ко многим то данные для поля берем с другой таблицы
        if ($retw->set_one_to_many) {
            $fields = Model::factory('All')->get_other_table($retw->set_one_to_many, $fields, $this->id);
        }


        //какие будут отображатся при редактировании
        if ($retw->edit_fields != null) {
            //вычисляяем пересечение масивов по ключам
            $field =  array_intersect_key($fields, array_flip($retw->edit_fields));
            $field[$key_primary] = $fields[$key_primary];
        } else {
            $field = $fields;
        }

        $show_views->show_views_property = array(
            'field' => $field,
            'key_primary' => $key_primary, //id первичный ключ
            'obj' => $_GET['obj'],
            'name_colums_table_show' => $retw->new_name_column); //передаем названия полей новые

        $this->template->render = $show_views;


        $crud_style = $retw->static_style();

        $this->template->scripts = $crud_style['scripts'];
        $this->template->styles = $crud_style['styles'];

    }





    //новая запись
    public function action_add () {


        if (isset($_POST['add'])) {
            //die(print_r($_FILES));
            $re = unserialize(base64_decode($_POST['obj']));
        } else {
            $re = unserialize(base64_decode($_GET['obj']));
        }

        $retw = call_user_func(array($re['callback_functions_array']['class'],
            $re['callback_functions_array']['function']));

        //установка язика
        I18n::lang($retw->set_lang);

        //флаг для запуска колбеков только при срабатывании екшена
        $retw->render = true;

        $name_count = Model::factory('All')->name_count($retw->table);


        if (isset($_POST['add'])) {
            //die(print_r($_GET));
            foreach ($name_count as $name_count_rows) {

                //если это масив то сериализуем для multiple полей
                if (isset($_POST[$name_count_rows['COLUMN_NAME']])) {

                    if (is_array($_POST[$name_count_rows['COLUMN_NAME']])) {
                        //подготовка масиивов для передачи в модель
                        $name_count_insert[] = $name_count_rows['COLUMN_NAME'];
                        //удаляем пустые элементы масива
                        $befor_serialise = array_filter($_POST[$name_count_rows['COLUMN_NAME']]);

                        //если обявлен метод записи в таблицу а не сериализация
                        if ($retw->set_one_to_many) {

                            $insert[$name_count_rows['COLUMN_NAME']] = $befor_serialise;

                        } else {
                            $insert[$name_count_rows['COLUMN_NAME']] = serialize($befor_serialise);
                        }


                    } else {
                        $name_count_insert[] = $name_count_rows['COLUMN_NAME'];
                        $insert[$name_count_rows['COLUMN_NAME']] = $_POST[$name_count_rows['COLUMN_NAME']];
                    }
                //если поля нету то проверяем масив $_FILES
                } else {

                    //если поле определено как file
                    if (!empty($retw->type_field_upload[$name_count_rows['COLUMN_NAME']])) {
                        //die(print_r($name_count_rows['COLUMN_NAME']));
                        //получаем масив с типом поля и путь к дирикктории хранения файла
                        $dir_path = $retw->type_field_upload[$name_count_rows['COLUMN_NAME']];

                        //добавляем название поля если он есть в масиве $_FILES
                        $name_count_insert[] = $name_count_rows['COLUMN_NAME'];

                        //проверяем является ли масивом если multiple
                        if (is_array($_FILES[$name_count_rows['COLUMN_NAME']]['name'])) {


                            foreach ($_FILES[$name_count_rows['COLUMN_NAME']]['name'] as $key => $rows_name) {
                                //проверка на наличие отправляемого файла

                                if ($rows_name != '') {
                                    //расширение файла
                                    $type_file = '.'. strtolower(pathinfo($rows_name, PATHINFO_EXTENSION));
                                    //генерируем имя файла с префиксом
                                    $name_file =  $dir_path[2].uniqid().$type_file;
                                    //получаем абсолютный путь к директории храния файла
                                    $file_path = $this->uploads_dir_absolut($dir_path[1]);
                                    //сохранение файла
                                    $uploaded = Upload::save(array('tmp_name' => $_FILES[$name_count_rows['COLUMN_NAME']]['tmp_name'][$key]), $name_file, $file_path['absolut']);
                                    //создание масива относительных путей
                                    $file_update[] = $file_path['relative'].$name_file;
                                }
                            }

                            //die(print_r($file_update));
                            if ($retw->set_one_to_many) {
                                $insert[$name_count_rows['COLUMN_NAME']] = $file_update;
                            } else {
                                $insert[$name_count_rows['COLUMN_NAME']] = serialize($file_update);
                            }

                        } else {
                            //если не multiple
                            if ($_FILES[$name_count_rows['COLUMN_NAME']]['name'] != '') {

                                //расширение файла
                                $type_file = '.'. strtolower(pathinfo($_FILES[$name_count_rows['COLUMN_NAME']]['name'], PATHINFO_EXTENSION));

                                //генерация имени файла $dir_path[2] - префикс
                                $name_file =  $dir_path[2].uniqid().$type_file;
                                //получаем масив путей абсолютный и относительный
                                $file_path = $this->uploads_dir_absolut($dir_path[1]);

                                $uploaded = Upload::save($_FILES[$name_count_rows['COLUMN_NAME']], $name_file, $file_path['absolut']);

                                if ($uploaded)
                                {
                                    //относительный путь к файлу запись в базу
                                    $insert[$name_count_rows['COLUMN_NAME']] = $file_path['relative'].$name_file;
                                    // set file type

                                }

                            } else { //если файл не был передан
                                $insert[$name_count_rows['COLUMN_NAME']] = '';
                            }

                        }

                    }

                }

            }

            if ($retw->callback_before_insert != null) {
                //переиницыалзация хука
                $retw->callback_before_insert($retw->callback_before_insert['name_function']);

                $insert_befor = $insert;
                $insert = call_user_func(array($re['callback_functions_array']['class'],
                    $retw->callback_before_insert['name_function']), $insert);

            }

            if ($insert !== false) {
                //если хук ничего не возвращает пишем введенные в форму данные исходные
                if ($insert == ''){
                    $insert = $insert_befor;
                }

                //проверяем обявлен ли метод отношение один ко многим
                if ($retw->set_one_to_many) {
                    //удалем значение поля которое должно быть записано в другую таблицу в основной таблиице
                    //это поле останется пустым
                    $other_insert = $insert;
                    $insert = $this->clear_field_insert($retw->set_one_to_many, $insert);

                }

                //удаляем ключи из масивов (названия полей)
                $name_count_insert = array_values($name_count_insert);
                //die(print_r($name_count_insert));
                $insert_value = array_values($insert);

                $result = Model::factory('All')->insert($retw->table, $name_count_insert, $insert_value);

                //делаем запись в указаную таблицу
                if ($retw->set_one_to_many) {
                    Model::factory('all')->set_other_table($retw->set_one_to_many, $other_insert, $result);
                }

            }

            if ($retw->callback_after_insert != null) {
                //переиницыализация
                $retw->callback_after_insert($retw->callback_after_insert['name_function'], 'true');

                $query_array_del = Model::factory('All')->select_all_where($retw->table, $result);
                call_user_func(array($re['callback_functions_array']['class'],
                    $retw->callback_after_insert['name_function']), $query_array_del[0]);

            }


            Request::initial()->redirect(Kohana::$config->load('crudconfig.base_url'));
        }


        //получаем первичный ключ
        $key_primary = Model::factory('All')->information_table($retw->table, true);
        $key_primary = $key_primary[0]->COLUMN_NAME;

        //создаем масив полей для вывода в форме добавления
        foreach ($name_count as $name_count_rows) {
            //не пишем поле id в масив
            if ($name_count_rows['COLUMN_NAME'] != $key_primary) {
                $fields[] = $name_count_rows['COLUMN_NAME'];
            }

        }

        //если определены поля которые должны отображатся при добавлении
        if ($retw->add_field != null) {
            //вычисляем схождение массивов
           $fields =  array_intersect($retw->add_field, $fields);
        }


        //типы полей на основе типов mysql
        $information_shem = Model::factory('All')->information_table($retw->table);
        $type_field = $retw->shows_type_input_default($information_shem);

        //полечаем значения для переопределения типов полей
        if (!empty($retw->set_field_type)) {

            //переопределяем масив $retw->set_field_type если передан параметр  $relation_one
            $retw->reload_field_type($retw->set_field_type);
            $new_type_field = $retw->set_field_type;


        } else {
            $new_type_field = null;
        }


        //отключение редактора
        if (!empty($retw->disable_editor)) {
            $disable_editor = $retw->disable_editor;
        } else {
            $disable_editor = null;
        }

        //добавляет к полю select атрибут multiple
        if (!empty($retw->select_multiselect)) {
            $select_multiselect = $retw->select_multiselect;
        } else {
            $select_multiselect = null;
        }

        $viev_add = View::factory('page/add');

        //валидация полей
        if ($retw->validation != null) {
            $retw->validation_views();
            $viev_add->script_validate = $retw->validation;
        }

        $viev_add->add_property = array('field' => $fields,
            'obj' => $_GET['obj'],
            'disable_editor' => $disable_editor, //отключение редактора
            'select_muliselect' => $select_multiselect,
            'new_type_field' => $new_type_field, //типы полей для переопределения дефолтных
            'type_field' => $type_field, //типы полей по дефолту
            'name_colums_table_show' => $retw->new_name_column);

        $this->template->render = $viev_add;

        $crud_style = $retw->static_style();

        $this->template->scripts = $crud_style['scripts'];
        $this->template->styles = $crud_style['styles'];
    }


    //очистка поля перед записью в основную таблицу
    private function clear_field_insert ($arr_field_new_table, $arr_insert_val) {
        foreach ($arr_field_new_table as $rows) {
            if ($arr_insert_val[$rows['field_old']]) {
                $arr_insert_val[$rows['field_old']] = '';
            }
        }

        return $arr_insert_val;
    }

}