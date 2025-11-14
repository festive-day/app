<?php
/**
 * EtchParser test class.
 *
 * @package Etch
 */

declare(strict_types=1);

namespace Etch\Preprocessor\Utilities\Tests;

use WP_UnitTestCase;
use Etch\Preprocessor\Utilities\EtchParser;

/**
 * Class EtchParserTest
 */
class EtchParserTest extends WP_UnitTestCase {
	/**
	 * Test the is_dynamic_expression method returns true for simple dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_true_for_simple_dynamic_expressions() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{someVar}' ) );
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{anotherVar}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns true for simple dynamic expression with modifier.
	 */
	public function test_is_dynamic_expression_returns_true_for_simple_dynamic_expression_with_modifier() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{someVar.format()}' ) );
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{anotherVar.toUpperCase()}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns true for string dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_true_for_string_dynamic_expressions() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{"stringValue"}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns true for number dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_true_for_number_dynamic_expressions() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{123}' ) );
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{45.67}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns true for boolean dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_true_for_boolean_dynamic_expressions() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{true}' ) );
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{false}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns true for complex dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_true_for_complex_dynamic_expressions() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{user.name.toUpperCase()}' ) );
		$this->assertEquals( true, EtchParser::is_dynamic_expression( "{post.title.format('Y-m-d')}" ) );
		$this->assertEquals( true, EtchParser::is_dynamic_expression( "{items.filter(type: 'active')}" ) );
	}

	/**
	 * Test the is_dynamic_expression method returns true for array dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_true_for_array_dynamic_expressions() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{[1, 2, 3]}' ) );
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{["a", "b", "c"]}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns true for object dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_true_for_object_dynamic_expressions() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{{key: "value", num: 42}}' ) );
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{{nested: {innerKey: "innerValue"}}}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns true for chained dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_true_for_chained_dynamic_expressions() {
		$this->assertEquals( true, EtchParser::is_dynamic_expression( '{user.getProfile().getName().toUpperCase()}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns false for dynamic expressions with unbalanced braces.
	 */
	public function test_is_dynamic_expression_returns_false_for_unbalanced_braces() {
		$this->assertEquals( false, EtchParser::is_dynamic_expression( '{someVar' ) );
		$this->assertEquals( false, EtchParser::is_dynamic_expression( 'someVar}' ) );
		$this->assertEquals( false, EtchParser::is_dynamic_expression( '{{someVar}' ) );
		$this->assertEquals( false, EtchParser::is_dynamic_expression( '{someVar}}' ) );
		$this->assertEquals( false, EtchParser::is_dynamic_expression( '{{someVar}}}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns false for strings starting and ending with braces but not standalone dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_false_for_non_standalone_expressions() {
		$this->assertEquals( false, EtchParser::is_dynamic_expression( '{firstExpression} middle {lastExpression}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns false for empty or too short strings.
	 */
	public function test_is_dynamic_expression_returns_false_for_empty_or_short_strings() {
		$this->assertEquals( false, EtchParser::is_dynamic_expression( '' ) );
		$this->assertEquals( false, EtchParser::is_dynamic_expression( '{a' ) );
		$this->assertEquals( false, EtchParser::is_dynamic_expression( 'a}' ) );
	}

	/**
	 * Test the is_dynamic_expression method returns false for invalid dynamic expressions.
	 */
	public function test_is_dynamic_expression_returns_false_for_invalid_expressions() {
		$this->assertEquals( false, EtchParser::is_dynamic_expression( 'notDynamic' ) );
		$this->assertEquals( false, EtchParser::is_dynamic_expression( '{invalid}expression' ) );
	}
}
