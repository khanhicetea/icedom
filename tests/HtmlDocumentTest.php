<?php

use IceTea\IceDOM\HtmlDocument;

it('creates an empty HTML document with DOCTYPE', function () {
    $document = new HtmlDocument([], 'html');

    expect((string) $document)->toBe("<!DOCTYPE html>\n<html></html>");
});

it('creates HTML document with content', function () {
    $document = new HtmlDocument(['Hello World'], 'html');

    expect((string) $document)->toBe("<!DOCTYPE html>\n<html>Hello World</html>");
});
