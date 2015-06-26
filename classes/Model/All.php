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
    public function select_all_where ($table, $id = null, $join = null) {

        $key_primary = $this->information_table($table, true);
        $this->key_primary = $key_primary[0]->COLUMN_NAME;

        if ($id != null) {

            if (!empty($join)) {
                $query_table = DB::select()->from($table)
                    ->where($this->key_primary, '=', $id)
                    ->execute()->as_array();

                $result = array();
                $join_arr = array();
                foreach ($join as $rows) {
                    $query_join = DB::select()->from($rows[1])
                        ->where($rows[2], '=', $query_table[0][$rows[0]])
                        ->execute()->as_array();
                    foreach ($query_join[0] as $name_column => $row_join) {
                        $join_arr[$rows[1].'@'.$name_column] = $row_join;
                    }
                    $result = array_merge($result, $join_arr);
                }
                $result = array_merge($query_table[0], $result);
                $query[0] = $result;
                return $query;

            } else {
               return DB::select()->from($table)
                    ->where($this->key_primary, '=', $id)
                    ->execute()->as_array();
            }

        } else {
            return DB::select()->from($table)
                ->execute()->as_array();
        }
    }


    //удаление по id
    public function delete ($table, $id, $join = null) {

        $key_primary = $this->information_table($table, true);
        $this->key_primary = $key_primary[0]->COLUMN_NAME;

        if ($join !=null) {

            $query = DB::delete($table)
                ->where($this->key_primary, '=', $id)
                ->execute();

            foreach ($join as $joins) {
                DB::delete($joins[1])
                    ->where($joins[2], '=', $id)
                    ->execute();
            }

            return $query;

        } else {
            return DB::delete($table)
                ->where($this->key_primary, '=', $id)
                ->execute();
        }

    }

    //удаление груповое
    public function group_delete ($table, $idArr, $join = null) {

        $key_primary = $this->information_table($table, true);
        $this->key_primary = $key_primary[0]->COLUMN_NAME;

        if ($join != null) {

            DB::delete($table)
                ->where($this->key_primary, 'IN', $idArr)
                ->execute();

            foreach ($join as $joins) {
                DB::delete($joins[1])
                    ->where($joins[2], 'IN', $idArr)
                    ->execute();
            }

        } else {
            return DB::delete($table)
                ->where($this->key_primary, 'IN', $idArr)
                ->execute();
        }
    }

    //ОБНОВЛЕНЕ
    public function update ($table, $field, $id, $key_primary, $join = null) {


        if ($join != null) {

            $array_update = Cruds::parse_name_column($field);
            //die(print_r($array_update));
            DB::update($table)
                ->set($array_update['table'])
                ->where($key_primary, '=', $id)
                ->execute();

            foreach ($array_update['join'] as $name_table => $row_join) {

                foreach ($join as $joines) {
                    if ($joines[1] == $name_table) {
                        $id = $row_join[$joines[3]];
                        DB::update($name_table)
                            ->set($row_join)
                            ->where($joines[3], '=', $id)
                            ->execute();
                    }
                }
            }
        } else {

            DB::update($table)
                ->set($field)
                ->where($key_primary, '=', $id)
                ->execute();
        }
    }

    //добавление
    public function insert ($table, $column_table, $value_table, $join = null) {

        //таблицы связаные если есть
        if ($join != null) {

            $array_insert = Cruds::parse_name_column(array_combine($column_table, $value_table));

            $query = DB::insert($table, array_keys($array_insert['table']))
                ->values(array_values($array_insert['table']))
                ->execute();


            foreach ($array_insert['join'] as $name_table => $row_join) {
                $row_joins = array();
                foreach ($join as $joines) {
                    if ($name_table == $joines[1]) {
                        $row_joins = $row_join;
                        $row_joins[$joines[2]] = $query[0];
                    }
                }

                $query_j = DB::insert($name_table, array_keys($row_joins))
                    ->values(array_values($row_joins))
                    ->execute();
            }

            return $query[0];

        } else {

            $query = DB::insert($table, $column_table)
                ->values($value_table)
                ->execute();
            return $query[0]; //возвращаем id

        }

    }

    //ПОЛУЧАЕМ названия ПОЛЯ ТАБЛИИЦЫ
    public function name_count ($table, $join = null) {

        if ($join != null) {

            $name_table = DB::query(Database::SELECT,
                'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas');
            $name_table->param(':tab', $table);
            $name_table->param(':bas', Kohana::$config->load('crudconfig.database'));
            $name_table = $name_table->execute()->as_array();

            $join_arr = array();
            $result_mod = array();

            foreach ($join as $row_join) {
                $result_mod = array();
                $name_colums_table = DB::query(Database::SELECT,
                    'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas');
                $name_colums_table->param(':tab', $row_join[1]);
                $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));
                $result = $name_colums_table->cached()->execute()->as_array();

                foreach ($result as $result_join) {
                    $result_mod[] = array('COLUMN_NAME' => $row_join[1].'@'.$result_join['COLUMN_NAME']);
                }

                $join_arr = array_merge($join_arr ,$result_mod);
            }

            $result = array_merge($name_table,  $join_arr);
            return $result;

        } else {

            $name_colums_table = DB::query(Database::SELECT,
                'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas');
            $name_colums_table->param(':tab', $table);
            $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));

            return $name_colums_table->execute()->as_array();
        }
    }

    public function information_table ($table, $key_primary = null, $join_table = null) {

        if ($key_primary != null) {
            $key_primary = 'PRI';
            $name_colums_table = DB::query(Database::SELECT,
                'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas AND COLUMN_KEY = :key_primary');
            $name_colums_table->param(':tab', $table);
            $name_colums_table->param(':key_primary', $key_primary);
            $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));

            return $name_colums_table->cached()->as_object()->execute();
        } else {

            if ($join_table != null) {

                $join_table[] = array(1 => $table);
                $result_arr = array();

                foreach ($join_table as $tableName => $rows) {
                    $name_colums_table = DB::query(Database::SELECT,
                        'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas');
                    $name_colums_table->param(':tab', $rows[1]);
                    $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));
                    $result = $name_colums_table->cached()->execute()->as_array();
                    $result_arr = array_merge($result_arr,$result);
                }

                return $result_arr;
            } else {
                $name_colums_table = DB::query(Database::SELECT,
                    'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :tab AND TABLE_SCHEMA = :bas');
                $name_colums_table->param(':tab', $table);
                $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));

                return $name_colums_table->cached()->execute()->as_array();
            }

        }


    }

    //количество записей
    public function count_table ($table, $set_where = null) {

        if ($set_where != null) {
            $sele_where = 'WHERE '.$set_where['colum'].' '.$set_where['operation'].' '.$set_where['value'];
            $count_table =  DB::query(Database::SELECT,'SELECT COUNT(*) FROM '.$table.' '.$sele_where);
        } else {
            $count_table =  DB::query(Database::SELECT,'SELECT COUNT(*) FROM '.$table);
        }

        return $count_table->execute()->as_array();
    }


    //типы полей таблицы
    private function information_data_type ($table, $join_table = null) {

        if ($join_table != null) {
            $join_arr = array();

            $data_type = DB::query(Database::SELECT, 'SELECT COLUMN_NAME,DATA_TYPE
                                                      FROM INFORMATION_SCHEMA.COLUMNS
                                                      WHERE table_name = :tab
                                                        AND TABLE_SCHEMA = :bas');
            $data_type->param(':tab', $table);
            $data_type->param(':bas', Kohana::$config->load('crudconfig.database'));
            $result_table = array();
            foreach ($data_type->cached()->execute()->as_array() as $row) {
                $result_table[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
            }


            foreach ($join_table as $row_join) {
                $result_mod = array();
                $name_colums_table = DB::query(Database::SELECT,
                                                        'SELECT COLUMN_NAME,DATA_TYPE
                                                      FROM INFORMATION_SCHEMA.COLUMNS
                                                      WHERE table_name = :tab
                                                        AND TABLE_SCHEMA = :bas');
                $name_colums_table->param(':tab', $row_join[1]);
                $name_colums_table->param(':bas', Kohana::$config->load('crudconfig.database'));
                $result = $name_colums_table->cached()->execute()->as_array();

                foreach ($result as $result_join) {
                    $result_mod[$row_join[1].'@'.$result_join['COLUMN_NAME']] = $result_join['DATA_TYPE'];
                }

                $join_arr = array_merge($join_arr ,$result_mod);
            }
            return array_merge($result_table, $join_arr);
        } else {

            $data_type = DB::query(Database::SELECT, 'SELECT COLUMN_NAME,DATA_TYPE
                                                      FROM INFORMATION_SCHEMA.COLUMNS
                                                      WHERE table_name = :tab
                                                        AND TABLE_SCHEMA = :bas');
            $data_type->param(':tab', $table);
            $data_type->param(':bas', Kohana::$config->load('crudconfig.database'));

            foreach ($data_type->cached()->execute()->as_array() as $row) {
                $result[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
            }

            return $result;
        }

    }



    private function str_join_replace ($column) {
            return str_replace("@", ".", $column);
    }



    //пагинация
    public function paginationAjax ($limit, $ofset = null, $table, $order_column, $order_by, $like = null, $column_like, $set_where = null, $join = null) {

        if ($ofset == '' or $ofset == null) {
            $ofset = 0;
        }

        //die(print_r($order_column));

        if ($like != '' and $like !== null) {

            $i=0;
            $Sql ='';

            $like = str_replace ("'", "\'", $like);

            //получаем поля и тыпы к ним
            $name_type_column = $this->information_data_type($table, $join);

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
                    if ($name_type_column[$column] != 'date' AND $name_type_column[$column] != 'time') {

                        if ($join != null) {
                            $column = $this->str_join_replace($column);
                        }

                        $Sql .= $column . ' LIKE ' . "'%" . $like . "%'" . $or;

                    }
                } else {

                    if ($join != null) {
                        $column = $this->str_join_replace($column);
                    }

                    $Sql .= $column . ' LIKE ' . "'%" . $like . "%'" . $or;
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
            $sele_where = ' WHERE '.$set_where['colum'].' '.$set_where['operation'].' '.$set_where['value'];
        } else {
            $sele_where = '';
        }


        //если есть обеденненые таблицы
        if ($join != null) {

            $joins_string = $this->create_joint_query($table, $join, $column_like);

            //количество найденых в таблице
            $query_count =  DB::query(Database::SELECT,
                'SELECT COUNT(*) as cou FROM ' .$table.' '.$joins_string['q'].' '.$sele_where.' '.$likeSql)
                ->execute()
                ->as_array();

            $order_column = $this->str_join_replace($order_column);

            $query = DB::query(Database::SELECT,
                'SELECT '.$joins_string['asCoun'].' FROM '.$table.' '.$joins_string['q'].' '.$sele_where.' '.$likeSql.' '.
                'ORDER BY '. $order_column.' '.$order_by.'
            LIMIT '.$ofset.','.$limit)
                ->execute()
                ->as_array();

        } else {

            //количество найденых в таблице
            $query_count =  DB::query(Database::SELECT,
                'SELECT COUNT(*) as cou FROM ' .$table.' '.$sele_where.' '.$likeSql)
                ->execute()
                ->as_array();

            $query = DB::query(Database::SELECT,
                'SELECT * FROM '.$table.' '.$sele_where.' '.$likeSql.' '.
                'ORDER BY '. $order_column.' '.$order_by.'
            LIMIT '.$ofset.','.$limit)
                ->cached()
                ->execute()
                ->as_array();
        }

        return array('query' => $query, 'count' => $query_count[0]['cou']);
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

    /**
     * @param $table
     * @param $join
     * @param $column
     * @return array
     * формирование строки запроса для обеденных таблиц
     */
    private function create_joint_query ($table, $join, $column) {

        $array_counts = Cruds::parse_name_column(array_flip($column));
        $str = '';
        $str_as = '';
        foreach ($join as $joins) {
            $str .= ' INNER JOIN '.$joins[1].' ON '.$table.'.'.$joins[0].'='.$joins[1].'.'.$joins[2].' ';
        }

        $str_as = implode(', ', array_flip($array_counts['table']));
        foreach ($array_counts['join'] as $name_table => $row_join) {

            foreach ($row_join as $name_column => $row_coun) {
                $str_as .= ', '.$name_column.' AS '.'`'.$name_table.'@'.$name_column.'`'.' ';
            }

        }

        return array('q' => $str, 'asCoun' =>  $str_as);
    }


    //получение данных из другой таблицы
    public function get_table_relativ ($Table, $field, $field_value, $wheres_arr = null, $oder_by = null) {

        $this->get_pars_string($field);

        if ($wheres_arr == null) {

            $query = DB::select()
                ->from($Table)
                ->cached()
                ->execute()
                ->as_array();
        } else {
            //присваиваем id записи основной таблицы
            if ($wheres_arr[2] == 'IDKEY') {
                $wheres_arr[2] = Cruds::$id;
            }

            $query = DB::select()
                ->from($Table)
                ->cached()
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