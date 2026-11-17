<?php

namespace Tests\Concerns;

trait UsesIsolatedDatabase
{
    protected function assertIsolatedDatabase(): void
    {
        // Refuse to run database tests against the configured application database.
        $this->assertFalse($this->app->configurationIsCached(), 'Clear cached configuration before running tests.');
        $this->assertSame('testing', $this->app->environment());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
    }
}
