<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="w-buton-form">
    <form action="/<?=Kohana::$config->load('crudconfig.base_url')?>/new/<?=$url?>" method="post">
        <input type="hidden" name="obj" value="<?=$obj?>"/>
        <input type="hidden" name="func" value="<?=$name_function?>">
        <input type="hidden" name="id" value="<?=$id?>"/>
        <button type="submit" class="new-action btn btn-primary btn-sm"><span class="<?=$icon?>"></span> <?=$name_action?></button>
    </form>
</div>