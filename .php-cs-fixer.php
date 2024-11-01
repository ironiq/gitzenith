<?php

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/tests')
;

$config = new PhpCsFixer\Config();
return $config->setRules([
		'@Symfony' => true,
		'trim_array_spaces' => false,
		'declare_parentheses' => false,
		'no_spaces_after_function_name' => true,
		'global_namespace_import' => [
			'import_classes' => true,
			'import_constants' => false,
			'import_functions' => false,
		],
		'braces_position' => [
			'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'anonymous_functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
		],
		'single_space_around_construct' => [
			'constructs_followed_by_a_single_space' => [],
		],
		'control_structure_continuation_position' => [
			'position' => 'next_line',
		],
		'spaces_inside_parentheses' => [
			'space' => 'single',
		],
		'function_declaration' => [
			'closure_fn_spacing' => 'none',
			'closure_function_spacing' => 'none',
		],
	])
	->setIndent("\t")
	->setRiskyAllowed(true)
	->setFinder($finder)
;
