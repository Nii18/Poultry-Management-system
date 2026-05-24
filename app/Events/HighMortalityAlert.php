<?php

namespace App\Events;

use App\Models\Flock;
use App\Models\DailyLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HighMortalityAlert
{
    use Dispatchable, SerializesModels;

    public $flock;
    public $log;
    public $rate;

    public function __construct(Flock $flock, DailyLog $log, $rate)
    {
        $this->flock = $flock;
        $this->log = $log;
        $this->rate = $rate;
    }
}
