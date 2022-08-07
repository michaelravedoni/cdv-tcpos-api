<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class QueueCheck extends Check
{
    public function run(): Result
    {
        $output = shell_exec('pm2 describe cdv-tcpos-api-queue');

        $result = Result::make();
        $result->shortSummary("pm2 describe cdv-tcpos-api-queue");

        if (Str::contains($output, 'online')) {
            return $result->ok();
        } else {
            return $result->failed();
        }
    }
}
