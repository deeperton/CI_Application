<?php

/**
 *
 *
 * @version
 * @author Artyuh Anton <deeperton@gmail.com>
 * @copyright 2008
 */

function CI($name) {
    $CI = & get_instance();
    return $CI->$name;
}

function let(&$value, $default){
    if(!isset ($value)){
        return $default;
    } else {
        return $value;
    }
}

function utf8_strlen($s) {
    $c = strlen($s); $l = 0;
    for ($i = 0; $i < $c; ++$i) if ((ord($s[$i]) & 0xC0) != 0x80) ++$l;
    return $l;
}

function utf8_strtolower($string) {
    $convert_to = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
        "v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
        "ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
        "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
        "ь", "э", "ю", "я"
    );
    $convert_from = array(
        "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
        "V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
        "Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
        "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ъ",
        "Ь", "Э", "Ю", "Я"
    );

    return str_replace($convert_from, $convert_to, $string);
}

/**
 * Генерация массива ключ=>значение по указанным столбцам из указанного массива
 *
 * @param array $array
 * @param string колонка где искать Ключ
 * @param ыекштп колонка где искать Значение
 * @return <type>
 */
function key_value($array, $key = 'id', $value = 'name') {
    if(!is_array($array)) {
        return false;
    }
    $ret = null;
    foreach($array as $k=>$v) {
        $ret[$v[$key]] = $v[$value];
    }

    return $ret;
}

/**
 * Поиск в массиве ассоциативных строк -- результатов из БД
 *
 * @param Array массив где ищем
 * @param String ключ колонки в которой ищем
 * @param mixed что именно ищем
 */
function search($haystack, $key, $needle, $more = false){
    if(!is_array($haystack)){
        return false;
    }
    $needle = str_replace('"', '\"', $needle);
    $key = str_replace('"', '\"', $key);
    $ret = array(false, $more);

    array_walk($haystack, create_function('&$a, $b, $c', 'if(isset($a["' . $key . '"]) && $a["' . $key . '"] == "' . $needle . '") if(!$c[1]) $c[0] = $b; else $c[0][] = $a;'), &$ret);

    return $ret[0];
}
/* -- класический вариант
function search($arr, $key, $needValue, $moreOne = false) {
    if(!is_array($arr) || count($arr) == 0) {
        return false;
    }
    $ret = false;
    if(!$moreOne) {
        foreach($arr as $i => $v) {
            if(isset($v[$key]) && $v[$key] == $needValue) {
                $ret = $i;
                break;
            }
        }
    } else {
        foreach($arr as $i => $v) {
            if(isset($v[$key]) && $v[$key] == $needValue) {
                $ret[] = $i;
            }
        }
    }
    return $ret;
}*/

/**
 * Возвращает массив по массиву индексов, пропускает неопределенные
 *
 * @param $arr array массив источник
 * @param $arrInd array массив индексов
 * @return array новый массив
 */
function sub_array_by_indexes(&$arr, $arrInd) {
    $ret = false;
    for($i = 0; $i < count($arrInd); $i++) {
        if(isset($arr[$arrInd[$i]])) {
            $ret[] = $arr[$arrInd[$i]];
        }
    }
    return $ret;
}

/**
 * Поиск в массиве ассоциативных строк значения ключа по заданному ключу и значнию
 *
 * @param Array где ищем
 * @param String имя известного ключа
 * @param mixed значение известного ключа
 * @param String имя ключа искомого значения.
 */
function searchByKey($arr, $key, $value, $needKey) {
    $i = search($arr, $key, $value);
    //$f = func_get_args();
    //die(print_r($f, true) . print_r($i, true));
    if($i !== false && isset($arr[$i][$needKey])) {
        return $arr[$i][$needKey];
    }
    return false;
}

function searchByKey_array(&$arr, $key, $value) {
    if(!is_array($arr) || count($arr) == 0) {
        return false;
    }

    $ret = array();
    for($i = 0; $i < count($arr); $i++) {
        if(isset($arr[$i][$key]) && $arr[$i][$key] == $value) {
            $ret[] = $arr[$i];
        }
    }
    return $ret;
}

/**
 * возвращает подмассив
 *
 * @param Array Массив
 * @param Integer кол-во элементов
 * @param Integer первый элемент
 * @return Array
 */
function sub_array($arr, $count, $start = 0) {
    if(!is_array($arr) || (count($arr) < $start)) {
        return false;
    }

    $ret = Array();
    if($count = null) {
        $count = count($arr);
    }
    $i = 0;
    foreach($arr as $k => $v) {
        if(($i >= $start) && $i < $count) {
            $ret[$k] = $v;
        }
        $i++;
    }
    return $ret;
}

function sub_array_by_key(&$arr, $key) {
    if(!is_array($arr)) {
        return false;
    }
    $ret = array();
    for($i = 0; $i < count($arr); $i++) {
        if(isset($arr[$i][$key])) {
            $ret[] = $arr[$i][$key];
        }
    }
    return $ret;
}

