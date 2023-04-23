<?php
if (!defined('DEDEINC')) exit ('dedebiz');
//允许的函数
$GLOBALS['allowedCalls'] = array(
    //系统
    'var_dump',
    //数学
    'ceil',
    'floor',
    'fmod',
    'log',
    'mt_rand',
    'mt_srand',
    'pow',
    'rand',
    'sqrt',
    'srand',
    //变量
    'empty',
    'floatval',
    'intval',
    'is_array',
    'is_binary',
    'is_bool',
    'is_double',
    'is_float',
    'is_int',
    'is_integer',
    'is_long',
    'is_null',
    'is_numeric',
    'is_real',
    'is_scalar',
    'is_string',
    'is_unicode',
    'isset',
    'strval',
    'unset',
    //数组
    'array_change_key_case',
    'array_chunk',
    'array_combine',
    'array_count_values',
    'array_diff_assoc',
    'array_diff_key',
    'array_diff',
    'array_fill_keys',
    'array_fill',
    'array_flip',
    'array_intersect_assoc',
    'array_intersect_key',
    'array_intersect',
    'array_key_exists',
    'array_keys',
    'array_merge_recursive',
    'array_merge',
    'array_multisort',
    'array_pad',
    'array_pop',
    'array_product',
    'array_push',
    'array_rand',
    'array_reverse',
    'array_search',
    'array_shift',
    'array_slice',
    'array_splice',
    'array_sum',
    'array_unique',
    'array_unshift',
    'array_values',
    'array',
    'arsort',
    'asort',
    'compact',
    'count',
    'current',
    'each',
    'end',
    'in_array',
    'key',
    'krsort',
    'ksort',
    'natcasesort',
    'natsort',
    'next',
    'pos',
    'prev',
    'range',
    'reset',
    'rsort',
    'shuffle',
    'sizeof',
    'sort',
    //字符串
    'json_encode',
    'json_decode',
    'json_last_error',
    'json_last_error_msg',
    'base64_decode',
    'base64_encode',
    'urlencode',
    'urldecode',
    'parse_url',
    'addslashes',
    'addcslashes',
    'chop',
    'count_chars',
    'explode',
    'implode',
    'join',
    'levenshtein',
    'ltrim',
    'metaphone',
    'money_format',
    'number_format',
    'rtrim',
    'similar_text',
    'soundex',
    'str_getcsv',
    'str_ireplace',
    'str_pad',
    'str_repeat',
    'str_replace',
    'str_rot13',
    'str_shuffle',
    'str_split',
    'str_word_count',
    'strcasecmp',
    'strchr',
    'strcmp',
    'strcspn',
    'stripos',
    'stristr',
    'strlen',
    'strnatcasecmp',
    'strnatcmp',
    'strncasecmp',
    'strncmp',
    'strpbrk',
    'strpos',
    'strrchr',
    'strrev',
    'strripos',
    'strrpos',
    'strspn',
    'strstr',
    'strtolower',
    'strtoupper',
    'strtr',
    'substr_compare',
    'substr_count',
    'substr_replace',
    'substr',
    'trim',
    'ucfirst',
    'ucwords',
    'wordwrap',
    //dede内置
    'html2text',
    'removexss',
    'htmlreplace',
    'getmktime',
    'getpinyin',
    'cn_substr',
    'cn_substrr',
    'mydate',
    'subday',
    'addday',
    'getdatetimemk',
    'getdatemk',
    'floortime',
    'getcururl',
    'utf82gb',
    'gb2utf8',
    'u2utf8',
    'utf82u',
    'big52gb',
    'gb2big5',
    'litimgurls',
    'split',
    //时间
    'strtotime',
    'date',
    'idate',
    'gmdate',
    'mktime',
    'gmmktime',
    'checkdate',
    'strftime',
    'gmstrftime',
    'time',
    'localtime',
    'getdate',
    'date_create',
    'date_create_immutable',
    'date_create_from_format',
    'date_create_immutable_from_format',
    'date_parse',
    'date_parse_from_format',
    'date_get_last_errors',
    'date_format',
    'date_modify',
    'date_add',
    'date_sub',
    'date_timezone_get',
    'date_timezone_set',
    'date_offset_get',
    'date_diff',
    'date_time_set',
    'date_date_set',
    'date_isodate_set',
    'date_timestamp_set',
    'date_timestamp_get',
    'timezone_open',
    'timezone_name_get',
    'timezone_name_from_abbr',
    'timezone_offset_get',
    'timezone_transitions_get',
    'timezone_location_get',
    'timezone_identifiers_list',
    'timezone_abbreviations_list',
    'timezone_version_get',
    'date_interval_create_from_date_string',
    'date_interval_format',
    'date_default_timezone_set',
    'date_default_timezone_get',
    'date_sunrise',
    'date_sunset',
    'date_sun_info',
    //mb字符串处理
    'mb_convert_case',
    'mb_strtoupper',
    'mb_strtolower',
    'mb_language',
    'mb_internal_encoding',
    'mb_http_input',
    'mb_http_output',
    'mb_detect_order',
    'mb_substitute_character',
    'mb_parse_str',
    'mb_output_handler',
    'mb_preferred_mime_name',
    'mb_strlen',
    'mb_strpos',
    'mb_strrpos',
    'mb_stripos',
    'mb_strripos',
    'mb_strstr',
    'mb_strrchr',
    'mb_stristr',
    'mb_strrichr',
    'mb_substr_count',
    'mb_substr',
    'mb_strcut',
    'mb_strwidth',
    'mb_strimwidth',
    'mb_convert_encoding',
    'mb_detect_encoding',
    'mb_list_encodings',
    'mb_encoding_aliases',
    'mb_convert_kana',
    'mb_encode_mimeheader',
    'mb_decode_mimeheader',
    'mb_convert_variables',
    'mb_encode_numericentity',
    'mb_decode_numericentity',
    'mb_send_mail',
    'mb_get_info',
    'mb_check_encoding',
    'mb_ord',
    'mb_chr',
    'mb_scrub',
    'mb_regex_encoding',
    'mb_regex_set_options',
    'mb_ereg',
    'mb_eregi',
    'mb_ereg_replace',
    'mb_eregi_replace',
    'mb_ereg_replace_callback',
    'mb_split',
    'mb_ereg_match',
    'mb_ereg_search',
    'mb_ereg_search_pos',
    'mb_ereg_search_regs',
    'mb_ereg_search_init',
    'mb_ereg_search_getregs',
    'mb_ereg_search_getpos',
    'mb_ereg_search_setpos',
);
//允许的语法
$GLOBALS['allowedTokens'] = array(
    'T_AND_EQUAL',
    'T_ARRAY',
    'T_ARRAY_CAST',
    'T_AS',
    'T_BOOLEAN_AND',
    'T_BOOLEAN_OR',
    'T_BOOL_CAST',
    'T_BREAK',
    'T_CASE',
    'T_CHARACTER',
    'T_CONCAT_EQUAL',
    'T_CONSTANT_ENCAPSED_STRING',
    'T_CONTINUE',
    'T_CURLY_OPEN',
    'T_DEC',
    'T_DECLARE',
    'T_DEFAULT',
    'T_DIV_EQUAL',
    'T_DNUMBER',
    'T_DO',
    'T_DOUBLE_ARROW',
    'T_DOUBLE_CAST',
    'T_ELSE',
    'T_ELSEIF',
    'T_EMPTY',
    'T_ENCAPSED_AND_WHITESPACE',
    'T_ENDDECLARE',
    'T_ENDFOR',
    'T_ENDFOREACH',
    'T_ENDIF',
    'T_ENDSWITCH',
    'T_FOR',
    'T_FOREACH',
    'T_IF',
    'T_INC',
    'T_INT_CAST',
    'T_ISSET',
    'T_IS_EQUAL',
    'T_IS_GREATER_OR_EQUAL',
    'T_IS_IDENTICAL',
    'T_IS_NOT_EQUAL',
    'T_IS_NOT_IDENTICAL',
    'T_IS_SMALLER_OR_EQUAL',
    'T_LNUMBER',
    'T_LOGICAL_AND',
    'T_LOGICAL_OR',
    'T_LOGICAL_XOR',
    'T_MINUS_EQUAL',
    'T_MOD_EQUAL',
    'T_MUL_EQUAL',
    'T_NUM_STRING',
    'T_OR_EQUAL',
    'T_PLUS_EQUAL',
    'T_RETURN',
    'T_SL',
    'T_SL_EQUAL',
    'T_SR',
    'T_SR_EQUAL',
    'T_STRING',
    'T_STRING_CAST',
    'T_STRING_VARNAME',
    'T_SWITCH',
    'T_UNSET',
    'T_UNSET_CAST',
    'T_VARIABLE',
    'T_WHILE',
    'T_WHITESPACE',
    'T_XOR_EQUAL',
);
//禁止的表达式
$GLOBALS['disallowedExpressions'] = array(
    '/`/',
    '/\$\W/',
    '/(\]|\})\s*\(/',
    '/\$\w\w*\s*\(/',
);
//执行脚本
function evalCode($code)
{
    ob_start();
    $code = eval('if (0){'."\n".$code."\n".'}');
    ob_end_clean();
    return $code !== false;
}
//校验脚本
function checkCode($code)
{
    global $allowedCalls, $allowedTokens, $disallowedExpressions;
    $tokens = token_get_all('<?php '.$code.' ?>');
    $errors = array();
    $braces = 0;
    foreach ($tokens as $token) {
        if ($token == '{') $braces = $braces + 1;
        else if ($token == '}') $braces = $braces - 1;
        if ($braces < 0) {
            $errors[0]['name'] = 'Syntax error.';
            break;
        }
    }
    if (empty($errors)) {
        if ($braces) $errors[0]['name'] = 'Unbalanced braces.';
    } else if (!evalCode($code)) {
        $errors[0]['name'] = 'Syntax error.';
    }
    if (empty($errors)) foreach ($disallowedExpressions as $disallowedExpression) {
        unset($matches);
        preg_match($disallowedExpression, $code, $matches);
        if ($matches) {
            $errors[0]['name'] = 'Execution operator / variable function name / variable variable name detected.';
            break;
        }
    }
    if (empty($errors)) {
        unset($tokens[0]);
        unset($tokens[0]);
        array_pop($tokens);
        array_pop($tokens);
        $i = 0;
        foreach ($tokens as $key => $token) {
            $i++;
            if (is_array($token)) {
                $id = token_name($token[0]);
                switch ($id) {
                    case ('T_STRING'):
                    if (in_array(strtolower($token[1]), $allowedCalls) === false) {
                        $errors[$i]['name'] = 'Illegal function: '.$token[1];
                        $errors[$i]['line'] = $token[2];
                    }
                    break;
                    default:
                    if (in_array($id, $allowedTokens) === false) {
                        $errors[$i]['name'] = 'Illegal token: '.$token[1];
                        $errors[$i]['line'] = $token[2];
                    }
                    break;
                }
            }
        }
    }
    if (!empty($errors)) {
        return $errors;
    }
}
//错误提示
function htmlErrors($errors = null)
{
    if ($errors) {
        $errorsHTML = "<div style='width:98%;margin:1rem auto;color:#842029;background:#f8d7da;border-color:#842029;position:relative;padding:.75rem 1.25rem;border:1px solid transparent;border-radius:.5rem'>";
        $errorsHTML .= '内嵌脚本缺失，请添加该函数：';
        $errorsHTML .= '<dl>';
        foreach ($errors as $error) {
            if ($error['line']) {
                $errorsHTML .= '<dt>Line '.$error['line'].'</dt>';
            }
            $errorsHTML .= '<dd>'.$error['name'].'</dd>';
        }
        $errorsHTML .= '</dl>';
        $errorsHTML .= "</div>\r\n";
        echo $errorsHTML;
    }
}
?>