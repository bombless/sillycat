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
function DumpTokens($arr){
    if($arr === null)echo "NULL\n";
    foreach($arr as $item){
        echo '[', $item[0], ', ', $item[1], ']', "\n";
    }
}
function DumpEveryToken(){
    $args = func_get_args();
    foreach($args as $i){
        echo "\n";
        DumpTokens($i);
    }
}
function AssertSimilar($expr, $val, $ex){
    if(!$val && $ex){
        echo '!!error: <<<', $expr, '>>> get NULL', "\n";
        exit;
    }
    if(SimilarTokens($val, $ex)){
        echo "passed:\n";
        echo '<<<', $expr, '>>> = <<<';
        DumpTokens($ex);
        echo '>>>', "\n";
    }else{
        echo "!!error:\n";
        echo 'expect <<<', $expr, '>>> = ', "\n<<<";
        DumpTokens($ex);
        echo '>>>, get ', "\n<<<";
        DumpTokens($val);
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
require($dirname . '../beta/tokenizer.php');
$foo = \Tokenizer\Tokenizer::ConcatCharacters('foo');
$bar = \Tokenizer\Tokenizer::ConcatCharacters('bar');
$space = \Tokenizer\Tokenizer::ConcatCharacters(' ')->Kleene();
$e = new \Tokenizer\Engine(['foo', $foo], ['bar', $bar], ['space', $space]);
var_dump($e->Test(''), $e->Test('foo'), $e->Test('bar'));
DumpEveryToken($e->Parse('bar   foo'), $e->Parse('foobar'));
ini_set('memory_limit','512M');
$e = new \Tokenizer\Engine(
                ['identifier', \Tokenizer\Tokenizer::Identifier()],
                ['white-space', \Tokenizer\Tokenizer::Whitespace()],
                ['type-specifier', \Tokenizer\Tokenizer::ConcatCharacters('char')],
                ['semicolon', \Tokenizer\NFA::CreateSingleTransition(';')]);
DumpEveryToken($e->Parse('char a;'));
