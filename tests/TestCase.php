<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Indicates if database is available for testing
     */
    protected static $databaseAvailable = null;

    /**
     * Check database availability before setting up traits
     */
    protected function setUpTraits()
    {
        // Check database availability once
        if (static::$databaseAvailable === null) {
            static::$databaseAvailable = $this->checkDatabaseAvailability();
        }

        // Skip test if database is required but not available
        if (!static::$databaseAvailable && $this->usesDatabaseTraits()) {
            $this->markTestSkipped('Database driver not available. Install php-sqlite3 or configure MySQL.');
        }

        return parent::setUpTraits();
    }

    /**
     * Check if database connection is available
     */
    protected function checkDatabaseAvailability(): bool
    {
        return true;
    }

    /**
     * Determine if test uses database traits
     */
    protected function usesDatabaseTraits(): bool
    {
        $uses = array_flip(class_uses_recursive(static::class));
        return isset($uses['Illuminate\Foundation\Testing\RefreshDatabase']) ||
               isset($uses['Illuminate\Foundation\Testing\DatabaseMigrations']) ||
               isset($uses['Illuminate\Foundation\Testing\DatabaseTransactions']);
    }
}
