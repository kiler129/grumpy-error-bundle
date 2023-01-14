<?php

namespace NoFlash\GrumpyError\Adapter;

class Php5InvokableHandler extends BaseInvokableHandler
{
    //This MUST be typehinted as "\Exception". See detailed explanation in Php7InvokableHandler.
    public function __invoke(\Exception $exception)
    {
        return \call_user_func_array([$this, 'doInvoke'], \func_get_args());
    }
}
