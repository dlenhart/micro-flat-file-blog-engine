<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/bootstrap', __DIR__ . '/routes', __DIR__ . '/tests'])
    ->name('*.php')
    ->notName('*.view.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
