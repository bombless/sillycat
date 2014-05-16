<?php

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
function AssertEqual($lhs, $rhs){
    if($lhs == $rhs){
        echo 'passed: <<<', $lhs, '>>> equals <<<', $rhs, '>>>', "\n";
    }else{
        echo '!!error: <<<';
        echo($lhs);
        echo '>>> not equal <<<';
        echo($rhs);
        echo '>>>', "\n";
    }
}
function TokensAssertEqual($lhs, $rhs){
    if($lhs == $rhs){
        echo 'passed: ';
        echo '<<<', DumpTokens($lhs), '>>> equals <<<', DumpTokens($rhs), ">>>\n";
    }else{
        echo '!!error: ', '<<<', DumpTokens($lhs),
            '>>> not equal <<<', DumpTokens($rhs), ">>>\n";
    }
}
$dirname = dirname(__FILE__) . '/';
require($dirname . '../beta/tokenizer.php');
$foo = \Tokenizer\Tokenizer::ConcatCharacters('foo');
$bar = \Tokenizer\Tokenizer::ConcatCharacters('bar');
$space = \Tokenizer\Tokenizer::ConcatCharacters(' ')->Kleene();
$e = new \Tokenizer\Engine(['foo', $foo], ['bar', $bar], ['space', $space]);
AssertEqual($e->Test(''), 'space');
AssertEqual($e->Test('foo'), 'foo');
AssertEqual($e->Test('bar'), 'bar');
TokensAssertEqual($e->Parse('bar   foo'),
    [['bar', 'bar'], ['space', '   '], ['foo', 'foo']]);
TokensAssertEqual($e->Parse('foobar'), [['foo', 'foo'], ['bar', 'bar']]);
ini_set('memory_limit','512M');
$e = new \Tokenizer\Engine(
                ['identifier', \Tokenizer\Tokenizer::Identifier()],
                ['white-space', \Tokenizer\Tokenizer::Whitespace()],
                ['type-specifier', \Tokenizer\Tokenizer::ConcatCharacters('char')],
                ['semicolon', \Tokenizer\NFA::CreateSingleTransition(';')]);
TokensAssertEqual($e->Parse('char a;'),
    [['type-specifier', 'char'], ['white-space', ' '], ['identifier', 'a'], ['semicolon', ';']]);
