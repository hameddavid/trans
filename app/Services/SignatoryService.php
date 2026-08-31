<?php

namespace App\Services;

use App\Models\Signatory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SignatoryService
{
    public function __construct(protected AuthService $authService) {}

    public function getSignatory(string $documentType): array
    {
        $signatory = Signatory::getActive($documentType);

        if ($signatory) {
            return [
                'name' => $signatory->name,
                'title' => $signatory->title,
                'for' => $signatory->for_title,
                'signature_path' => $signatory->signature_path
                    ? storage_path('app/' . $signatory->signature_path)
                    : null,
            ];
        }

        $config = config("documents.signatories.{$documentType}", []);

        return [
            'name' => $config['name'] ?? '',
            'title' => $config['title'] ?? '',
            'for' => $config['for'] ?? 'REGISTRAR',
            'signature_path' => null,
        ];
    }

    public function requestSignatory(int $adminId, string $adminEmail, array $data): Signatory
    {
        return Signatory::create([
            'admin_id' => $adminId,
            'name' => $data['name'],
            'title' => $data['title'],
            'for_title' => $data['for_title'] ?? 'REGISTRAR',
            'document_type' => $data['document_type'],
            'staff_email' => $adminEmail,
            'status' => 'pending',
        ]);
    }

    public function approve(Signatory $signatory, int $approvedBy): void
    {
        $this->downloadSignature($signatory);

        $signatory->status = 'approved';
        $signatory->approved_by = $approvedBy;
        $signatory->approved_at = now();
        $signatory->save();

        $signatory->activate();
    }

    public function reject(Signatory $signatory, int $rejectedBy): void
    {
        $signatory->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'is_active' => false,
        ]);
    }

    public function refreshSignature(Signatory $signatory): void
    {
        $this->downloadSignature($signatory);
    }

    protected function downloadSignature(Signatory $signatory): void
    {
        $staffData = $this->authService->fetchStaffByEmail($signatory->staff_email);

        if (!$staffData || empty($staffData['sig'])) {
            return;
        }

        try {
            $response = Http::withoutVerifying()->timeout(15)->get($staffData['sig']);

            if (!$response->successful()) {
                \Log::warning('Failed to download signature', ['url' => $staffData['sig']]);
                return;
            }

            $extension = pathinfo(parse_url($staffData['sig'], PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = "signatures/{$signatory->id}.{$extension}";

            Storage::put($filename, $response->body());
            $signatory->update(['signature_path' => $filename]);
        } catch (\Exception $e) {
            \Log::error('Signature download failed', ['error' => $e->getMessage()]);
        }
    }
}
