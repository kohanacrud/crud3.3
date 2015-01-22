<?php defined('SYSPATH') or die('No direct script access.'); ?>
<?
/**
 * Created by PhpStorm.
 * User: Vitalik
 * Date: 09.01.2015
 * Time: 22:44
 */

?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <table class="table table-striped">

                <?foreach ($show_views_property['field'] as $name_fied => $value_fild):?>
                    <?if ($name_fied != $show_views_property['key_primary']):?>

                        <?if (isset($show_views_property['name_colums_table_show'][$name_fied])):?>

                            <tr class="info">
                                <td><?=$show_views_property['name_colums_table_show'][$name_fied]?>:</td>
                                <td><?=$value_fild?></td>

                            </tr>

                        <?else:?>

                            <tr class="info">
                                <td><?=$name_fied?>:</td>
                                <td><?=$value_fild?></td>
                            </tr>

                        <?endif?>

                    <?endif?>

                <?endforeach?>

            </table>

        </div>

    </div>

    <nav>
        <ul class="pager">
            <li class="previous"><a href="/<?=Kohana::$config->load('crudconfig.base_url')?>/"><span aria-hidden="true">&larr;</span> <?=__('LANG_PREVIOUS')?></a></li>

        </ul>
    </nav>
</div>

