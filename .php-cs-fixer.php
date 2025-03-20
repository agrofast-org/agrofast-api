<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@PhpCsFixer' => true,
    'ordered_imports' => true,
    'no_unused_imports' => true,
    'no_mixed_echo_print' => ['use' => 'print'],
    'echo_tag_syntax' => ['format' => 'short'],
    'increment_style' => ['style' => 'post'],
    'yoda_style' => false,
    'concat_space' => true,
    'method_chaining_indentation' => true,
];

$finder = Finder::create()
    ->in([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
;
