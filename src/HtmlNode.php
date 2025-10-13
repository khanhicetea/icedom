<?php

namespace IceTea\IceDOM;

use Closure;

use function array_is_list;
use function count;
use function htmlspecialchars;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function str_replace;

class HtmlNode extends Node
{
    public const BOOLEAN_ATTRS = ['allowfullscreen', 'async', 'autofocus', 'autoplay', 'checked', 'controls', 'default', 'defer', 'disabled', 'formnovalidate', 'hidden', 'ismap', 'itemscope', 'loop', 'multiple', 'muted', 'nomodule', 'novalidate', 'open', 'readonly', 'required', 'reversed', 'selected'];

    public const VOID_TAGS = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

    protected string $tagName;

    protected array $attrs = [];

    protected bool $isVoid = false;

    public function __construct(
        array $children = [],
        ?string $tagName = null,
        array $attrs = [],
        bool $isVoid = false,
    ) {
        parent::__construct($children);
        $this->tagName = $tagName;
        $this->attrs = $attrs;
        $this->isVoid = $isVoid;
    }

    public static function tag(string $tagName, mixed $firstArgument, ?array $children, bool $isVoid = false): static
    {
        if (is_string($firstArgument)) {
            return new static(
                is_array($children) ? $children : [],
                $tagName,
                ['_' => $firstArgument],
                $isVoid
            );
        }

        if (is_array($firstArgument)) {
            if (array_is_list($firstArgument)) {
                return new static($firstArgument, $tagName, [], $isVoid);
            }

            if (isset($firstArgument[0]) && ! isset($firstArgument['_'])) {
                $firstArgument['_'] = $firstArgument[0];
                unset($firstArgument[0]);
            }

            return new static(
                is_array($children) ? $children : [],
                $tagName,
                $firstArgument,
                $isVoid
            );
        }

        if ($firstArgument === null && is_array($children)) {
            return new static($children, $tagName, [], $isVoid);
        }

        return new static([$firstArgument], $tagName, [], $isVoid);
    }

    protected function attributesToString(): string
    {
        if (count($this->attrs) == 0) {
            return '';
        }

        $attrStrs = [];
        foreach ($this->attrs as $key => $value) {
            if ($key == '_') {
                $attrStrs[] = " {$value}";

                continue;
            }

            if ($value instanceof Closure) {
                $value = $value($this);
            }

            if ($value === false && in_array($key, self::BOOLEAN_ATTRS)) {
                continue;
            }

            if (
                $value === true ||
                (in_array($key, self::BOOLEAN_ATTRS) && (bool) $value)
            ) {
                $attrStrs[] = " {$key}";
            } elseif ($value !== null) {
                $key = htmlspecialchars($key, static::ENT_FLAGS);
                $value = htmlspecialchars((string) $value, static::ENT_FLAGS);
                $attrStrs[] = " {$key}=\"{$value}\"";
            }
        }

        return implode('', $attrStrs);
    }

    public function setAttribute($key, $value): static
    {
        $this->attrs[$key] = $value;

        return $this;
    }

    public function getAttribute($key, $default = null)
    {
        return $this->attrs[$key] ?? $default;
    }

    public function __call($key, $args): static
    {
        $key = str_replace('_', '-', $key);

        return $this->setAttribute($key, $args[0] ?? null);
    }

    public function __get($key)
    {
        $key = str_replace('_', '-', $key);

        return $this->getAttribute($key);
    }

    public function id($id): static
    {
        return $this->setAttribute('id', $id);
    }

    public function classes(array|string|null ...$args): static
    {
        if (empty($args)) {
            return null;
        }

        $classes = [];
        foreach ($args as $arg) {
            if (is_string($arg)) {
                $classes[$arg] = true;
            } elseif (is_array($arg)) {
                foreach ($arg as $key => $value) {
                    if (is_int($key) && is_string($value)) {
                        $classes[$value] = true;
                    } elseif (is_string($key)) {
                        $classes[$key] = (bool) $value;
                    }
                }
            }
        }

        $class = implode(' ', array_keys(array_filter($classes)));

        return $this->setAttribute('class', $class);
    }

    public function __toString()
    {
        if ($this->isVoid) {
            return "<{$this->tagName}{$this->attributesToString()}>";
        }

        return "<{$this->tagName}{$this->attributesToString()}>{$this->childrenToString()}</{$this->tagName}>";
    }

    public function toArray()
    {
        $closureFunction = function ($value) {
            return $value instanceof Closure ? $value($this) : $value;
        };
        $closureChildFunction = function ($value) {
            if ($value instanceof HtmlNode) {
                return $value->toArray();
            }

            return $value;
        };

        return [
            'children' => array_map($closureChildFunction, $this->children),
            'tagName' => $this->tagName,
            'attrs' => array_map($closureFunction, $this->attrs),
            'isVoid' => $this->isVoid,
        ];
    }

