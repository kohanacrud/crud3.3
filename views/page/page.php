<?php defined('SYSPATH') or die('No direct script access.'); ?>


<script>


    $(document).ready( function () {





        var table = $('#example').dataTable({
            "pagingType": "full_numbers",
            "processing": true,
            "serverSide": true,
            "sLengthSelect": "form-control",
            "sDom": '<"top"l<?=$table_propery['activ_operation']['search']?><?=$table_propery['activ_operation']['enable_export']?>>rt<"bottom"ip><"clear">',//<"clear">

            /*
             l - Показать  записей
             f - поиск
             rt - таблица
             ip - итформация и  пагинация
            */

            ///http://192.168.0.10:7799/admin/media/js/DataTables-1.10.0/extensions/TableTools/swf/copy_csv_xls_pdf.swf
            "tableTools": {
                "sSwfPath": "/<?=Kohana::$config->load('crudconfig.base_url')?>/media/js/DataTables-1.10.0/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
                "aButtons": [

                    "csv",
                    "xls",
                    "pdf",
                    {
                        "sExtends":     "copy",
                        "sButtonText": "<?=__('LANG_BUTTON_COPY')?>"
                    }
                ]


            },

            "bAutoWidth": false,

            "oLanguage": {
                "sProcessing": "<img src='<?php echo Route::url('docs/media', array('file' => 'img/loader.GIF')) ?>'>",
                "sZeroRecords": "<?=__('LANG_NO_RECORD')?>",
                "sInfo": "<?=__('LANG_INFO')?>",
                "sLengthMenu": "<?=__('LANG_MENY')?>",
                "sSearch": "<?=__('LANG_SEARCH')?>",
                "sInfoEmpty": "No entries to show",




                "oPaginate": {
                    "sNext": "<?=__('LANG_NEXT')?>",
                    "sLast": "<?=__('LANG_LAST')?>",
                    "sFirst": "<?=__('LANG_FIRST')?>",
                    "sPrevious": "<?=__('LANG_PREVIONS')?>"

                }
            },

            "aoColumnDefs": [

                <?if ($table_propery['activ_operation']['enable_delete_group']):?>
                    {

                        "bSortable": false,
                        "bSearchable": false,
                        "aTargets": [0]

                    }
                <?endif?>

                <?if (($table_propery['activ_operation']['edit']!= true or $table_propery['add_action_url_icon'] != '' or $table_propery['activ_operation']['delete'] != true) AND ($table_propery['activ_operation']['enable_delete_group'])):?>
                    ,
                <?endif?>

                <?if ($table_propery['activ_operation']['edit']!= true or $table_propery['add_action_url_icon'] != '' or $table_propery['activ_operation']['delete'] != true):?>
                    {
                        "aTargets": [-1],
                        "bSortable": false,
                        "bSearchable": false,
                        "sWidth": "25%"
                    }
                <?endif?>

            ],

            "ajax": {
                "url": "/<?=Kohana::$config->load('crudconfig.base_url')?>/ajax/showTableAjax",

                "data": function ( d ) {
                    d.obj = $('input[name="obj"]').val();

                }
            },
            //после выполнения ajax запроса
            "fnInitComplete" : function () {

                //this.fnPageChange(parseInt($('.w-number-page').val()));
               // this.fnPageChange(2);

            }




        });


        table.on( 'page.dt',   function (e) {

            var page = table.fnSettings()._iDisplayStart / table.fnSettings()._iDisplayLength + 1;
            $('.w-number-page').val(page -1);

        });


        $('#table-crud').DataTable({
            "pagingType": "full_numbers"

        });

        //удаление group
        $('.w-del-array').click(function(){

            if ($(this).hasClass('delete-query')) {

                $('#myModal-group').modal('hide');

                var chek = $('.w-chec-table:checked').serialize();
                var objecr = $('input[name="obj"]').val();
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: "/<?=Kohana::$config->load('crudconfig.base_url')?>/delete",
                    data: chek+"&obj="+objecr+"&del_arr=1",
                    success: function(response) {

                    }
                });


                var inD = new Array();

                $('tr').each(function(i){
                    i = i-2;
                    if ($(this).hasClass('selected')) {
                        inD.push(i);
                    }
                });

                table.fnDeleteRow(inD);

            } else {

                $('#myModal-group').modal('show');
            }

            return false;
        });



        //delete
        $(document).on('click', '.delete', function(){

            if ($(this).hasClass('delete-query')) {

                $('#myModal').modal('hide');

                var chek = $('.serialise-form').val();

                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: "/<?=Kohana::$config->load('crudconfig.base_url')?>/delete",
                    data: chek,
                    success: function(response) {

                    }
                });

                var inDb;
                $('tr').each(function(i){
                    i = i-2;
                    if ($('.del-fal').hasClass('sends')) {
                        inDb = i;
                    }
                });

                table.fnDeleteRow([inDb],function (dtSettings, row) {


                }, true);


            } else {

                chek = $(this).parent().serialize();
                $('.serialise-form').val(chek);
                $(this).parent().parent().parent().parent().addClass('sends');
                $('#myModal').modal('show');

            }

            return false;

        });




        //edit
