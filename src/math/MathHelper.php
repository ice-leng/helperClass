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
     * 等比列累加(22元素相加排列)
     *
     * @param array   $arr   需要排列求和数据
     * @param boolean $isLog 是否展示log
     *
     * @return array [
     *                  'resetData' => [1,2,3]//去重后的 等比差排列
     *                  'data' =>   [1 => [1=>1]]// 显示 排列组合
     *              ]
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function addTOArrange(array $arr, $isLog = false)
    {
        // 等比差排列累加数据
        $log = [];
        $length = count($arr);
        $arrangementClassRoomSeatData[1][$length] = $arr;
        if ($isLog) {
            $log[1][$length] = array_keys($arr);
        }
        for ($time = $length; $time >= 1; --$time) {
            $count = count($arrangementClassRoomSeatData);
            $start = $len = ($time - 1);
            $level = $count + 1;
            $j = -1;
            $k = 0;
            for ($l = $start; $l >= 1; --$l) {
                $j++;
                if ($j !== 0 && $level !== 2) {
                    if ($k === 0) {
                        $k = $len + 1;
                    } else {
                        $k += ($len + 2 - $j);
                    }
                }
                for ($i = 1; $i <= $l; $i++) {
                    if ($level !== 2) {
                        $oneValue = $arrangementClassRoomSeatData[$level - 1][$len + 1][$k];
                    } else {
                        $oneValue = $arr[$j];
                    }
                    $arrangementClassRoomSeatData[$level][$len][] = $oneValue + $arr[($i + $level + $j - 2)];
                    if ($isLog) {
                        $log[$level][$len][] = [
                            $level !== 2 ? $log[$level - 1][$len + 1][$k] : $j,
                            $i + $level + $j - 2,
                        ];
                    }
                }
            }
        }
        $resetData = [];
        foreach ($arrangementClassRoomSeatData as $data) {
            $resetData = array_merge($resetData, array_shift($data));
        }
        $resetData = array_unique($resetData);
        $d = [
            'resetData' => $resetData,
            'data'      => $arrangementClassRoomSeatData,
        ];
        if ($isLog) {
            $d['log'] = $log;
        }
        return $d;
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
                if ($j !== 0 && $level !== 2) {
                    if ($k === 0) {
                        $k = $len + 1;
                    } else {
                        $k += ($len + 2 - $j);
                    }
                }
                for ($i = 1; $i <= $l; $i++) {
                    if ($level !== 2) {
                        $oneValue = $data[$level - 1][$len + 1][$k];
                    } else {
                        $oneValue = $arr[$j];
                    }
                    if (is_array($oneValue)) {
                        $data[$level][$len][] = array_merge($oneValue, [$arr[$i + $level + $j - 2]]);
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