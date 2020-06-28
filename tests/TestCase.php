<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function response(string $path)
    {
        return file_get_contents(__DIR__ . '/' . 'Responses' . '/' . ltrim($path, '/'));
    }
}
