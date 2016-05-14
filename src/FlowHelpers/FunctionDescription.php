<?php namespace AOP_UT\FlowHelpers;

class FunctionDescription
{
    public $className;
    public $methodName;

    public function FunctionDescription($className = '', $methodName = '')
    {
        $this->className = $className;
        $this->methodName = $methodName;
    }

    public function setSignature($functionSignature)
    {
        $functionElements = preg_split('/::|\(\)/',  $functionSignature);
        $this->className = $functionElements[0];
        $this->methodName = $functionElements[1];
    }

    public function getSignature()
    {
        return $this->className . "::" . $this->methodName . '()';
    }

    public function getClassConstructorSignature()
    {
        $classRefl = new \ReflectionClass($this->className);
        return $this->className . '::' . $classRefl->getConstructor()->name . '()';
    }

    public function isStatic()
    {
        $methodChecker = new \ReflectionMethod('\\' . $this->className, $this->methodName);
        return $methodChecker->isStatic();
    }

    public function isConstructor()
    {
        $refl = new ReflectionClass($this->className);
        $constructor = $refl->getConstructor();
        if ($constructor && $constructor->name == $this->methodName) {
            return true;
        } else {
            return false;
        }
    }

    public function __toString()
    {
        return $this->getSignature();
    }
}
