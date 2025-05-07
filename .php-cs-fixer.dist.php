<?php

$finder = (new PhpCsFixer\Finder())
    ->in(['./src', './config'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12:risky' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;