    // TODO : String buffering

    // protected function echoAttributes()
    // {
    //     if (!$this->attrs) return;
    //
    //     foreach ($this->attrs as $key => $value) {
    //         echo " ";
    //         if ($key == '_') {
    //             echo $value;
    //             continue;
    //         }
    //
    //         $key = \htmlspecialchars($key, static::ENT_FLAGS);
    //         $value = $this->tryEvalClosure($value);
    //
    //         if (
    //             $value === true ||
    //             (\in_array($key, self::BOOLEAN_ATTRS) && !!$value)
    //         ) {
    //             echo $key;
    //         } elseif ($value !== null) {
    //             $value = \htmlspecialchars((string) $value, static::ENT_FLAGS);
    //             echo $key;
    //             echo "=\"";
    //             echo $value;
    //             echo "\"";
    //         }
    //     }
    // }

    // public function echo()
    // {
    //     if (in_array($this->tagName, self::SELF_CLOSING_TAGS)) {
    //         echo "<";
    //         echo $this->tagName;
    //         $this->echoAttributes();
    //         echo " />";
    //         return;
    //     }
    //
    //     echo "<";
    //     echo $this->tagName;
    //     $this->echoAttributes();
    //     echo ">";
    //     $this->echoChildren();
    //     echo "</";
    //     echo $this->tagName;
    //     echo ">";
    // }

    // GENERATED
    public function accept($value): static
    {
        $this->attrs['accept'] = $value;

        return $this;
    }

    public function acceptCharset($value): static
    {
        $this->attrs['accept-charset'] = $value;

        return $this;
    }

    public function accesskey($value): static
    {
        $this->attrs['accesskey'] = $value;

        return $this;
    }

    public function action($value): static
    {
        $this->attrs['action'] = $value;

        return $this;
    }

    public function align($value): static
    {
        $this->attrs['align'] = $value;

        return $this;
    }

    public function alt($value): static
    {
        $this->attrs['alt'] = $value;

        return $this;
    }

    public function async($value = true): static
    {
        $this->attrs['async'] = $value;

        return $this;
    }

    public function autocapitalize($value): static
    {
        $this->attrs['autocapitalize'] = $value;

        return $this;
    }

    public function autocomplete($value): static
    {
        $this->attrs['autocomplete'] = $value;

        return $this;
    }

    public function autofocus($value = true): static
    {
        $this->attrs['autofocus'] = $value;

        return $this;
    }

    public function autoplay($value = true): static
    {
        $this->attrs['autoplay'] = $value;

        return $this;
    }

    public function bgcolor($value): static
    {
        $this->attrs['bgcolor'] = $value;

        return $this;
    }

    public function border($value): static
    {
        $this->attrs['border'] = $value;

        return $this;
    }

    public function buffered($value): static
    {
        $this->attrs['buffered'] = $value;

        return $this;
    }

    public function capture($value): static
    {
        $this->attrs['capture'] = $value;

        return $this;
    }

    public function challenge($value): static
    {
        $this->attrs['challenge'] = $value;

        return $this;
    }

    public function charset($value): static
    {
        $this->attrs['charset'] = $value;

        return $this;
    }

    public function checked($value = true): static
    {
        $this->attrs['checked'] = $value;

        return $this;
    }

    public function cite($value): static
    {
        $this->attrs['cite'] = $value;

        return $this;
    }

    public function class($value): static
    {
        $this->attrs['class'] = $value;

        return $this;
    }

    public function code($value): static
    {
        $this->attrs['code'] = $value;

        return $this;
    }

    public function codebase($value): static
    {
        $this->attrs['codebase'] = $value;

        return $this;
    }

    public function color($value): static
    {
        $this->attrs['color'] = $value;

        return $this;
    }

    public function cols($value): static
    {
        $this->attrs['cols'] = $value;

        return $this;
    }

    public function colspan($value): static
    {
        $this->attrs['colspan'] = $value;

        return $this;
    }

    public function content($value): static
    {
        $this->attrs['content'] = $value;

        return $this;
    }

    public function contenteditable($value): static
    {
        $this->attrs['contenteditable'] = $value;

        return $this;
    }

    public function contextmenu($value): static
    {
        $this->attrs['contextmenu'] = $value;

        return $this;
    }

    public function controls($value = true): static
    {
        $this->attrs['controls'] = $value;

        return $this;
    }

    public function coords($value): static
    {
        $this->attrs['coords'] = $value;

        return $this;
    }

    public function crossorigin($value): static
    {
        $this->attrs['crossorigin'] = $value;

        return $this;
    }

    public function csp($value): static
    {
        $this->attrs['csp'] = $value;

        return $this;
    }

    public function data($value): static
    {
        $this->attrs['data'] = $value;

        return $this;
    }

