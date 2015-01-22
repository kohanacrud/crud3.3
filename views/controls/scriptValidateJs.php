<?php defined('SYSPATH') or die('No direct script access.'); ?>
<?
/**
 * Created by PhpStorm.
 * User: Vitalik
 * Date: 10.01.2015
 * Time: 12:03
 */
?>

<style>

    label.error {
        font-weight: bold;
        color: #BB4442;
        padding: 2px 8px;
        margin-top: 2px;
    }

</style>
<script>

    $(document).ready(function(){

        var field_property = <?=$json_rules?>;
        var field_message = <?=$json_messages?>;


        $('#w-form-add, #w-form-edit').validate({
            rules: field_property,

            messages: field_message,

            highlight: function(element) {
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                $(element).closest('.form-group').find('.glyphicon-ok').hide();
                $(element).closest('.form-group').find('.glyphicon-remove').show();
            },
            success: function(element) {
                element.addClass('valid').closest('.form-group').removeClass('has-error').addClass('has-success');
                element.addClass('valid').closest('.form-group').find('.glyphicon-remove').hide();
                element.addClass('valid').closest('.form-group').find('.glyphicon-ok').show();
            }

        });

    });



</script>