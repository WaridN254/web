<?php

namespace App\Services\Email;

use App\Models\Email;
use App\Models\EmailAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmailAttachmentService
{
    public function store(Email $email, UploadedFile $file, string $tenantId): EmailAttachment
    {
        $directory = 'emails/' . $tenantId;
        $filename = Str::uuid() . '_' . $file->getClientOriginalName();

        $storedPath = Storage::disk('local')->putFile($directory, $file, $filename);

        return EmailAttachment::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'email_id' => $email->id,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $filename,
            'storage_disk' => 'local',
            'storage_path' => $directory . '/' . $filename,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    public function storeContent(Email $email, string $content, string $originalName, string $mimeType, string $tenantId): EmailAttachment
    {
        $directory = 'emails/' . $tenantId;
        $filename = Str::uuid() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalName);

        Storage::disk('local')->put($directory . '/' . $filename, $content);

        return EmailAttachment::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'email_id' => $email->id,
            'original_name' => $originalName,
            'file_name' => $filename,
            'storage_disk' => 'local',
            'storage_path' => $directory . '/' . $filename,
            'file_size' => strlen($content),
            'mime_type' => $mimeType,
        ]);
    }

    public function download(EmailAttachment $attachment, string $tenantId): BinaryFileResponse
    {
        if ($attachment->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized access to attachment');
        }

        $path = $attachment->storage_path;

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path, $attachment->original_name);
        }

        // Fallback to absolute file_path
        if (!empty($attachment->file_path) && file_exists($attachment->file_path)) {
            return response()->download($attachment->file_path, $attachment->original_name);
        }

        abort(404, 'Attachment file not found');
    }

    public function delete(EmailAttachment $attachment): void
    {
        $path = $attachment->storage_path;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }

        // Also clean up legacy absolute path
        if (!empty($attachment->file_path) && file_exists($attachment->file_path)) {
            @unlink($attachment->file_path);
        }

        $attachment->delete();
    }
}
