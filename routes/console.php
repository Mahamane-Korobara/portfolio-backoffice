<?php

use App\Jobs\PublishScheduledArticles;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new PublishScheduledArticles)->everyMinute();
