<?php

namespace NoFlash\GrumpyError\DependencyInjection\Compiler;

use NoFlash\GrumpyError\Adapter\ControllerWrapper;
use NoFlash\GrumpyError\Adapter\InvokableHandler;
use NoFlash\GrumpyError\Adapter\Php5InvokableHandler;
use NoFlash\GrumpyError\Adapter\Php7InvokableHandler;
use NoFlash\GrumpyError\Grumpifier;
use NoFlash\GrumpyError\GrumpyErrorHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

class MaskErrorController implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->maskErrorController($container) ||  //v4.4 - 6.3+
        $this->wrapTwigExceptionController($container); //v3.3 to <4.4
    }

    private function maskErrorController(ContainerBuilder $container)
    {
        //Newer Symfony versions have an alias 'error_controller' which we can just replace the alias
        if (!$container->hasDefinition('error_controller')) {
            return false;
        }

        $org = $container->findDefinition('error_controller')
                         ->setPublic(false);
        $container->setDefinition('_error_controller', $org);

        $handlerClass = PHP_MAJOR_VERSION >= 7 ? Php7InvokableHandler::class : Php5InvokableHandler::class;
        $rep = $container->register('error_controller', $handlerClass);
        $rep->setArgument(0, $this->registerGrumpifier($container))
            ->setArgument(1, new Reference('_error_controller'))
            ->setPublic(true);

        return true;
    }

    private function wrapTwigExceptionController(ContainerBuilder $container)
    {
        //In older versions (like v3.3) this is handled by twig bundle (up to 4.4)
        //The parameter contains a service reference, which is either a name of a service or "service.name:methodName"
        // or "service.name::methodName"
        if (!$container->hasParameter('twig.exception_listener.controller')) {
            return false;
        }

        //Since 4.4 the parameter do exist, but it is null as it was replaced by framework one
        //See https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md#twigbundle
        //Since it was replaced, a new "error_controller" service id was introduced which is easier to replace ;)
        $orgController = $container->getParameter('twig.exception_listener.controller');
        if ($orgController === null) {
            return false;
        }

        $controller = $this->resolveControllerSyntax($orgController);

        $wrapperId = 'grumpy_bundle.error_controller_wrapper';
        $wrapper = $container->register($wrapperId, ControllerWrapper::class);
        $wrapper->setArgument(0, $this->registerGrumpifier($container))
                ->setArgument(1, $controller)
                ->setPublic(true) //since Symfony 3.4 all services are private but controllers must be public
        ;


        $container->setParameter(
            'twig.exception_listener.controller',
            \sprintf('%s%swrapCall', $wrapperId, (Kernel::VERSION_ID < 40100) ? ':' : '::')
        );

        return true;
    }

    /**
     * @param string $controller
     *
     * @return callable
     */
    private function resolveControllerSyntax($controller)
    {
        //Symfony 4.1 changed the usual syntax:
        //
        // - https://github.com/symfony/twig-bundle/commit/5d86d6376f3f090351f38b74d44dfeb68617e98b
        // - https://github.com/symfony/symfony/pull/26085

        //Symfony >=4.1 will then use "service::method" syntax
        if ($methodPos = \strrpos($controller, '::')) {
             return [new Reference(\substr($controller, 0, $methodPos)), \substr($controller, $methodPos + 2)];
        }

        //Symfony <4.1 "service:method" syntax
        if ($methodPos = \strrpos($controller, ':')) {
            return [new Reference(\substr($controller, 0, $methodPos)), \substr($controller, $methodPos + 1)];
        }

        //Fallback to invokable controller class
        return new Reference($controller);
    }

    /**
     * @return Reference
     */
    private function registerGrumpifier(ContainerBuilder $container)
    {
        $grumpifierId = 'grumpy_bundle.grumpifier';
        $container->register($grumpifierId, Grumpifier::class);

        return new Reference($grumpifierId);
    }
}
