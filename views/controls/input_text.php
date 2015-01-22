<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?//print_r($value_fild)?>

<?if (!empty($multiple)):?>

    <?
        if(!empty($value_fild)) {

            try {
                $arr_value = unserialize($value_fild);
            } catch (Exception $e) {
                $arr_value = $value_fild;
            }

        }
    ?>

    <div class="w-input-form">

        <?if (!empty($arr_value)):?>

            <?if (is_array($arr_value)): //при условии что получен десереализованый масив?>

                <?foreach ($arr_value as $row):?>
                    <div class="entry input-group">
                        <input class="form-control" type="<?=$type_field?>" name="<?=$name_fied?>[]" value="<?=$row?>">
                        <span class="glyphicon glyphicon-remove form-control-feedback" style="display: none"></span>
                        <span class="glyphicon glyphicon-ok form-control-feedback" style="display: none"></span>
                            <span class="input-group-btn">
                                <button class="btn btn-remove btn-danger" type="button"><span class="glyphicon glyphicon-minus" style="padding: 3px"></span></button>
                            </span>
                    </div>
                <?endforeach?>

            <?else://если в базе не сериализованый обьект а подключили multiple?>

                <div class="entry input-group">
                    <input class="form-control" type="<?=$type_field?>" name="<?=$name_fied?>[]" value="<?=$arr_value?>">
                    <span class="glyphicon glyphicon-remove form-control-feedback" style="display: none"></span>
                    <span class="glyphicon glyphicon-ok form-control-feedback" style="display: none"></span>
                            <span class="input-group-btn">
                                <button class="btn btn-remove btn-danger" type="button"><span class="glyphicon glyphicon-minus" style="padding: 3px"></span></button>
                            </span>
                </div>

            <?endif?>
        <?endif?>

        <div class="entry input-group">
            <input <?=$attr?> class="form-control"
                              type="<?=$type_field?>"
                              name="<?=$name_fied?>[]"
                              value=""/>
            <span class="glyphicon glyphicon-remove form-control-feedback" style="display: none"></span>
            <span class="glyphicon glyphicon-ok form-control-feedback" style="display: none"></span>
                <span class="input-group-btn">
                    <button class="btn btn-success btn-add" type="button">
                        <span class="glyphicon glyphicon-plus" style="padding: 3px"></span>
                    </button>
                </span>
        </div>


    </div>


<?else:?>

    <?
        switch ($type_field) {

            case 'date':
                $date_types = 'glyphicon-calendar';
                $data_class = 'form_date';
                $data_format = 'yyyy-mm-dd';
            break;

            case 'datetime':
                $date_types = 'glyphicon-th';
                $data_class = 'form_datetime';
                $data_format = 'dd MM yyyy - HH:ii p';
            break;

            case 'time':
                $date_types = 'glyphicon-time';
                $data_class = 'form_time';
                $data_format = 'hh:ii';
            break;

        }


    ?>

    <?if ($type_field == 'text'):?>
        <input <?=$attr?> class="form-control"
                          type="text"
                          name="<?=$name_fied?>"
                          value="<?if (!empty($value_fild)) echo $value_fild?>"
                          id="<?=$name_fied?>"/>
        <span class="glyphicon glyphicon-remove form-control-feedback" style="display: none"></span>
        <span class="glyphicon glyphicon-ok form-control-feedback" style="display: none"></span>
    <?else:?>

        <div class="input-group date col-md-5 <?=$data_class?>" data-date="" data-date-format="<?=$data_format?>" data-link-field="<?=$name_fied?>" data-link-format="<?=$data_format?>">
            <input <?=$attr?> class="form-control"
                              type="text"
                              readonly
                              name="<?=$name_fied?>"
                              value="<?if (!empty($value_fild)) echo $value_fild?>"
                              id="<?=$name_fied?>"/>
            <span class="glyphicon glyphicon-remove form-control-feedback" style="display: none"></span>
            <span class="glyphicon glyphicon-ok form-control-feedback" style="display: none"></span>
            <span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
            <span class="input-group-addon"><span class="glyphicon <?=$date_types?>"></span></span>
        </div>
        <input type="hidden" id="<?=$name_fied?>" value="" />


    <?endif?>

<?endif?>
