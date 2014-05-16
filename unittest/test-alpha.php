<?php
function SimilarTokens($lhs, $rhs){
    if(!is_array($lhs) || !is_array($rhs))return false;
    $i = $j = 0;
    while($i >= count($lhs) && $j >= count($rhs)){
        while(isset($lhs[$i]) && $lhs[$i][0] == 'white-space')$i += 1;
        while(isset($rhs[$j]) && $rhs[$j][0] == 'white-space')$j += 1;
        if($lhs[$i] != $rhs[$j])return false;
    }
    return true;
}
function DumpTokens($arr){
    if($arr === null)return "NULL\n";
    $buf = "\n";
    $ret = '';
    foreach($arr as $item){
        $temp = '['. $item[0]. ', '. $item[1]. ']';
        if(strlen($buf. $temp) > 80){
            $ret .= $buf. "\n";
            $buf = '';
        }
        $buf .= $temp;
    }
    return $ret . $buf. "\n";
}
function AssertSimilar($expr, $val, $ex){
    if(!$val && $ex){
        echo '!!error: <<<', $expr, '>>> get NULL', "\n";
        exit;
    }
    if(SimilarTokens($val, $ex)){
        echo "passed:\n";
        echo '<<<', $expr, '>>> = <<<', DumpTokens($ex), '>>>', "\n";
    }else{
        echo "!!error:\n";
        echo 'expect <<<', $expr, '>>> = ', "\n<<<";
        echo DumpTokens($ex);
        echo '>>>, get ', "\n<<<";
        echo DumpTokens($val);
        echo '>>> instead.', "\n";
        exit;
    }
}
function AssertNotNull($expr, $ac){
    $str = '';
    for($i = 0; $i < strlen($expr); ++$i){
        if(ord($expr[$i]) <= 32)$str .= sprintf('\\x%02X', ord($expr[$i]));
        else $str .= $expr[$i];
    }
    if($ac !== null){
        echo 'passed: <<<', $str, '>>> = ', $ac;
    }else{
        echo '!!error: get NULL when testing <<<', $str, '>>>';
    }
    echo "\n";
}
$dirname = dirname(__FILE__) . '/';
require($dirname . '../alpha/tokenizer.php');

$tokenizer = new \Tokenizer\Tokenizer();
$phrases0 = ['int', ' ', 'i', "\n", 'char', ' ', 'c', '=', "'a'", ';', "\n"];
foreach($phrases0 as $item){
    AssertNotNull($item, $tokenizer->Test($item));
}
$src0 = file_get_contents($dirname . 'src0.c');
$tokens0 = $tokenizer->Parse($src0);
AssertSimilar($src0, $tokens0, [
    ['type-specifier', 'int'], ['identifier', 'i'], ['semicolon', ';'],
    ['type-specifier', 'char'], ['identifier', 'c'], ['assignment-operator', '='],
    ['character-constant', "'a'"], ['semicolon', ';'],
    ['type-specifier', 'void'], ['identifier', 'foo'], ['semicolon', ';']]);
$src1 = file_get_contents($dirname . 'src1.c');
$tokens1 = $tokenizer->Parse($src1);
AssertSimilar($src1, $tokens1, [
    ['type-specifier', 'char'], ['operator', '*'], ['assignment-operator', '='],
    ['string-literal', '"abc"'], ['semicolon', ';'],
    ['for', 'for'], ['operator', '('], ['semicolon', ';'], ['operator', '*'],
    ['identifier', 'p'], ['operator', '!='], ['character-constant', "'\\0'"],
     ['semicolon', ';'], ['operator', '++'], ['operator', ')'], ['semicolon', ';']]);
$src2 = file_get_contents($dirname . 'src2.c');
$tokens2 = $tokenizer->Parse($src2);
AssertSimilar($src2, $tokens2, [
    ['type-specifier', 'void'], ['identifier', 'foo'], ['operator', '('],
    ['type-specifier', 'char'], ['identifier', 'a'], ['operator', ')'],
    ['operator', '{'], ['storage-class-specifier', 'typedef'],
    ['type-specifier', 'char'], ['identifier', 'ch_t'], ['semicolon', ';'],
    ['identifier', 'ch_t'], ['identifier', 'c'], ['semicolon', ';'],
    ['return', 'return'], ['semicolon', ';'], ['operator', '}']]);
