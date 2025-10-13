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
    use HtmlAttributeMethods;

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
            return $this;
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
}
