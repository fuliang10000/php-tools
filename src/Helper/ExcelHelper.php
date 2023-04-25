<?php


namespace Fuliang\PhpTools\Helper;


class ExcelHelper
{

    /**
     * 用户fputcsv导出excel.
     *
     * @param string $fileName 文件名称
     * @param array $heads 头列表
     * @param array $data 数据
     */
    public function exportCsv(string $fileName, array $heads, array $data)
    {
        // 不限定时间
        set_time_limit(0);
        // 内存限定
        ini_set('memory_limit', '1024M');
        // 输出Excel文件头
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename = {$fileName}" . '.csv');
        header('Cache-Control: max-age=0');

        // 打开PHP文件句柄，php://output 表示直接输出到浏览器
        $fp = fopen('php://output', 'a');
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM
        // 输出Excel列名信息
        array_walk($heads, [StrHelper::class, 'strIconv']);
        // 将数据通过fputcsv写到文件句柄
        fputcsv($fp, $heads);

        // 输出Excel内容
        foreach ($data as $one) {
            array_walk($one, 'str_iconv');
            fputcsv($fp, $one);
        }

        fclose($fp);
        exit;
    }

    /**
     * 导出excel.
     *
     * @param [type] $fileName                                                                                            [文件名]
     * @param [type] $arr_field                                                                                           [excel 的title字段]
     * @param [type] $arr_list                                                                                            [ 导出的数组数据]
     * @param [type] $k_time                                                                                              [ 要格式化转换时间的字段]
     * @param [type] $array_keys                                                                                          [要导出数组的 键名  keys]
     * @param [type] $model                                                                                               array[列=>宽度]
     * @param [type] $title                                                                                               sheet名称
     * @param [type] $statistics                                                                                          统计头数组 array(array（"A1数据","B1数据"...）...)
     * @param [type] $list_title_index                                                                                    列表头的行数
     * @param [type] $style=array("A1"=>array("align"=>"center,left,right","weight"=>'bold'),"height"=array("3"=>"25"..))
     * @author fuliang
     *
     */
    public function exportExcel($fileName, $arr_field, $arr_list, $array_keys, $k_time = 'createtime', $model = [], $title = null, $statistics = [], $list_title_index = 1, $style = [])
    {
        // 加载PHPExcel.php
        header('Content-type:text/html;charset=utf-8');
        if (empty($arr_list) || !is_array($arr_list)) {
            echo '<script>
                        alert("数据必须是数组，且不能为空！");
                        history.go("-1");
                    </script>';
            exit;
        }
        if (empty($fileName)) {
            exit('文件名不能为空');
        }
        // 设置文件名
        $date = date('Y_m_d', time());
        $fileName .= "_{$date}.xlsx";
        //新建
        $resultPHPExcel = new \PHPExcel();
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp; //保存在php://temp
        $cacheSettings = [' memoryCacheSize ' => '80MB'];
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $countList = count($arr_list);
        $countField = count($arr_field);
        $abc = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        //头部统计
        if (!empty($statistics)) {
            foreach ($statistics as $index => $value) {
                foreach ($value as $k => $v) {
                    $resultPHPExcel->getActiveSheet()->setCellValue($abc[$k] . ($index + 1), $v);
                }
            }
        }
        // 设置文件title
        for ($i = 0; $i < $countField; ++$i) {
            $resultPHPExcel->getActiveSheet()->setCellValue($abc[$i] . $list_title_index, $arr_field[$i]);
        }
        // 设置单元格内容
        for ($i = 0; $i < $countList; ++$i) {
            for ($o = 0; $o < $countField; ++$o) {
                if ($array_keys[$o] == $k_time) {
                    $resultPHPExcel->getActiveSheet()->setCellValue($abc[$o] . ($i + $list_title_index + 1), date('Y-m-d H:i:s', $arr_list[$i][$array_keys[$o]]));
                } else {
                    $resultPHPExcel->getActiveSheet()->setCellValue($abc[$o] . ($i + $list_title_index + 1), @$arr_list[$i][$array_keys[$o]]);
                }
            }
        }
        //设置sheet的title
        if (!empty($title)) {
            $resultPHPExcel->getActiveSheet()->setTitle($title);
        }
        //设置列宽度
        if (count($model) > 0) {
            foreach ($model as $k => $v) {
                $resultPHPExcel->getActiveSheet()->getColumnDimension($k)->setWidth($v);
            }
        } else {
            for ($o = 0; $o < $countField; ++$o) {
                $resultPHPExcel->getActiveSheet()->getColumnDimension($abc[$o])->setAutoSize(true);
            }
        }
        //设置样式
        if (count($style) > 0) {
            foreach ($style as $k => $arr) {
                foreach ($arr as $key => $value) {
                    //行的高度
                    if ('height' == $k) {
                        $resultPHPExcel->getActiveSheet()->getRowDimension($key)->setRowHeight($value);
                    }
                    //文字对齐方式
                    if ('align' == $key) {
                        if ('center' == $value) {
                            $resultPHPExcel->getActiveSheet()->getStyle($k)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //左右居中
                            $resultPHPExcel->getActiveSheet()->getStyle($k)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); //垂直居中
                        }
                    }
                    //加粗
                    if ('weight' == $key) {
                        if ('bold' == $value) {
                            $resultPHPExcel->getActiveSheet()->getStyle($k)->getFont()->setBold(true);
                        }
                    }
                }
            }
        }
        //设置导出文件名
        $outputFileName = $fileName;
        $xlsWriter = new \PHPExcel_Writer_Excel2007($resultPHPExcel);
        $xlsWriter->setOffice2003Compatibility(true);
        ob_end_clean(); //清除缓冲区  避免乱码
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Disposition:inline;filename="' . $outputFileName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        $xlsWriter->save('php://output');
        exit;
    }



    /**
     * 导入Excel.
     *
     * @param $fileName
     * @param string $encode
     * @return array
     * @throws PHPExcel_Exception
     * @throws Exception
     * @author fuliang
     *
     */
    public function importExcel($fileName, $encode = 'utf-8')
    {
        $excelData = [];

        if (!file_exists($fileName)) {
            return $excelData;
        }

        header("Content-type:text/html;charset={$encode}");
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        if (!$objReader->canRead($fileName)) {
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($fileName);

        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

        for ($row = 2; $row <= $highestRow; ++$row) {
            for ($col = 0; $col < $highestColumnIndex; ++$col) {
                $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }

        return $excelData;
    }
}