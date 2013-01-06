<?php

namespace Kunststube\POTools;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/nikic/php-parser/lib/bootstrap.php';


class Extractor extends \PHPParser_NodeVisitorAbstract {
    
    const VARIABLE = 0;
    const MESSAGE  = 1;
    const PLURAL   = 2;
    const DOMAIN   = 3;
    const CATEGORY = 4;
    const CONTEXT  = 5;
    
    protected $parser,
              $poFactory,
              $file,
              $strings = array();
    
    protected $functions = array(
        '_'          => array(self::MESSAGE),
        'gettext'    => array(self::MESSAGE),
        'ngettext'   => array(self::MESSAGE, self::PLURAL, self::VARIABLE),
        'dgettext'   => array(self::DOMAIN, self::MESSAGE),
        'dngettext'  => array(self::DOMAIN, self::MESSAGE, self::PLURAL, self::VARIABLE),
        'dcgettext'  => array(self::DOMAIN, self::MESSAGE, self::CATEGORY),
        'dcngettext' => array(self::DOMAIN, self::MESSAGE, self::PLURAL, self::VARIABLE, self::CATEGORY)
    );
    
    public function __construct(POStringFactory $poFactory = null) {
        $this->parser = new \PHPParser_Parser(new \PHPParser_Lexer);

        if (!$poFactory) {
            require_once __DIR__ . '/POStringFactory.php';
            $poFactory = new POStringFactory;
        }
        $this->poFactory = $poFactory;
    }
    
    public function extractFile($file) {
        return $this->extractSource(file_get_contents($file), $file);
    }
    
    public function extractSource($source, $file = null) {
        $this->file    = $file;
        $this->strings = array();
        
        $stmts = $this->parser->parse($source);
        $this->extractStrings($stmts);
        return $this->strings;
    }
    
    protected function extractStrings(array $stmts) {
        $traverser = new \PHPParser_NodeTraverser;
        $traverser->addVisitor($this);
        $traverser->traverse($stmts);
    }
    
    public function leaveNode(\PHPParser_Node $node) {
        if ($node instanceof \PHPParser_Node_Expr_FuncCall && isset($this->functions[$node->name->toString()])) {
            $this->parseFunctionNode($node, $this->functions[$node->name->toString()]);
        }
    }
    
    protected function parseFunctionNode(\PHPParser_Node_Expr_FuncCall $node, array $argsSpec) {
        $args = $node->args;
        if (count($args) != count($argsSpec)) {
            throw new \InvalidArgumentException;
        }
        
        $string = array();
        
        for ($i = 0, $length = count($args); $i < $length; $i++) {
            if ($argsSpec[$i] == self::VARIABLE) {
                continue;
            } else if (!($args[$i]->value instanceof \PHPParser_Node_Scalar_String)) {
                throw new \InvalidArgumentException;
            }
            $string[$argsSpec[$i]] = $args[$i]->value->value;
        }
        
        $this->addString($string, $node);
    }
    
    protected function addString(array $string, \PHPParser_Node_Expr_FuncCall $node) {
        if (!isset($string[self::MESSAGE])) {
            throw new \InvalidArgumentException('$string requires MESSAGE key');
        }
        
        $POString = $this->poFactory->construct($string[self::MESSAGE]);
        
        foreach ($string as $type => $value) {
            switch ($type) {
                case self::PLURAL :
                    $POString->setMsgidPlural($value);
                    break;
                case self::DOMAIN :
                    $POString->setDomain($value);
                    break;
                case self::CATEGORY :
                    $POString->setCategory($value);
                    break;
                case self::CONTEXT :
                    $POString->setContext($value);
                    break;
            }
        }
        
        $POString->addReference("$this->file:" . $node->getAttribute('startLine'));
        $this->strings[] = $POString;
    }
    
}