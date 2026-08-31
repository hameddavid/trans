<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTranscripts extends Command
{
    protected $signature = 'transcripts:cleanup {--days=30 : Delete PDFs older than this many days} {--dry-run : List files without deleting}';
    protected $description = 'Delete generated transcript PDFs older than the signed download link expiry';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $basePath = base_path();
        $patterns = ['*_cover.pdf', '*.pdf'];
        $deleted = 0;
        $skipped = 0;

        foreach (File::files($basePath) as $file) {
            if ($file->getExtension() !== 'pdf') {
                continue;
            }

            if ($file->getMTime() >= $cutoff->timestamp) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("Would delete: {$file->getFilename()} (modified: " . date('Y-m-d H:i', $file->getMTime()) . ")");
                $deleted++;
                continue;
            }

            File::delete($file->getPathname());
            $deleted++;
        }

        $storagePath = storage_path('app');
        foreach (File::files($storagePath) as $file) {
            if ($file->getExtension() !== 'pdf') {
                continue;
            }

            if ($file->getMTime() >= $cutoff->timestamp) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("Would delete: storage/app/{$file->getFilename()} (modified: " . date('Y-m-d H:i', $file->getMTime()) . ")");
                $deleted++;
                continue;
            }

            File::delete($file->getPathname());
            $deleted++;
        }

        $action = $dryRun ? 'Would delete' : 'Deleted';
        $this->info("{$action} {$deleted} expired PDFs (kept {$skipped} within {$days}-day window).");
        Log::info("transcripts:cleanup — {$action} {$deleted} PDFs older than {$days} days.");

        return self::SUCCESS;
    }
}
