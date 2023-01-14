<?php

namespace NoFlash\GrumpyError\Adapter;

use NoFlash\GrumpyError\Grumpifier;
use Symfony\Component\HttpFoundation\Response;

/**
 * This wrapper is used in newer Symfony versions, where a error handler can just be replaced with a non-controller
 */
abstract class BaseInvokableHandler
{
    /**
     * @var Grumpifier
     */
    protected $grumpifier;

    /**
     * @var callable
     */
    protected $errorController;

    public function __construct(Grumpifier $grumpifier, callable $controller)
    {
        $this->grumpifier = $grumpifier;
        $this->errorController = $controller;
    }

    public function __call($name, array $arguments)
    {
        return \call_user_func_array([$this->errorController, $name], $arguments);
    }

    protected function doInvoke()
    {
        //in PHP <7.0 there's uniform variable syntax, so ($this->foo)() doesn't work
        $result = \call_user_func_array($this->errorController, \func_get_args());
        if (!($result instanceof Response)) {
            return $result;
        }

        $result->setContent($this->grumpifier->filterString($result->getContent()));

        return $result;
    }
}
