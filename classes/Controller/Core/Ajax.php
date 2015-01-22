<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by PhpStorm.
 * User: Vitalik
 * Date: 31.05.14
 * Time: 17:18
 */

class Controller_Core_Ajax extends Controller {

    public function action_showTableAjax () {

        $re = unserialize(base64_decode($_GET['obj']));

        //переиницыализация круда
        $retw = call_user_func(array($re['callback_functions_array']['class'],
            $re['callback_functions_array']['function']));
        $retw->select_table();
        $retw->ajax_query($_GET);

    }

    //метод для вывода статики
    public function action_media () {

        $file = $this->request->param('file');


        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $file = substr($file, 0, -(strlen($ext) + 1));

//        die(print_r($file));

        $file = Kohana::find_file('media', $file, $ext);

        $this->check_cache(sha1($this->request->uri()).filemtime($file), $this->request);

        $this->response->body(file_get_contents($file));

    }

}