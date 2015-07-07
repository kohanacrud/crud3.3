<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by PhpStorm.
 * User: Vitalik
 * Date: 08.05.14
 * Time: 14:01
 */

class Cruds extends Controller_Core_Main {

    public $table; //название таблицы
    public $name_colums_table; //названия полей таблицы
    public $new_name_column; //переименованые названия полей
    public $render = null; //рендер
    public $key_primary; //хранит первичный ключ таблицы
    public  $set_lang = 'en'; //язык
    public $name_function = null;
    //хранит массив вызова обьекта Cruds
    public  $class_metod = null;
    public $column_array = null; //определение вызова метода определения полей таблицы
    public $remove_delete = null; //уброать кнопку удалить
    public $remove_edit = null; //уброать кнопку редактировать
    public $remove_add = null; //уброать кнопку добавит
    private $show_views = false; //кнопка просмотра записи
    private $icon_edit = false; //переопредилить иконку кнопки редактирования
    private $icon_delete = false; //переопредилить иконку кнопки удалить
    public $disable_editor = array(); //отключение редактора
    private $disable_search = 'f'; //отключение поиска по таблице
    private $enable_delete_group; //включить груповое удаление
    private $enable_export; //включить експорт
    private $set_where = null;
    public $set_field_type = array(); //типы полей
    public $type_field_upload = array(); //тип поля file
    private $object_serial; //масив параметров для сериализации
    public $add_field = null; //поля которые будут видны при добавлении
    public $edit_fields = null; //поля которые будут видны при редактировании
    public $add_action = null; //добавить екшен
    public $name_colums_table_show; //названия полей
    public $name_colums_ajax; //название полей для аякса
    public $relation_one; //получить содержимое другой таблицы
    private $tmp_name_column_file; //временно для rows

    public $toptip_fields = null; //подсказки для полей

    //хуки
    public $callback_before_delete = null; //перед удалением
    public $callback_after_delete = null; //после удаления
    public $callback_before_edit = null; //перед обновлением
    public $callback_after_edit = null; //после обновления
    public $callback_before_insert = null; //перед добавлением
    public $callback_after_insert = null; //после добавления

    public $callback_befor_show_edit = null; //перед открытием редактирования
    public $callback_befor_show_add = null; //перед открытием добавления

    public $set_one_to_many = null; //один ко многим
    public $set_many_to_many = null; //многие ко многим
    public $select_multiselect = null; //изменяет поле select
    public $validation = null; //принимает масив значений для валидации
    public $validation_messages = null; //принимает масив строк которые отображаются в случае ошибки
    public $curent_uri = null;
    private $rules = array();
    private $messages = array();
    public $join_table = null; //масив таблиц для обьединения
    public $table_join_key = null; //масив таблица поле pri table@pagespri
    public static $id = null; //хранит id записи

    public $add_script_edit = null; //хранит масив путей к файлам скриптов
    public $add_script_add = null;
    public $add_style_edit = null;
    public $add_style_add = null;

    public function __construct () {
        parent::before();
    }

    public function  load_table ($table) {
        $this->table = $table;

        //установка языка
        //определяем точку вызова
        $debug = debug_backtrace();

        $this->class_metod = array('function' => $debug[1]['function'],
            'class' => $debug[1]['class'],
            'callback_function_name' => __FUNCTION__
        );
        //для редиректа
        $this->curent_uri = parse_url(Request::$initial->referrer());
        $this->curent_uri = $this->curent_uri['path'];
    }

    //отображаемые столбцы
    public function show_columns () {
        $this->column_array = func_get_args();
    }

    //метот блокирования кнопки удалить
    public function remove_delete () {
        $this->remove_delete = true;
    }

    //метот блокирования кнопки редактировать
    public function remove_edit () {
        $this->remove_edit = true;
    }

    //вывод кнопки просмотра записи
    public function show_views ($icon_class = null) {
        if ($icon_class == null) {
            $this->show_views = true;
        } else {
            $this->show_views = $icon_class;
        }
    }

    //метод переопределяет иконку кноки редактирования название пропадает принимает имя класса
    public function icon_edit ($icon_class) {
        $this->icon_edit = $icon_class;
    }

    //метод переопределяет иконку кноки удаления название пропадает принимает имя класса
    public function icon_delete ($icon_class) {
        $this->icon_delete = $icon_class;
    }

