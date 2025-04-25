<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Enable foreign key support for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }
}

