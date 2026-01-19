<?php

namespace App\Console\Commands;

use App\Models\MstEvidence;
use App\Models\TrsActivityeval;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupEvidenceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evidence:cleanup 
                            {--eval= : Specific eval_id to process (optional, processes all if not specified)}
                            {--dry-run : Show what would be changed without actually updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up legacy evidence data by replacing "judul - no_dokumen" format with pure "judul_dokumen"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $evalId = $this->option('eval');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting evidence cleanup...');

        // Get unique eval_ids to process
        $evalIds = $evalId 
            ? [$evalId] 
            : TrsActivityeval::whereNotNull('evidence')
                ->where('evidence', '!=', '')
                ->distinct()
                ->pluck('eval_id')
                ->toArray();

        $this->info('Processing ' . count($evalIds) . ' evaluation(s)...');

        $totalUpdated = 0;
        $totalSkipped = 0;

        foreach ($evalIds as $currentEvalId) {
            $this->line("\n📋 Processing eval_id: {$currentEvalId}");

            // Load master evidence for this eval
            $masterEvidence = MstEvidence::where('eval_id', $currentEvalId)
                ->pluck('judul_dokumen')
                ->map(fn($j) => trim($j))
                ->filter()
                ->unique()
                ->toArray();

            if (empty($masterEvidence)) {
                $this->warn("  ⚠️ No master evidence found for eval_id: {$currentEvalId}, skipping...");
                continue;
            }

            $this->info("  Found " . count($masterEvidence) . " master evidence records");

            // Get activity evaluations with evidence
            $activityEvals = TrsActivityeval::where('eval_id', $currentEvalId)
                ->whereNotNull('evidence')
                ->where('evidence', '!=', '')
                ->get();

            foreach ($activityEvals as $activityEval) {
                $originalEvidence = $activityEval->evidence;
                $lines = preg_split('/\r\n|\r|\n/', $originalEvidence);
                $cleanedLines = [];
                $hasChanges = false;

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    // Try to match with master evidence
                    $matched = false;
                    foreach ($masterEvidence as $masterTitle) {
                        // Check if line starts with the master title
                        if (stripos($line, $masterTitle) === 0) {
                            $cleanedLines[] = $masterTitle;
                            if ($line !== $masterTitle) {
                                $hasChanges = true;
                            }
                            $matched = true;
                            break;
                        }
                    }

                    // If no match found, keep original (might be manually entered)
                    if (!$matched) {
                        $cleanedLines[] = $line;
                    }
                }

                $cleanedEvidence = implode("\n", array_unique($cleanedLines));

                if ($hasChanges || $originalEvidence !== $cleanedEvidence) {
                    if ($dryRun) {
                        $this->line("  📝 Would update activity_id: {$activityEval->activity_id}");
                        $this->line("     FROM: " . substr(str_replace("\n", " | ", $originalEvidence), 0, 80) . "...");
                        $this->line("     TO:   " . substr(str_replace("\n", " | ", $cleanedEvidence), 0, 80) . "...");
                    } else {
                        $activityEval->evidence = $cleanedEvidence;
                        $activityEval->save();
                        $this->line("  ✅ Updated activity_id: {$activityEval->activity_id}");
                    }
                    $totalUpdated++;
                } else {
                    $totalSkipped++;
                }
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Total updated: {$totalUpdated}");
        $this->info("Total skipped (no changes): {$totalSkipped}");

        if ($dryRun && $totalUpdated > 0) {
            $this->newLine();
            $this->warn("⚠️ This was a DRY RUN. Run without --dry-run to apply changes.");
        }

        return Command::SUCCESS;
    }
}
