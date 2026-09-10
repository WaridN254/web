<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_user_primary_key_matches_postgres_bigint_schema(): void
    {
        $this->assertSame('int', User::make()->getKeyType());
        $this->assertTrue(User::make()->getIncrementing());
    }
}
