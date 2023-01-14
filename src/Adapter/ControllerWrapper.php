<?php

namespace NoFlash\GrumpyError\Adapter;

use NoFlash\GrumpyError\Grumpifier;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * This adapter is used when we need to fake a real-ish controller in older Symfony versions
 */
class ControllerWrapper
{
    /**
     * @var Grumpifier
     */
    private $grumpifier;

    /**
     * @var callable
     */
    private $exceptionController;

    public function __construct(Grumpifier $grumpifier, callable $exceptionController)
    {
        $this->grumpifier = $grumpifier;
        $this->exceptionController = $exceptionController;
    }

    public function wrapCall(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        //sometimes more arguments can be passed and PHP <5.6 doesn't support "...$arg"
        $result = \call_user_func_array($this->exceptionController, \func_get_args());
        if (!($result instanceof Response)) {
            return $result;
        }

        $result->setContent($this->grumpifier->filterString($result->getContent()));

        return $result;
    }
}
