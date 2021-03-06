<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

trait TScalarConstraint
{
    use TLeafConstraint;

    abstract protected function specificValidateValue(
        \Graphpinator\Value\Value $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void;

    protected static function varianceValidateOneOf(array $greater, array $smaller) : bool
    {
        foreach ($smaller as $value) {
            if (!\in_array($value, $greater, true)) {
                return false;
            }
        }

        return true;
    }

    final protected function validateValue(
        \Graphpinator\Value\Value $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        if ($value instanceof \Graphpinator\Value\NullValue) {
            return;
        }

        if ($value instanceof \Graphpinator\Value\VariableValue) {
            $this->validateValue($value->getConcreteValue(), $arguments);

            return;
        }

        if ($value instanceof \Graphpinator\Value\ListValue) {
            foreach ($value as $item) {
                $this->validateValue($item, $arguments);
            }

            return;
        }

        $this->specificValidateValue($value, $arguments);
    }
}
