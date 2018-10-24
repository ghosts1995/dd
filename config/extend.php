<?php


/**
 * 设置信息
 * @param string $msg
 * @param int $code
 * @param array $data
 * @return array
 */
function setMsg($msg = 'system exception', $code = 10001, $data = [], $bool = 1)
{
    return ['bool' => (int) $bool, 'msg' => (string)$msg, 'code' => (int)$code, 'data' => (array)$data];
}

/**
 * 成功返回
 * @param $data
 * @param string $msg
 * @return array
 */
function ok($data = [], $msg = 'ok')
{
    return setMsg($msg, 200, $data);
}

/**
 * 错误返回
 * @param string $msg
 * @param int $code
 * @return array
 */
function error($msg = 'error param', $code = 206)
{
    return setMsg($msg, $code, [], 0);
}

/**
 * 返回结果
 * @param $msg
 * @return array
 */
function results($msg)
{
    if ($msg['bool']) {
        return ok($msg['data']);
    } else {
        return no($msg['msg']);
    }
}


/**
 * 生成code
 * @param int $length
 * @return int
 */
function generateCode($length = 4)
{
    return rand(pow(10, ($length - 1)), pow(10, $length) - 1);
}


/**
 * 判断数组维度
 * @param $vDim
 * @return int
 */
function array_count($vDim)
{
    if (!is_array($vDim)) {
        return 0;
    } else {
        $max1 = 0;
        foreach ($vDim as $item1) {
            $t1 = array_count($item1);
            if ($t1 > $max1) $max1 = $t1;
        }
        return $max1 + 1;
    }
}



###########the dd function


function merge_sort($array, $comparison)
{
    if (count($array) < 2) {
        return $array;
    }
    $middle = ceil(count($array) / 2);
    return merge(merge_sort(slice($array, 0, $middle), $comparison), merge_sort(slice($array, $middle), $comparison), $comparison);
}

function slice($table, $start, $end = null)
{
    $table = array_values($table);
    if ($end) {
        return array_slice($table, $start, $end);
    } else {
        return array_slice($table, $start);
    }
}

function merge($left, $right, $comparison)
{
    $result = array();
    while ((count($left) > 0) && (count($right) > 0)) {
        if (call_user_func($comparison, $left[0], $right[0]) <= 0) {
            $result[] = array_shift($left);
        } else {
            $result[] = array_shift($right);
        }
    }
    while (count($left) > 0) {
        $result[] = array_shift($left);
    }
    while (count($right) > 0) {
        $result[] = array_shift($right);
    }
    return $result;
}


