<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Infra\Roles\Models\Permissions\Permissions;
use Infra\Roles\Models\Roles;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Roles::firstOrCreate(['nama' => 'admin']);
        $roleKadin = Roles::firstOrCreate(['nama' => 'kadin']);
        $roleKabid = Roles::firstOrCreate(['nama' => 'kabid']);
        $rolePegawai = Roles::firstOrCreate(['nama' => 'pegawai']);

        $all = Permissions::all()->pluck('id', 'function');

        $assign = function (Roles $role, array $functions) use ($all) {
            $ids = collect($functions)->map(fn($f) => $all[$f] ?? null)->filter()->values()->all();
            $role->permission()->syncWithoutDetaching($ids);
        };

        // admin: all permissions
        $roleAdmin->permission()->syncWithoutDetaching(Permissions::all()->pluck('id')->all());

        // kadin: broad rights on services + read-only users/roles/perms + storage
        $assign($roleKadin, [
            'view-report','create-report','update-report','delete-report','review-report','export-report','manage-assignees','submit-report',
            'presign-evidence','finalize-evidence','delete-evidence',
            'list-notifications','mark-notification','read-all-notifications',
            'view-user','view-unit','upload-file','edit-user-profile',
            'view-asset','add-asset','edit-asset','delete-asset','activate-asset','deactivate-asset','maintain-asset','retire-asset','attach-asset-report','detach-asset-report',
            'view-asset-category','add-asset-category','edit-asset-category','delete-asset-category',
            'view-location','add-location','edit-location','delete-location',
            'view-report-category','add-report-category','edit-report-category','delete-report-category',
            'view-letter','add-incoming-letter','add-outgoing-letter','edit-letter','delete-letter','analyze-letter',
            'view-classification','add-classification','edit-classification','delete-classification']);

        // kabid: unit-level rights and approvals + minimal users/storage
        $assign($roleKabid, [
           'view-report','create-report','update-report','delete-report','review-report','export-report','manage-assignees','submit-report',
            'presign-evidence','finalize-evidence','delete-evidence',
            'list-notifications','mark-notification','read-all-notifications',
            'view-user','view-unit','upload-file','edit-user-profile',
            'view-asset','add-asset','edit-asset','delete-asset','activate-asset','deactivate-asset','maintain-asset','retire-asset','attach-asset-report','detach-asset-report',
            'view-asset-category','add-asset-category','edit-asset-category','delete-asset-category',
            'view-location','add-location','edit-location','delete-location',
            'view-report-category','add-report-category','edit-report-category','delete-report-category',
            'view-letter','add-incoming-letter','add-outgoing-letter','edit-letter','delete-letter','analyze-letter',
            'view-classification','add-classification','edit-classification','delete-classification'
        ]);

        // pegawai: create/update own, manage evidence + storage + edit own profile
        $assign($rolePegawai, [
            'view-report','submit-report','presign-evidence','finalize-evidence','delete-evidence',
            'list-notifications','mark-notification','read-all-notifications',
            'view-user', 'upload-file','edit-user-profile',
            'view-asset','add-asset','edit-asset','delete-asset','activate-asset','deactivate-asset','maintain-asset','retire-asset','attach-asset-report','detach-asset-report',
            'view-asset-category','add-asset-category','edit-asset-category','delete-asset-category',
            'view-location','add-location','edit-location','delete-location',
            'view-report-category','add-report-category','edit-report-category','delete-report-category',
            'view-letter','add-incoming-letter','add-outgoing-letter','edit-letter','delete-letter','analyze-letter',
            'view-classification','add-classification','edit-classification','delete-classification'
        ]);
    }
}
