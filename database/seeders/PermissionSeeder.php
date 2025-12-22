<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infra\Roles\Models\Permissions\Permissions;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Users service
            ['function' => 'add-user', 'apps' => 'users'],
            ['function' => 'edit-user', 'apps' => 'users'],
            ['function' => 'delete-user', 'apps' => 'users'],
            ['function' => 'view-user', 'apps' => 'users'],
            ['function' => 'edit-user-profile', 'apps' => 'users'],

            // Roles/Permissions service
            ['function' => 'add-role', 'apps' => 'roles'],
            ['function' => 'edit-role', 'apps' => 'roles'],
            ['function' => 'delete-role', 'apps' => 'roles'],
            ['function' => 'view-role', 'apps' => 'roles'],
            ['function' => 'view-permission', 'apps' => 'permissions'],
            ['function' => 'view-permission-apps', 'apps' => 'permissions'],

            // Storage service
            ['function' => 'upload-file', 'apps' => 'storage'],

            // Reports service
            ['function' => 'view-report', 'apps' => 'reports'],
            ['function' => 'create-report', 'apps' => 'reports'],
            ['function' => 'update-report', 'apps' => 'reports'],
            ['function' => 'delete-report', 'apps' => 'reports'],
            ['function' => 'export-report', 'apps' => 'reports'],
            ['function' => 'submit-report', 'apps' => 'reports'],
            ['function' => 'manage-assignees', 'apps' => 'reports'],

            // Approvals service
            ['function' => 'review-report', 'apps' => 'approvals'],

            // Evidences service
            ['function' => 'presign-evidence', 'apps' => 'evidences'],
            ['function' => 'finalize-evidence', 'apps' => 'evidences'],
            ['function' => 'delete-evidence', 'apps' => 'evidences'],

            // Notifications service
            ['function' => 'list-notifications', 'apps' => 'notifications'],
            ['function' => 'mark-notification', 'apps' => 'notifications'],
            ['function' => 'read-all-notifications', 'apps' => 'notifications'],

            // Assets service
            ['function' => 'view-asset', 'apps' => 'assets'],
            ['function' => 'add-asset', 'apps' => 'assets'],
            ['function' => 'edit-asset', 'apps' => 'assets'],
            ['function' => 'delete-asset', 'apps' => 'assets'],
            ['function' => 'activate-asset', 'apps' => 'assets'],
            ['function' => 'deactivate-asset', 'apps' => 'assets'],
            ['function' => 'maintain-asset', 'apps' => 'assets'],
            ['function' => 'retire-asset', 'apps' => 'assets'],
            ['function' => 'attach-asset-report', 'apps' => 'assets'],
            ['function' => 'detach-asset-report', 'apps' => 'assets'],
            
            // Locations service
            ['function' => 'view-location', 'apps' => 'locations'],
            ['function' => 'add-location', 'apps' => 'locations'],
            ['function' => 'edit-location', 'apps' => 'locations'],
            ['function' => 'delete-location', 'apps' => 'locations'],
            
            // Units service
            ['function' => 'add-unit', 'apps' => 'units'],
            ['function' => 'edit-unit', 'apps' => 'units'],
            ['function' => 'delete-unit', 'apps' => 'units'],
            ['function' => 'view-unit', 'apps' => 'units'],
        ];

        // Upsert by function, update apps to reflect microservice name
        if (! Permissions::where('function', 'activate-asset')->exists()) {
            Permissions::where('function', 'borrow-asset')->update(['function' => 'activate-asset']);
        }

        if (! Permissions::where('function', 'deactivate-asset')->exists()) {
            Permissions::where('function', 'return-asset')->update(['function' => 'deactivate-asset']);
        }

        foreach ($permissions as $permission) {
            Permissions::updateOrCreate(['function' => $permission['function']], ['apps' => $permission['apps']]);
        }
    }
}