/**
 * Выборка под массива значений из ассоциативного массива.
 * Аналог SELECT $needKey FROM $arr WHERE $key = $value
 *
 * @param array $arr
 * @param str $key
 * @param mixed $value
 * @param str $needKey
 * @return array линейный массив значений ключа $needKey
 */
function sub_array_search(&$arr, $key, $value, $needkey) {
    if(!is_array($arr) || count($arr) == 0) {
        return false;
    }

    $l = count($arr);
    $ret = array();
    for($i = 0; $i < $l; $i++) {
        if(isset($arr[$i][$key]) && $arr[$i][$key] == $value && isset($arr[$i][$needkey])) {
            $ret[] = $arr[$i][$needkey];
        }
    }

    return $ret;
}

function array_key_multi_sort($arr, $l , $f='strnatcasecmp')
{
    usort($arr, create_function('$a, $b', "return $f(\$a['$l'], \$b['$l']);"));
    return($arr);
}

function table_to_tree_array($arr, $mk = 'id', $sk = 'parent_id', $child = 'child') {
    if(!$arr) {
        return array();
    }

    $l = count($arr);
    for($i = 0; $i < $l; $i++) {
        $mas[ $arr[$i][$mk] ] = &$arr[$i];
    }

    foreach($mas as $k => $v) {
        $mas[ $v[$sk] ][$child][] = &$mas[$k];
    }

    $res = array();
    foreach($arr as $v) {
        if(isset($v[$sk]) && $v[$sk] == 0) {
            $res[] = $v;
        }
    }
    $arr = $res;
    return $arr;
}

function RuEn($text) {
    $ru = array(
        'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
        'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
    );
    $trans = array(
        'a','b','v','g','d','e','yo', 'zh', 'z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','ts', 'ch', 'sh', 'sch','\'','y','\'','e','yu', 'ya',
        'A','B','V','G','D','E','YO', 'ZH', 'Z','I','J','K','L','M','N','O','P','R','S','T','U','F','H','TS', 'CH', 'SH', 'SCH','\'','Y','\'','E','YU', 'YA'
    );
    $ret = str_replace($ru, $trans, $text);

    $ret = strtolower($ret);
    $ret = preg_replace("%[^a-z0-9]+%i", '-', $ret);
    $ret = preg_replace("%[^\w\d-]+%", '', $ret);
    $ret = preg_replace("%^-*(.+?)-*$%", '$1', $ret);

    return strtolower($ret);
}
////////////////////////////////////////////////////////////////////////////////
function buildTree($arr, $id_key, $text_key, $child_key, $class, $extra_s, $deph = 0) {
    $_s = '';
    $l = sizeof($arr);
    for($i = 0; $i < $l; $i++) {
        $_s .= repeater("\t", $deph)."<li class=\"".$class."\" id=\"id_".
            $arr[$i][$id_key]."\"><span class=\"text\">" .
            $arr[$i][$text_key] . "</span> " .
            str_replace('{id}', $arr[$i][$id_key], $extra_s) . "\n";
        if(isset($arr[$i][$child_key])) {
            $deph++;
            $_s .= "<ul>\n" . buildTree($arr[$i][$child_key], $id_key, $text_key, $child_key, $class, $extra_s,$deph) . "</ul>\n";

        }
        $_s .= "</li>\n";
    }
    return $_s;
}

function _parse_single($key, $val, $string, $left = '{', $right = '}') {
    if($string == '') {
        return '';
    }

    return str_replace($left . $key . $right, $val, $string);
}

function _parse_multiple($str, $arr, $left = '{', $right = '}'){
    foreach($arr as $k => $v){
        if(!is_array($v)) $str = _parse_single($k, $v, $str, $left, $right);
    }
    return $str;
}

function buildTreeBySpaces($arr, $count, $curr, $id_key = 'id', $caption_key = 'caption', $child_key = 'child', $deph = 0) {
    $_s = '';
    $l = count($arr);

    for($i = 0; $i < $l; $i++) {
        $_s .= '<option value="' . $arr[$i][$id_key] . '"' .
            (($curr == $arr[$i][$id_key]) ? ('selected="selected"') : ('')) .
            '>' . repeater('&nbsp;', $count * $deph) . $arr[$i][$caption_key] . '</option>';
        if(isset($arr[$i][$child_key])) {
            $_s .= buildTreeBySpaces($arr[$i][$child_key], $count, $curr, $id_key, $caption_key, $child_key, $deph + 1);
        }

    }
    return $_s;
}

////////////////////////////////////////////////////////////////////////////////

function simple_view($___file, $vars){
    extract($vars);
    ob_start();

    @include($___file);

    $___out = ob_get_contents();
    @ob_end_clean();
    return $___out;
}

/**
 * Замена текста. Если замена произошла, то в конец добавляеться шаблон, иначе возвращаеться обрабатываемая строка.
 *
 * @param string $search
 * @param string $replace
 * @param string $subject
 * @param string $template
 * @return string
 */
function template_replace_concat($search, $replace, $subject, $template = ''){
    $ret = str_replace($search, $replace, $subject);
    if($ret != $subject){
        return $ret.$template;
    } else {
        return $ret;
    }
}

/**/
