<?php

namespace App\Events;

use App\Models\Flock;
use App\Models\DailyLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbnormalTemperatureAlert
{
    use Dispatchable, SerializesModels;

    public $flock;
    public $log;
    public $type;

    public function __construct(Flock $flock, DailyLog $log, $type)
    {
        $this->flock = $flock;
        $this->log = $log;
        $this->type = $type;
    }
}