    public function set_where ($colum, $operation, $value) {
        $this->set_where = array('colum' => $colum,
            'operation' =>  $operation,
            'value' => $value);
    }

    public function edit_render ($id) {

        $this->object_serial = array('table' => $this->table,
            'callback_functions_array' => $this->class_metod
        );

        $obj = base64_encode(serialize($this->object_serial));

        $this->curent_uri = $_SERVER['REQUEST_URI'];
        return Request::factory('core_crud/edit?obj='.$obj.'&id='.$id.'&edit_renderer=1')->execute()->body();
    }

    //метод рендера круда
    public function render () {
        //вид круда
        $about = View::factory('/page/page');

        //передача  в вид содержимого таблицы метода select_table
        $about->table_propery = $this->select_table();
        $this->render = true;
        //возвращает  отрендереный вид
        //установка язика
        I18n::lang($this->set_lang);
        //загрузка статики
        $this->static_style();
        $this->template->render = $about;
        return $this->template;
    }

    //валидация полей
    public function validation ($field_name, $property_field_arr, $property_messages_arr) {
        $this->rules[$field_name] = $property_field_arr;
        $this->messages[$field_name] = $property_messages_arr;
        $this->validation_messages = json_encode($this->messages);
        $this->validation = json_encode($this->rules);
    }

    public function validation_views () {
        $this->validation = View::factory('controls/scriptValidateJs', array('json_rules' => $this->validation, 'json_messages' => $this->validation_messages));
    }

    public function static_style () {

        $media = Route::get('docs/media');

        $styles = array(
            $media->uri(array('file' => 'js/DataTables-1.10.0/media/css/jquery.dataTables.css')) => 'screen',
            $media->uri(array('file' => 'css/bootstrap-theme.min.css')) => 'screen',
            $media->uri(array('file' => 'css/bootstrap.min.css')) => 'screen',
            $media->uri(array('file' => 'css/style.css')) => 'screen',
            $media->uri(array('file' => 'js/DataTables-1.10.0/extensions/TableTools/css/dataTables.tableTools.min.css'))=> 'screen',
            $media->uri(array('file' => 'js/lightbox/css/lightbox.css')) => 'screen',
            $media->uri(array('file' => 'css/chosen.min.css')) => 'screen',
            $media->uri(array('file' => 'css/bootstrap-datetimepicker.min.css')) => 'screen'
            //$media->uri(array('file' => 'js/DataTables-1.10.0/extensions/FixedHeader/css/dataTables.fixedHeader.min.css'))=> 'screen'
        );

        $this->template->styles = $styles;

        // Add scripts
        $scripts = array(
            $media->uri(array('file' => 'js/DataTables-1.10.0/media/js/jquery.js')),
            $media->uri(array('file' => 'js/DataTables-1.10.0/media/js/jquery.dataTables.js')),
            $media->uri(array('file' => 'js/tinymce/jquery.tinymce.min.js')),
            $media->uri(array('file' => 'js/tinymce/tinymce.min.js')),
            $media->uri(array('file' => 'js/bootstrap.min.js')),
            $media->uri(array('file' => 'js/app.js')),
            $media->uri(array('file' => 'js/DataTables-1.10.0/extensions/TableTools/js/dataTables.tableTools.min.js')),
            $media->uri(array('file' => 'js/lightbox/js/lightbox.min.js')),
            $media->uri(array('file' => 'js/chosen.jquery.min.js')),
            $media->uri(array('file' => 'js/bootstrap-datetimepicker.min.js')),
            $media->uri(array('file' => 'js/jquery.validate.min.js')),
            //$media->uri(array('file' => 'js/DataTables-1.10.0/extensions/FixedHeader/js/dataTables.fixedHeader.min.js')),
            //'/js/DataTables-1.10.0/extensions/TableTools/swf/copy_csv_xls_pdf.swf',
            $media->uri(array('file' => 'css/loader.GIF'))
        );

        $this->template->scripts = $scripts;
        return array('scripts' => $scripts, 'styles' => $styles);
    }


    //метод переименования полей
    public function show_name_column($new_name_column = array()) {
        //если метод вызван
        if ($new_name_column != '') {
            $this->new_name_column = $new_name_column;
        }

        if ($this->name_colums_table != '') {
            foreach ($this->name_colums_table as $key => $row) {

                if (isset($this->new_name_column[$row['COLUMN_NAME']])) {
                    $tmp[$key] = array('COLUMN_NAME' => $this->new_name_column[$row['COLUMN_NAME']]);
                } else {
                    $tmp[$key] = array('COLUMN_NAME' => $row['COLUMN_NAME']);
                }
            }
            return $tmp;
        }
    }



