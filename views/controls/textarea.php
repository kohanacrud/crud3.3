<?php defined('SYSPATH') or die('No direct script access.'); ?>


<textarea <?=$attr?> class="form-control <?if ($disable_editor_class != '') echo $disable_editor_class //добавляем клас если полю не отключен редактор?>"
        name="<?=$name_fied?>"
        id="<?=$name_fied?>"><?if (!empty($value_fild)) echo $value_fild?></textarea>
<span class="glyphicon glyphicon-remove form-control-feedback" style="display: none"></span>
<span class="glyphicon glyphicon-ok form-control-feedback" style="display: none"></span>