    public function datetime($value): static
    {
        $this->attrs['datetime'] = $value;

        return $this;
    }

    public function decoding($value): static
    {
        $this->attrs['decoding'] = $value;

        return $this;
    }

    public function default($value = true): static
    {
        $this->attrs['default'] = $value;

        return $this;
    }

    public function defer($value = true): static
    {
        $this->attrs['defer'] = $value;

        return $this;
    }

    public function dir($value): static
    {
        $this->attrs['dir'] = $value;

        return $this;
    }

    public function dirname($value): static
    {
        $this->attrs['dirname'] = $value;

        return $this;
    }

    public function disabled($value = true): static
    {
        $this->attrs['disabled'] = $value;

        return $this;
    }

    public function download($value): static
    {
        $this->attrs['download'] = $value;

        return $this;
    }

    public function draggable($value): static
    {
        $this->attrs['draggable'] = $value;

        return $this;
    }

    public function enctype($value): static
    {
        $this->attrs['enctype'] = $value;

        return $this;
    }

    public function enterkeyhint($value): static
    {
        $this->attrs['enterkeyhint'] = $value;

        return $this;
    }

    public function for($value): static
    {
        $this->attrs['for'] = $value;

        return $this;
    }

    public function form($value): static
    {
        $this->attrs['form'] = $value;

        return $this;
    }

    public function formaction($value): static
    {
        $this->attrs['formaction'] = $value;

        return $this;
    }

    public function formenctype($value): static
    {
        $this->attrs['formenctype'] = $value;

        return $this;
    }

    public function formmethod($value): static
    {
        $this->attrs['formmethod'] = $value;

        return $this;
    }

    public function formnovalidate($value = true): static
    {
        $this->attrs['formnovalidate'] = $value;

        return $this;
    }

    public function formtarget($value): static
    {
        $this->attrs['formtarget'] = $value;

        return $this;
    }

    public function headers($value): static
    {
        $this->attrs['headers'] = $value;

        return $this;
    }

    public function height($value): static
    {
        $this->attrs['height'] = $value;

        return $this;
    }

    public function hidden($value = true): static
    {
        $this->attrs['hidden'] = $value;

        return $this;
    }

    public function high($value): static
    {
        $this->attrs['high'] = $value;

        return $this;
    }

    public function href($value): static
    {
        $this->attrs['href'] = $value;

        return $this;
    }

    public function hreflang($value): static
    {
        $this->attrs['hreflang'] = $value;

        return $this;
    }

    public function httpEquiv($value): static
    {
        $this->attrs['http-equiv'] = $value;

        return $this;
    }

    public function icon($value): static
    {
        $this->attrs['icon'] = $value;

        return $this;
    }

    public function importance($value): static
    {
        $this->attrs['importance'] = $value;

        return $this;
    }

    public function integrity($value): static
    {
        $this->attrs['integrity'] = $value;

        return $this;
    }

    public function intrinsicsize($value): static
    {
        $this->attrs['intrinsicsize'] = $value;

        return $this;
    }

    public function inputmode($value): static
    {
        $this->attrs['inputmode'] = $value;

        return $this;
    }

    public function ismap($value = true): static
    {
        $this->attrs['ismap'] = $value;

        return $this;
    }

    public function itemprop($value): static
    {
        $this->attrs['itemprop'] = $value;

        return $this;
    }

    public function keytype($value): static
    {
        $this->attrs['keytype'] = $value;

        return $this;
    }

    public function kind($value): static
    {
        $this->attrs['kind'] = $value;

        return $this;
    }

    public function label($value): static
    {
        $this->attrs['label'] = $value;

        return $this;
    }

    public function lang($value): static
    {
        $this->attrs['lang'] = $value;

        return $this;
    }

    public function language($value): static
    {
        $this->attrs['language'] = $value;

        return $this;
    }

    public function loading($value): static
    {
        $this->attrs['loading'] = $value;

        return $this;
    }

    public function list($value): static
    {
        $this->attrs['list'] = $value;

        return $this;
    }

    public function loop($value = true): static
    {
        $this->attrs['loop'] = $value;

        return $this;
    }

    public function low($value): static
    {
        $this->attrs['low'] = $value;

        return $this;
    }

    public function manifest($value): static
    {
        $this->attrs['manifest'] = $value;

        return $this;
    }

    public function max($value): static
    {
        $this->attrs['max'] = $value;

        return $this;
    }

    public function maxlength($value): static
    {
        $this->attrs['maxlength'] = $value;

        return $this;
    }

    public function minlength($value): static
    {
        $this->attrs['minlength'] = $value;

        return $this;
    }

    public function media($value): static
    {
        $this->attrs['media'] = $value;

        return $this;
    }

    public function method($value): static
    {
        $this->attrs['method'] = $value;

        return $this;
    }

