<?php

namespace App\Controller;

use \Psr\Container\ContainerInterface as ContainerInterface;

/**
 * Controller class
 */
class Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }


}
