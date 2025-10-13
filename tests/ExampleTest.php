<?php

it('can test', function () {
    expect(true)->toBeTrue();
});

it('html_doc', function () {
    expect(
        (string) _html([
            _body([
                _h1(['Iceeeee Teaaaaa']),
            ]),
        ])
    )->toBeString("<!DOCTYPE html>\n<html><body><h1>Iceeeee Teaaaaa</h1></body></html>");
});
