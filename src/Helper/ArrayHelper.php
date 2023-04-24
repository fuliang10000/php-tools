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
    function getMakeNum(int $total, int $ceil = 5): array
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
}