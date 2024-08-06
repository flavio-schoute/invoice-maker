<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->path([
        '/^app/',
        '/^src/',
    ]);

/** @var PhpCsFixer\Config $config */
$config = include 'vendor/paqtcom/coding-standards/rules/php-cs-fixer.php';

return $config->setFinder($finder);
