<?php




use IceTea\IceDOM\HtmlNode;
use IceTea\IceDOM\Node;
use IceTea\IceDOM\HtmlDocument;
use IceTea\IceDOM\RawNode;
use IceTea\IceDOM\SafeString;
use IceTea\IceDOM\SlotNode;
use IceTea\IceDOM\IfElseNode;
use IceTea\IceDOM\EchoNode;

// Primitive Nodes

function _raw(...$children) : RawNode
{
    return new RawNode($children);
}
function _safe($string) : SafeString
{
    return new SafeString($string);
}
function _slot($slotFunction = null) : SlotNode
{
    return new SlotNode([], $slotFunction);
}
function _if($condition) : IfElseNode
{
    return new IfElseNode([], [], $condition);
}
function _echo(...$children): EchoNode
{
    return new EchoNode($children);
}
function _h($tagName, $arg = null): IceTea\IceDOM\HtmlNode
{
    return IceTea\IceDOM\HtmlNode::tag($tagName, $arg, []);
}
function clsf($format, string|null ...$args) {
    $params = array_map(function ($arg) {
        return is_null($arg) ? '' : $arg;
    }, $args);
    if (count(array_filter($params)) === 0) return '';
    return sprintf($format, ...$params);
}

// HTML Tag Nodes

