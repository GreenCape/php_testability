<?php
namespace edsonmedina\php_testability;

use PhpParser;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

class NodeWrapper
{
	private $node;
	public  $line;
	public  $endLine;

	public function __construct (PhpParser\Node $node) 
	{
		$this->node    = $node;
		$this->line    = $node->getLine();
		$this->endLine = $node->getAttribute('endLine');
	}

	public function getVarList() 
	{
		return $this->node->vars;
	}

	public function getName() 
	{	
		$name      = '';
		$separator = '';

		if (!empty($this->node->class->parts)) 
		{
			if (is_array($this->node->class->parts)) 
			{
				// fully qualified names
				$name .= join ('\\', $this->node->class->parts);
			} 
			else 
			{
				$name .= $this->node->class->parts;
			}

			$separator = '::';
		}

		if ($this->node->name instanceof Expr\Variable) 
		{
			$name .= $separator . $this->node->getAttribute('name');
		} 
		elseif ($this->node->name instanceof Expr\ArrayDimFetch) 
		{
			$name .= 'variable function';
		} 
		else
		{
			if (!empty($this->node->name)) 
			{
				$name .= $separator . $this->node->name;
			}
		}
	
		return $name;
	}

	public function isSameClassAs ($classname) 
	{
		$name = end($this->node->class->parts);
		return ($name === $classname || $name === 'self');
	}

	public function hasChildren() 
	{
		return isset($this->node->stmts);
	}

    /**
     * Is node allowed on global space?
     * @param NodeWrapper $node
     * @return bool
     */
    public function isAllowedOnGlobalSpace () 
    {
    	// TODO use specification pattern
        return (
	        	$this->node instanceof Stmt\Class_
	        	|| $this->node instanceof Stmt\Trait_ 
	        	|| $this->node instanceof Stmt\Function_
	        	|| ($this->node instanceof Stmt\UseUse || $this->node instanceof Stmt\Use_)
	        	|| ($this->node instanceof Stmt\Namespace_ || $this->node instanceof Node\Name)
	        	|| $this->node instanceof Stmt\Interface_
        	);
    }
}