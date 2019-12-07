<?php

namespace app\common\controller;

use think\Controller;
use think\loader;
use think\Session;

class Common extends Controller
{


    public function _initialize()
    {

    }

    protected function _return($code = '0', $msg = '', $data = [])
    {
        if ($this->request->isAjax()) {
            exit(json_encode(array(
                'code' => $code,
//                'msg' => $msg,
                'data' => $data,
            )));
        } else {
            if($code == 0){
                $this->success($msg);
            }else{
                $this->error($msg);
            }
        }
    }

    protected function ajaxReturn($res)
    {
        echo json_encode($res);
        exit();
    }

    /**
     * 封装检查表单提交字段信息是否符合规则
     * @param type $modelName 模型名称
     * @param type $scene 验证场景
     * @param type $batch 是否批量验证
     * @param type $returnType 1-返回结果 true/false 2-报错或者Ajax返回数据
     * @return type
     */
    protected function checkFormDataReturn($modelName, $scene = false, $batch = false, $callback = false)
    {
        $res = $this->checkFormData($modelName, $scene, $batch, $callback);
        if ($this->request->isAjax()) {
            $this->ajaxReturn($res);
        }
        if ($res["code"] != 0) {
            $this->error($res["msg"]);
        }
    }

    protected function checkFormData($modelName, $scene = false, $batch = false, $callback = false)
    {
        $data = $this->request->param();

        return $this->checkData($data, $modelName, $scene, $batch, $callback);
    }

    protected function checkData($data, $modelName, $scene = false, $batch = false, $callback = false)
    {
        $validate = strpos($modelName, 'Validate') !== false ? $this->{$modelName} : $this->{$modelName . 'Validate'};//Loader::validate($modelName);
        $result = $validate->scene($scene)->batch($batch)->check($data);
        $endRes = true;
        if (!$result) {
            $endRes = false;
            $errorField = $validate->getErrorField();
            $errorMsg = $validate->getError();
        }
        if ($endRes) {
            $return = array(
                "code" => 0,
                "msg" => "验证成功"
            );
        } else {
            $return = array(
                "code" => 1,
                "field" => $errorField,
                "msg" => $errorMsg
            );
        }
        if ($return['code'] == 0 && $callback) {
            return call_user_func($callback);
        }
        return $return;
    }

    protected function generate_random_string($length = 64, $chars = '')
    {
        $chars = $chars != '' ? $chars : 'ab]{}<>~`+cdefghijk_ [=lmnop!@#$%^&*(qrstuvwxy:/2SAD#%!@#&SG@#**zABIJKLMNOPQRST_ [=U6789)-,CDEFGH.;:/?VWXYZ012345|';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $str;
    }


    protected function redirectToTopFramework($url = "/")
    {
        $html = "<script type='text/javascript'>";
        $html .= "if(window.top != undefined){window.top.location.href='" . $url . "'}else{window.location.href='" . $url . "'}";
        $html .= "</script>";
        echo $html;
        exit();
    }


    public function makedir($dir)
    {
        $dir = rtrim($dir, "/");
        $arr = explode("/", $dir);
        $path = '';
        for ($i = 0; $i < count($arr); $i++) {
            $path .= $arr[$i] . '/';
            if (!is_dir($path)) {
                mkdir($path, 0777);
            }
        }
    }


    protected function getTrueQuery($type = "get")
    {
        switch ($type) {
            case "get":
                $query = $this->request->get();
                break;
            default :
                $query = $this->request->get();
                break;
        }
        $path = $this->request->path();
        foreach ($query as $key => $value) {
            if (strpos($key, $path) !== false) {
                unset($query[$key]);
            }
        }
        return $query;
    }

    protected function getTruePaginator($paginator = 30)
    {
        return array(
            "list_rows" => $paginator,
            "query" => $this->getTrueQuery()
        );
    }

    protected function assignCurrentPage()
    {
        $currentPage = $this->getCurrentPage();
        $this->assign("currentPage", $currentPage);
    }

    protected function getCurrentPage()
    {
        return $this->request->param("page", "1");
    }

