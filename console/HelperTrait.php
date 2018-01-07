<?php

namespace Every8d\Console;

trait HelperTrait
{
    public function getErrorMessage(\Exception $e): string
    {
        return ($e->getCode() !== 0)
            ? sprintf('%d, %s', $e->getCode(), $e->getMessage())
            : $e->getMessage();
    }
}
