<?php

namespace Matthias\SymfonyServiceDefinitionValidator;

class Configuration
{
    private $evaluateExpressions = false;

    /**
     * Configure whether or not to evaluate expression arguments
     *
     * @param boolean $evaluateExpressions
     */
    public function setEvaluateExpressions($evaluateExpressions)
    {
        $this->evaluateExpressions = (boolean) $evaluateExpressions;

        return $this;
    }

    public function getEvaluateExpressions()
    {
        return $this->evaluateExpressions;
    }
}
