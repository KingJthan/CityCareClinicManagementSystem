<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_user_role_checks_accept_nested_arrays(): void
    {
        $user = new User([
            'role' => 'admin',
        ]);

        $this->assertTrue($user->hasRole([['admin']]));
        $this->assertTrue($user->hasAnyRole(['doctor', 'admin']));
        $this->assertFalse($user->hasRole([['doctor']]));
    }
}
