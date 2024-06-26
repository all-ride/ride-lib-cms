<?php

namespace ride\library\cms\content\text\variable;

use ride\library\reflection\ReflectionHelper;

/**
 * Implementation to parse context variables
 */
class ContextVariableParser extends AbstractVariableParser {
    protected $reflectionHelper;

    /**
     * Constructs a new variable parser
     * @param \ride\library\reflection\ReflectionHelper $reflectionHelper
     * @return null
     */
    public function __construct(ReflectionHelper $reflectionHelper) {
        $this->reflectionHelper = $reflectionHelper;
    }

    /**
     * Parses the provided variable
     * @param string $variable Full variable
     * @return mixed Value of the variable if resolved, null otherwise
     */
    public function parseVariable($variable) {
        $tokens = explode('.', $variable);
        if ($tokens[0] !== 'context') {
            return null;
        }

        array_shift($tokens);

        $value = null;
        do {
            $token = array_shift($tokens);

            if ($value === null) {
                $value = $this->textParser->getNode()->getContext($token);
            } else {
                $value = $this->reflectionHelper->getProperty($value, $token);
            }

            if ($value === null) {
                break;
            }
        } while ($tokens);

        return $value;
    }

}
