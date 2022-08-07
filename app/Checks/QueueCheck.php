<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class QueueCheck extends Check
{
    public function run(): Result
    {
        $command = Artisan::call('queue:monitor default');
        $output = Artisan::output();

        $result = Result::make();
        $result->shortSummary("{$output}%");

        if (Str::contains($output, 'OK')) {
            return $result->ok();
        } else {
            return $result->failed();
        }
    }
}
