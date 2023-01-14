<?php

namespace NoFlash\GrumpyError;

use NoFlash\GrumpyError\DependencyInjection\Compiler\MaskErrorController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GrumpyErrorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MaskErrorController());
    }
}
