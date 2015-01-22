<?php defined('SYSPATH') or die('No direct script access.'); ?>
<?
/**
 * Created by PhpStorm.
 * User: Vitalik
 * Date: 09.01.2015
 * Time: 22:18
 */
?>

<div class="w-buton-form">
    <form action="/<?=Kohana::$config->load('crudconfig.base_url')?>/show_views" method="get">
        <input type="hidden" name="obj" value="<?=$obj?>"/>
        <input type="hidden" name="id" value="<?=$id?>"/>
        <button type="submit" data-obj="<?=$obj?>" data-id="<?=$id?>" class="views btn btn-info btn-sm">
            <?if ($icon_class !== null){?><span class="glyphicon <?=$icon_class?>" aria-hidden="true"></span><? } else { echo __('LANG_SHOW_VIEWS'); } ?>
        </button>
    </form>
</div>