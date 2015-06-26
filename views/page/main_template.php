<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?if (!empty($styles)):?>
    <?php foreach ($styles as $style => $media) echo HTML::style($style, array('media' => $media), NULL, TRUE), "\n" ?>
<?endif?>

<?if (!empty($scripts)):?>
    <?php foreach ($scripts as $script) echo HTML::script($script, NULL, NULL, TRUE), "\n" ?>
<?endif?>

<div class="conteiner-crud" style="margin: 1%;">

<?=@$render;?>
</div>
