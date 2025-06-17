<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\CreatesClient;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, CreatesClient;
}
