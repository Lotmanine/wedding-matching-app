<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanUploads extends Command
{
    protected $signature = 'wedding:cleanup-orphan-uploads {--dry-run}';

    protected $description = 'Remove provider upload files whose owning User no longer exists, or whose parent temp dir is older than 1 hour.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $deleted = 0;
        $cutoff = now()->subHour()->timestamp;

        foreach ($disk->directories('providers') as $dir) {
            $segment = basename($dir);

            if ($segment === 'temp') {
                foreach ($disk->allFiles($dir) as $file) {
                    if ($disk->lastModified($file) < $cutoff) {
                        if (! $dryRun) {
                            $disk->delete($file);
                        }
                        $deleted++;
                    }
                }

                continue;
            }

            $userId = (int) $segment;
            if ($userId <= 0) {
                continue;
            }

            if (User::where('id', $userId)->doesntExist()) {
                foreach ($disk->allFiles($dir) as $file) {
                    if (! $dryRun) {
                        $disk->delete($file);
                    }
                    $deleted++;
                }
            }
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Removed {$deleted} orphan upload file(s).");

        return self::SUCCESS;
    }
}
