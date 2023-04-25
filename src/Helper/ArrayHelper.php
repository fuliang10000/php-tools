<?php

namespace Fuliang\PhpTools\Helper;


class ArrayHelper
{
    /**
     * 将一个数组加入到另一个数组的指定位置.
     *
     * @param array $array 原数组
     * @param int $position 加入的位置
     * @param array $insertArray 要加入的数组
     *
     * For example,
     *
     * ```php
     * $array = [
     *    [1, 2, 3],
     *    [4, 5, 6],
     *    [7, 8, 9]
     * ];
     * $position = 1;
     * $insertArray = [
     *    ['a', 'b', 'c'],
     *    ['d', 'e', 'f']
     * ];
     *
     * // the result is:
     * $array = [
     *    [1, 2, 3],
     *    ['a', 'b', 'c'],
     *    ['d', 'e', 'f'],
     *    [4, 5, 6],
     *    [7, 8, 9]
     * ];
     * ```
     * @return array
     */
    public function arrayInsert(array &$array, int $position, array $insertArray):array
    {
        $first_array = array_splice($array, 0, $position);
        $array = array_merge($first_array, $insertArray, $array);

        return $array;
    }

    /**
     * 根据总数和取模数，获取取模为0的数.
     *
     * @param int $total 总数
     * @param int $ceil 取模数 默认：5
     *
     * For example,
     *
     * ```php
     * $total = 20;
     * $ceil = 5;
     *
     * // the result is:
     * // [5, 10, 15, 20]
     * ```
     * @return array
     */
    public function getMakeNum(int $total, int $ceil = 5): array
    {
        $returnArr = [];
        $i = 1;
        while ($i <= $total) {
            if (0 == $i % $ceil) {
                array_push($returnArr, $i);
            }
            ++$i;
        }

        return $returnArr;
    }

    /**
     * 数组分页函数.
     *
     * @param array $array 查询出来的所有数组
     * @param int $page 当前第几页
     * @param int $count 每页多少条数据
     * @return array [需要的数据,总页数,总记录数]
     *
     */
    public function arrayPage(array $array, int $page = 1, int $count = 10): array
    {
        global $totalPage;

        // 判断当前页面是否为空 如果为空就表示为第一页面
        $page = (empty($page) || $page <= 1) ? 1 : $page;

        // 计算每次分页的开始位置
        $start = ($page - 1) * $count;

        $total = count($array);

        // 计算总页面数
        $totalPage = ceil($total / $count);

        // 拆分数据
        $list = array_slice($array, $start, $count);

        return [$list, $totalPage, $total];
    }

    /**
     * 多位数组排序.
     *
     * @param array $arr
     * @param string $key
     * @param int $sort_order
     * @param int $sort_type
     *
     * @return array
     */
    public function arrayMultiSort(array $arr, string $key, int $sort_order = SORT_DESC, int $sort_type = SORT_NUMERIC): array
    {
        foreach ($arr as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$key];
            }
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arr);

        return $arr;
    }
}