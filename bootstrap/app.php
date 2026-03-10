<?php

use App\Core\Container\Container;
use App\Core\Facades\Facade;

$container = new Container();

Facade::setContainer($container);