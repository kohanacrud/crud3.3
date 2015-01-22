<?php defined('SYSPATH') or die('No direct script access.'); ?>


<?php foreach ($styles as $style => $media) echo HTML::style($style, array('media' => $media), NULL, TRUE), "\n" ?>

<?php foreach ($scripts as $script) echo HTML::script($script, NULL, NULL, TRUE), "\n" ?>

<div class="conteiner-crud" style="margin: 1%;">

<?=@$render;?>
</div>
