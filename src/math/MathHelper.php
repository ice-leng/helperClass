<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/4/1
 * Time: 上午11:05
 */

namespace lengbin\helper\math;


class MathHelper
{
    /**
     * 等比差排列累加
     *
     * @param array $arr 需要排列求和数据  array $arr [1, 2, 3, 4]
     *
     * @return array [
     *                  'resetData' => [1,2,3]//去重后的 等比差排列
     *                  'data' =>   [1 => [1=>1]]// 显示 排列组合
     *              ]
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function equalToTheDifferenceAccumulate(array $arr)
    {
        // 等比差排列累加数据
        $length = count($arr);
        $result[1][$length] = $arr;
        for ($time = $length; $time >= 1; --$time) {
            $count = count($result);
            $start = $len = ($time - 1);
            $level = $count + 1;
            $j = -1;
            $k = 0;
            for ($l = $start; $l >= 1; --$l) {
                $j++;
                if ($j != 0 && $level != 2) {
                    if ($k == 0) {
                        $k = $len + 1;
                    } else {
                        $k += ($len + 2 - $j);
                    }
                }
                for ($i = 1; $i <= $l; $i++) {
                    if ($level != 2) {
                        $oneValue = $result[($level - 1)][$len + 1][$k];
                    } else {
                        $oneValue = $arr[$j];
                    }
                    $result[$level][$len][] = $oneValue + $arr[($i + $level + $j - 2)];
                }
            }
        }
        $resetData = [];
        foreach ($result as $data) {
            $resetData = array_merge($resetData, array_shift($data));
        }
        $resetData = array_unique($resetData);
        return [
            'resetData' => $resetData,
            'data'      => $result,
        ];
    }

    /**
     * 冒泡排序  从大到小
     *
     * @param array $array             [
     *                                 [1, 12],
     *                                 [2, 6],
     *                                 [3, 5],
     *                                 [4, 12],
     *                                 [5, 19],
     *                                 [6, 17],
     *                                 [7, 15],
     *                                 [8, 14],
     *                                 ];
     *
     * @param int   $compareKey        需要对比的值
     * @param bool  $sortMax           从大到小 还是 从小到大
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function bubbleSort(array $array, $compareKey = 0, $sortMax = true)
    {
        $length = count($array);
        for ($i = 1; $i < $length; $i++) {
            for ($j = 0; $j < ($length - $i); $j++) {
                if ($sortMax) {
                    if (is_array($array[$j])) {
                        if ($array[$j][$compareKey] < $array[$j + 1][$compareKey]) {
                            $arr = $array[$j + 1];
                            $array[$j + 1] = $array[$j];
                            $array[$j] = $arr;
                        }
                    } else {
                        if ($array[$j] < $array[$j + 1]) {
                            $arr = $array[$j + 1];
                            $array[$j + 1] = $array[$j];
                            $array[$j] = $arr;
                        }
                    }
                } else {
                    if (is_array($array[$j])) {
                        if ($array[$j][$compareKey] > $array[$j + 1][$compareKey]) {
                            $arr = $array[$j + 1];
                            $array[$j + 1] = $array[$j];
                            $array[$j] = $arr;
                        }
                    } else {
                        if ($array[$j] > $array[$j + 1]) {
                            $arr = $array[$j + 1];
                            $array[$j + 1] = $array[$j];
                            $array[$j] = $arr;
                        }
                    }
                }
            }
        }
        return $array;
    }

    /**
     * 2个数组的排列
     * 幂的排列
     *
     * @param array $baseNumbers 底数 数组
     * @param array $powers      幂 数组
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function powerCombination($baseNumbers, $powers)
    {
        $result = [];
        $baseNumberCount = count($baseNumbers);
        $powerCount = count($powers);
        $index = array_fill(0, $powerCount, 0);
        $pow = pow($baseNumberCount, $powerCount);
        for ($i = 1; $i <= $pow; $i++) {
            $data = [];
            if ($i != 1) {
                foreach ($index as $k => $v) {
                    if ((($v + 1) % $baseNumberCount) == 0) {
                        $index[$k] = 0;
                        continue;
                    }
                    $index[$k] += 1;
                    break;
                }
            }
            foreach ($powers as $key => $power) {
                $data[$power] = $baseNumbers[$index[$key]];
            }
            $result[] = $data;
        }
        return $result;
    }

    /**
     * 排列
     *
     * @param array $arr [1, 2, 3, 4]
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function arrangement($arr)
    {
        $length = count($arr);
        $data[1][$length] = $arr;
        $results[1] = $arr;
        for ($time = $length; $time >= 1; --$time) {
            $count = count($data);
            $start = $len = ($time - 1);
            $level = $count + 1;
            $j = -1;
            $k = 0;
            for ($l = $start; $l >= 1; --$l) {
                $j++;
                if ($j != 0 && $level != 2) {
                    if ($k == 0) {
                        $k = $len + 1;
                    } else {
                        $k += ($len + 2 - $j);
                    }
                }
                for ($i = 1; $i <= $l; $i++) {
                    if ($level != 2) {
                        $oneValue = $data[($level - 1)][$len + 1][$k];
                    } else {
                        $oneValue = $arr[$j];
                    }
                    if (is_array($oneValue)) {
                        $data[$level][$len][] = array_merge($oneValue, [$arr[($i + $level + $j - 2)]]);
                    } else {
                        $data[$level][$len][] = [$oneValue, $arr[($i + $level + $j - 2)]];
                    }
                }
                $results[$level] = $data[$level][$len];
            }
        }
        return $results;
    }

}