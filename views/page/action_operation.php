<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?=__('LANG_ACTION')?> <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">

        <li>
            <? if($table_propery['activ_operation']['edit'] != true):?>
                <form id="form-edit" action="/<?=Kohana::$config->load('crudconfig.base_url')?>/edit" method="get">
                    <input type="hidden" name="obj" value="<?=$table_propery['obj_serial']?>"/>
                    <input type="hidden" name="id" value="<?=$rows_query[$table_propery['key_primary']]?>"/>
                    <button type="submit" class="edit"><span class="glyphicon glyphicon-edit"></span> <?=__('LANG_EDIT')?></button>

                </form>
            <?endif?>
        </li>



        <?if($table_propery['add_action_url_icon'] != ''):?>

            <?foreach ($table_propery['add_action_url_icon'] as $rows_action):?>
                <li>
                    <form action="/<?=Kohana::$config->load('crudconfig.base_url')?>/new/<?=$rows_action['url']?>" method="post">
                        <input type="hidden" name="obj" value="<?=$table_propery['obj_serial']?>"/>
                        <input type="hidden" name="func" value="<?=$rows_action['name_function']?>">
                        <input type="hidden" name="id" value="<?=$rows_query[$table_propery['key_primary']]?>"/>
                        <button type="submit" class="new-action"><span class="<?=$rows_action['icon']?>"></span> <?=$rows_action['name_action']?></button>

                    </form>
                </li>
            <?endforeach?>
        <?endif?>




        <li class="divider"></li>

        <li>

            <? if($table_propery['activ_operation']['delete'] != true):?>
                <form id="form-delete" action="/<?=Kohana::$config->load('crudconfig.base_url')?>/delete" method="post">

                    <input type="hidden" name="id" value="<?=$rows_query[$table_propery['key_primary']]?>"/>
                    <input type="hidden" name="obj" value="<?=$table_propery['obj_serial']?>"/>
                    <button type="submit" class="delete"><span class="glyphicon glyphicon-remove-circle"></span> <?=__('LANG_DELETE')?></button>

                </form>
            <?endif?>


        </li>
    </ul>
</div>
