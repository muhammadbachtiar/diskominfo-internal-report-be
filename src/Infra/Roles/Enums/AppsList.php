<?php

namespace Infra\Roles\Enums;

use Infra\Shared\Enums\Traits\hasCaseResolve;

enum AppsList: string
{
    use hasCaseResolve;

    case Core = 'core';
    case Users = 'users';
    case Roles = 'roles';
    case Permissions = 'permissions';
    case Storage = 'storage';
    case Reports = 'reports';
    case Approvals = 'approvals';
    case Evidences = 'evidences';
    case Notifications = 'notifications';
    case Assets = 'assets';
    case Units = 'units';
    case Locations = 'locations';
    case AssetCategories = 'asset-categories';
    case ReportCategories = 'report-categories';
    case Letters = 'letters';
    case Classifications = 'classifications';
}
