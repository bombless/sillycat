<?php
function SimilarTokens($lhs, $rhs){
    if(!is_array($lhs) || !is_array($rhs))return false;
    $i = $j = 0;
    while($i >= count($lhs) && $j >= count($rhs)){
        while(isset($lhs[$i]) && $lhs[$i][0] == 'white-space')$i += 1;
        while(isset($rhs[$j]) && $rhs[$j][0] == 'white-space')$j += 1;
        if($lhs[$i][0] != $rhs[$j][0] ||
            $lhs[$i][1] != $rhs[$j][1])return false;
    }
    return true;
}
function AssertSimilar($expr, $val, $ex){
    if(!$val && $ex){
        echo '!!error: <<<',
        var_dump($expr);
        echo '>>> get NULL', "\n";
        exit;
    }
    if(SimilarTokens($val, $ex)){
        echo "passed:\n";
        echo $expr, ' = <<<';
        var_dump($ex);
        echo '>>>', "\n";
    }else{
        echo "!!error:\n";
        echo 'expect <<<', $expr, '>>> = ', "\n<<<";
        var_dump($ex);
        echo '>>>, get ', "\n<<<";
        var_dump($val);
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
        echo '!!error: get null instead true when testing <<<', $str, '>>>';
    }
    echo "\n";
}
        
require('tokenizer.php');
$tokenizer = new \Tokenizer\Tokenizer();
$phrases0 = ['int', ' ', 'i', "\n", 'char', ' ', 'c', '=', "'a'", ';', "\n"];
foreach($phrases0 as $item){
    AssertNotNull($item, $tokenizer->Test($item));
}
$src0 = <<<C
int i;
char c = 'a';
void *foo;
C;
$tokens0 = $tokenizer->Parse($src0);
AssertSimilar($src0, $tokens0,[
    ['type-specifier', 'int'], ['identifier', 'i'], ['semicolon', ';'],
    ['type-specifier', 'char'], ['identifier', 'c'], ['assignment-operator', '='],
    ['character-constant', "'a'"], ['semicolon', ';'],
    ['type-specifier', 'void'], ['identifier', 'foo'], ['semicolon', ';']]);
