<?php

use yii\db\ActiveRecord;

function getSetting($id)
{
    return Yii::$app->getModule('setting')->get($id);
}

function sqlDate($time = null, $time_format = "H:i:s")
{
    if ($time && !is_numeric($time)) {
        $time = strtotime($time);
    }

    return date("Y-m-d" . ($time_format ? " $time_format" : ""), $time);
}

function userHasRole()
{
    return call_user_func_array([Yii::$app->user, 'hasRole'], func_get_args());
}

function userHasPriv($permName, $priv = 'read')
{
    return Yii::$app->user->checkAccess(Yii::$app->user->id, $permName, $priv);
}

function listBulan()
{
    return [
        'Jan', 'Feb', 'Mar', 'Apr',
        'Mei', 'Jun', 'Jul', 'Agu',
        'Sep', 'Okt', 'Nov', 'Des'
    ];
}

function generateNo($name)
{
    $file = Yii::getAlias("@app/runtime/numgen-$name.lastnum");
    $data = @file_get_contents($file);
    $yearNow = date('Y');

    if ($data) {
        [$year, $lastNo] = explode(':', $data);
        $lastNo = $year != $yearNow ? 1 : $lastNo + 1;
    } else {
        $lastNo = 1;
    }

    file_put_contents($file, "$yearNow:$lastNo");

    return $lastNo;
}

function formatNo($no, $length = 6, $prefix = '')
{
    return $prefix . sprintf("%0{$length}d", $no);
}

function addReferrerToGET()
{
    parse_str(parse_url(Yii::$app->request->referrer, PHP_URL_QUERY) ?: '', $_GET);
}

function terbilang($n)
{
    $sebutan = [1 => 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    $n = floor($n);

    return match (true) {
        $n < 12 => $sebutan[$n] ?? null,
        $n < 20 => terbilang($n - 10) . ' Belas',
        $n < 100 => terbilang($n / 10) . ' Puluh ' . terbilang($n % 10),
        $n < 200 => 'Seratus ' . terbilang($n - 100),
        $n < 1000 => terbilang($n / 100) . ' Ratus ' . terbilang($n % 100),
        $n < 2000 => 'Seribu ' . terbilang($n - 1000),
        $n < 1000000 => terbilang($n / 1000) . ' Ribu ' . terbilang($n % 1000),
        $n < 1000000000 => terbilang($n / 1000000) . ' Juta ' . terbilang($n % 1000000),
        $n < 1000000000000 => terbilang($n / 1000000000) . ' Miliar ' . terbilang($n % 1000000000),
        $n < 1000000000000000 => terbilang($n / 1000000000000) . ' Triliun ' . terbilang($n % 1000000000000),
    };
}

function latestCommit()
{
    exec('cd ' . dirname(__DIR__, 2) .' && git log -1', $commit);
    $commit[3] = trim($commit[4] ?? '');
    unset($commit[4]);
    return $commit;
}

/** Generate active record attribut name */
function AR_attribute(string|ActiveRecord $ar, string $attr)
{
    try {
        $ar = get_class($ar);
    } catch (\Throwable $t) {
    }

    try {
        return $ar::tableName() . '.' . $attr;
    } catch (\Throwable $t) {
    }
}

function formatAngka($val)
{
    return number_format(floatval($val ?? 0), 2, ",", ".");
}

function formatRp($val)
{
    return 'Rp ' . formatAngka($val);
}

function formatPersen($val, $total)
{
    return $total ? formatAngka($val / $total * 100) : 0;
}

/**
 * Searches for a specific value in an array.
 *
 * @param array $array The array to search in.
 * @param callable|string $key The value to search for. It can be either a callback function or a string key.
 * @param mixed $default The default value to return if the value is not found. Defaults to null.
 * @return mixed The value found in the array, if it exists. The default value, if the value is not found.
 */
function arrayFind($array, $key, $default = null)
{
    if ($key instanceof Closure) {
        foreach ($array as $idx => $value) {
            if ($key($value, $idx)) {
                return $value;
            }
        }

        return $default;
    }

    return yii\helpers\ArrayHelper::getValue($array, $key, $default);
}

/**
 * Searches for a specific value in an array and returns its index.
 *
 * @param array $array The array to search in.
 * @param callable|string $key The value to search for. It can be either a callback function or a string key.
 * @return int|false The index of the first element in the array that matches the condition specified by the $key function, or false if no element matches the condition.
 */
function arrayFindIndex($array, $key)
{
    if ($key instanceof Closure) {
        foreach ($array as $idx => $value) {
            if ($key($value, $idx)) {
                return $idx;
            }
        }

        return false;
    }

    return array_search($key, $array);
}

/**
 * Searches for a specific value in an array and returns both the value and its index.
 *
 * @param array $array The array to search in.
 * @param \Closure $predicate A closure function that takes a value as input and returns a boolean indicating whether the value matches the desired condition.
 * @return array An array containing the value and index of the first element in the array that matches the condition specified by the $predicate function. If no element matches the condition, the array contains null values for both the value and index.
 */
function arrayFindValueAndIndex($array, \Closure $predicate)
{
    foreach ($array as $idx => $value) {
        if ($predicate($value)) {
            return [$value, $idx];
        }
    }

    return [null, null];
}