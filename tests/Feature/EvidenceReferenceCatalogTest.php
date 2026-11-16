<?php

namespace Tests\Feature;

use App\Models\MstEvidence;
use App\Models\MstEvidenceRef;
use App\Models\MstOrganization;
use App\Models\User;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EvidenceReferenceCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        $this->withoutVite();
        $this->app['view']->getFinder()->prependLocation(base_path('tests/Fixtures/views'));

        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->string('name'); $table->string('role'); $table->string('access_profile');
            $table->unsignedBigInteger('organization_id')->nullable(); $table->string('organisasi')->nullable(); $table->boolean('isActivated')->default(true); $table->timestamps();
        });
        Schema::create('mst_organization', function (Blueprint $table): void {
            $table->id('organization_id'); $table->string('organization_name'); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('trs_userorganization', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger('organization_id');
            $table->boolean('is_primary')->default(true); $table->unsignedBigInteger('assigned_by')->nullable(); $table->timestamps();
        });
        Schema::create('mst_eval', function (Blueprint $table): void {
            $table->id('eval_id'); $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('tahun')->nullable(); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('access_assignments', function (Blueprint $table): void {
            $table->id(); $table->morphs('assignable'); $table->unsignedBigInteger('user_id'); $table->string('access_profile'); $table->timestamps();
        });
        Schema::create('mst_focusarea', function (Blueprint $table): void {
            $table->increments('id'); $table->string('name'); $table->string('version')->default('2019');
        });
        Schema::create('mst_objective', function (Blueprint $table): void {
            $table->string('objective_id')->primary(); $table->integer('focus_area_id'); $table->string('objective');
            $table->text('objective_description')->nullable(); $table->text('objective_purpose')->nullable();
        });
        Schema::create('mst_practice', function (Blueprint $table): void {
            $table->string('practice_id')->primary(); $table->string('objective_id'); $table->string('practice_name');
        });
        Schema::create('mst_practicemetr', function (Blueprint $table): void {
            $table->id(); $table->string('practice_id'); $table->text('description');
        });
        Schema::create('mst_infoflowoutput', function (Blueprint $table): void {
            $table->id('output_id'); $table->string('practice_id'); $table->text('description');
        });
        Schema::create('mst_policy', function (Blueprint $table): void {
            $table->id('policy_id'); $table->string('objective_id'); $table->text('policy'); $table->text('description')->nullable();
        });
        Schema::create('mst_SIA', function (Blueprint $table): void {
            $table->id('sia_id'); $table->string('objective_id'); $table->text('description');
        });
        Schema::create('mst_evidence', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('eval_id')->nullable(); $table->unsignedBigInteger('organization_id');
            $table->string('judul_dokumen');
            foreach (['no_dokumen', 'tahun_terbit', 'tahun_kadaluarsa', 'tipe', 'pengesahan', 'pemilik_dokumen', 'klasifikasi', 'grup', 'link', 'ket_tipe', 'summary'] as $field) $table->text($field)->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('mst_evidence_ref', function (Blueprint $table): void {
            $table->id(); $table->integer('focus_area_id'); $table->string('objective_id'); $table->string('nama_dokumen');
            $table->text('keterangan')->nullable(); $table->text('referensi_sumber')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
            $table->unique(['focus_area_id', 'objective_id', 'nama_dokumen']);
        });
        Schema::create('trs_ref_evidence', function (Blueprint $table): void {
            $table->id(); $table->foreignId('evidence_id')->constrained('mst_evidence');
            $table->foreignId('evidence_ref_id')->constrained('mst_evidence_ref'); $table->timestamps();
            $table->unique(['evidence_id', 'evidence_ref_id']);
        });
        (require database_path('migrations/2026_04_02_034817_create_permission_tables.php'))->up();
        foreach (PermissionCatalog::all() as $permission) Permission::findOrCreate($permission, 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::table('mst_focusarea')->insert(['id' => 1, 'name' => 'Core Model', 'version' => '2019']);
        DB::table('mst_objective')->insert(['objective_id' => 'EDM01', 'focus_area_id' => 1, 'objective' => 'Governance framework', 'objective_description' => 'Deskripsi objective', 'objective_purpose' => 'Tujuan objective']);
        MstOrganization::create(['organization_name' => 'Company A']);
        MstOrganization::create(['organization_name' => 'Company B']);
    }

    public function test_overview_renders_practices_metrics_and_indonesian_document_requirements(): void
    {
        $reference = $this->reference('Pedoman Tata Kelola TI');
        DB::table('mst_practice')->insert(['practice_id' => 'EDM01.01', 'objective_id' => 'EDM01', 'practice_name' => 'Evaluate governance']);
        DB::table('mst_practicemetr')->insert(['practice_id' => 'EDM01.01', 'description' => 'Jumlah keputusan yang ditinjau']);
        DB::table('mst_infoflowoutput')->insert(['practice_id' => 'EDM01.01', 'description' => 'Governance principles']);

        $this->actingAs($this->user())->get(route('cobit_component.helicopter', ['objective_id' => 'EDM01', 'focus_area' => 1]))
            ->assertOk()->assertSee('Ringkasan GAMO')->assertSee('EDM01.01')->assertSee('Jumlah keputusan yang ditinjau')
            ->assertSee($reference->nama_dokumen)->assertSee('Governance principles');
    }

    public function test_multiple_references_can_be_saved_replaced_and_cleared(): void
    {
        $a = $this->reference('Pedoman Tata Kelola TI'); $b = $this->reference('Matriks RACI');
        $this->actingAs($this->user())->postJson(route('master-evidence.store'), [
            'organization_id' => 1, 'judul_dokumen' => 'Dokumen perusahaan', 'reference_ids' => [$a->id, $b->id],
        ])->assertCreated();
        $evidence = MstEvidence::firstOrFail();
        $this->assertNull($evidence->eval_id);
        $this->assertSame(1, (int) $evidence->organization_id);
        $this->assertSame(2, $evidence->references()->count());
        $this->get(route('master-evidence.index', ['organization_id' => 1]))
            ->assertOk()->assertSee('Ref Kebutuhan')->assertSee('Pedoman Tata Kelola TI');

        $this->putJson(route('master-evidence.update', $evidence->id), ['organization_id' => 1, 'judul_dokumen' => 'Metadata only'])->assertOk();
        $this->assertSame(2, $evidence->references()->count());

        $this->putJson(route('master-evidence.update', $evidence->id), ['organization_id' => 1, 'judul_dokumen' => 'Revisi', 'reference_ids' => [$b->id]])->assertOk();
        $this->assertEquals([$b->id], $evidence->references()->pluck('mst_evidence_ref.id')->all());
        $this->putJson(route('master-evidence.update', $evidence->id), ['organization_id' => 1, 'judul_dokumen' => 'Revisi', 'reference_ids' => []])->assertOk();
        $this->assertSame(0, $evidence->references()->count());
    }

    public function test_other_company_evidence_and_invalid_references_are_rejected(): void
    {
        $reference = $this->reference('Pedoman');
        $evidence = MstEvidence::create(['organization_id' => 2, 'judul_dokumen' => 'Company B private']);
        $this->actingAs($this->user())->putJson(route('master-evidence.update', $evidence->id), [
            'organization_id' => 1, 'judul_dokumen' => 'Changed', 'reference_ids' => [$reference->id],
        ])->assertForbidden();
        $this->assertSame('Company B private', $evidence->fresh()->judul_dokumen);
        $this->assertSame(0, DB::table('trs_ref_evidence')->count());
        $this->get(route('master-evidence.index', ['organization_id' => 1]))->assertOk()->assertDontSee('Company B private');
        $this->postJson(route('master-evidence.store'), ['organization_id' => 1, 'judul_dokumen' => 'Invalid', 'reference_ids' => [99999]])->assertUnprocessable();
        $reference->update(['is_active' => false]);
        $this->postJson(route('master-evidence.store'), ['organization_id' => 1, 'judul_dokumen' => 'Inactive', 'reference_ids' => [$reference->id]])->assertUnprocessable();
        $this->assertSame(1, MstEvidence::count());
    }

    public function test_catalog_write_requires_permission_and_the_correct_model_objective(): void
    {
        $this->actingAs($this->user(['cobit.view']))->postJson(route('cobit_component.evidence-ref.store'), [])->assertForbidden();
        $this->actingAs($this->user())->postJson(route('cobit_component.evidence-ref.store'), [
            'focus_area_id' => 1, 'objective_id' => 'MISSING', 'nama_dokumen' => 'Wrong model',
        ])->assertUnprocessable();
        $this->post(route('cobit_component.evidence-ref.store'), [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Kebutuhan baru', 'keterangan' => 'Penjelasan sederhana',
        ])->assertRedirect();
        $this->assertSame(1, MstEvidenceRef::where('nama_dokumen', 'Kebutuhan baru')->count());
        $reference = MstEvidenceRef::firstOrFail();
        $this->put(route('cobit_component.evidence-ref.update', $reference->id), [
            'nama_dokumen' => 'Kebutuhan direvisi', 'focus_area_id' => 999, 'objective_id' => 'MISSING',
        ])->assertRedirect();
        $this->assertSame('Kebutuhan direvisi', $reference->fresh()->nama_dokumen);
        $this->assertSame('EDM01', $reference->fresh()->objective_id);
    }

    public function test_pages_still_open_before_the_ref_sql_is_applied(): void
    {
        Schema::drop('trs_ref_evidence'); Schema::drop('mst_evidence_ref');
        $this->actingAs($this->user())->get(route('master-evidence.index', ['organization_id' => 1]))
            ->assertOk()->assertDontSee('Ref Kebutuhan</th>', false);
        $this->get(route('cobit_component.helicopter', ['objective_id' => 'EDM01', 'focus_area' => 1]))
            ->assertOk()->assertSee('Katalog ref belum diaktifkan');
        $this->get(route('master-evidence.dictionary', ['organization_id' => 1]))
            ->assertOk()->assertSee('Kamus Referensi')->assertSee('Katalog ref belum diaktifkan');
    }

    public function test_dictionary_is_available_to_assessment_readers_and_keeps_the_company_context(): void
    {
        $this->reference('Pedoman dalam kamus');
        $this->actingAs($this->user(['assessments.view']));

        $this->get(route('master-evidence.index', ['organization_id' => 1]))
            ->assertOk()->assertSee('Kamus Referensi')->assertDontSee('data-select-evidence-ref', false);
        $this->get(route('master-evidence.dictionary', ['organization_id' => 1]))
            ->assertOk()->assertSee('Kamus Referensi')->assertSee('Pedoman dalam kamus')
            ->assertSee(htmlspecialchars(route('master-evidence.index', ['organization_id' => 1, 'reference_gamo' => 'EDM01']), ENT_QUOTES), false)
            ->assertSee('name="organization_id" value="1"', false)
            ->assertDontSee('Tambah Ref');
        $this->get(route('cobit_component.helicopter'))->assertForbidden();
        $this->get(route('master-evidence.dictionary', ['organization_id' => 2]))->assertForbidden();
    }

    public function test_dictionary_reference_input_and_edit_return_to_the_dictionary(): void
    {
        $this->actingAs($this->user(['assessments.view', 'design-factors.input']));
        $destination = route('master-evidence.dictionary', ['focus_area' => 1, 'objective_id' => 'EDM01', 'organization_id' => 1]);
        $context = ['return_context' => 'dictionary', 'return_organization_id' => 1];

        $this->post(route('cobit_component.evidence-ref.store'), $context + [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Ref dari kamus',
        ])->assertRedirect($destination);
        $reference = MstEvidenceRef::firstOrFail();
        $this->get($destination)->assertOk()->assertSee('name="return_context" value="dictionary"', false);

        $this->put(route('cobit_component.evidence-ref.update', $reference->id), $context + [
            'nama_dokumen' => 'Ref kamus diperbarui',
        ])->assertRedirect($destination);
        $this->assertSame('Ref kamus diperbarui', $reference->fresh()->nama_dokumen);

        $this->postJson(route('cobit_component.evidence-ref.store'), [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Wrong company',
            'return_context' => 'dictionary', 'return_organization_id' => 2,
        ])->assertForbidden();
        $this->assertSame(1, MstEvidenceRef::count());
    }

    public function test_master_evidence_shows_reference_navigation_above_add_evidence_and_keeps_filters(): void
    {
        $this->reference('EDM only requirement');
        DB::table('mst_objective')->insert(['objective_id' => 'APO02', 'focus_area_id' => 1, 'objective' => 'Managed Strategy']);
        MstEvidenceRef::create(['focus_area_id' => 1, 'objective_id' => 'APO02', 'nama_dokumen' => 'Rencana Strategis TI', 'is_active' => true]);
        MstEvidence::create(['organization_id' => 1, 'judul_dokumen' => 'Existing strategy evidence', 'grup' => 'APO', 'tipe' => 'Design']);

        $response = $this->actingAs($this->user())->get(route('master-evidence.index', [
            'organization_id' => 1, 'reference_gamo' => 'APO02', 'search' => 'Existing', 'grup' => 'APO', 'tipe' => 'Design',
        ]))->assertOk()->assertSee('Kamus Referensi · APO02')->assertSee('Existing strategy evidence');

        $html = $response->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);
        $panel = $xpath->query('//*[@id="masterEvidenceReferencePanel"]')->item(0);
        $this->assertNotNull($panel);
        $this->assertStringContainsString('Rencana Strategis TI', $panel->textContent);
        $this->assertStringNotContainsString('EDM only requirement', $panel->textContent);
        $this->assertSame(1, $xpath->query('//*[@id="masterEvidenceReferencePanel"]//input[@data-select-evidence-ref]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="addMasterEvidenceModal"]//*[@data-select-evidence-ref or @name="reference_ids[]" or @id="evidenceReferenceSearch"]')->length);
        $this->assertSame(0, $xpath->query('//*[@id="addMasterEvidenceModal"]//*[contains(@class, "evidence-ref-option")]')->length);
        $this->assertSame('APO02', $xpath->evaluate('string(//*[@id="referenceGamoSelect"]/option[@selected]/@value)'));
        foreach (['organization_id' => '1', 'search' => 'Existing', 'grup' => 'APO', 'tipe' => 'Design'] as $field => $value) {
            $this->assertSame($value, $xpath->evaluate('string(//*[@id="referenceGamoForm"]//input[@name="'.$field.'"]/@value)'));
        }
        $this->assertLessThan(strpos($html, 'id="addMasterEvidenceButton"'), strpos($html, 'id="masterEvidenceReferencePanel"'));

        $this->get(route('master-evidence.index', ['organization_id' => 1, 'reference_gamo' => 'EDM01']))
            ->assertOk()->assertSee('Kamus Referensi · EDM01');
    }

    public function test_master_evidence_opens_the_full_dictionary_before_company_selection(): void
    {
        $this->reference('Pedoman tata kelola');
        DB::table('mst_practice')->insert(['practice_id' => '"EDM01.01"', 'objective_id' => 'EDM01', 'practice_name' => '"Evaluate governance"']);
        DB::table('mst_practicemetr')->insert(['practice_id' => '"EDM01.01"', 'description' => '"Governance metric"']);
        MstEvidence::create(['organization_id' => 1, 'judul_dokumen' => 'Company evidence stays scoped']);
        $this->actingAs($this->user());

        $response = $this->get(route('master-evidence.index'))->assertOk()
            ->assertSee('Kamus Referensi · EDM01')->assertSee('Pedoman tata kelola')
            ->assertSee('Evaluate governance')->assertSee('Governance metric')
            ->assertDontSee('Company evidence stays scoped')->assertDontSee('data-select-evidence-ref', false)
            ->assertDontSee('id="addMasterEvidenceButton"', false);
        $html = $response->getContent();
        $this->assertLessThan(strpos($html, 'id="company-evidence"'), strpos($html, 'id="masterEvidenceReferencePanel"'));
        $this->assertLessThan(strpos($html, 'id="company-evidence"'), strpos($html, 'Governance metric'));
        $this->assertStringContainsString('name="reference_gamo" value="EDM01"', $html);

        DB::table('mst_objective')->insert(['objective_id' => 'APO02', 'focus_area_id' => 1, 'objective' => 'Managed Strategy']);
        MstEvidenceRef::create(['focus_area_id' => 1, 'objective_id' => 'APO02', 'nama_dokumen' => 'Rencana Strategis TI', 'is_active' => true]);
        $this->get(route('master-evidence.index', ['reference_gamo' => 'APO02']))
            ->assertOk()->assertSee('Kamus Referensi · APO02')->assertSee('Rencana Strategis TI')->assertDontSee('Pedoman tata kelola');
        $this->get(route('master-evidence.index', ['organization_id' => 2]))->assertForbidden();
    }

    public function test_assessment_reader_without_company_assignment_can_still_read_the_dictionary(): void
    {
        $this->reference('General document requirement');
        $reader = $this->user(['assessments.view']);
        $reader->organizations()->detach();
        $reader->forceFill(['organization_id' => null, 'organisasi' => null])->save();
        $this->actingAs($reader)->get(route('master-evidence.index'))
            ->assertOk()->assertSee('General document requirement')
            ->assertSee('Belum ada company yang ditetapkan')
            ->assertDontSee('Tambah Ref')->assertDontSee('data-select-evidence-ref', false);
    }

    public function test_reference_changes_return_to_master_evidence_with_company_and_filters_preserved(): void
    {
        $this->actingAs($this->user(['assessments.view', 'design-factors.input']));
        $context = ['return_context' => 'master', 'return_organization_id' => 1,
            'return_search' => 'Existing', 'return_grup' => 'EDM', 'return_tipe' => 'Design'];
        $destination = route('master-evidence.index', ['reference_gamo' => 'EDM01', 'organization_id' => 1,
            'search' => 'Existing', 'grup' => 'EDM', 'tipe' => 'Design']);

        $this->post(route('cobit_component.evidence-ref.store'), $context + [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Ref dari halaman utama',
        ])->assertRedirect($destination);
        $reference = MstEvidenceRef::firstOrFail();
        $this->put(route('cobit_component.evidence-ref.update', $reference->id), $context + [
            'nama_dokumen' => 'Ref utama diperbarui',
        ])->assertRedirect($destination);
        $this->get($destination)->assertOk()->assertSee('name="return_context" value="master"', false);

        $this->postJson(route('cobit_component.evidence-ref.store'), array_replace($context, ['return_organization_id' => 2]) + [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Disallowed company',
        ])->assertForbidden();
        $this->assertSame(1, MstEvidenceRef::count());
        $this->post(route('cobit_component.evidence-ref.store'), [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'General reference before company selection',
            'return_context' => 'master',
        ])->assertRedirect(route('master-evidence.index', ['reference_gamo' => 'EDM01']));
    }

    private function user(?array $permissions = null): User
    {
        $user = User::create(['name' => 'Assessor', 'role' => 'user', 'access_profile' => 'assessor', 'organization_id' => 1]);
        $user->isActivated = true;
        $user->organizations()->attach(1, ['is_primary' => true]);
        $user->givePermissionTo($permissions ?? ['cobit.view', 'design-factors.input', 'assessments.view', 'assessments.input']);

        return $user;
    }

    public function test_reference_can_link_to_inputs_and_outputs_and_display_their_practice_and_endpoints(): void
    {
        $this->enableFlowMappings();
        $this->actingAs($this->user());
        $this->post(route('cobit_component.evidence-ref.store'), [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Pedoman tata kelola',
            'referensi_sumber' => 'Catatan sumber lama', 'return_context' => 'master',
            'flow_mappings' => [['direction' => 'input', 'flow_id' => 11], ['direction' => 'output', 'flow_id' => 11]],
        ])->assertRedirect(route('master-evidence.index', ['reference_gamo' => 'EDM01']));
        $reference = MstEvidenceRef::firstOrFail();
        $this->assertSame(2, $reference->infoflowMappings()->count());
        $response = $this->get(route('master-evidence.index'))->assertOk()
            ->assertSee('Input · EDM01.01')->assertSee('Output · EDM01.01')
            ->assertSee('Dari: APO12.01')->assertSee('Ke: All EDM; APO01.01')
            ->assertSee('Catatan sumber lama');
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($dom);
        $editor = $xpath->query('//*[@id="editEvidenceRef'.$reference->id.'"]//*[@data-ref-flow-editor]')->item(0);
        $this->assertNotNull($editor);
        $options = json_decode($editor->getAttribute('data-options'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(['input', 'output'], array_values(array_unique(array_column($options, 'direction'))));
        $this->assertSame([11, 12], array_values(array_unique(array_column($options, 'id'))));
        $this->assertCount(2, json_decode($editor->getAttribute('data-value'), true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_metadata_edits_preserve_flow_mappings_and_explicit_changes_do_not_remove_company_evidence_links(): void
    {
        $this->enableFlowMappings();
        $this->actingAs($this->user());
        $reference = $this->reference('Original reference');
        $other = $this->reference('Other reference');
        $mapping = app(\App\Services\Assessment\Evidence\ReferenceInfoflowMapping::class);
        $mapping->synchronize($reference, ['flow_mappings' => [['direction' => 'input', 'flow_id' => 11], ['direction' => 'output', 'flow_id' => 11]]]);
        $mapping->synchronize($other, ['flow_mappings' => [['direction' => 'output', 'flow_id' => 11]]]);
        $ids = $reference->infoflowMappings()->pluck('id')->all();
        $evidence = MstEvidence::create(['organization_id' => 1, 'judul_dokumen' => 'Company document']);
        $evidence->references()->attach($reference->id);

        $url = route('cobit_component.evidence-ref.update', $reference->id);
        $this->put($url, ['nama_dokumen' => 'Metadata only'])->assertRedirect();
        $this->assertSame($ids, $reference->infoflowMappings()->pluck('id')->all());
        $this->put($url, ['nama_dokumen' => 'Mapped output', 'flow_mappings' => [['direction' => 'output', 'flow_id' => 12]]])->assertRedirect();
        $this->assertSame(12, (int) $reference->infoflowMappings()->firstOrFail()->output_id);
        $this->assertSame(1, $other->infoflowMappings()->count());
        $this->put($url, ['nama_dokumen' => 'Cleared mapping', 'flow_mappings_present' => 1])->assertRedirect();
        $this->assertSame(0, $reference->infoflowMappings()->count());
        $this->assertSame(1, $evidence->references()->count());
        $this->assertSame(1, $other->infoflowMappings()->count());
    }

    public function test_foreign_gamo_models_unknown_directions_and_duplicate_mappings_are_rejected_atomically(): void
    {
        $this->enableFlowMappings();
        $this->actingAs($this->user());
        $reference = $this->reference('Unchanged reference');
        $url = route('cobit_component.evidence-ref.update', $reference->id);
        foreach ([21, 31, 41, 999] as $id) {
            $this->putJson($url, ['nama_dokumen' => 'Must not be saved', 'flow_mappings' => [['direction' => 'output', 'flow_id' => $id]]])->assertUnprocessable();
            $this->assertSame('Unchanged reference', $reference->fresh()->nama_dokumen);
        }
        $this->putJson($url, ['nama_dokumen' => 'Invalid direction', 'flow_mappings' => [['direction' => 'other', 'flow_id' => 11]]])->assertUnprocessable();
        $this->putJson($url, ['nama_dokumen' => 'Duplicate', 'flow_mappings' => [['direction' => 'input', 'flow_id' => 11], ['direction' => 'input', 'flow_id' => 11]]])->assertUnprocessable();
        $this->postJson(route('cobit_component.evidence-ref.store'), [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Invalid create',
            'flow_mappings' => [['direction' => 'input', 'flow_id' => 31]],
        ])->assertUnprocessable();
        $this->assertSame(1, MstEvidenceRef::count());
        $this->assertSame(0, DB::table('trs_evidence_ref_infoflow')->count());
    }

    public function test_mapping_requires_editor_permission_and_is_optional_for_other_reference_sources(): void
    {
        $this->enableFlowMappings();
        $reference = $this->reference('Enterprise governance guiding principles');
        $this->actingAs($this->user(['assessments.view']));
        $this->get(route('master-evidence.index'))->assertOk()->assertSee('Belum dipetakan ke Information Flow')
            ->assertDontSee('data-ref-flow-editor', false);
        $this->assertSame(0, $reference->infoflowMappings()->count());
        $this->putJson(route('cobit_component.evidence-ref.update', $reference->id), [
            'nama_dokumen' => 'Forbidden', 'flow_mappings' => [['direction' => 'output', 'flow_id' => 11]],
        ])->assertForbidden();
        $this->assertSame(0, $reference->infoflowMappings()->count());
    }

    public function test_mapped_source_tracks_flow_changes_and_deletion_without_deleting_the_reference(): void
    {
        $this->enableFlowMappings();
        $reference = $this->reference('Live mapped reference');
        app(\App\Services\Assessment\Evidence\ReferenceInfoflowMapping::class)->synchronize($reference, ['flow_mappings' => [
            ['direction' => 'input', 'flow_id' => 11], ['direction' => 'output', 'flow_id' => 11],
        ]]);
        DB::table('mst_infoflowoutput')->where('output_id', 11)->update(['description' => 'Updated authoritative output', 'to' => 'APO01.02']);
        $this->actingAs($this->user())->get(route('master-evidence.index'))->assertOk()->assertSee('Updated authoritative output')->assertSee('Ke: APO01.02');
        DB::table('mst_infoflowinput')->where('input_id', 11)->delete();
        $this->assertSame(1, $reference->infoflowMappings()->count());
        $this->assertSame(1, MstEvidenceRef::count());
    }

    public function test_missing_mapping_migration_rejects_mapping_writes_without_losing_legacy_reference_data(): void
    {
        $this->actingAs($this->user());
        $this->postJson(route('cobit_component.evidence-ref.store'), [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Mapping unavailable',
            'flow_mappings' => [['direction' => 'output', 'flow_id' => 11]],
        ])->assertStatus(409);
        $this->assertSame(0, MstEvidenceRef::count());
        $this->post(route('cobit_component.evidence-ref.store'), [
            'focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => 'Other source', 'referensi_sumber' => 'Organizational structures / RACI',
        ])->assertRedirect();
        $this->assertSame(1, MstEvidenceRef::count());
    }

    private function enableFlowMappings(): void
    {
        DB::statement('PRAGMA foreign_keys = ON');
        Schema::create('mst_infoflowinput', function (Blueprint $table) {
            $table->id('input_id'); $table->string('practice_id'); $table->text('from')->nullable(); $table->text('description');
        });
        Schema::table('mst_infoflowoutput', fn (Blueprint $table) => $table->text('to')->nullable());
        foreach ([[2, '2019'], [25, '5']] as [$id, $version]) DB::table('mst_focusarea')->insert(['id' => $id, 'name' => 'Other model', 'version' => $version]);
        foreach ([['APO01', 1], ['EDM01.M2', 2], ['EDM01.M25', 25]] as [$id, $focus]) DB::table('mst_objective')->insert([
            'objective_id' => $id, 'focus_area_id' => $focus, 'objective' => 'Other objective',
        ]);
        foreach ([['"EDM01.01"', 'EDM01', 11], ['EDM01.02', 'EDM01', 12], ['APO01.01', 'APO01', 21], ['EDM01.01.M2', 'EDM01.M2', 31], ['EDM01.01.M25', 'EDM01.M25', 41]] as [$practice, $objective, $id]) {
            DB::table('mst_practice')->insert(['practice_id' => $practice, 'objective_id' => $objective, 'practice_name' => '"Practice name"']);
            DB::table('mst_infoflowinput')->insert(['input_id' => $id, 'practice_id' => $practice, 'from' => $id === 11 ? 'APO12.01' : 'Outside COBIT', 'description' => 'Input description '.$id]);
            DB::table('mst_infoflowoutput')->insert(['output_id' => $id, 'practice_id' => $practice, 'to' => 'All EDM; APO01.01', 'description' => 'Output description '.$id]);
        }
        (require database_path('migrations/2026_11_04_000001_create_evidence_reference_infoflows.php'))->up();
    }

    private function reference(string $name): MstEvidenceRef
    {
        return MstEvidenceRef::create(['focus_area_id' => 1, 'objective_id' => 'EDM01', 'nama_dokumen' => $name, 'is_active' => true]);
    }
}
