<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?if (!empty($edit_property['scripts_edit'])):?>
    <?foreach ($edit_property['scripts_edit'] as $rows):?>
        <?=HTML::script($rows)?>
    <?endforeach?>
<?endif?>

<?if (!empty($edit_property['styles_edit'])):?>
    <?foreach ($edit_property['styles_edit'] as $rows):?>
        <?=HTML::style($rows)?>
    <?endforeach?>
<?endif?>

<!--    HTML::script-->
<?=isset($script_validate) ? $script_validate:''?>

<script>


    $(document).on('click', '#loading-example-btn', function(){
        $(this).attr('disabled', true);
        $('#circularG').show();
        var form_edit = $('#w-form-edit');
        tinyMCE.triggerSave();
        form_edit.attr('target','hiddenframe');
        form_edit.submit();

        form_edit.removeAttr('target');

    });


</script>
<!--<pre>--><?//print_r($edit_property)?><!--</pre>-->

    <div id="circularG" style="display: none">
        <div id="circularG_1" class="circularG">
        </div>
        <div id="circularG_2" class="circularG">
        </div>
        <div id="circularG_3" class="circularG">
        </div>
        <div id="circularG_4" class="circularG">
        </div>
        <div id="circularG_5" class="circularG">
        </div>
        <div id="circularG_6" class="circularG">
        </div>
        <div id="circularG_7" class="circularG">
        </div>
        <div id="circularG_8" class="circularG">
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">

            <form method="POST" enctype="multipart/form-data" id="w-form-edit" class="form-horizontal" role="form" >

                <?foreach ($edit_property['field'] as $name_fied => $value_fild):?>
                    <?if ($name_fied != $edit_property['key_primary'] and empty($edit_property['join_key'][$name_fied])):?>
                        <div class="form-group has-feedback">
                            <?if (isset($edit_property['name_colums_table_show'][$name_fied])):?>

                                <label for="<?=$edit_property['name_colums_table_show'][$name_fied]?>" class="col-sm-2 control-label"><?=$edit_property['name_colums_table_show'][$name_fied]?></label>

                            <?else:?>

                                <label for="<?=$name_fied?>" class="col-sm-2 control-label"><?=$name_fied?></label>

                            <?endif?>
                            <div class="col-sm-10">

                                <? //переопределение типов полей полей
                                    if (!empty($edit_property['new_type_field'][$name_fied])) {

                                        if ($edit_property['new_type_field'][$name_fied]['type_field']  == 'textarea') {
                                            $edit_property['type_field'][$name_fied] = array('tag' => 'textarea');
                                        } else {
                                            $edit_property['type_field'][$name_fied] = $edit_property['new_type_field'][$name_fied]['type_field'];
                                        }


                                        if (!empty($edit_property['new_type_field'][$name_fied]['field_value'])) {
                                               $origin_value_fild = $value_fild; //первоначальное значение поля

                                               $value_fild =  $edit_property['new_type_field'][$name_fied]['field_value']; //переопределенное значение
                                        }

                                        //для checkbox с раскладом $crud->set_field_type('name', 'checkbox');
                                        if (empty($edit_property['new_type_field'][$name_fied]['field_value']) and  $edit_property['new_type_field'][$name_fied]['type_field'] == 'checkbox') {
                                            $origin_value_fild = $value_fild;
                                        }


                                    }
                                ?>

                                <?//присваивание типов полей?>

                                <?if (is_array($edit_property['type_field'][$name_fied])):?>

                                    <?if ($edit_property['type_field'][$name_fied]['tag'] == 'textarea'):?>


                                        <?
                                            if (empty($edit_property['disable_editor'][$name_fied])) {
                                                $editor_class = 'add-editor';
                                            } else {
                                                $editor_class = '';
                                            }

                                            if (!empty($edit_property['new_type_field'][$name_fied]['attr'])) {

                                                $attr = $edit_property['new_type_field'][$name_fied]['attr'];
                                            } else {
                                                $attr = '';
                                            }

                                            $data = array(
                                                'title' => !empty($edit_property['toptip'][$name_fied]) ? $edit_property['toptip'][$name_fied] : '',
                                                'value_fild' => $value_fild,
                                                'name_fied' => $name_fied,
                                                'disable_editor_class' => $editor_class,
                                                'attr' => $attr
                                            );

                                            echo View::factory('controls/textarea', $data);


                                        ?>

                                    <?endif?>

                                <?else:?>

                                    <?php

                                        //добавляет к полю select атрибут multiple
                                        if (!empty($edit_property['select_muliselect'][$name_fied])) {
                                            $multiselect = $edit_property['select_muliselect'][$name_fied];

                                        } else {
                                            $multiselect = '';
                                        }

                                        //атрибуты
                                        if (!empty($edit_property['new_type_field'][$name_fied]['attr'])) {

                                            $attr = $edit_property['new_type_field'][$name_fied]['attr'];
                                        } else {
                                            $attr = '';
                                        }

                                        //множественный выбор
                                        if (!empty($edit_property['new_type_field'][$name_fied]['multiple'])){
                                            $multiple = $edit_property['new_type_field'][$name_fied]['multiple'];
                                        } else {
                                            $multiple = null;
                                        }

                                        //тип файлов картинки или другие файлы
                                        if (!empty($edit_property['type_field_upload'][$name_fied][4])){
                                            $type_field_upload = $edit_property['type_field_upload'][$name_fied][4];
                                        } else {
                                            $type_field_upload = null;
                                        }

                                        //если флажок или радио
                                        if ($edit_property['type_field'][$name_fied] == 'checkbox' or $edit_property['type_field'][$name_fied] == 'radio') {

                                            $data = array(
                                                'type_field' => $edit_property['type_field'][$name_fied], //тип поля
                                                'origin_value_fild' => $origin_value_fild, //значение получено из таблицы
                                                'value_fild' => $value_fild, //возможно изминенное значение
                                                'name_fied' => $name_fied, //имя поля name
                                                'attr' => $attr
                                            );

                                            echo View::factory('controls/checkbox', $data);
                                        //если селект
                                        } elseif ($edit_property['type_field'][$name_fied] == 'select') {

                                            //die(print_r($origin_value_fild));
                                            $data = array(
                                                'origin_value_fild' => $origin_value_fild,
                                                'value_fild' => $value_fild,
                                                'multiple' => $multiple,
                                                'name_fied' => $name_fied,
                                                'attr' => $attr,
                                                'multiselect' => $multiselect //изменяет поле select
                                            );

                                            echo View::factory('controls/select', $data);
                                        //если file
                                        } elseif ($edit_property['type_field'][$name_fied] == 'file') {

                                            $data = array(
                                                'title' => !empty($edit_property['toptip'][$name_fied]) ? $edit_property['toptip'][$name_fied] : '',
                                                'value_fild' => $value_fild,
                                                'name_fied' => $name_fied,
                                                'multiple' => $multiple,
                                                'type_field_upload' => $type_field_upload
                                            );

                                            echo View::factory('controls/input_file', $data);
                                        //остальние текстовые дата номер и т.д
                                        } else {

                                            $data = array(
                                                'title' => !empty($edit_property['toptip'][$name_fied]) ? $edit_property['toptip'][$name_fied] : '',
                                                'type_field' => $edit_property['type_field'][$name_fied],
                                                'value_fild' => $value_fild,
                                                'multiple' => $multiple,
                                                'name_fied' => $name_fied,
                                                'attr' => $attr
                                            );

                                            echo View::factory('controls/input_text', $data);
                                        }


                                    ?>


                                <?endif?>

                            </div>
                        </div>
                    <?else:?>

                        <?//id в скрытом поле?>
                        <input type="hidden"  name="<?=$name_fied?>" value="<?=$value_fild?>">
                        <?if (!empty($edit_property['join_key'])):?>

                            <? foreach ($edit_property['join_key'] as $name_key => $join_key_val): ?>
                                <input type="hidden"  name="<?=$name_key?>" value="<?=$join_key_val?>">
                            <?endforeach?>

                        <?endif?>


                    <?endif?>
                <?endforeach?>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <input type="hidden" name="obj" value="<?=$edit_property['obj']?>"/>
                        <input type="hidden" name="edit"/>
                        <input type="hidden" name="curent_uri" value="<?=$curent_uri?>"/>
                        <?if ($edit_property['remove_save'] !== true):?>
                            <button type="submit" id="loading-save" class="btn btn-success btn-lg"><?=__('LANG_SAVE')?> <span class="glyphicon glyphicon-floppy-disk"></span></button>
                        <?endif?>
                        <button type="button" id="loading-example-btn" data-loading-text="<?=__('LANG_BUTTON_LOAD_APLY')?>" class="btn btn-primary btn-lg"><?=__('LANG_BUTTON_APLY')?> <span class="glyphicon glyphicon-floppy-saved"></span></button>
                    </div>
                </div>

            </form>
            <iframe id="hiddenframe" name="hiddenframe" style="width:0; height:0; border:0"></iframe>

        </div>
    </div>


