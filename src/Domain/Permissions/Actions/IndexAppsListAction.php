<?php

namespace Domain\Permissions\Actions;

use Infra\Roles\Enums\AppsList;
use Infra\Shared\Foundations\Action;

class IndexAppsListAction extends Action
{
    public function execute()
    {
        return AppsList::getCases();
    }
}
