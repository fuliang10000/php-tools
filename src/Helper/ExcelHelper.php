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
}