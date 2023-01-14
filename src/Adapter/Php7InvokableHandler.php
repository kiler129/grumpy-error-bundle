<?php

namespace NoFlash\GrumpyError\Adapter;

class Php7InvokableHandler extends BaseInvokableHandler
{
    //This must be typehinted at \Throwable because container in newer Symfony versions relies on it. It cannot be
    // typehinted \Throwable in PHP5 as it doens't exist... and in PHP7+ we have e.e. TypeError. So yeah, two adapters
    // are needed ;')
    public function __invoke(\Throwable $exception)
    {
        return $this->doInvoke(...\func_get_args());
    }
}
