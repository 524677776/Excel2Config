<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/14
 * Time: 22:49
 */

class GenHelper {
    static private $inst;

    private function __construct(){}

    static function getInst()
    {
        if(!self::$inst){
            self::$inst = new GenHelper();
        }

        return self::$inst;
    }

    function get_base_name($path_full)
    {
        $path_parts = pathinfo($path_full);
        return $path_parts['basename'];
    }

    function get_class_from_ini()
    {
        $ret = array();
        $handle = fopen(CONFIG_FILE, "r");
        $contents = fread($handle, filesize(CONFIG_FILE));
        fclose($handle);

        if($contents){
            $ret = explode(';', $contents);
        }

        foreach($ret as &$r){
            $r = trim($r);
        }

        return $ret;
    }

    function lower_case_column_en(&$column_en){
        foreach ($column_en as &$item_name) {
            $item_name = strtolower($item_name);
            $item_name = trim($item_name);
        }
    }

    //将 字符串转为 引号 字符串 (字符串外面加引号的意思)
    function row_add_quotation_marks(&$rows){
        //将 字符串转为 引号 字符串 (字符串外面加引号的意思)
        foreach ($rows as &$item_name) {
            foreach ($item_name as $re => &$real_cell) {
                if(is_string($real_cell)){
                    if(strpos($real_cell, '#') != false){
                        unset($item_name[$re]);
                        list($k, $v) = explode('#', $real_cell);
                        $item_name[$k] = $v;
                    }elseif(strpos($real_cell, ';') != false){
                        $real_cell = explode(';', $real_cell);
                        foreach($real_cell as $key => $cel){
                            if(strpos($cel, '#') === false) continue;
                            list($k, $v) = explode('#', $cel);
                            unset($real_cell[$key]);
                            $real_cell[$k] = $v;
                        }
                    }else{
                        $real_cell = urlencode($real_cell);
                    }
                }
            };
        }
    }

    function row_kick_client($row, $column_flag)
    {
        foreach($column_flag as $k => $flag){
            if($flag == 'client' && isset($row[$k])){
                unset($row[$k]);
            }
        }

        return $row;
    }

    function row_add_key($row, $column_en)
    {
        $ret = array();
        foreach ($row as $k => $v) {
            if(isset($column_en[$k]) && !empty($column_en[$k]) )
                $ret[$column_en[$k]] = $v;
        }

        return $ret;
    }
}