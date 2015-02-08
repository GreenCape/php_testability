<?php

require_once __DIR__.'/../vendor/autoload.php';
use edsonmedina\php_testability\AbstractIssue;
use PhpParser\Node\Expr\Exit_;

class AbstractIssueTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @covers edsonmedina\php_testability\AbstractIssue::__construct
	 */
	public function testConstructor ()
	{
		$node = $this->getMockBuilder('PhpParser\Node\Expr\StaticCall')
		             ->disableOriginalConstructor()
		             ->getMock();

		$issue = $this->getMockBuilder('edsonmedina\php_testability\AbstractIssue')
		              ->setConstructorArgs(array($node))
		              ->getMockForAbstractClass();

		$this->assertAttributeEquals($node, 'node', $issue);
	}	

	/**
	 * @covers edsonmedina\php_testability\AbstractIssue::getLine
	 */
	public function testGetLine ()
	{
		$node = $this->getMockBuilder('PhpParser\Node\Expr\StaticCall')
		             ->disableOriginalConstructor()
		             ->getMock();

		$node->method('getLine')->willReturn (123);

		$issue = $this->getMockBuilder('edsonmedina\php_testability\AbstractIssue')
		              ->setConstructorArgs(array($node))
		              ->getMockForAbstractClass();

		$this->assertEquals (123, $issue->getLine());
	}	
}