    //метод запроса на выборку данных таблицы
    public function select_table () {
        //возвращает названия полей таблицы
        $this->name_colums_table = Model::factory('All')->name_count($this->table, $this->join_table);
        //die(print_r($this->name_colums_table));
        //определяем какие поля будут выводится
        if ($this->column_array != null) {
            // print_r($this->name_colums_table);
            foreach ($this->name_colums_table as $colums_table){
                foreach ($this->column_array as $colum){
                    if ($colums_table['COLUMN_NAME'] == $colum) {
                        $new_colums[] = array('COLUMN_NAME' => $colum);
                    }
                }
            }
            //переопределяем обьект
            $this->name_colums_table = $new_colums;
        }
        //название полей для передачи в модель аякс
        $this->name_colums_ajax = $this->name_colums_table;
        //назначение имен полям
        if ($this->new_name_column != '') {
            //если вызов состоялся то метод переиницыализируется
            $this->name_colums_table_show = $this->show_name_column($this->new_name_column);
        } else {
            $this->name_colums_table_show = $this->name_colums_table;
        }

        //масив параметров для сериализации
        $this->object_serial = array('table' => $this->table,
            'callback_functions_array' => $this->class_metod
        );

        //имя поля первичного ключа
        $key_primary = Model::factory('All')->information_table($this->table, true);
        $this->key_primary = $key_primary[0]->COLUMN_NAME;

        return array(
            'key_primary' => $this->key_primary,
            //'add_insert' => 'asd',
            'add_action_url_icon' => $this->add_action, //добавление екшенов
            'activ_operation' => array(
                'delete' => $this->remove_delete,
                'edit' => $this->remove_edit,
                'add' => $this->remove_add,
                'search' => $this->disable_search,
                'enable_delete_group' => $this->enable_delete_group,
                'enable_export' => $this->enable_export), //передача состояния кнопок удаления редактирования добавления
            'name_colums_table' => $this->name_colums_table,
            'name_colums_table_show' => $this->name_colums_table_show, //названия полей таблицы
            'obj_serial' => base64_encode(serialize($this->object_serial)) //передача сериализованого обьекта
        );
    }







