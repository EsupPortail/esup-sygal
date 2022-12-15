<?php

$settings = [
    'view-dirs'     => [getcwd() . '/code'],
    'template-dirs' => [getcwd() . '/code/template'],
    'generator-dirs' => [getcwd() . '/code/generator'],
    'generator-output-dir' => '/tmp/UnicaenCode',
    'triggers' => [],
    'author' => 'Paul Hochon <paul.hochon at unicaen.fr>',
];

return [
    'unicaen-code' => $settings,
];