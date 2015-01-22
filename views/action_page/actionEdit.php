<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="w-buton-form">
    <form action="/<?=Kohana::$config->load('crudconfig.base_url')?>/edit" method="get">
        <input type="hidden" name="obj" value="<?=$obj?>"/>
        <input type="hidden" name="id" value="<?=$id?>"/>
        <button type="submit" data-obj="<?=$obj?>" data-id="<?=$id?>" class="edit btn btn-success btn-sm">
            <?if ($icon_edit == null):?>
                <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> <?=__('LANG_EDIT')?>
            <?else:?>
                <span class="glyphicon <?=$icon_edit?>" aria-hidden="true"></span>
            <?endif?>
        </button>
    </form>
</div>