    //обработка аякс запроса пагинация сортировка поиск возвращает JSON
    public function ajax_query ($get) {


        $count = Model::factory('All')->count_table($this->table, $this->set_where);



        //если колонка с чекбоксами то добавляем в первый елемент масива первый столбик дублируем 0 и 1 одинаковы
        if ($this->enable_delete_group) {
            $column[0] = $this->name_colums_ajax[0]['COLUMN_NAME'];
        }

        //колонки таблицы для отображения
        foreach ($this->name_colums_ajax as $key => $rows_column) {
            //если добавляется колонка групового удаления делаем перещет номером колонок
            $column[] = $rows_column['COLUMN_NAME'];
        }


        //поле для сортировки
        $order_column = $column[$get['order'][0]['column']];

        //die(print_r($this->name_colums_ajax));
        //принимаем тип сортировки asc или DESC
        $order_by = $get['order'][0]['dir'];
        //строка поиска
        $search_like = $get['search']['value'];
        $obj = base64_encode(serialize($this->object_serial));
        //подготовка из масива в строку полей для передачи в модель
        $query = Model::factory('All')->paginationAjax(
            $get['length'], //сколько записей нужно выбрать
            $get['start'], //с какой записи начать выборку
            $this->table, //имя таблицы
            $order_column, //название поля по которому будет идти сортировка
            $order_by, //тип сортировки ASK DESK
            $search_like, //строка поиска
            $column, //поля таблицы
            $this->set_where, //метод условие выборки set_where ()
            $this->join_table);

        //иницыализация языкового класса
        I18n::lang($this->set_lang);
        //меняем названия поля и номера по порядку местами
        $array_flip_column = array_flip($column);

        //die(print_r($query['query']));

        //абсолютный путь к корню удаляется последний символ слеш
        $path_absolute = substr(DOCROOT, 0, strlen(DOCROOT)-1);

        foreach ($query['query'] as $rows) {
            //редактировать
            if ($this->remove_edit !== true) {

                $icon_edit = null;
                if ($this->icon_edit !== false) {
                    $icon_edit = $this->icon_edit;
                }

                $data = array(
                    'obj' => $obj,
                    'id' => $rows[$this->key_primary],
                    'icon_edit' => $icon_edit
                );
                //добавляем форму
                $htm_edit = View::factory('action_page/actionEdit', $data);
            } else {
                $htm_edit = '';
            }

            //кнопка просмотра
            if ($this->show_views !== false) {
                $icon_vievs = null;
                if ($this->show_views !== true) {
                    $icon_vievs = $this->show_views;
                }

                $data = array(
                    'obj' => $obj,
                    'id' => $rows[$this->key_primary],
                    'icon_class' => $icon_vievs
                );
                //добавляем форму
                $htm_show_views = View::factory('action_page/actionShowViews', $data);
            } else {
                $htm_show_views = '';
            }

            //новые екшены
            $htm_action = '';
            if ($this->add_action != '') {
                foreach ($this->add_action as $rows_action) {

                    $data = array(
                        'url' => $rows_action['url'],
                        'obj' => $obj,
                        'id' => $rows[$this->key_primary],
                        'name_function' => $rows_action['name_function'],
                        'icon' => $rows_action['icon'],
                        'name_action' => $rows_action['name_action']
                    );

                    $htm_action .= View::factory('action_page/actionNewAction', $data);
                }
            } else {
                $htm_action = '';
            }

            //удалить
            if ($this->remove_delete !== true) {

                $icon_delete = null;
                if ($this->icon_delete !== false) {
                    $icon_delete = $this->icon_delete;
                }

                $data = array(
                    'obj' => $obj,
                    'id' => $rows[$this->key_primary],
                    'icon_delete' => $icon_delete
                );
                //добавляем форму
                $htm_delete = View::factory('action_page/actionDel', $data);

            } else {
                $htm_delete = '';
            }

            $this->tmp_name_column_file = $rows; //сохраняем предидущее состояниемасива для получения не урезаных данных
            //удаляем все теги перед выводом в таблицу
            $rows = array_map(array($this, 'no_tag'), $rows);
            //Вычислить пересечение массивов, сравнивая ключи
            $array_intersect_key_rows = array_intersect_key($rows, $array_flip_column);

            if (!empty($this->type_field_upload)) {
                //die(print_r($this->tmp_name_column_file));
                foreach ($this->type_field_upload as $colum_name => $row_array) {
                    $exemple_id = uniqid();
                    //если есть параметр выводим в таблицу

                    if ($row_array[3] == 'views' and $row_array[4] == 'img') {

                           // die(print_r($array_intersect_key_rows[$colum_name]));
                            try {

                                $new_rows = '';
                                foreach (unserialize($this->tmp_name_column_file[$colum_name])  as $key => $url_relativ) {
                                    // не отображаем картинки на главной кроме первой
                                    if ($key != 0) {
                                        $display = 'style="display: none"' ;
                                    } else {
                                        $display = '';
                                    }
                                    $new_rows .= '<a class="example-image-link" href="'.$url_relativ.'" data-lightbox="example-'.$exemple_id.'" data-title="Optional caption."'.$display.'><img class="example-image" src="'.$url_relativ.'" width="100" /></a>';
                                }

                                $array_intersect_key_rows[$colum_name] = $new_rows;

                            } catch (Exception $e) {

                                //если файл существует
                                if (file_exists($path_absolute.$this->tmp_name_column_file[$colum_name]) AND ($this->tmp_name_column_file[$colum_name] != '')) {
                                    //переопределяем уже с тегом
                                    $array_intersect_key_rows[$colum_name] = '<a class="example-image-link" href="'.$this->tmp_name_column_file[$colum_name].'" data-lightbox="example-'.$exemple_id.'" data-title="Optional caption."><img class="example-image" src="'.$this->tmp_name_column_file[$colum_name].'" width="100" /></a>';
                                } else {
                                    $array_intersect_key_rows[$colum_name] = 'Файла нету';
                                }
                            }

                    } else {
                        // тут выброс екзекшена если вписана лабуда
                    }
                }
            }

            $tmp_array = array_values($array_intersect_key_rows);
            //кнопки удалить редактировать и новых екшенов
            if ($this->enable_delete_group) {
                //добавляем в начало масива чекбоксы
                array_unshift($tmp_array, '<input type="checkbox" class="w-chec-table" name="id_del_array[]" value="'.$rows[$this->key_primary].'">');
            }

            $tmp_array[] = $htm_show_views.$htm_edit.$htm_action.$htm_delete;
            $dataQuery[] = $tmp_array;
        }

        //количество записей после поиска
        if ($search_like != '')  {
            $record_count = $query['count'];
        } else {
            $record_count = $count[0]['COUNT(*)'];
        }

        //если в базе ничего не найдено то присваиваем пустое значение
        if (empty($query['query'])) {
            $record_count = 0;
            $dataQuery = '';
        }

        $re = array('draw' => $get['draw'],
            'recordsTotal' => $count[0]['COUNT(*)'], //всего записей в таблице
            'recordsFiltered' => $record_count, //оставшиееся количество после поиска
            'data' => $dataQuery); //данные для отображения в таблице
        echo json_encode($re);
    }