    public function min($value): static
    {
        $this->attrs['min'] = $value;

        return $this;
    }

    public function multiple($value = true): static
    {
        $this->attrs['multiple'] = $value;

        return $this;
    }

    public function muted($value = true): static
    {
        $this->attrs['muted'] = $value;

        return $this;
    }

    public function name($value): static
    {
        $this->attrs['name'] = $value;

        return $this;
    }

    public function novalidate($value = true): static
    {
        $this->attrs['novalidate'] = $value;

        return $this;
    }

    public function open($value = true): static
    {
        $this->attrs['open'] = $value;

        return $this;
    }

    public function optimum($value): static
    {
        $this->attrs['optimum'] = $value;

        return $this;
    }

    public function pattern($value): static
    {
        $this->attrs['pattern'] = $value;

        return $this;
    }

    public function ping($value): static
    {
        $this->attrs['ping'] = $value;

        return $this;
    }

    public function placeholder($value): static
    {
        $this->attrs['placeholder'] = $value;

        return $this;
    }

    public function poster($value): static
    {
        $this->attrs['poster'] = $value;

        return $this;
    }

    public function preload($value): static
    {
        $this->attrs['preload'] = $value;

        return $this;
    }

    public function radiogroup($value): static
    {
        $this->attrs['radiogroup'] = $value;

        return $this;
    }

    public function readonly($value = true): static
    {
        $this->attrs['readonly'] = $value;

        return $this;
    }

    public function referrerpolicy($value): static
    {
        $this->attrs['referrerpolicy'] = $value;

        return $this;
    }

    public function rel($value): static
    {
        $this->attrs['rel'] = $value;

        return $this;
    }

    public function required($value = true): static
    {
        $this->attrs['required'] = $value;

        return $this;
    }

    public function reversed($value = true): static
    {
        $this->attrs['reversed'] = $value;

        return $this;
    }

    public function rows($value): static
    {
        $this->attrs['rows'] = $value;

        return $this;
    }

    public function rowspan($value): static
    {
        $this->attrs['rowspan'] = $value;

        return $this;
    }

    public function sandbox($value): static
    {
        $this->attrs['sandbox'] = $value;

        return $this;
    }

    public function scope($value): static
    {
        $this->attrs['scope'] = $value;

        return $this;
    }

    public function scoped($value): static
    {
        $this->attrs['scoped'] = $value;

        return $this;
    }

    public function selected($value = true): static
    {
        $this->attrs['selected'] = $value;

        return $this;
    }

    public function shape($value): static
    {
        $this->attrs['shape'] = $value;

        return $this;
    }

    public function size($value): static
    {
        $this->attrs['size'] = $value;

        return $this;
    }

    public function sizes($value): static
    {
        $this->attrs['sizes'] = $value;

        return $this;
    }

    public function slot($value): static
    {
        $this->attrs['slot'] = $value;

        return $this;
    }

    public function span($value): static
    {
        $this->attrs['span'] = $value;

        return $this;
    }

    public function spellcheck($value): static
    {
        $this->attrs['spellcheck'] = $value;

        return $this;
    }

    public function src($value): static
    {
        $this->attrs['src'] = $value;

        return $this;
    }

    public function srcdoc($value): static
    {
        $this->attrs['srcdoc'] = $value;

        return $this;
    }

    public function srclang($value): static
    {
        $this->attrs['srclang'] = $value;

        return $this;
    }

    public function srcset($value): static
    {
        $this->attrs['srcset'] = $value;

        return $this;
    }

    public function start($value): static
    {
        $this->attrs['start'] = $value;

        return $this;
    }

    public function step($value): static
    {
        $this->attrs['step'] = $value;

        return $this;
    }

    public function style($value): static
    {
        $this->attrs['style'] = $value;

        return $this;
    }

    public function summary($value): static
    {
        $this->attrs['summary'] = $value;

        return $this;
    }

    public function tabindex($value): static
    {
        $this->attrs['tabindex'] = $value;

        return $this;
    }

    public function target($value): static
    {
        $this->attrs['target'] = $value;

        return $this;
    }

    public function title($value): static
    {
        $this->attrs['title'] = $value;

        return $this;
    }

    public function translate($value): static
    {
        $this->attrs['translate'] = $value;

        return $this;
    }

    public function type($value): static
    {
        $this->attrs['type'] = $value;

        return $this;
    }

    public function usemap($value): static
    {
        $this->attrs['usemap'] = $value;

        return $this;
    }

    public function value($value): static
    {
        $this->attrs['value'] = $value;

        return $this;
    }

    public function width($value): static
    {
        $this->attrs['width'] = $value;

        return $this;
    }

    public function wrap($value): static
    {
        $this->attrs['wrap'] = $value;

        return $this;
    }
    // GENERATED
}
