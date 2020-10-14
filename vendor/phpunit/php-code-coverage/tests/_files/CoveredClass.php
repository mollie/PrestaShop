<?php

namespace MolliePrefix;

class CoveredParentClass
{
    private function privateMethod()
    {
    }
    protected function protectedMethod()
    {
        $this->privateMethod();
    }
    public function publicMethod()
    {
        $this->protectedMethod();
    }
}
\class_alias('MolliePrefix\\CoveredParentClass', 'CoveredParentClass', \false);
class CoveredClass extends \MolliePrefix\CoveredParentClass
{
    private function privateMethod()
    {
    }
    protected function protectedMethod()
    {
        parent::protectedMethod();
        $this->privateMethod();
    }
    public function publicMethod()
    {
        parent::publicMethod();
        $this->protectedMethod();
    }
}
\class_alias('MolliePrefix\\CoveredClass', 'CoveredClass', \false);
