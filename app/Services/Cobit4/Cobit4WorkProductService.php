<?php

namespace App\Services\Cobit4;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class Cobit4WorkProductService
{
    public function available(): bool
    {
        // Listing preserves literal dots in table names; identifier wrapping would
        // otherwise interpret mst_wp_4.1 as a schema and a table.
        $tables = Schema::getTableListing();
        return in_array('mst_wp_4.1', $tables, true) && in_array('trs_wp_to_obj_4.1', $tables, true);
    }

    public function revision(Collection $rows): string
    {
        return hash('sha256', $rows->map(fn ($row) => [$row->wp_id, $row->wp, $row->description])->values()->toJson());
    }

    public function forProcess(string $processCode, int $focusAreaId): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        return DB::table(DB::raw('`mst_wp_4.1` as wp'))
            ->join(DB::raw('`trs_wp_to_obj_4.1` as relation'), 'relation.wp_id', '=', 'wp.wp_id')
            ->join('mst_cobit4_processes as process', 'process.id', '=', 'relation.process_id')
            ->where('process.code', strtoupper(trim($processCode)))
            ->where('process.focus_area_id', $focusAreaId)
            ->orderBy('wp.order_no')
            ->orderBy('wp.wp_id')
            ->get(['wp.wp_id', 'wp.wp', 'wp.description']);
    }

    public function synchronize(string $processCode, int $focusAreaId, array $rows, string $revision): Collection
    {
        abort_unless($this->available(), 409, 'Master Work Product belum tersedia.');
        $code = strtoupper(trim($processCode));

        return DB::transaction(function () use ($code, $focusAreaId, $rows, $revision) {
            $process = DB::table('mst_cobit4_processes')->where('code', $code)->where('focus_area_id', $focusAreaId)->lockForUpdate()->first();
            abort_unless($process, 404, 'Proses COBIT 4.1 tidak ditemukan.');
            $master = fn () => DB::table(DB::raw('`mst_wp_4.1`'));
            $links = fn () => DB::table(DB::raw('`trs_wp_to_obj_4.1`'));
            $stock = $master()->where('wp_id', 'like', $code.'-%')->lockForUpdate()->pluck('wp_id');
            $current = $this->forProcess($code, $focusAreaId);
            abort_unless(hash_equals($this->revision($current), $revision), 409, 'Work Product sudah berubah. Muat ulang halaman sebelum menyimpan.');
            $ids = $current->pluck('wp_id')->all();
            $canonical = fn ($id) => strtoupper(str_replace(' ', '', $id));
            $seen = [];
            $originals = [];
            foreach ($rows as $index => &$row) {
                $row['wp_id'] = strtoupper(trim($row['wp_id']));
                $original = $row['original_wp_id'] ?? null;
                $key = $canonical($row['wp_id']);
                $error = null;
                if (! preg_match('/^'.preg_quote($code, '/').'- *WP[1-9][0-9]*$/', $row['wp_id'])) {
                    $error = 'WP ID harus mengikuti proses '.$code.', contoh '.$code.'-WP1.';
                } elseif (isset($seen[$key])) {
                    $error = 'WP ID tidak boleh ganda.';
                } elseif ($original !== null && (! in_array($original, $ids, true) || in_array($original, $originals, true))) {
                    $error = 'Work Product tidak terhubung ke proses yang sedang diedit.';
                } elseif ($stock->contains(fn ($id) => $canonical($id) === $key && $id !== $original)) {
                    $error = 'WP ID sudah digunakan. Gunakan WP ID lain.';
                }
                if ($error) {
                    throw ValidationException::withMessages(['data.work_products.'.$index.'.wp_id' => $error]);
                }
                $seen[$key] = true;
                if ($original !== null) $originals[] = $original;
            }
            unset($row);

            $deleted = array_values(array_diff($ids, $originals));
            $links()->where('process_id', $process->id)->whereIn('wp_id', $deleted)->delete();
            $referenced = $links()->whereIn('wp_id', $deleted)->pluck('wp_id')->all();
            $master()->whereIn('wp_id', array_values(array_diff($deleted, $referenced)))->delete();

            $order = (int) $master()->max('order_no');
            foreach ($rows as $row) {
                $original = $row['original_wp_id'] ?? null;
                $values = ['wp_id' => $row['wp_id'], 'wp' => trim($row['wp']),
                    'description' => trim($row['description'] ?? ''), 'updated_at' => now()];
                if ($original !== null) {
                    // The catalog foreign key cascades WP ID changes to its links.
                    $master()->where('wp_id', $original)->update($values);
                } else {
                    $master()->insert($values + ['order_no' => ++$order, 'created_at' => now()]);
                    $links()->insert(['process_id' => $process->id, 'wp_id' => $row['wp_id'], 'created_at' => now(), 'updated_at' => now()]);
                }
            }

            return $this->forProcess($code, $focusAreaId);
        });
    }
}
