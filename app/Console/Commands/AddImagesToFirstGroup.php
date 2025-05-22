<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\MediaGroup;
use Illuminate\Console\Command;

class AddImagesToFirstGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $media = Media::query()->get();
        foreach ($media as $image) {
            $image->media_groups()->sync([
                MediaGroup::first()->id
            ]);
        }
    }
}
