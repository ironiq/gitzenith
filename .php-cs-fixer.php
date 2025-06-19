<?php

$finder = PhpCsFixer\Finder::create()
	->in( __DIR__ . '/src' )
	->in( __DIR__ . '/tests' )
;

$config = new PhpCsFixer\Config();

return $config
	->setRiskyAllowed(true)
	->setIndent("\t")
	->setRules([
		'@Symfony' => true,
		'braces_position' => [
			'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'anonymous_functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
		],
		'concat_space' => [
			'spacing' => 'one'
		],
		'control_structure_continuation_position' => ['position' => 'next_line'],
		'declare_parentheses' => false,
		'function_declaration' => [
			'closure_fn_spacing' => 'none',
			'closure_function_spacing' => 'none',
			'trailing_comma_single_line' => true,
		],
		'global_namespace_import' => [
			'import_classes' => true,
			'import_constants' => false,
			'import_functions' => false,
		],
		'no_spaces_after_function_name' => true,
		'single_space_around_construct' => [
			'constructs_followed_by_a_single_space' => [],
		],
		'spaces_inside_parentheses' => [
			'space' => 'single',
		],
		'trim_array_spaces' => false,
		'yoda_style' => [
			'equal' => false,
			'identical' => false,
			'less_and_greater' => false,
			'always_move_variable' => false
		],
	])
	->setFinder($finder)
;
