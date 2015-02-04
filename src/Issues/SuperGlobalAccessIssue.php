<?php
namespace edsonmedina\php_testability\Issues;
use edsonmedina\php_testability\AbstractIssue;
use edsonmedina\php_testability\NodeWrapper;

class SuperGlobalAccessIssue extends AbstractIssue
{
	public function getTitle()
	{
		return "Super global access";
	}

	public function getID()
	{
        $obj = new NodeWrapper ($this->node);
        return '$'.$this->node->var->name;
	}
}