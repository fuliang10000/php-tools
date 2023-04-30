<?php

namespace Fuliang\PhpTools\Helper;


class TreeHelper
{

    /**
     * 将格式数组转换为基于标题的树（实际还是列表，只是通过在相应字段加前缀实现类似树状结构）.
     * @param array $list
     * @param int $level 进行递归时传递用的参数
     * @param string $title
     * @param array $formatTree 过渡用的中间数组
     * @return array
     */
    public function _toFormatTree(array $list, int $level = 0, string $title = 'title', array &$formatTree = []): array
    {
        foreach ($list as $key => $val) {
            $title_prefix = str_repeat('--------| ', $level);
            $val['level'] = $level;
            $val['title_prefix'] = 0 == $level ? '' : $title_prefix;
            $val['title_show'] = 0 == $level ? $val[$title] : $title_prefix . $val[$title];
            if (!array_key_exists('_child', $val)) {
                array_push($formatTree, $val);
            } else {
                $child = $val['_child'];
                unset($val['_child']);
                array_push($formatTree, $val);
                $this->_toFormatTree($child, $level + 1, $title); //进行下一层递归
            }
            return $formatTree;
        }
    }

    /**
     * 将格式数组转换为树.
     * @param array $list
     * @param string $title
     * @param string $pk
     * @param string $pid
     * @param int $root
     * @param bool $strict
     * @return array
     */
    public function toFormatTree(array $list, string $title = 'title', string $pk = 'id', string $pid = 'pid', int $root = 0, bool $strict = true): array
    {
        $list = $this->listToTree($list, $pk, $pid, '_child', $root, $strict);
        $formatTree = [];
        $this->_toFormatTree($list, 0, $title);

        return $formatTree;
    }

    /**
     * 将数据集转换成Tree（真正的Tree结构）.
     * @param array $list 要转换的数据集
     * @param string $pk ID标记字段
     * @param string $pid parent标记字段
     * @param string $child 子代key名称
     * @param int $root 返回的根节点ID
     * @param bool $strict 默认严格模式
     * @return array
     */
    public function listToTree(array $list, string $pk = 'id', string $pid = 'pid', string $child = '_child', int $root = 0, bool $strict = true): array
    {
        // 创建Tree
        $tree = [];
        // 创建基于主键的数组引用
        $refer = [];
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parent_id = $data[$pid];
            if (null === $parent_id || (string)$root === $parent_id) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parent_id])) {
                    $parent = &$refer[$parent_id];
                    $parent[$child][] = &$list[$key];
                } else {
                    if (false === $strict) {
                        $tree[] = &$list[$key];
                    }
                }
            }
        }

        return $tree;
    }

    /**
     * 将listToTree的树还原成列表.
     * @param array $tree 原来的树
     * @param string $child 孩子节点的键
     * @param string $order 排序显示的键，一般是主键 升序排列
     * @param array $list 过渡用的中间数组，
     * @return array  返回排过序的列表数组
     */
    public function treeToList(array $tree, string $child = '_child', string $order = 'id', array &$list = []): array
    {
        if (is_array($tree)) {
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if (isset($reffer[$child])) {
                    unset($reffer[$child]);
                    $this->treeToList($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = $this->listSortBy($list, $order, $sortBy = 'asc');
        }

        return $list;
    }

    /**
     * 对查询结果集进行排序.
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param string $sortBy 排序类型 asc正向排序 desc逆向排序 nat自然排序
     * @return array
     */
    public function listSortBy(array $list, string $field, string $sortBy = 'asc'): array
    {
        $refer = $resultSet = [];
        foreach ($list as $i => $data) {
            $refer[$i] = &$data[$field];
        }
        switch ($sortBy) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val) {
            $resultSet[] = &$list[$key];
        }

        return $resultSet;
    }
}