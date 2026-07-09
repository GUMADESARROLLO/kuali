<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            DepartmentSeeder::class,
            CategorySeeder::class,
            AdminUserSeeder::class,
            DepartmentUsersSeeder::class,
            AgentSeeder::class,
            CalendarSeeder::class,
            SlaRuleSeeder::class,
            TicketSeeder::class,
            TicketCommentSeeder::class,
        ]);
    }
}
