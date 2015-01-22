<?php defined('SYSPATH') or die('No direct script access.'); ?>

<style type="text/css">
    .chosen-container-single .chosen-single {
        border-radius: 5px 0 0 5px;
    }
</style>


 <?//die(print_r($multiselect))?>


    <?if (!empty($multiple)):?>

        <?
        if(!empty($origin_value_fild)) {

            try {
                $arr_origin_value_fild = $origin_value_fild;
                $origin_value_fild = unserialize($arr_origin_value_fild);

            } catch (Exception $e) {
                $origin_value_fild =  $arr_origin_value_fild;
            }

        }
        ?>

        <?//выбор стиля поля select?>
        <?if (!empty($multiselect)):?>

            <?if (!empty($origin_value_fild)): //проверка на существование если это add?>

                <?if (is_array($origin_value_fild)): //делаем проверку масив ли это?>
                        <?$orig_row_val = array_flip($origin_value_fild)?>
                        <div class="entry input-group">
                            <select <?=$attr?> data-placeholder="Выбрать..." class="form-control chosen-select" style="width:500px; height: 20px;" <?=$multiselect?> name="<?=$name_fied?>[]" id="">
                                <?foreach ($value_fild as $val => $row):?>
                                    <option value="<?=$val?>" <?if (isset($orig_row_val[$val])):?> selected <?endif?>><?=$row?></option>
                                <?endforeach?>
                            </select>
                        </div>

                <?else:?>
                    <div class="entry input-group">
                        <select <?=$attr?> data-placeholder="Выбрать..." class="form-control chosen-select" style="width:500px; height: 20px;" <?=$multiselect?> name="<?=$name_fied?>[]" id="">
                            <?foreach ($value_fild as $val => $row):?>
                                <option value="<?=$val?>"><?=$row?></option>
                            <?endforeach?>
                        </select>
                    </div>
                <?endif?>

            <?else:?>

                <div class="entry input-group">
                    <select <?=$attr?> data-placeholder="Выбрать..." class="form-control chosen-select" style="width:500px; height: 20px;" <?=$multiselect?> name="<?=$name_fied?>[]" id="">
                        <?foreach ($value_fild as $val => $row):?>
                            <option value="<?=$val?>"><?=$row?></option>
                        <?endforeach?>
                    </select>
                </div>
            <?endif?>

        <?else:?>

            <div class="w-input-form">
                <?if (!empty($origin_value_fild)): //проверка на существование если это add?>
                    <?if (is_array($origin_value_fild)):?>
                        <?foreach ($origin_value_fild as $key_orig_row_val => $orig_row_val):?>
                            <?//поля с кнопкой -?>
                            <div class="entry input-group">
                                <select <?=$attr?> data-placeholder="Выбрать..." class="form-control chosen-select" name="<?=$name_fied?>[]" id="">
                                        <?foreach ($value_fild as $val => $row):?>
                                            <option value="<?=$val?>" <?if ($orig_row_val == $val):?> selected <?endif?>><?=$row?></option>
                                        <?endforeach?>
                                </select>
                                <span class="input-group-btn">
                                    <button class="btn btn-remove btn-danger" type="button" style="height: 23px; padding: 0 9px 2px 9px;"><span class="glyphicon glyphicon-minus" style="padding: 3px"></span></button>
                                </span>

                            </div>

                        <?endforeach?>
                    <?endif?>
                <?endif?>

                <?//поле с кнопкой +?>
                <div class="entry input-group">
                    <select <?=$attr?> data-placeholder="Выбрать..." class="form-control chosen-select" name="<?=$name_fied?>[]" id="">
                        <?foreach ($value_fild as $val => $row):?>
                            <option value="<?=$val?>"><?=$row?></option>
                        <?endforeach?>
                    </select>
                    <span class="input-group-btn">
                        <button class="btn btn-success btn-add" type="button" style="height: 23px; padding: 0 9px 2px 9px;">
                            <span class="glyphicon glyphicon-plus" style="padding: 3px"></span>
                        </button>
                    </span>
                </div>

            </div>
        <?endif?>

    <?else:?>

        <select <?=$attr?> data-placeholder="Выбрать..." class="form-control chosen-select" name="<?=$name_fied?>" id="">
            <?//проверяем вляется ли переменная масивом?>
            <?if(is_array($value_fild)):?>
                <?foreach ($value_fild as $val => $row):?>
                    <option value="<?=$val?>" <?if (!empty($origin_value_fild)):?><?if ($origin_value_fild == $val):?> selected <?endif?><?endif?>><?=$row?></option>
                <?endforeach?>
            <?else:?>
                <option value="<?=$value_fild?>"><?=$value_fild?></option>
            <?endif?>
        </select>
        
    <?endif?>

