<?php

namespace Ekersten\Resizer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;


class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resizer:clear_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all generated images';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->errors = array();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $target_path = 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . config('resizer.storage_folder');
        if (!Storage::exists($target_path)) {
            $this->error('Storage path ' . config('resizer.storage_folder') . ' does not exist!');
        } else {
            $file = new Filesystem;
            $file->cleanDirectory('storage/' . $target_path);
            $this->line('Storage path ' . config('resizer.storage_folder') . ' cleared!');
        }
    }

}
