<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: Vitalik
 * Date: 11.05.14
 * Time: 23:47
 * To change this template use File | Settings | File Templates.
 */
class Model_All extends Model
{
    protected  $key_primary;
    private $field_names_array; //масив полей таблицы которые были спарсены напр. {username} ( {last_name} {first_name} )

    //выборка по id или без него
    public function select_all_where ($table, $id = null) {

        $key_primary = $this->information_table($table, true);
        $this->key_primary = $key_primary[0]->COLUMN_NAME;

        if ($id != null) {
            return DB::select()->from($table)
            ->where($this->key_primary, '=', $id)
            ->execute()->as_array();

        } else {
            return DB::select()->from($table)
                ->execute()->as_array();
        }
    }


    //удаление по id
    public function delete ($table, $id) {

        $key_primary = $this->information_table($table, true);
        $this->key_primary = $key_primary[0]->COLUMN_NAME;

        return DB::delete($table)
            ->where($this->key_primary, '=', $id)
            ->execute();
    }

    //удаление груповое
    public function group_delete ($table, $idArr) {

        $key_primary = $this->information_table($table, true);
        $this->key_primary = $key_primary[0]->COLUMN_NAME;
        //array('john', 'jane')
        return DB::delete($table)
            ->where($this->key_primary, 'IN', $idArr)
            ->execute();
    }

    //ОБНОВЛЕНЕ
    public function update ($table, $field, $id) {
        $key_primary = $this->information_table($table, true);
        $this->key_primary = $key_primary[0]->COLUMN_NAME;
        DB::update($table)
            ->set($field)
            ->where($this->key_primary, '=', $id)
            ->execute();
    }
    //добавление
    public function insert ($table, $column_table, $value_table) {
        $query = DB::insert($table, $column_table)
            ->values($value_table)
            ->execute();
        return $query[0]; //возвращаем id
    }

