<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/tests')
;

$config = new PhpCsFixer\Config();
return $config->setRules([
		'@Symfony' => true,
		'global_namespace_import' => [
			'import_classes' => true,
			'import_constants' => false,
			'import_functions' => false,
		],
		'no_spaces_after_function_name' => true,
		'spaces_inside_parentheses' => ['space' => 'single',]
	])
	->setIndent("\t")
	->setRiskyAllowed(true)
	->setFinder($finder)
;
