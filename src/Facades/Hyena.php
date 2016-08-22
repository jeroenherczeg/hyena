<?php
namespace Jeroenherczeg\Hyena\Facades;

use Illuminate\Support\Facades\Facade;

class Hyena extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hyena';
    }
}