    private function no_tag ($n) {
        $str = strip_tags($n);
        return Text::limit_chars($str, 100);
    }

//    callback

    //перед удалением
    public function callback_before_delete ($name_function) {
        //проверяем запускается ли из екшенов и определяем метод
        $this->callback_before_delete = array('name_function' => $name_function);
    }

    //после удаления
    public function callback_after_delete ($name_function) {
        $this->callback_after_delete = array('name_function' => $name_function);
    }

    //перед обновлением
    public function callback_before_edit ($name_function) {
            $this->callback_before_edit = array('name_function' => $name_function);
    }

    public function callback_after_edit ($name_function) {
        $this->callback_after_edit = array('name_function' => $name_function);
    }


    //добавить екшен
    public function add_action ($name_function, $name_action, $url, $icon = null) {

        if ($this->render === true) {
            call_user_func(array($this->class_metod['class'],
                $name_function));
        }

        $this->add_action[] = array('name_function' => $name_function,
            'name_action' => $name_action,
            'url' => $url,
            'icon' => $icon);
    }

    public function set_lang ($lang) {
        $this->set_lang = $lang;
    }

    //типы полей по умолчанию
    public function shows_type_input_default ($information_shem) {

        $retuyr = array(
            'varchar' => 'text',
            'text' => array('tag' => 'textarea'),
            'date' => 'date',
            'int' => 'number',
            'bigint' => 'number',
            'tinyint' => 'number',
            'smallint' => 'number',
            'mediumint' => 'number',
            'float' => 'number',
            'double' => 'number',
            'bool'=> 'checkbox',
            'boolean' => 'checkbox',
            'bit' => 'checkbox',
            'char' =>  array('tag' => 'textarea'),
            'tinytext' => 'text',
            'mediumtext' => array('tag' => 'textarea'),
            'longtext' => array('tag' => 'textarea'),
            'tinyblob' => 'text',
            'blob' => 'text',
            'mediumblob' => array('tag' => 'textarea'),
            'longblob' => array('tag' => 'textarea'),
            'datetime' => 'datetime',
            'time' => 'time',
            'year' => 'month',
            'timestamp' => 'datetime');

        foreach($information_shem as $row) {

            $flag = false;
            //проверяем нет ли связаных таблиц
            if ($this->join_table != null) {
                foreach ($this->join_table as $row_join) {

                    if ($row['TABLE_NAME'] == $row_join[1]) {
                        $row['COLUMN_NAME'] = $row_join[1].'@'.$row['COLUMN_NAME'];
                        $flag = true;
                    }
                }
            }
            //все кроме первичного ключа
            if ($row['COLUMN_KEY'] != 'PRI' or $row['COLUMN_KEY'] == 'MUL' or $flag === true) {
                if (isset($retuyr[$row['DATA_TYPE']])) {
                    $new[$row['COLUMN_NAME']] = $retuyr[$row['DATA_TYPE']];
                } else {
                    $new[$row['COLUMN_NAME']] = 'text';
                }
            }
        }

        return $new;
    }

    //хук добавить
    public function callback_before_insert ($name_function) {
            $this->callback_before_insert = array('name_function' => $name_function);
    }


    public function callback_after_insert ($name_function) {
        $this->callback_after_insert = array('name_function' => $name_function);
    }


    public function callback_befor_show_edit ($name_function){
        $this->callback_befor_show_edit = array('name_function' => $name_function);
    }

    public function callback_befor_show_add ($name_function){
        $this->callback_befor_show_add = array('name_function' => $name_function);
    }

    //отображение полей при добавлении
    public function add_field () {
        $this->add_field = func_get_args();
    }

