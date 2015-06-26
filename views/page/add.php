<?php defined('SYSPATH') or die('No direct script access.'); ?>
<?if (!empty($add_property['scripts_add'])):?>
    <?foreach ($add_property['scripts_add'] as $rows):?>
        <?=HTML::script($rows)?>
    <?endforeach?>
<?endif?>

<?if (!empty($add_property['styles_add'])):?>
    <?foreach ($add_property['styles_add'] as $rows):?>
        <?=HTML::style($rows)?>
    <?endforeach?>
<?endif?>

<?=isset($script_validate) ? $script_validate:''?>

<script>
    $(document).ready(function(){
        $(document).on('click', '#loading-example-btn', function(){

            var form_add = $('#w-form-add');
            tinyMCE.triggerSave();
            form_add.attr('target','hiddenframe');
            form_add.submit();
            $(this).attr("disabled", "disabled");

        });

        $(document).on('click', '.w-save-add', function(){

            if ($('#w-form-add').attr("target")) {
                $('#w-form-add').removeAttr("target");
                document.location.href = '/<?=Kohana::$config->load('crudconfig.base_url')?>';
            } else {
                $('#w-form-add').submit();
            }

        });
    });


</script>
<!--<pre>--><?//print_r($add_property)?><!--</pre>-->

    <div class="row">
        <div class="col-md-12">

            <form id="w-form-add" class="form-horizontal" role="form" action="" method="POST" enctype="multipart/form-data">

                <?foreach ($add_property['field'] as  $name_fied):?>

                    <?if (empty($add_property['join_key'][$name_fied])):?>

                        <div class="form-group has-feedback">

                        <?if (isset($add_property['name_colums_table_show'][$name_fied])):?>
                                <label for="<?=$name_fied?>" class="col-sm-2 control-label"><?=$add_property['name_colums_table_show'][$name_fied]?></label>
                            <?else:?>
                                <label for="<?=$name_fied?>" class="col-sm-2 control-label"><?=$name_fied?></label>
                        <?endif?>

                        <div class="col-sm-10">

                            <? //переопределение типов полей полей
                                if (!empty($add_property['new_type_field'][$name_fied])) {

                                    if ($add_property['new_type_field'][$name_fied]['type_field']  == 'textarea') {
                                        $add_property['type_field'][$name_fied] = array('tag' => 'textarea');
                                    } else {
                                        $add_property['type_field'][$name_fied] = $add_property['new_type_field'][$name_fied]['type_field'];
                                    }


                                    if (!empty($add_property['new_type_field'][$name_fied]['field_value'])) {
                                        $value_fild =  $add_property['new_type_field'][$name_fied]['field_value'];
                                    } else {
                                        $value_fild = null;
                                    }

                                }
                            ?>

                            <?//присваивание типов полей?>
                            <?if (is_array($add_property['type_field'][$name_fied])):?>

                                <?if ($add_property['type_field'][$name_fied]['tag'] == 'textarea'):?>


                                    <?

                                        if (empty($add_property['disable_editor'][$name_fied])) {
                                            $editor_class = 'add-editor';
                                        } else {
                                            $editor_class = '';
                                        }

                                        if (!empty($add_property['new_type_field'][$name_fied]['attr'])) {
                                            $attr = $add_property['new_type_field'][$name_fied]['attr'];
                                        } else {
                                            $attr = '';
                                        }

                                        $data = array(
                                            'title' => !empty($edit_property['toptip'][$name_fied]) ? $edit_property['toptip'][$name_fied] : '',
                                            'name_fied' => $name_fied,
                                            'disable_editor_class' => $editor_class,
                                            'attr' => $attr
                                        );

                                        echo View::factory('controls/textarea', $data);

                                    ?>

                                <?endif?>

                            <?else:?>

                                <?
                                    //добавляет к полю select атрибут multiple
                                    if (!empty($add_property['select_muliselect'][$name_fied])) {
                                        $multiselect = $add_property['select_muliselect'][$name_fied];
                                    } else {
                                        $multiselect = '';
                                    }

                                    //атрибуты
                                    if (!empty($add_property['new_type_field'][$name_fied]['attr'])) {

                                        $attr = $add_property['new_type_field'][$name_fied]['attr'];
                                    } else {
                                        $attr = '';
                                    }

                                    //множественный выбор
                                    if (!empty($add_property['new_type_field'][$name_fied]['multiple'])){
                                        $multiple = $add_property['new_type_field'][$name_fied]['multiple'];
                                    } else {
                                        $multiple = null;
                                    }

                                    //тип файлов картинки или другие файлы
                                    if (!empty($add_property['type_field_upload'][$name_fied][4])){
                                        $type_field_upload = $add_property['type_field_upload'][$name_fied][4];
                                    } else {
                                        $type_field_upload = null;
                                    }

                                    //если флажок или радио
                                    if ($add_property['type_field'][$name_fied] == 'checkbox' or $add_property['type_field'][$name_fied] == 'radio') {

                                        $data = array(
                                            'value_fild' => $value_fild,
                                            'type_field' => $add_property['type_field'][$name_fied], //тип поля
                                            'name_fied' => $name_fied, //имя поля name
                                            'attr' => $attr
                                        );

                                        echo View::factory('controls/checkbox', $data);
                                        //если селект
                                    } elseif ($add_property['type_field'][$name_fied] == 'select') {

                                        $data = array(
                                            'value_fild' => $value_fild,
                                            'multiple' => $multiple,
                                            'name_fied' => $name_fied,
                                            'attr' => $attr,
                                            'multiselect' => $multiselect //изменяет поле select
                                        );

                                        echo View::factory('controls/select', $data);
                                        //если file
                                    } elseif ($add_property['type_field'][$name_fied] == 'file') {

                                        $data = array(
                                            'title' => !empty($edit_property['toptip'][$name_fied]) ? $edit_property['toptip'][$name_fied] : '',
                                            'name_fied' => $name_fied,
                                            'multiple' => $multiple,
                                            'type_field_upload' => $type_field_upload
                                        );

                                        echo View::factory('controls/input_file', $data);
                                        //остальние текстовые дата номер и т.д
                                    } else {

                                        $data = array(
                                            'title' => !empty($edit_property['toptip'][$name_fied]) ? $edit_property['toptip'][$name_fied] : '',
                                            'type_field' => $add_property['type_field'][$name_fied],
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

                    <?endif?>

                <?endforeach?>


                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <input type="hidden" name="obj" value="<?=$add_property['obj']?>"/>
                        <input type="hidden"  name="add"/>
                        <input type="hidden" name="curent_uri" value="<?=$curent_uri?>"/>
                        <button type="button" class="btn btn-default btn-lg w-save-add"><?=__('LANG_SAVE')?> <span class="glyphicon glyphicon-floppy-disk"></span></button>
                        <button type="button" id="loading-example-btn" data-loading-text="<?=__('LANG_BUTTON_LOAD_APLY')?>" class="btn btn-primary btn-lg"><?=__('LANG_BUTTON_APLY')?> <span class="glyphicon glyphicon-floppy-saved"></span></button>
                    </div>
                </div>

            </form>
            <iframe id="hiddenframe" name="hiddenframe" style="width:0; height:0; border:0"></iframe>

        </div>
    </div>