    //ПОЛУЧАЕМ названия ПОЛЯ ТАБЛИИЦЫ
    public function name_count ($table) {
        $name_colums_table = DB::query(Database::SELECT,
            'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas');
        $name_colums_table->param(':tab', $table);
        $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));

        return $name_colums_table->execute()->as_array();
    }

    public function information_table ($table, $key_primary = null) {

        if ($key_primary != null) {
            $key_primary = 'PRI';
            $name_colums_table = DB::query(Database::SELECT,
                'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas AND COLUMN_KEY = :key_primary');
            $name_colums_table->param(':tab', $table);
            $name_colums_table->param(':key_primary', $key_primary);
            $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));

            return $name_colums_table->as_object()->execute();
        } else {

            $name_colums_table = DB::query(Database::SELECT,
                'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas');
            $name_colums_table->param(':tab', $table);
            $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));

            return $name_colums_table->execute()->as_array();
        }


    }

    //количество записей
    public function count_table ($table, $set_where = null) {

        if ($set_where != null) {

            $sele_where = 'WHERE '.$set_where['colum'].$set_where['operation'].$set_where['value'];
            $count_table =  DB::query(Database::SELECT,'SELECT COUNT(*) FROM '.$table.' '.$sele_where);

        } else {

            $count_table =  DB::query(Database::SELECT,'SELECT COUNT(*) FROM '.$table);
        }

        return $count_table->execute()->as_array();
    }


    //типы полей таблицы
    private function information_data_type ($table) {



    $data_type = DB::query(Database::SELECT, 'SELECT COLUMN_NAME,DATA_TYPE
                                                  FROM INFORMATION_SCHEMA.COLUMNS
                                                  WHERE table_name = :tab
                                                    AND TABLE_SCHEMA = :bas');
    $data_type->param(':tab', $table);
    $data_type->param(':bas', Kohana::$config->load('crudconfig.database'));

    foreach ($data_type->execute()->as_array() as $row) {
        $result[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
    }

    return $result;
}


    //пагинация
    public function paginationAjax ($limit, $ofset = null, $table, $order_column, $order_by, $like = null, $column_like, $set_where = null) {


       // die('sdfsdf');
        if ($ofset == '' or $ofset == null) {
            $ofset = 0;
        }

        if ($like != '' and $like !== null) {
            $i=0;
            $Sql ='';

           // if ($like == "'") {
                $like = str_replace ("'", "\'", $like);
                //$like = "\'";
           // }

            //получаем поля и тыпы к ним
            $name_type_column = $this->information_data_type($table);


                //die(print_r($name_type_column));

            foreach ($column_like as $key => $column) {
                $i++;
                if ($i >= 1) {
                    $or = ' OR ';
                } else {
                    $or = '';
                }

                if (count($column_like) == $i) {
                   $or = '';
                }

                if (mb_detect_encoding($like) != 'ASCII') {

                    if ($name_type_column[$column] != 'date') {

                        $Sql .= $column.' LIKE '. "'%".$like."%'" .$or ;
                    }

                } else {

                    $Sql .= $column.' LIKE '. "'%".$like."%'" .$or ;
                }

            }

            if ($set_where != null) {
                $likeSql = ' AND ('.$Sql.') ';
            } else {
                $likeSql = ' WHERE '.$Sql.' ';
            }

        } else {
            $likeSql = '';
        }

        //формируем часть запроса для метода условия выборки seе_where()
        if ($set_where != null) {
            $sele_where = ' WHERE '.$set_where['colum'].$set_where['operation'].$set_where['value'];
        } else {
            $sele_where = '';
        }


        $query_count =  DB::query(Database::SELECT,
            'SELECT * FROM ' .$table.' '.$sele_where.' '.$likeSql)
            ->execute()
            ->as_array();

        $query = DB::query(Database::SELECT,
            'SELECT * FROM '.$table.' '.$sele_where.' '.$likeSql.' '.
            'ORDER BY '. $order_column.' '.$order_by.'
            LIMIT '.$ofset.','.$limit)
            ->execute()
            ->as_array();

        return array('query' => $query, 'count' => count($query_count));
    }

    private function get_pars_string ($field) {

        if(!strstr($field,'{')) {
            $this->field_names_array = $field;
        } else {
            $temp1 = explode('{', $field);
            unset($temp1[0]);

            $field_names_array = array();
            foreach ($temp1 as $field)
                list($field_names_array[]) = explode('}', $field);

           $this->field_names_array = $field_names_array;
        }
    }



    //получение данных из другой таблицы
    public function get_table_relativ ($Table, $field, $field_value, $wheres_arr = null, $oder_by = null) {


        $this->get_pars_string($field);

        if ($wheres_arr == null) {

            $query = DB::select()
                ->from($Table)
                ->execute()
                ->as_array();
        } else {
            //присваиваем id записи основной таблицы
            if ($wheres_arr[2] == 'IDKEY') {
                $wheres_arr[2] = Cruds::$id;
            }

            $query = DB::select()
                ->from($Table)
                ->where($wheres_arr[0], $wheres_arr[1], $wheres_arr[2])
                ->execute()
                ->as_array();

        }


        if (!empty($query)) {
            //проверяем если масив
            if (is_array($this->field_names_array)) {

                foreach ($this->field_names_array as $field_row) {
                    $temp[] = '{' . $field_row . '}';
                }

                foreach ($query as $rows) {

                    foreach ($this->field_names_array as $field_names_array_rows) {
                        $arr_recurs[] = $rows[$field_names_array_rows];
                    }

                    $str_row = str_replace($temp, $arr_recurs, $field);

                    $result[$rows[$field_value]] = $str_row;
                    $arr_recurs = array();
                }

                return $result;

            } else {

                foreach ($query as $rows) {
                    $result[$rows[$field_value]] = $rows[$this->field_names_array];
                }

                return $result;
            }

        } else {
            return false;
        }


    }


    //запись в таблицу
    public function set_other_table ($arr_table_other, $arr_insert, $parent_id) {

        foreach ($arr_table_other as $rows_other) {

            if (isset($arr_insert[$rows_other['field_old']])) {

                foreach ($arr_insert[$rows_other['field_old']] as $row_insert) {

                    $query = DB::insert($rows_other['table'], array($rows_other['field_new'], $rows_other['parent_id']))
                        ->values(array($row_insert, $parent_id))->execute();

                }


            }

        }

    }

    //получить запись из таблицы
    public function get_other_table ($arr_table_other, $edit_value, $parent_id, $no_serialise=true) {

        foreach ($arr_table_other as $rows) {

            $query = DB::select($rows['field_new'])->from($rows['table'])
                ->where($rows['parent_id'], '=', $parent_id)
                ->execute()->as_array();

            if (!empty($query)) {
                foreach ($query as $query_row) {
                    $result[] = $query_row[$rows['field_new']];
                }


                if ($no_serialise) {
                    if (isset($edit_value[$rows['field_old']])) {
                        $edit_value[$rows['field_old']] = serialize($result);
                    }
                } else {
                    if (isset($edit_value[$rows['field_old']])) {
                        $edit_value[$rows['field_old']] = $result;
                    }
                }
            }



            $result = array();
        }

        return $edit_value;
    }

    //удаление 1-n
    public function delete_other_table ($arr_table_other, $id) {

        foreach ($arr_table_other as $rows) {

            $query = DB::delete($rows['table'])
                ->where($rows['parent_id'], '=', $id)
                ->execute();
        }

    }

    //обновление записи для 1-n
    public function update_other_table ($arr_table_other, $edit_value, $id) {

        $this->delete_other_table($arr_table_other, $id);
        $this->set_other_table($arr_table_other, $edit_value, $id);

    }

}