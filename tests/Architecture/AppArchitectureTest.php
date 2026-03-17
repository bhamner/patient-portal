<?php

use Pest\Arch\ArchExpectation;

arch('no debugging functions are used')
    ->expect(['dd', 'dump', 'ray', 'logger'])
    ->not->toBeUsed();

arch('controllers follow Laravel naming conventions')
    ->preset()
    ->laravel();

arch()->preset()->php();