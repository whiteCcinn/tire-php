<?php

/**********************************************************\
 *                                                        *
 * tire.php                                               *
 *                                                        *
 * Author: Cai wenhui <471113744@qq.com>                  *
 *                                                        *
\**********************************************************/
class tire
{
    // 字典树
    public $tree = array();

    // 索引敏感词
    private $indexCode = array();

    private $statistics = false;

    /**
     * 添加词典.
     */
    public function add(string $words)
    {
        $tree = &$this->tree;
        foreach ($this->split($words) as $word) {
            $code = $this->utf8_transform_ascii($word);
            $tree = &$this->insertNode($tree, $code);
        }
        $tree['end'] = true;

        return $this;
    }

    /**
     * 插入节点.
     *
     * @param mixed $code
     *
     * @return array
     */
    private function &insertNode(&$tree, $code)
    {
        if (isset($tree[ $code ])) {
            return $tree[ $code ];
        }

        $tree[ $code ] = array();

        return $tree[ $code ];
    }

    /**
     * 查找关键字.
     *
     * @param mixed $tree
     * @param mixed $code
     * @param mixed $prefix
     *
     * @return array
     */
    private function &beginFind(&$tree, $code, &$prefix = '')
    {
        if (isset($tree[ $code ])) {
            $prefix = $prefix . "\u{$code}";

            return $tree[ $code ];
        }

        $prefix = '';

        return $this->tree;
    }

    /**
     * 匹配敏感词.
     *
     * @param string $text
     * @param mixed  $statistics
     * @param mixed  $first
     *
     * @return array
     */
    public function seek(string $text, $statistics = false, $first = true)
    {
        $match            = array();
        $this->statistics = $statistics;
        $tree             = &$this->tree;
        foreach ($this->split($text) as $k => $word) {
            if (!$first && !$k) {
                continue;
            }
            $code = $this->utf8_transform_ascii($word);
            $tree = &$this->beginFind($tree, $code, $sensitive);
            if (isset($tree['end'])) {
                !$this->_is_exist($sensitive, $statistics) && $match[] = $this->ascii_transform_utf8($sensitive);
            }
        }

        foreach ($match as $words) {
            $match = array_merge($match, $this->seek($words, $statistics, false));
        }

        return $match;
    }

    /**
     * 匹配次数.
     */
    public function statistics()
    {
        if (!$this->statistics) {
            return false;
        }
        $that      = $this;
        $indexCode = array();
        array_walk($this->indexCode, function ($statistics, &$sensitive) use ($that, &$indexCode) {
            $sensitive               = $that->ascii_transform_utf8($sensitive);
            $indexCode[ $sensitive ] = $statistics;
        });

        return $indexCode;
    }

    private function _is_exist($sensitive, $statistics = false)
    {
        if (isset($this->indexCode[ $sensitive ])) {
            $statistics && $this->indexCode[ $sensitive ]++;

            return true;
        } else {
            $this->indexCode[ $sensitive ] = 1;

            return false;
        }
    }

    /**
     * 单字符转换ascii.
     *
     * @param $utf8_str
     *
     * @return string
     */
    public function utf8_transform_ascii($utf8_str)
    {
        if (ord($utf8_str) < 127) {
            return ord($utf8_str);
        }

        $ascii = (ord(@$utf8_str{0}) & 0xF) << 12;
        $ascii |= (ord(@$utf8_str{1}) & 0x3F) << 6;
        $ascii |= (ord(@$utf8_str{2}) & 0x3F);

        return $ascii;
    }

    /**
     * ascii编码转汉字.
     *
     * @param $ascii
     *
     * @return string
     */
    public function ascii_transform_utf8($ascii)
    {
        if (strpos($ascii, '\u') !== false) {
            $asciis = explode('\u', $ascii);
            array_shift($asciis);
        } else {
            $asciis = array($ascii);
        }
        $utf8_str = '';
        foreach ($asciis as $ascii) {
            $ascii = (int) $ascii;
            $ord_1 = 0xe0 | ($ascii >> 12);
            $ord_2 = 0x80 | (($ascii >> 6) & 0x3f);
            $ord_3 = 0x80 | ($ascii & 0x3f);
            $utf8_str .= chr($ord_1) . chr($ord_2) . chr($ord_3);
        }

        return $utf8_str;
    }

    /**
     * utf8拆字.
     *
     * @param string $str
     *
     * @return Generator
     */
    private function split(string $str)
    {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = $str[ $i ];
            $n = ord($c);
            if (($n >> 7) == 0) {
                //0xxx xxxx, asci, single
                yield $c;
            } elseif (($n >> 4) == 15) { //1111 xxxx, first in four char
                if ($i < $len - 3) {
                    yield $c . $str[ $i + 1 ] . $str[ $i + 2 ] . $str[ $i + 3 ];
                    $i += 3;
                }
            } elseif (($n >> 5) == 7) {
                //111x xxxx, first in three char
                if ($i < $len - 2) {
                    yield $c . $str[ $i + 1 ] . $str[ $i + 2 ];
                    $i += 2;
                }
            } elseif (($n >> 6) == 3) {
                //11xx xxxx, first in two char
                if ($i < $len - 1) {
                    yield $c . $str[ $i + 1 ];
                    $i++;
                }
            }
        }
    }
}
