<?php

namespace Infra\Shared\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as firstController;
use Infra\Shared\Concerns\InteractsWithResponse;

class BaseController extends firstController
{
    use AuthorizesRequests, DispatchesJobs, InteractsWithResponse,ValidatesRequests;
}