function _a() : HtmlNode { return HtmlNode::tag('a', func_get_args(), false); }
function _abbr() : HtmlNode { return HtmlNode::tag('abbr', func_get_args(), false); }
function _address() : HtmlNode { return HtmlNode::tag('address', func_get_args(), false); }
function _area() : HtmlNode { return HtmlNode::tag('area', func_get_args(), true); }
function _article() : HtmlNode { return HtmlNode::tag('article', func_get_args(), false); }
function _aside() : HtmlNode { return HtmlNode::tag('aside', func_get_args(), false); }
function _audio() : HtmlNode { return HtmlNode::tag('audio', func_get_args(), false); }
function _b() : HtmlNode { return HtmlNode::tag('b', func_get_args(), false); }
function _base() : HtmlNode { return HtmlNode::tag('base', func_get_args(), true); }
function _bdi() : HtmlNode { return HtmlNode::tag('bdi', func_get_args(), false); }
function _bdo() : HtmlNode { return HtmlNode::tag('bdo', func_get_args(), false); }
function _blockquote() : HtmlNode { return HtmlNode::tag('blockquote', func_get_args(), false); }
function _body() : HtmlNode { return HtmlNode::tag('body', func_get_args(), false); }
function _br() : HtmlNode { return HtmlNode::tag('br', func_get_args(), true); }
function _button() : HtmlNode { return HtmlNode::tag('button', func_get_args(), false); }
function _canvas() : HtmlNode { return HtmlNode::tag('canvas', func_get_args(), false); }
function _caption() : HtmlNode { return HtmlNode::tag('caption', func_get_args(), false); }
function _cite() : HtmlNode { return HtmlNode::tag('cite', func_get_args(), false); }
function _code() : HtmlNode { return HtmlNode::tag('code', func_get_args(), false); }
function _col() : HtmlNode { return HtmlNode::tag('col', func_get_args(), true); }
function _colgroup() : HtmlNode { return HtmlNode::tag('colgroup', func_get_args(), false); }
function _data() : HtmlNode { return HtmlNode::tag('data', func_get_args(), false); }
function _datalist() : HtmlNode { return HtmlNode::tag('datalist', func_get_args(), false); }
function _del() : HtmlNode { return HtmlNode::tag('del', func_get_args(), false); }
function _details() : HtmlNode { return HtmlNode::tag('details', func_get_args(), false); }
function _dfn() : HtmlNode { return HtmlNode::tag('dfn', func_get_args(), false); }
function _dialog() : HtmlNode { return HtmlNode::tag('dialog', func_get_args(), false); }
function _div() : HtmlNode { return HtmlNode::tag('div', func_get_args(), false); }
function _dl() : HtmlNode { return HtmlNode::tag('dl', func_get_args(), false); }
function _dd() : HtmlNode { return HtmlNode::tag('dd', func_get_args(), false); }
function _dt() : HtmlNode { return HtmlNode::tag('dt', func_get_args(), false); }
function _em() : HtmlNode { return HtmlNode::tag('em', func_get_args(), false); }
function _embed() : HtmlNode { return HtmlNode::tag('embed', func_get_args(), true); }
function _fieldset() : HtmlNode { return HtmlNode::tag('fieldset', func_get_args(), false); }
function _figcaption() : HtmlNode { return HtmlNode::tag('figcaption', func_get_args(), false); }
function _figure() : HtmlNode { return HtmlNode::tag('figure', func_get_args(), false); }
function _footer() : HtmlNode { return HtmlNode::tag('footer', func_get_args(), false); }
function _form() : HtmlNode { return HtmlNode::tag('form', func_get_args(), false); }
function _h1() : HtmlNode { return HtmlNode::tag('h1', func_get_args(), false); }
function _h2() : HtmlNode { return HtmlNode::tag('h2', func_get_args(), false); }
function _h3() : HtmlNode { return HtmlNode::tag('h3', func_get_args(), false); }
function _h4() : HtmlNode { return HtmlNode::tag('h4', func_get_args(), false); }
function _h5() : HtmlNode { return HtmlNode::tag('h5', func_get_args(), false); }
function _h6() : HtmlNode { return HtmlNode::tag('h6', func_get_args(), false); }
function _head() : HtmlNode { return HtmlNode::tag('head', func_get_args(), false); }
function _header() : HtmlNode { return HtmlNode::tag('header', func_get_args(), false); }
function _hgroup() : HtmlNode { return HtmlNode::tag('hgroup', func_get_args(), false); }
function _hr() : HtmlNode { return HtmlNode::tag('hr', func_get_args(), true); }
function _html() : HtmlDocument { return HtmlDocument::tag('html', func_get_args(), false); }
function _i() : HtmlNode { return HtmlNode::tag('i', func_get_args(), false); }
function _iframe() : HtmlNode { return HtmlNode::tag('iframe', func_get_args(), false); }
function _img() : HtmlNode { return HtmlNode::tag('img', func_get_args(), true); }
function _input() : HtmlNode { return HtmlNode::tag('input', func_get_args(), true); }
function _ins() : HtmlNode { return HtmlNode::tag('ins', func_get_args(), false); }
function _kbd() : HtmlNode { return HtmlNode::tag('kbd', func_get_args(), false); }
function _label() : HtmlNode { return HtmlNode::tag('label', func_get_args(), false); }
function _legend() : HtmlNode { return HtmlNode::tag('legend', func_get_args(), false); }
function _li() : HtmlNode { return HtmlNode::tag('li', func_get_args(), false); }
function _link() : HtmlNode { return HtmlNode::tag('link', func_get_args(), true); }
function _main() : HtmlNode { return HtmlNode::tag('main', func_get_args(), false); }
function _map() : HtmlNode { return HtmlNode::tag('map', func_get_args(), false); }
function _mark() : HtmlNode { return HtmlNode::tag('mark', func_get_args(), false); }
function _meta() : HtmlNode { return HtmlNode::tag('meta', func_get_args(), true); }
function _meter() : HtmlNode { return HtmlNode::tag('meter', func_get_args(), false); }
function _nav() : HtmlNode { return HtmlNode::tag('nav', func_get_args(), false); }
function _noscript() : HtmlNode { return HtmlNode::tag('noscript', func_get_args(), false); }
function _object() : HtmlNode { return HtmlNode::tag('object', func_get_args(), false); }
function _ol() : HtmlNode { return HtmlNode::tag('ol', func_get_args(), false); }
function _optgroup() : HtmlNode { return HtmlNode::tag('optgroup', func_get_args(), false); }
function _option() : HtmlNode { return HtmlNode::tag('option', func_get_args(), false); }
function _output() : HtmlNode { return HtmlNode::tag('output', func_get_args(), false); }
function _p() : HtmlNode { return HtmlNode::tag('p', func_get_args(), false); }
function _param() : HtmlNode { return HtmlNode::tag('param', func_get_args(), true); }
function _picture() : HtmlNode { return HtmlNode::tag('picture', func_get_args(), false); }
function _pre() : HtmlNode { return HtmlNode::tag('pre', func_get_args(), false); }
function _progress() : HtmlNode { return HtmlNode::tag('progress', func_get_args(), false); }
function _q() : HtmlNode { return HtmlNode::tag('q', func_get_args(), false); }
function _rp() : HtmlNode { return HtmlNode::tag('rp', func_get_args(), false); }
function _rt() : HtmlNode { return HtmlNode::tag('rt', func_get_args(), false); }
function _ruby() : HtmlNode { return HtmlNode::tag('ruby', func_get_args(), false); }
function _s() : HtmlNode { return HtmlNode::tag('s', func_get_args(), false); }
function _samp() : HtmlNode { return HtmlNode::tag('samp', func_get_args(), false); }
function _script() : HtmlNode { return HtmlNode::tag('script', func_get_args(), false); }
function _section() : HtmlNode { return HtmlNode::tag('section', func_get_args(), false); }
function _select() : HtmlNode { return HtmlNode::tag('select', func_get_args(), false); }
function _small() : HtmlNode { return HtmlNode::tag('small', func_get_args(), false); }
function _source() : HtmlNode { return HtmlNode::tag('source', func_get_args(), true); }
function _span() : HtmlNode { return HtmlNode::tag('span', func_get_args(), false); }
function _strong() : HtmlNode { return HtmlNode::tag('strong', func_get_args(), false); }
function _style() : HtmlNode { return HtmlNode::tag('style', func_get_args(), false); }
function _sub() : HtmlNode { return HtmlNode::tag('sub', func_get_args(), false); }
function _summary() : HtmlNode { return HtmlNode::tag('summary', func_get_args(), false); }
function _sup() : HtmlNode { return HtmlNode::tag('sup', func_get_args(), false); }
function _table() : HtmlNode { return HtmlNode::tag('table', func_get_args(), false); }
function _tbody() : HtmlNode { return HtmlNode::tag('tbody', func_get_args(), false); }
function _td() : HtmlNode { return HtmlNode::tag('td', func_get_args(), false); }
function _template() : HtmlNode { return HtmlNode::tag('template', func_get_args(), false); }
function _textarea() : HtmlNode { return HtmlNode::tag('textarea', func_get_args(), false); }
function _tfoot() : HtmlNode { return HtmlNode::tag('tfoot', func_get_args(), false); }
function _th() : HtmlNode { return HtmlNode::tag('th', func_get_args(), false); }
function _thead() : HtmlNode { return HtmlNode::tag('thead', func_get_args(), false); }
function _time() : HtmlNode { return HtmlNode::tag('time', func_get_args(), false); }
function _title() : HtmlNode { return HtmlNode::tag('title', func_get_args(), false); }
function _tr() : HtmlNode { return HtmlNode::tag('tr', func_get_args(), false); }
function _track() : HtmlNode { return HtmlNode::tag('track', func_get_args(), true); }
function _u() : HtmlNode { return HtmlNode::tag('u', func_get_args(), false); }
function _ul() : HtmlNode { return HtmlNode::tag('ul', func_get_args(), false); }
function _var() : HtmlNode { return HtmlNode::tag('var', func_get_args(), false); }
function _video() : HtmlNode { return HtmlNode::tag('video', func_get_args(), false); }
function _wbr() : HtmlNode { return HtmlNode::tag('wbr', func_get_args(), true); }
function _svg() : HtmlNode { return HtmlNode::tag('svg', func_get_args(), false); }
function _circle() : HtmlNode { return HtmlNode::tag('circle', func_get_args(), false); }
function _ellipse() : HtmlNode { return HtmlNode::tag('ellipse', func_get_args(), false); }
function _line() : HtmlNode { return HtmlNode::tag('line', func_get_args(), false); }
function _polygon() : HtmlNode { return HtmlNode::tag('polygon', func_get_args(), false); }
function _polyline() : HtmlNode { return HtmlNode::tag('polyline', func_get_args(), false); }
function _rect() : HtmlNode { return HtmlNode::tag('rect', func_get_args(), false); }
function _path() : HtmlNode { return HtmlNode::tag('path', func_get_args(), false); }
function _text() : HtmlNode { return HtmlNode::tag('text', func_get_args(), false); }
function _tspan() : HtmlNode { return HtmlNode::tag('tspan', func_get_args(), false); }
function _textPath() : HtmlNode { return HtmlNode::tag('textPath', func_get_args(), false); }
function _g() : HtmlNode { return HtmlNode::tag('g', func_get_args(), false); }
function _defs() : HtmlNode { return HtmlNode::tag('defs', func_get_args(), false); }
function _use() : HtmlNode { return HtmlNode::tag('use', func_get_args(), false); }
function _symbol() : HtmlNode { return HtmlNode::tag('symbol', func_get_args(), false); }
function _image() : HtmlNode { return HtmlNode::tag('image', func_get_args(), false); }
function _marker() : HtmlNode { return HtmlNode::tag('marker', func_get_args(), false); }
function _pattern() : HtmlNode { return HtmlNode::tag('pattern', func_get_args(), false); }
function _clipPath() : HtmlNode { return HtmlNode::tag('clipPath', func_get_args(), false); }
function _mask() : HtmlNode { return HtmlNode::tag('mask', func_get_args(), false); }
function _linearGradient() : HtmlNode { return HtmlNode::tag('linearGradient', func_get_args(), false); }
function _radialGradient() : HtmlNode { return HtmlNode::tag('radialGradient', func_get_args(), false); }
function _stop() : HtmlNode { return HtmlNode::tag('stop', func_get_args(), false); }
function _filter() : HtmlNode { return HtmlNode::tag('filter', func_get_args(), false); }
function _feBlend() : HtmlNode { return HtmlNode::tag('feBlend', func_get_args(), false); }
function _feColorMatrix() : HtmlNode { return HtmlNode::tag('feColorMatrix', func_get_args(), false); }
function _feComponentTransfer() : HtmlNode { return HtmlNode::tag('feComponentTransfer', func_get_args(), false); }
function _feComposite() : HtmlNode { return HtmlNode::tag('feComposite', func_get_args(), false); }
function _feConvolveMatrix() : HtmlNode { return HtmlNode::tag('feConvolveMatrix', func_get_args(), false); }
function _feDiffuseLighting() : HtmlNode { return HtmlNode::tag('feDiffuseLighting', func_get_args(), false); }
function _feDisplacementMap() : HtmlNode { return HtmlNode::tag('feDisplacementMap', func_get_args(), false); }
function _feDistantLight() : HtmlNode { return HtmlNode::tag('feDistantLight', func_get_args(), false); }
function _feDropShadow() : HtmlNode { return HtmlNode::tag('feDropShadow', func_get_args(), false); }
function _feFlood() : HtmlNode { return HtmlNode::tag('feFlood', func_get_args(), false); }
function _feFuncA() : HtmlNode { return HtmlNode::tag('feFuncA', func_get_args(), false); }
function _feFuncB() : HtmlNode { return HtmlNode::tag('feFuncB', func_get_args(), false); }
function _feFuncG() : HtmlNode { return HtmlNode::tag('feFuncG', func_get_args(), false); }
function _feFuncR() : HtmlNode { return HtmlNode::tag('feFuncR', func_get_args(), false); }
function _feGaussianBlur() : HtmlNode { return HtmlNode::tag('feGaussianBlur', func_get_args(), false); }
function _feImage() : HtmlNode { return HtmlNode::tag('feImage', func_get_args(), false); }
function _feMerge() : HtmlNode { return HtmlNode::tag('feMerge', func_get_args(), false); }
function _feMergeNode() : HtmlNode { return HtmlNode::tag('feMergeNode', func_get_args(), false); }
function _feMorphology() : HtmlNode { return HtmlNode::tag('feMorphology', func_get_args(), false); }
function _feOffset() : HtmlNode { return HtmlNode::tag('feOffset', func_get_args(), false); }
function _fePointLight() : HtmlNode { return HtmlNode::tag('fePointLight', func_get_args(), false); }
function _feSpecularLighting() : HtmlNode { return HtmlNode::tag('feSpecularLighting', func_get_args(), false); }
function _feSpotLight() : HtmlNode { return HtmlNode::tag('feSpotLight', func_get_args(), false); }
function _feTile() : HtmlNode { return HtmlNode::tag('feTile', func_get_args(), false); }
function _feTurbulence() : HtmlNode { return HtmlNode::tag('feTurbulence', func_get_args(), false); }
function _animate() : HtmlNode { return HtmlNode::tag('animate', func_get_args(), false); }
function _animateMotion() : HtmlNode { return HtmlNode::tag('animateMotion', func_get_args(), false); }
function _animateTransform() : HtmlNode { return HtmlNode::tag('animateTransform', func_get_args(), false); }
function _mpath() : HtmlNode { return HtmlNode::tag('mpath', func_get_args(), false); }
function _set() : HtmlNode { return HtmlNode::tag('set', func_get_args(), false); }
function _foreignObject() : HtmlNode { return HtmlNode::tag('foreignObject', func_get_args(), false); }