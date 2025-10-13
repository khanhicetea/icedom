<?php




use Icetea\IceDOM\HtmlNode;
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
function _h($tagName, $arg = null): Icetea\IceDOM\HtmlNode
{
    return Icetea\IceDOM\HtmlNode::tag($tagName, $arg, []);
}
function clsf($format, string|null ...$args) {
    $params = array_map(function ($arg) {
        return is_null($arg) ? '' : $arg;
    }, $args);
    if (count(array_filter($params)) === 0) return '';
    return sprintf($format, ...$params);
}

// HTML Tag Nodes

function _a(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('a', $arg, $children, false); }
function _abbr(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('abbr', $arg, $children, false); }
function _address(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('address', $arg, $children, false); }
function _area(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('area', $arg, $children, true); }
function _article(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('article', $arg, $children, false); }
function _aside(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('aside', $arg, $children, false); }
function _audio(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('audio', $arg, $children, false); }
function _b(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('b', $arg, $children, false); }
function _base(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('base', $arg, $children, true); }
function _bdi(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('bdi', $arg, $children, false); }
function _bdo(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('bdo', $arg, $children, false); }
function _blockquote(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('blockquote', $arg, $children, false); }
function _body(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('body', $arg, $children, false); }
function _br(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('br', $arg, $children, true); }
function _button(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('button', $arg, $children, false); }
function _canvas(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('canvas', $arg, $children, false); }
function _caption(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('caption', $arg, $children, false); }
function _cite(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('cite', $arg, $children, false); }
function _code(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('code', $arg, $children, false); }
function _col(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('col', $arg, $children, true); }
function _colgroup(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('colgroup', $arg, $children, false); }
function _data(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('data', $arg, $children, false); }
function _datalist(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('datalist', $arg, $children, false); }
function _del(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('del', $arg, $children, false); }
function _details(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('details', $arg, $children, false); }
function _dfn(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('dfn', $arg, $children, false); }
function _dialog(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('dialog', $arg, $children, false); }
function _div(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('div', $arg, $children, false); }
function _dl(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('dl', $arg, $children, false); }
function _dd(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('dd', $arg, $children, false); }
function _dt(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('dt', $arg, $children, false); }
function _em(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('em', $arg, $children, false); }
function _embed(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('embed', $arg, $children, true); }
function _fieldset(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('fieldset', $arg, $children, false); }
function _figcaption(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('figcaption', $arg, $children, false); }
function _figure(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('figure', $arg, $children, false); }
function _footer(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('footer', $arg, $children, false); }
function _form(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('form', $arg, $children, false); }
function _h1(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('h1', $arg, $children, false); }
function _h2(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('h2', $arg, $children, false); }
function _h3(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('h3', $arg, $children, false); }
function _h4(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('h4', $arg, $children, false); }
function _h5(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('h5', $arg, $children, false); }
function _h6(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('h6', $arg, $children, false); }
function _head(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('head', $arg, $children, false); }
function _header(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('header', $arg, $children, false); }
function _hgroup(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('hgroup', $arg, $children, false); }
function _hr(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('hr', $arg, $children, true); }
function _html(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlDocument { return HtmlDocument::tag('html', $arg, $children, false); }
function _i(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('i', $arg, $children, false); }
function _iframe(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('iframe', $arg, $children, false); }
function _img(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('img', $arg, $children, true); }
function _input(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('input', $arg, $children, true); }
function _ins(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('ins', $arg, $children, false); }
function _kbd(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('kbd', $arg, $children, false); }
function _label(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('label', $arg, $children, false); }
function _legend(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('legend', $arg, $children, false); }
function _li(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('li', $arg, $children, false); }
function _link(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('link', $arg, $children, true); }
function _main(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('main', $arg, $children, false); }
function _map(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('map', $arg, $children, false); }
function _mark(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('mark', $arg, $children, false); }
function _meta(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('meta', $arg, $children, true); }
function _meter(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('meter', $arg, $children, false); }
function _nav(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('nav', $arg, $children, false); }
function _noscript(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('noscript', $arg, $children, false); }
function _object(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('object', $arg, $children, false); }
function _ol(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('ol', $arg, $children, false); }
function _optgroup(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('optgroup', $arg, $children, false); }
function _option(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('option', $arg, $children, false); }
function _output(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('output', $arg, $children, false); }
function _p(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('p', $arg, $children, false); }
function _param(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('param', $arg, $children, true); }
function _picture(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('picture', $arg, $children, false); }
function _pre(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('pre', $arg, $children, false); }
function _progress(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('progress', $arg, $children, false); }
function _q(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('q', $arg, $children, false); }
function _rp(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('rp', $arg, $children, false); }
function _rt(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('rt', $arg, $children, false); }
function _ruby(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('ruby', $arg, $children, false); }
function _s(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('s', $arg, $children, false); }
function _samp(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('samp', $arg, $children, false); }
function _script(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('script', $arg, $children, false); }
function _section(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('section', $arg, $children, false); }
function _select(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('select', $arg, $children, false); }
function _small(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('small', $arg, $children, false); }
function _source(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('source', $arg, $children, true); }
function _span(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('span', $arg, $children, false); }
function _strong(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('strong', $arg, $children, false); }
function _style(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('style', $arg, $children, false); }
function _sub(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('sub', $arg, $children, false); }
function _summary(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('summary', $arg, $children, false); }
function _sup(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('sup', $arg, $children, false); }
function _table(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('table', $arg, $children, false); }
function _tbody(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('tbody', $arg, $children, false); }
function _td(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('td', $arg, $children, false); }
function _template(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('template', $arg, $children, false); }
function _textarea(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('textarea', $arg, $children, false); }
function _tfoot(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('tfoot', $arg, $children, false); }
function _th(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('th', $arg, $children, false); }
function _thead(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('thead', $arg, $children, false); }
function _time(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('time', $arg, $children, false); }
function _title(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('title', $arg, $children, false); }
function _tr(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('tr', $arg, $children, false); }
function _track(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('track', $arg, $children, true); }
function _u(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('u', $arg, $children, false); }
function _ul(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('ul', $arg, $children, false); }
function _var(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('var', $arg, $children, false); }
function _video(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('video', $arg, $children, false); }
function _wbr(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('wbr', $arg, $children, true); }
function _svg(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('svg', $arg, $children, false); }
function _circle(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('circle', $arg, $children, false); }
function _ellipse(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('ellipse', $arg, $children, false); }
function _line(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('line', $arg, $children, false); }
function _polygon(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('polygon', $arg, $children, false); }
function _polyline(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('polyline', $arg, $children, false); }
function _rect(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('rect', $arg, $children, false); }
function _path(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('path', $arg, $children, false); }
function _text(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('text', $arg, $children, false); }
function _tspan(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('tspan', $arg, $children, false); }
function _textPath(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('textPath', $arg, $children, false); }
function _g(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('g', $arg, $children, false); }
function _defs(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('defs', $arg, $children, false); }
function _use(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('use', $arg, $children, false); }
function _symbol(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('symbol', $arg, $children, false); }
function _image(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('image', $arg, $children, false); }
function _marker(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('marker', $arg, $children, false); }
function _pattern(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('pattern', $arg, $children, false); }
function _clipPath(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('clipPath', $arg, $children, false); }
function _mask(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('mask', $arg, $children, false); }
function _linearGradient(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('linearGradient', $arg, $children, false); }
function _radialGradient(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('radialGradient', $arg, $children, false); }
function _stop(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('stop', $arg, $children, false); }
function _filter(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('filter', $arg, $children, false); }
function _feBlend(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feBlend', $arg, $children, false); }
function _feColorMatrix(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feColorMatrix', $arg, $children, false); }
function _feComponentTransfer(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feComponentTransfer', $arg, $children, false); }
function _feComposite(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feComposite', $arg, $children, false); }
function _feConvolveMatrix(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feConvolveMatrix', $arg, $children, false); }
function _feDiffuseLighting(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feDiffuseLighting', $arg, $children, false); }
function _feDisplacementMap(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feDisplacementMap', $arg, $children, false); }
function _feDistantLight(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feDistantLight', $arg, $children, false); }
function _feDropShadow(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feDropShadow', $arg, $children, false); }
function _feFlood(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feFlood', $arg, $children, false); }
function _feFuncA(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feFuncA', $arg, $children, false); }
function _feFuncB(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feFuncB', $arg, $children, false); }
function _feFuncG(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feFuncG', $arg, $children, false); }
function _feFuncR(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feFuncR', $arg, $children, false); }
function _feGaussianBlur(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feGaussianBlur', $arg, $children, false); }
function _feImage(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feImage', $arg, $children, false); }
function _feMerge(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feMerge', $arg, $children, false); }
function _feMergeNode(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feMergeNode', $arg, $children, false); }
function _feMorphology(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feMorphology', $arg, $children, false); }
function _feOffset(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feOffset', $arg, $children, false); }
function _fePointLight(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('fePointLight', $arg, $children, false); }
function _feSpecularLighting(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feSpecularLighting', $arg, $children, false); }
function _feSpotLight(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feSpotLight', $arg, $children, false); }
function _feTile(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feTile', $arg, $children, false); }
function _feTurbulence(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('feTurbulence', $arg, $children, false); }
function _animate(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('animate', $arg, $children, false); }
function _animateMotion(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('animateMotion', $arg, $children, false); }
function _animateTransform(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('animateTransform', $arg, $children, false); }
function _mpath(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('mpath', $arg, $children, false); }
function _set(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('set', $arg, $children, false); }
function _foreignObject(null | string | array | Closure | Node | SafeString $arg = null, ?array $children = null) : HtmlNode { return HtmlNode::tag('foreignObject', $arg, $children, false); }