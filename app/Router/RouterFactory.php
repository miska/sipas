<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;
        $router->addRoute('/<id [0-9a-f]+>', 'Paste:Show');
        $router->addRoute('/', 'Paste:Create');
        return $router;
    }
}
