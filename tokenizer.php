<?php
namespace Tokenizer{
    class Status{
        private $acceptOffset;
        private $pool;
        private function __construct(){
        }
        public static function CreateUnit(){
            $ret = new self;
            $item = new \stdClass;
            $item->map = [];
            $item->closure = [];
            $ret->pool = [$item];
            $ret->acceptOffset = 0;
            return $ret;
        }
        public function CreateSingleTransition($chr){
            $chr = $chr[0];
            $s = new \stdClass;
            $s->map = [$chr => 1];
            $s->closure = [];
            $e = new \stdClass;
            $e->map = [];
            $e->closure = [];
            $ret = new self;        
            $ret->pool = [$s, $e];
            $ret->acceptOffset = 1;
            return $ret;
        }
        protected function Adjust($limit){
            $ret = new self;
            $ret->acceptOffset = $this->acceptOffset + $limit;
            $ret->pool = [];
            foreach($this->pool as $i => $ptr){
                if(!$ptr)continue;
                $item = new \stdClass;
                $item->map = [];
                $item->closure = [];
                foreach($ptr->map as $k => $v){
                    $item->map[$k] = $v + $limit;
                }
                foreach($ptr->closure as $v){
                    array_push($item->closure, $v + $limit);
                }
                $ret->pool[$i + $limit] = $item;
            }
            return $ret;
        }
        public function Concat($rhs){
            $adjust = max(array_keys($this->pool)) + 1;
            if(!is_object($rhs))var_dump($rhs);
            $rhs = $rhs->Adjust($adjust);
            $pool = $this->pool;
            $offset = $this->acceptOffset;
            array_push($pool[$offset]->closure, $adjust);
            $ret = new self;
            $ret->acceptOffset = $rhs->acceptOffset;
            $ret->pool = $pool + $rhs->pool;
            return $ret;
        }
        public function Kleene(){
            $ret = $this->Adjust(1);
            $offsetOld = $ret->acceptOffset;
            $offsetNew = max(array_keys($ret->pool)) + 1;
            array_push($ret->pool[$offsetOld]->closure, 0);
            array_push($ret->pool[$offsetOld]->closure, $offsetNew);
            $s = new \stdClass;
            $s->map = [];
            $s->closure = [1, $offsetNew];
            $e = new \stdClass;
            $e->map = [];
            $e->closure = [];
            $ret->pool[0] = $s;
            $ret->pool[$offsetNew] = $e;
            $ret->acceptOffset = $offsetNew;
            return $ret;
        }
        public function Pipe($rhs){
            $ret = $this->Adjust(1);
            $adjust = max(array_keys($ret->pool)) + 1;
            $rhs = $rhs->Adjust($adjust);
            $s = new \stdClass;
            $s->closure = [1, $adjust];
            $s->map = [];
            $ret->pool[0] = $s;
            $ret->pool = $ret->pool + $rhs->pool;
            $offsetLhs = $ret->acceptOffset;
            $offsetRhs = $rhs->acceptOffset;
            $offsetRet = max(array_keys($rhs->pool)) + 1;
            $e = new \stdClass;
            $e->map = [];
            $e->closure = [];
            array_push($ret->pool[$offsetLhs]->closure, $offsetRet);
            array_push($ret->pool[$offsetRhs]->closure, $offsetRet);
            $ret->acceptOffset = $offsetRet;
            $ret->pool[$offsetRet] = $e;
            return $ret;
        }
        public function GetPool(){
            return $this->pool;
        }
        public function GetAccept(){
            return $this->acceptOffset;
        }
    }
    class Tokenizer extends Status{
        private $pool;
        private $tokens;
        public function __construct(){
            $this->Constructor(
                ['identifier', self::Identifier()],
                ['white-space', self::Whitespace()],
                ['type-specifier', self::ConcatCharacters('void')],
                ['type-specifier', self::ConcatCharacters('char')],
                ['type-specifier', self::ConcatCharacters('short')],
                ['type-specifier', self::ConcatCharacters('int')],
                ['type-specifier', self::ConcatCharacters('long')],
                ['type-specifier', self::ConcatCharacters('float')],
                ['type-specifier', self::ConcatCharacters('double')],
                ['type-specifier', self::ConcatCharacters('signed')],
                ['type-specifier', self::ConcatCharacters('unsigned')],
                ['type-specifier', self::ConcatCharacters('_Bool')],
                ['type-specifier', self::ConcatCharacters('_Complex')],
                ['type-specifier', self::ConcatCharacters('_Imaginary')],
                ['enum-specifier', self::ConcatCharacters('enum')],
                ['struct-or-union', self::ConcatCharacters('union')],
                ['struct-or-union', self::ConcatCharacters('struct')],
                ['string-literal', self::StringLiteral()],
                ['character-constant', self::CharLiteral()],
                ['storage-class-speciﬁer', self::ConcatCharacters('typedef')],
                ['storage-class-speciﬁer', self::ConcatCharacters('extern')],
                ['storage-class-speciﬁer', self::ConcatCharacters('static')],
                ['storage-class-speciﬁer', self::ConcatCharacters('auto')],
                ['storage-class-speciﬁer', self::ConcatCharacters('register')],
                ['type-qualifier', self::ConcatCharacters('const')],
                ['type-qualifier', self::ConcatCharacters('restrict')],
                ['type-qualifier', self::ConcatCharacters('volatile')],
                ['function-speciﬁer', self::ConcatCharacters('inline')],
                ['assignment-operator', self::ConcatCharacters('=')],
                ['assignment-operator', self::ConcatCharacters('*=')],
                ['assignment-operator', self::ConcatCharacters('/=')],
                ['assignment-operator', self::ConcatCharacters('%=')],
                ['assignment-operator', self::ConcatCharacters('+=')],
                ['assignment-operator', self::ConcatCharacters('-=')],
                ['assignment-operator', self::ConcatCharacters('<<=')],
                ['assignment-operator', self::ConcatCharacters('>>=')],
                ['assignment-operator', self::ConcatCharacters('&=')],
                ['assignment-operator', self::ConcatCharacters('^=')],
                ['assignment-operator', self::ConcatCharacters('|=')],
                ['operator', self::OneOf('&', '*', '+', '-', '~', '!')],
                ['operator', self::OneOf('[', ']', '(', ')', '{', '}', '.', '->')],
                ['operator', self::OneOf('++', '--', '/', '%', '<<', '>>')],
                ['operator', self::OneOf('<', '>', '<=', '>=', '==', '!=')],
                ['operator', self::OneOf('^', '|', '&&', '||', '?', ':', ';', '...')],
                ['operator', self::OneOf(',', '#', '#', '<:', ':>', '<%', '%>', '%:', '%:%:')],
                ['semicolon', Status::CreateSingleTransition(';')]);
        }
        private function Constructor(){
            $s0 = new \stdClass;
            $s0->map = [];
            $s0->closure = [];
            $count = 1;
            $pool = [$s0];
            $tokens = [];
            $args = func_get_args();
            foreach($args as $item){
                $fa = $item[1]->Adjust($count);
                $name = $item[0];
                array_push($s0->closure, $count);
                $count += count($fa->GetPool());
                $pool += $fa->GetPool();
                $tokens[$fa->GetAccept()] = $name;
            }
            $this->pool = $pool;
            $this->tokens = $tokens;
        }
        private function GetClosures($ref, $acc = []){
            $pool = $this->pool;
            $ret = array_diff($pool[$ref]->closure, $acc);
            $acc = array_merge($ret, $acc);
            $attach = [];
            foreach($ret as $item){
                $attach = array_merge($this->GetClosures($item, $acc), $attach);
            }
            return array_merge($ret, $attach);                
        }
        public function Test($str){
            $tokens = $this->tokens;
            $pool = $this->pool;
            $p = [0];
            $attach = $this->GetClosures(0);
            for(; strlen($str) > 0; $str = substr($str, 1)){
                $chr = $str[0];
                $p = array_merge($p, $attach);
                $attach = [];
                for($i = 0; $i < count($p); ++$i){
                    $item = $pool[$p[$i]];
                    if(array_key_exists($chr, $item->map)){
                        $p[$i] = $item->map[$chr];
                        $attach = array_merge($attach, $this->GetClosures($p[$i]));
                    }else{
                        for($j = $i + 1; $j < count($p); ++$j){
                            $p[$j - 1] = $p[$j];
                        }
                        array_pop($p);
                        $i -= 1;
                    }
                }
            }
            if(strlen($str) > 0)return null;
            $target = [];
            foreach($tokens as $offset => $name){
                if(in_array($offset, $p) || in_array($offset, $attach))$target[$offset] = $name;
            }
            if(count($target))return $target[max(array_keys($target))];
            return null;
        }
        private static function Whitespace(){
            return self::OneOf(' ', "\f", "\v", "\t", "\r", "\n")->Kleene();
        }
        private static function Identifier(){
            return self::Nondigit()->Concat(self::Nondigit()->Pipe(self::Digit())->Kleene());
        }
        private static function ConcatCharacters($str){
            $acc = Status::CreateUnit();
            for($i = 0; $i < strlen($str); ++$i){
                $acc = $acc->Concat(Status::CreateSingleTransition($str[$i]));
            }
            return $acc;
        }
        private static function OneOf(){
            $args = func_get_args();
            if(count($args) == 0)return Status::CreateUnit();
            $acc = self::ConcatCharacters($args[0]);
            for($i = 1; $i < count($args); ++$i){
                $acc = $acc->Pipe(self::ConcatCharacters($args[$i]));
            }
            return $acc;
        }
        private static function Nondigit(){
            $acc = Status::CreateSingleTransition('_');
            for($i = ord('A'); $i <= ord('Z'); ++$i){
                $acc = $acc->Pipe(Status::CreateSingleTransition(chr($i)));
            }
            for($i = ord('a'); $i <= ord('z'); ++$i){
                $acc = $acc->Pipe(Status::CreateSingleTransition(chr($i)));
            }
            return $acc;
        }
        private static function Digit(){
            $acc = Status::CreateSingleTransition('0');
            for($i = ord('1'); $i <= ord('9'); ++$i){
                $acc = $acc->Pipe(Status::CreateSingleTransition(chr($i)));
            }
            return $acc;
        }
        private static function OtherThan($chr){
            $acc = Status::CreateSingleTransition(chr(ord($chr) + 1));
            for($i = ord($chr) + 2; chr($i) != $chr; ++$i){
                $acc = $acc->Pipe(Status::CreateSingleTransition(chr($i)));
            }
            return $acc;
        }
        private static function StringLiteral(){
            $otherThan = self::OtherThan('"');
            $backSlash = Status::CreateSingleTransition('\\');
            $quote = Status::CreateSingleTransition('"');
            $escape = $backSlash->Concat($quote);
            $content = $otherThan->Pipe($escape)->Kleene();
            return $quote->Concat($content)->Concat($quote);
        }
        private static function CharLiteral(){
            $otherThan = self::OtherThan("'");
            $backSlash = Status::CreateSingleTransition('\\');
            $quote = Status::CreateSingleTransition("'");
            $escape = $backSlash->Concat($quote);
            $content = $otherThan->Pipe($escape)->Kleene();
            return $quote->Concat($content)->Concat($quote);
        }
        public function Parse($src){
            $ret = [];
            $len = strlen($src);
            $pos = 0;
            $end = $len - $pos;
            while($pos < $len && $pos < $end){
                $test = $this->Test(substr($src, $pos, $end - $pos));
                if($test){
                    $ret[] = [$test, substr($src, $pos, $end - $pos)];
                    $pos = $end;
                    $end = $len;
                }else{
                    $end -= 1;
                }
            }
            if($pos < $len)return null;
            return $ret;
        }
    }
}
