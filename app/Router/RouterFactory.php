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
        $router->addRoute('/<id [0-9a-f]+>/raw', 'Paste:ShowRaw');
        $router->addRoute('/list/[<page \d+>]', 'Paste:List');
        $router->addRoute('/', 'Paste:Create');
        $router->addRoute('/cron', 'Paste:Cron');
        return $router;
    }
}