//        $(document).on('click', '.edit', function(){
//            $.ajax({ // описываем наш запрос
//                type: "GET", // будем передавать данные через POST
//                dataType: "HTML", // указываем, что нам вернется JSON
//                url: "/<?=Kohana::$config->load('crudconfig.base_url')?>/edit", // запрос отправляем на контроллер Ajax метод addarticle
//                data: "obj="+$(this).data('obj')+"&id="+$(this).data('id'), // передаем данные из формы
//                success: function(response) { // когда получаем ответ
//                    //alert(response);
//                    //console.log(response);
//                    $('.conteiner-crud').html(response);
//                    //table.row($(this)).remove().draw(false);
//                    //$('.w-ter').text(response.test);
//                }
//            });
//
//
//            return false;
//        });




        //cecbox all
        $(document).on('click', '.w-chec-table-all-top', function(){

            if ($(this).prop('checked')) {

                $('.w-chec-table').each(function(i){

                    if ($(this).prop('checked') != true){
                        $(this).trigger('click');
                    }

                });

            } else {
                $('.w-chec-table').trigger('click');
            }

            state = $(this).prop('checked');

            if(state) {
                $('.w-del-array').prop('disabled', false);
            } else {
                $('.w-del-array').prop('disabled', true);
            }
        });




        //чекбокси построчно
        $(document).on('click', '.w-chec-table', function(){

            $(this).each(function(i){

                if ($(this).prop('checked') != true){
                    $('.w-chec-table-all-top').prop('checked', false);
                }

            });

            state = $(this).prop('checked');

            $(this).parents('tr').toggleClass('selected');

            if(state) {
                $('.w-del-array').prop('disabled', false);
            } else {
                $('.w-del-array').prop('disabled', true);
            }
        });


    });



</script>

<!--add-->

<?if ($table_propery['activ_operation']['enable_delete_group']):?>

    <div class="modal fade" id="myModal-group" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?=__('LANG_MODAL_DELETE_TITLE')?></h4>
                </div>
                <div class="modal-body">
                    <?=__('LANG_MODAL_DELETE_MSG')?>
                </div>
                <div class="modal-footer">
                    <input type="hidden" class="serialise-form" value="">
                    <button type="button" class="delete-query w-del-array btn btn-primary"><?=__('LANG_MODAL_BUTON_DELETE')?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?=__('LANG_MODAL_BUTON_CENSEL')?></button>
                </div>
            </div>
        </div>
    </div>

<?endif?>




<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?=__('LANG_MODAL_DELETE_TITLE')?></h4>
            </div>
            <div class="modal-body">
                <?=__('LANG_MODAL_DELETE_MSG')?>
            </div>
            <div class="modal-footer">
                <input type="hidden" class="serialise-form" value="">
                <button type="button" class="delete-query delete btn btn-primary"><?=__('LANG_MODAL_BUTON_DELETE')?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=__('LANG_MODAL_BUTON_CENSEL')?></button>
            </div>
        </div>
    </div>
</div>


<input type="hidden" class="w-number-page" value="0"/>


<?if ($table_propery['activ_operation']['add'] != true ):?>

    <div class="w-buton-befor-table">
        <form action="/<?=Kohana::$config->load('crudconfig.base_url')?>/add" method="get">
            <input type="hidden" name="obj" value="<?=$table_propery['obj_serial']?>"/>
            <button type="submit" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-plus-sign"></span> <?=__('LAND_ADD')?></button>
        </form>
    </div>

<?endif?>


<?if ($table_propery['activ_operation']['enable_delete_group']):?>

    <div class="w-buton-befor-table">
            <input type="hidden" name="obj" value="<?=$table_propery['obj_serial']?>"/>
            <input type="hidden" name="del_arr" value="1">
            <button type="submit" disabled class="delete btn btn-danger btn-sm w-del-array"><span class="glyphicon glyphicon-remove-circle"></span> <?=__('LANG_DELETE')?></button>
    </div>

<?endif?>


<table id="example" class="display">
    <thead>

    <tr>
        <?if ($table_propery['activ_operation']['enable_delete_group']):?>
            <th><input type="checkbox" class="w-chec-table-all-top"></th>
        <?endif?>

        <?foreach ($table_propery['name_colums_table_show'] as $rows_column):?>
            <th>
                <?=isset($rows_column['COLUMN_NAME']) ? $rows_column['COLUMN_NAME']: ''?>
            </th>
        <?endforeach?>

        <?if ($table_propery['activ_operation']['edit']!= true or $table_propery['add_action_url_icon'] != '' or $table_propery['activ_operation']['delete'] != true):?>
            <th></th>
        <?endif?>
    </tr>
    </thead>

    <tfoot>
    <tr>
        <?if ($table_propery['activ_operation']['enable_delete_group']):?>
            <th>#</th>
        <?endif?>

        <?foreach ($table_propery['name_colums_table_show'] as $rows_column):?>
        <th>
            <?=isset($rows_column['COLUMN_NAME']) ? $rows_column['COLUMN_NAME'] : ''?>
        </th>
        <?endforeach?>

        <?if ($table_propery['activ_operation']['edit']!= true or $table_propery['add_action_url_icon'] != '' or $table_propery['activ_operation']['delete'] != true):?>
            <th></th>
        <?endif?>

    </tr>
    </tfoot>
</table>
