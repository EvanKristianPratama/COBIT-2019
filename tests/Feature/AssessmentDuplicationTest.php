<?php

namespace Tests\Feature;

use App\Models\MstEval;
use App\Models\User;
use App\Services\Assessment\Eval\AssessmentDuplicationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentDuplicationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('access_profile')->nullable(); $t->timestamps(); });
        Schema::create('access_assignments', function (Blueprint $t) { $t->id(); $t->morphs('assignable'); $t->integer('user_id'); $t->string('access_profile'); $t->integer('assigned_by')->nullable(); $t->timestamps(); });
        Schema::create('mst_eval', function (Blueprint $t) { $t->id('eval_id'); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('organization_id')->nullable(); $t->integer('focus_area_id'); $t->integer('tahun'); $t->string('status'); $t->boolean('show_on_user_page')->default(true); $t->timestamps(); $t->softDeletes(); });
        Schema::create('trs_scoping', function (Blueprint $t) { $t->id(); $t->integer('eval_id'); $t->string('nama_scope'); $t->timestamps(); $t->softDeletes(); });
        Schema::create('trs_evaldetail', function (Blueprint $t) { $t->id(); $t->integer('eval_id'); $t->integer('scoping_id'); $t->string('domain_id'); $t->timestamps(); });
        Schema::create('mst_evidence', function (Blueprint $t) { $t->id(); $t->integer('eval_id')->nullable(); $t->integer('organization_id')->nullable(); $t->string('judul_dokumen'); $t->string('link')->nullable(); $t->timestamps(); $t->softDeletes(); });
        Schema::create('trs_ref_evidence', function (Blueprint $t) { $t->id(); $t->integer('evidence_id'); $t->integer('evidence_ref_id'); $t->timestamps(); });
        Schema::create('trs_activityeval', function (Blueprint $t) { $t->id(); $t->integer('eval_id'); $t->string('activity_id'); $t->string('level_achieved'); $t->text('evidence')->nullable(); $t->text('notes')->nullable(); $t->timestamps(); $t->softDeletes(); });
        Schema::create('trs_summaryreport', function (Blueprint $t) { $t->id(); $t->integer('eval_id'); $t->string('objective_id'); $t->text('kesimpulan')->nullable(); $t->text('rekomendasi')->nullable(); $t->text('roadmap_rekomendasi')->nullable(); $t->timestamps(); });
        Schema::create('trs_summaryactivity', function (Blueprint $t) { $t->id(); $t->integer('summary_id'); $t->integer('activityeval_id'); $t->integer('evidence_id')->nullable(); $t->string('miss_evidence')->nullable(); $t->timestamps(); });
        Schema::create('trs_objective_score', function (Blueprint $t) { $t->id(); $t->integer('eval_id'); $t->string('objective_id'); $t->integer('level'); $t->timestamps(); });
        Schema::create('trs_maturity_score', function (Blueprint $t) { $t->id(); $t->integer('eval_id'); $t->float('score'); $t->timestamps(); });
    }

    public function test_it_copies_content_and_remaps_relationships_without_changing_the_source(): void
    {
        $user = User::create(['name' => 'Assessor', 'access_profile' => 'assessor']);
        $source = MstEval::create(['user_id' => $user->id, 'organization_id' => 3, 'focus_area_id' => 1, 'tahun' => 2025, 'status' => 'finished']);
        $source->forceFill(['show_on_user_page' => false])->save();
        $scope = DB::table('trs_scoping')->insertGetId(['eval_id' => $source->eval_id, 'nama_scope' => 'Scope A']);
        DB::table('trs_evaldetail')->insert(['eval_id' => $source->eval_id, 'scoping_id' => $scope, 'domain_id' => 'EDM01']);
        $evidence = DB::table('mst_evidence')->insertGetId(['eval_id' => $source->eval_id, 'organization_id' => 3, 'judul_dokumen' => 'Policy', 'link' => 'https://example.com/policy']);
        $shared = DB::table('mst_evidence')->insertGetId(['eval_id' => null, 'organization_id' => 3, 'judul_dokumen' => 'Shared']);
        DB::table('trs_ref_evidence')->insert(['evidence_id' => $evidence, 'evidence_ref_id' => 9]);
        $activity = DB::table('trs_activityeval')->insertGetId(['eval_id' => $source->eval_id, 'activity_id' => 'ACT1', 'level_achieved' => 'F', 'evidence' => 'Policy', 'notes' => 'Note']);
        $report = DB::table('trs_summaryreport')->insertGetId(['eval_id' => $source->eval_id, 'objective_id' => 'EDM01', 'kesimpulan' => 'Conclusion', 'rekomendasi' => 'Recommendation', 'roadmap_rekomendasi' => '{"cells":["2027"]}']);
        DB::table('trs_summaryactivity')->insert([['summary_id' => $report, 'activityeval_id' => $activity, 'evidence_id' => $evidence, 'miss_evidence' => null], ['summary_id' => $report, 'activityeval_id' => $activity, 'evidence_id' => $shared, 'miss_evidence' => null], ['summary_id' => $report, 'activityeval_id' => $activity, 'evidence_id' => null, 'miss_evidence' => 'Missing']]);
        DB::table('trs_objective_score')->insert(['eval_id' => $source->eval_id, 'objective_id' => 'EDM01', 'level' => 3]);
        DB::table('trs_maturity_score')->insert(['eval_id' => $source->eval_id, 'score' => 3]);

        $copy = app(AssessmentDuplicationService::class)->duplicate($source, $user, 2026);
        $this->assertNotEquals($source->eval_id, $copy->eval_id);
        $this->assertSame('in_progress', $copy->status);
        $this->assertEquals(2026, $copy->tahun);
        $this->assertFalse($copy->show_on_user_page);
        $this->assertDatabaseHas('access_assignments', ['assignable_type' => $copy->getMorphClass(), 'assignable_id' => $copy->eval_id, 'user_id' => $user->id, 'access_profile' => 'assessor']);
        $newScope = DB::table('trs_scoping')->where('eval_id', $copy->eval_id)->first();
        $this->assertNotEquals($scope, $newScope->id);
        $this->assertDatabaseHas('trs_evaldetail', ['eval_id' => $copy->eval_id, 'scoping_id' => $newScope->id, 'domain_id' => 'EDM01']);
        $newEvidence = DB::table('mst_evidence')->where('eval_id', $copy->eval_id)->first();
        $this->assertNotEquals($evidence, $newEvidence->id);
        $this->assertSame('https://example.com/policy', $newEvidence->link);
        $this->assertDatabaseHas('trs_ref_evidence', ['evidence_id' => $newEvidence->id, 'evidence_ref_id' => 9]);
        $newActivity = DB::table('trs_activityeval')->where('eval_id', $copy->eval_id)->first();
        $newReport = DB::table('trs_summaryreport')->where('eval_id', $copy->eval_id)->first();
        $this->assertSame('Note', $newActivity->notes);
        $this->assertSame('F', $newActivity->level_achieved);
        $this->assertSame('Conclusion', $newReport->kesimpulan);
        $this->assertSame('{"cells":["2027"]}', $newReport->roadmap_rekomendasi);
        $this->assertDatabaseHas('trs_summaryactivity', ['summary_id' => $newReport->id, 'activityeval_id' => $newActivity->id, 'evidence_id' => $newEvidence->id]);
        $this->assertDatabaseHas('trs_summaryactivity', ['summary_id' => $newReport->id, 'activityeval_id' => $newActivity->id, 'evidence_id' => $shared]);
        $this->assertDatabaseHas('trs_summaryactivity', ['summary_id' => $newReport->id, 'activityeval_id' => $newActivity->id, 'miss_evidence' => 'Missing']);
        $this->assertDatabaseHas('trs_objective_score', ['eval_id' => $copy->eval_id, 'level' => 3]);
        $this->assertDatabaseHas('trs_maturity_score', ['eval_id' => $copy->eval_id, 'score' => 3]);
        DB::table('mst_evidence')->where('id', $newEvidence->id)->update(['judul_dokumen' => 'Changed']);
        DB::table('trs_activityeval')->where('id', $newActivity->id)->update(['notes' => 'Changed']);
        $this->assertDatabaseHas('mst_evidence', ['id' => $evidence, 'judul_dokumen' => 'Policy']);
        $this->assertDatabaseHas('trs_activityeval', ['id' => $activity, 'notes' => 'Note']);
        $this->assertDatabaseHas('mst_eval', ['eval_id' => $source->eval_id, 'tahun' => 2025, 'status' => 'finished']);
    }

    public function test_failed_copy_rolls_back_everything(): void
    {
        $user = User::create(['name' => 'Assessor']);
        $source = MstEval::create(['user_id' => $user->id, 'organization_id' => 3, 'focus_area_id' => 1, 'tahun' => 2025, 'status' => 'finished']);
        DB::table('trs_scoping')->insert(['eval_id' => $source->eval_id, 'nama_scope' => 'Scope A']);
        Schema::drop('trs_evaldetail');
        try {
            app(AssessmentDuplicationService::class)->duplicate($source, $user, 2026);
            $this->fail('Expected the copy to fail.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame(1, DB::table('mst_eval')->count());
            $this->assertSame(1, DB::table('trs_scoping')->count());
            $this->assertSame(0, DB::table('access_assignments')->count());
        }
    }
}
