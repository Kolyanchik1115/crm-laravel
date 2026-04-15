<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/app')
    ->in(__DIR__ . '/tests')
    ->exclude(['vendor']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // PSR-12
        '@PSR12' => true,

        // declare(strict_types=1)
        'declare_strict_types' => true,

        // Strict comparisons
        'strict_param' => true,
    ])
    ->setFinder($finder);
