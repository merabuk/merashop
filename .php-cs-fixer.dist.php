<?php

$finder = new PhpCsFixer\Finder()
    ->in(__DIR__)
    ->exclude(['assets', 'node_modules', 'var', 'vendor'])
;

return new PhpCsFixer\Config()
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        'general_phpdoc_annotation_remove' => false,
        'phpdoc_to_comment' => false,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
    ])
    ->setFinder($finder)
;
