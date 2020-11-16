<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

class Base64FileInputFactory
{
    /**
     * @param ContainerInterface $container
     * @return Base64FileInput
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Base64FileInput(
            $container->get(StreamFactoryInterface::class),
            $container->get(UploadedFileFactoryInterface::class)
        );
    }
}
