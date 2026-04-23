<?php

namespace Tests\Feature;

use App\Models\ClinicDocument;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_upload_and_download_their_own_identity_document(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'role' => 'patient',
            'status' => 'active',
        ]);
        $patient = Patient::create([
            'user_id' => $user->id,
            'patient_number' => 'CCP-26-2001',
            'first_name' => 'Document',
            'last_name' => 'Patient',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('documents.store', [], false), [
                'patient_id' => $patient->id,
                'document_type' => 'national_id',
                'title' => 'National ID',
                'document' => UploadedFile::fake()->create('national-id.pdf', 80, 'application/pdf'),
            ])
            ->assertRedirect();

        $document = ClinicDocument::firstOrFail();

        $this->assertSame($patient->id, $document->patient_id);
        $this->assertSame($user->id, $document->uploaded_by);
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($user)
            ->get(route('documents.download', $document, false))
            ->assertOk();
    }

    public function test_patient_cannot_upload_document_for_another_patient(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'role' => 'patient',
            'status' => 'active',
        ]);
        Patient::create([
            'user_id' => $user->id,
            'patient_number' => 'CCP-26-2002',
            'first_name' => 'Owner',
            'last_name' => 'Patient',
            'status' => 'active',
        ]);
        $otherPatient = Patient::create([
            'patient_number' => 'CCP-26-2003',
            'first_name' => 'Other',
            'last_name' => 'Patient',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('documents.store', [], false), [
                'patient_id' => $otherPatient->id,
                'document_type' => 'insurance_card',
                'title' => 'Insurance card',
                'document' => UploadedFile::fake()->create('insurance.pdf', 80, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('clinic_documents', 0);
    }

    public function test_staff_can_upload_role_document_without_patient_link(): void
    {
        Storage::fake('local');

        $housekeeping = User::factory()->create([
            'role' => 'housekeeping',
            'status' => 'active',
        ]);

        $this->actingAs($housekeeping)
            ->post(route('documents.store', [], false), [
                'document_type' => 'cleaning_checklist',
                'title' => 'Room readiness checklist',
                'document' => UploadedFile::fake()->create('checklist.pdf', 50, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('clinic_documents', [
            'owner_user_id' => $housekeeping->id,
            'uploaded_by' => $housekeeping->id,
            'document_type' => 'cleaning_checklist',
        ]);
    }

    public function test_documents_page_opens_for_each_supported_role(): void
    {
        $roles = ['admin', 'receptionist', 'doctor', 'cashier', 'pharmacist', 'radiology', 'rn', 'pct', 'housekeeping', 'nurse', 'dietary', 'patient'];

        foreach ($roles as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'status' => 'active',
            ]);

            if ($role === 'patient') {
                Patient::create([
                    'user_id' => $user->id,
                    'patient_number' => 'CCP-26-' . str_pad((string) random_int(3000, 9999), 4, '0', STR_PAD_LEFT),
                    'first_name' => 'Role',
                    'last_name' => 'Patient',
                    'status' => 'active',
                ]);
            }

            $this->actingAs($user)
                ->get(route('documents.index', [], false))
                ->assertOk()
                ->assertSee('Documents');
        }
    }
}
