<?php

it('can test', function () {
    expect(true)->toBeTrue();
});

it('html_doc', function () {
    expect(
        (string) _html()
    )->toEqual("<!DOCTYPE html>\n<html></html>");
});
