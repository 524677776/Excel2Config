<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/14
 * Time: 22:18
 */
class Gen {
    /** @var Gen $inst; */
    static private $inst;

    private function __construct(){}

    static function getInst()
    {
        if(!self::$inst){
            self::$inst = new Gen();
        }

        return self::$inst;
    }

    function load_xls_files()
    {
        include_once(LIBRARY_DIR.'PHPExcel/PHPExcel.php');

        $xls_files = array();
        foreach( glob(XLS_DIR."*.xls*") as $filename ){
            if(is_file($filename)){
                $xls_files[] = PHPExcel_IOFactory::load($filename);
            }
        }

        return $xls_files;
    }

    function process()
    {
        $xls_files = $this->load_xls_files();
        $sheets_data = array();

        /** @var PHPExcel $objPHPExcel */
        foreach( $xls_files as $objPHPExcel ){
                $sheet_names_arr = $objPHPExcel->getSheetNames();
                $ini_classes = GenHelper::getInst()->get_class_from_ini();
                foreach($sheet_names_arr as $k => $sheet_name){
                    if(in_array($sheet_name, $ini_classes)){
                        $objPHPExcel->setActiveSheetIndex($k);
                        $sheets_data[$sheet_name] = $objPHPExcel->getActiveSheet()->removeColumn()->toArray();
                    }
                }
        }

        $this->output($sheets_data);
    }

    function output($sheets_data)
    {
        foreach($sheets_data as $sheet_name => $sheetData){
            $column_en 	 = $sheetData[1];
            $column_flag = $sheetData[2];
            $rows = array_slice($sheetData, 3);
            GenHelper::getInst()->lower_case_column_en($column_en);
            GenHelper::getInst()->row_add_quotation_marks($rows);

            $data = array();
            foreach($rows as $k => $row){
                $php_row = GenHelper::getInst()->row_kick_client($row, $column_flag);
                $raw_row = GenHelper::getInst()->row_add_key($row, $column_en);
                $raw_php_row = GenHelper::getInst()->row_add_key($php_row, $column_en);
                $data = $this->array_merge_rec($data, $this->wrap_set_element($column_flag, $row, $raw_php_row));
                $rows[$k] = $raw_row;
            }

            $php_dir = OUTPUT_DIR.'php/'.date('Y-m-d');
            $json_dir = OUTPUT_DIR.'json/'.date('Y-m-d');
            if(!file_exists($php_dir))
                mkdir($php_dir);

            if(!file_exists($json_dir))
                mkdir($json_dir);

            $this->render_php($data, $sheet_name, $php_dir);
            $this->render_json($rows, $sheet_name, $json_dir);
        }
    }

    function set_element(&$column_flag, $row, $php_data)
    {
        $flag = array_pop($column_flag);
        $key = count($column_flag);
        if($flag == 'pri'){
            if(!$php_data){
                $php_data = $row;
            }
            return $this->set_element($column_flag, $row, array($row[$key] => $php_data));
        }
//
        if( $key > 0 ){
            return $this->set_element($column_flag, $row, $php_data);
        }

        return $php_data;
    }

    function wrap_set_element($column_flag, $row, $php_data)
    {
        return $this->set_element($column_flag, $row, $php_data);
    }

    function array_merge_rec($paArray1, $paArray2)
    {
        if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
        foreach ($paArray2 AS $sKey2 => $sValue2) {
            $paArray1[$sKey2] = $this->array_merge_rec(@$paArray1[$sKey2], $sValue2);
        }

        return $paArray1;
    }

    function render_php($data, $sheet_name, $php_dir)
    {
        $file = $php_dir .'/'.$sheet_name. '.cfg.php';
        $str = '<?php return ';
        $str .= var_export($data, true);
        $str .= ';';
        file_put_contents($file, urldecode($str));
    }

    function render_json($rows, $sheet_name, $json_dir)
    {
        $file = $json_dir .'/' .$sheet_name.'.cfg.json';
        file_put_contents($file, urldecode(json_encode($rows)));
    }
}