    /**
     * 一个二维数组排序函数
     */
    protected function erwei_array_sort($arr, $keys, $type = 'asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            if (is_object($v)) {
                $keysvalue[$k] = $v->$keys;
            } else {
                $keysvalue[$k] = $v[$keys];
            }
        }
        if (strtolower($type) == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /**
     * 重写get魔术方法,方便获取model实例
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        return false;
    }

    /**
     * 重写魔术方法isset
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->loadModel($name);
    }

    /**
     * 重写魔术方法set
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * 实例化加载model
     *
     * @param $model
     * @return bool
     */
    public function loadModel($model)
    {
        if (strpos($model, 'Validate') !== false) {
            $this->{$model} = \think\Loader::validate($model);
        } else {
            $this->{$model} = \think\Loader::model($model);
        }

        if (!$this->{$model}) {
            return false;
        }
        return true;
    }

    /**
     * 获取一个时间戳和现在的差值
     *
     * @param int $param
     */
    public function get_interval_from_now($param)
    {
        $now = time();
        return abs($now - intval($param));
    }

    /**
     * 获取指定位数的随机数
     *
     * @param int $length
     * @return string
     */
    protected function genRandomNumber($length = 15)
    {
        $nums = '0123456789';

        $out = $nums[mt_rand(1, strlen($nums) - 1)];


        for ($p = 0; $p < $length - 1; $p++)
            $out .= $nums[mt_rand(0, strlen($nums) - 1)];
        return $out;
    }

    /**
     * 获取大量随机数
     *
     * @param int $digits 每个数字的位数
     * @param int $amount 数量
     * @return mixed
     */
    protected function getBigGroupNumbers($digits, $amount)
    {
        if ($amount <= 0) {
            return false;
        }

        //防止超时和内存溢出
        set_time_limit(0);
        ini_set('memory_limit', '1280M');

        //计数器
        $index = 0;

        //循环生成
        $numbers = [];
        while (true) {
            $g = $this->genRandomNumber($digits);
            if (!isset($numbers[$g])) {
                $numbers[$g] = 1;
                $index++;
            }
            if ($index >= $amount) {
                break;
            }
        }

        return array_keys($numbers);
    }

//导入excel文件
    public function importExcel($filepath, $sheet_arr = array('0'), $first_row = 2, $relation = array(), $other = array())
    {
        header("content-type:text/html;charset=utf-8");
        Loader::import('PHPExcel.PHPExcel');
        Loader::import('PHPExcel.PHPExcel.IOFactory');
        Loader::import('PHPExcel.PHPExcel.Reader.Excel5');
        Loader::import('PHPExcel.PHPExcel.Reader.Excel2007');
        Loader::import('PHPExcel.PHPExcel.Shard.Date');
        $extension = substr($filepath, strrpos($filepath, ".") + 1);
        if ($extension == 'xlsx') {
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        } elseif ($extension == 'xls') {
            $PHPReader = new \PHPExcel_Reader_Excel5();
        }
        $PHPExcel = $PHPReader->load($filepath);
        $return = array();
        foreach ($sheet_arr as $sheet_index) {
            $currentSheet = $PHPExcel->getSheet($sheet_index);
            $allColumn = $currentSheet->getHighestColumn();
            $allRow = $currentSheet->getHighestRow();
            for ($currentRow = $first_row; $currentRow <= $allRow; $currentRow++) {
                $data = array();
                $emptyCount = 0;
                for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                    $address = $currentColumn . $currentRow;
                    $res = (string)$currentSheet->getCell($address)->getValue();
                    $res = trim($res);
                    if (in_array($currentColumn, $other) && is_numeric($res)) {
                        $res = gmdate("Y-m-d H:i:s", \PHPExcel_Shared_Date::ExcelToPHP((string)$currentSheet->getCell($address)->getValue()));
                    }

                    if ($res === "") {
                        $emptyCount++;
                    }
                    if (isset($relation[$currentColumn])) {
                        $data[$relation[$currentColumn]] = $res;
                    } else {
                        $data[$currentColumn] = $res;
                    }
                }
                if ($emptyCount < count($data)) {
                    //不是全部为空值得时候
                    $data["row"] = $currentRow;
                    $return[$sheet_index][] = $data;
                }
            }
        }
        return $return;
    }

    /**
     *
     * @param array $col_name
     * @param array $list
     * @param unknown $filename
     */

    public function exportExcel(Array $col_name, Array $list, $filename = '')
    {
        ob_end_clean();
        if (!is_array($col_name) || !is_array($list)) {
            exit("export excel failed ... ");
        }
        // 总共有多少列
        $col_count = count($col_name);
        // 总共有多少行
        $row_count = count($list);
        if (count($list[0]) != $col_count) {
            exit("columns not equals ... ");
        }
        $cellName = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG',
            'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN',
            'AO', 'AP', 'AQ', 'AR', 'AS', 'AT',
            'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
        );

        if ($col_count > count($cellName)) {
            exit("columns out of range!");
        }
        if (empty($filename)) {
            $filename = "TuTuYa System Excel Export - " . date("YmdHis");
        }
        set_time_limit(0); // 运行时间无限制
        header("content-type:text/html;charset=utf-8");
        // 引入PHPExcel库
        Loader::import('PHPExcel.PHPExcel');

        // 实例化
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator("TuTuYa System")
            ->setLastModifiedBy("TuTuYa System")
            ->setTitle($filename)
            ->setSubject($filename)
            ->setDescription("The document generated by TuTuYa")
            ->setKeywords("TuTuYa System")
            ->setCategory("TuTuYa System");

        // 设置列宽
        for ($i = 0; $i < $col_count; $i++) {
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension($cellName[$i])
                ->setWidth(30);
            $objPHPExcel->getActiveSheet()
                ->getStyle($cellName[$i])
                ->getAlignment()
                ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()
                ->getStyle($cellName[$i])
                ->getAlignment()
                ->setWrapText(true);
        }

        // 合并单元格
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$col_count - 1] . '1'); // 合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '共导出 ' . $row_count . ' 条记录 - Generated by TuTuYa System on ' . date('Y-m-d H:i:s'));
        $objPHPExcel->getActiveSheet()
            ->getStyle('A1')
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        // 设置行高
        $objPHPExcel->getActiveSheet()
            ->getRowDimension('1')
            ->setRowHeight(40);
        // 垂直居中
        $objPHPExcel->getActiveSheet()
            ->getStyle('A1')
            ->getAlignment()
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        // 输出列名
        for ($i = 0; $i < $col_count; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . "2", $col_name[$i]);
            $objPHPExcel->getActiveSheet()
                ->getStyle($cellName[$i] . "2")
                ->getAlignment()
                ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }
        $objPHPExcel->getActiveSheet()
            ->getRowDimension('2')
            ->setRowHeight(20);
        // 开始导出数据
        for ($i = 3; $i <= ($row_count + 2); $i++) {
            $objPHPExcel->getActiveSheet()
                ->getRowDimension($i)
                ->setRowHeight(-1);
            $row_data = $list[$i - 3];
            $j = 0;
            foreach ($row_data as $v) { // 插入一行数据
                if (!is_numeric($v)) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$j] . $i, $v);
                } else {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$j] . $i, ' ' . $v);
                }
                $objPHPExcel->getActiveSheet()
                    ->getStyle($cellName[$j] . $i)
                    ->getAlignment()
                    ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $j++;
            }
            unset($row_data);
        }
        // 输出文件
        $write = new \PHPExcel_Writer_Excel5($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        $filename = iconv('UTF-8', 'GBK//IGNORE', $filename);
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $filename . '.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
        exit();
    }


    /**
     * 发起异步http请求
     * @param string $url
     * @param string|array $post_string
     */
    public function postAsync($url, $post_string)
    {
        if (is_array($post_string)) {
            foreach ($post_string as $key => &$val) {
                if (is_array($val))
                    $val = implode(',', $val);
                $post_params[] = $key . '=' . urlencode($val);
            }
            $post_string = implode('&', $post_params);
        }
        $parts = parse_url($url);
        $fp = @fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

        $out = "POST " . $parts['path'] . "?" . $parts['query'] . " HTTP/1.1\r\n";
        $out .= "Host: " . $parts['host'] . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if (isset($post_string))
            $out .= $post_string;

        @fwrite($fp, $out);
        @fclose($fp);
    }

}