    //отображение полей при редактировании
    public function edit_fields () {
        $this->edit_fields = func_get_args();
    }

    //удаляет кнопку добавить
    public function remove_add () {
        $this->remove_add = true;
    }

    //типы полей
    public function set_field_type ($field_name, $type_field, $field_value = null, $multiple = null, $attr = null, $relation_one = null) {
        //все вызовы в один масив аргументов
        //если тип поля file то принимаем масив значений тип, путь
        if  (is_array($type_field)) {
            $this->type_field_upload[$field_name] = $type_field;
            $type_field = $type_field[0];
        }

        //проверяем передал ли масив
        if (!is_array($relation_one)) {
            $relation_one = null;
        }

        if ($attr != null) {
            $attr_str = '';
            foreach ($attr as $atribut => $value) {
                $attr_str .= ' '.$atribut.'="'.$value.'" ';
            }
        } else {
            $attr_str = null;
        }

        $this->set_field_type[$field_name] = array(
            'type_field' => $type_field,
            'field_value' => $field_value,
            'multiple' => $multiple,
            'attr' => $attr_str,
            'template_relation' => $relation_one
        );
    }

    //переоприделение масива метода set_field_type если передан параметр $relation_one
    public function reload_field_type ($arr_set_field_type) {

        foreach ($arr_set_field_type as $name_field => $field_rows) {

            if ($field_rows['template_relation'] != null) {

                if (isset($field_rows['template_relation'][3])){
                    $where_relation = $field_rows['template_relation'][3];
                } else {
                    $where_relation = null;
                }

                $field_rows['field_value'] = $this->relation_one($field_rows['template_relation'][0],
                                                                $field_rows['template_relation'][1],
                                                                $field_rows['template_relation'][2],
                                                                $where_relation);
            }
            $result[$name_field] = $field_rows;
        }
        $this->set_field_type = $result;
    }

    //проверяет является ли файл картинкой
    public function ist_images ($filename) {

        try {

            $img = getimagesize($filename);
            if ($img) {
                return true;
            }
        } catch (Exception $e) {

            return false;
        }

    }

    //отключение редактора
    public function disable_editor ($field_name) {
        $this->disable_editor[$field_name] = true;
    }

    //отключение поиска по таблице
    public function disable_search () {
        $this->disable_search = null;
    }

    //включить груповое удаление
    public function enable_delete_group () {
        $this->enable_delete_group = true;
    }

    public function enable_export () {
        $this->enable_export = 'T';
    }

    //выборка из таблицы набора значений
    public function relation_one ($Table, $field2, $field_value, $where_field) {
        $this->relation_one = Model::factory('All')->get_table_relativ($Table, $field2, $field_value, $where_field);
        return $this->relation_one;
    }

    //запись таблицы поле из которого берем и поле в которое записываем.
    public function set_one_to_many ($table, $field_old, $field_new, $parent_id) {
        $this->set_one_to_many[] = array('table' => $table,
                                        'field_old' => $field_old,
                                        'field_new' => $field_new,
                                        'parent_id' => $parent_id);
    }

    //изминяет поле select
    public function select_multiselect ($field_name) {
        $this->select_multiselect[$field_name] = 'multiple';
    }

    /**
     * передаем масив таблиц для обьединения
     */
    public function join (){
        $this->join_table[] = func_get_args();
        foreach ($this->join_table as $rows) {
            $this->table_join_key[$rows[1].'@'.$rows[3]] = $rows[1].'@'.$rows[3];
        }
    }

    /**
     * @param $arr
     * @return array
     * формируем масив
     */
    public static function parse_name_column ($arr) {

        $data = array();
        $tmp = '';
        foreach ($arr as $name_count => $rows) {
            $arr = explode("@", $name_count);

            if (isset($arr[1])) {
                $data['join'][$arr[0]][$arr[1]] = $rows;
            } else {
                $data['table'][$arr[0]] = $rows;
            }
        }
        return $data;
    }

    /**
     * @param $arr
     * подсказки для полей
     */
    public function toptip_fields ($arr){
        $this->toptip_fields = $arr;
    }

    public function add_script_edit (){
        $this->add_script_edit = func_get_args();
    }

    public function add_script_add (){
        $this->add_script_add = func_get_args();
    }

    public function add_style_edit (){
        $this->add_style_edit = func_get_args();
    }

    public function add_style_add (){
        $this->add_style_add = func_get_args();
    }

}


?>