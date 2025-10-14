<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../src/generated_html_tags.php';

$users = [
    ['name' => 'John', 'email' => 'john@example.com', 'role' => 'Admin'],
    ['name' => 'Jane', 'email' => 'jane@example.com', 'role' => 'User'],
];

$table = _table('class="users-table"', [
    _thead([
        _tr([
            _th(['Name']),
            _th(['Email']),
            _th(['Role']),
        ]),
    ]),
    _tbody(array_map(fn ($user) => _tr([
        _td([$user['name']]),
        _td([$user['email']]),
        _td([_span("class=\"badge badge-{$user['email']}\"", [$user['role']])]),
    ]), $users
    )),
]);

echo _html([
    _head([
        _title(['Ice Ice Icea']),
    ]),
    _body([
        _h1(['Ice Ice Icea']),
        _div()->map(range(0, 100), fn () => $table),
    ]),
]);
