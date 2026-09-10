<?php

namespace App\Services\Email;

use App\Models\Email;
use App\Models\EmailLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailLabelService
{
    public function create(string $name, string $color, string $tenantId): EmailLabel
    {
        return EmailLabel::create([
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'name' => $name,
            'color' => $color,
        ]);
    }

    public function update(EmailLabel $label, array $data): EmailLabel
    {
        $label->update([
            'name' => $data['name'] ?? $label->name,
            'color' => $data['color'] ?? $label->color,
        ]);

        return $label->fresh();
    }

    public function delete(EmailLabel $label): void
    {
        $label->emails()->detach();
        $label->delete();
    }

    public function attachToEmail(string $emailId, string $labelId): void
    {
        $email = Email::where('tenant_id', auth()->user()->tenant_id)
            ->where('id', $emailId)
            ->firstOrFail();

        $label = EmailLabel::where('tenant_id', auth()->user()->tenant_id)
            ->where('id', $labelId)
            ->firstOrFail();

        $email->labels()->syncWithoutDetaching([$labelId]);
    }

    public function detachFromEmail(string $emailId, string $labelId): void
    {
        $email = Email::where('tenant_id', auth()->user()->tenant_id)
            ->where('id', $emailId)
            ->firstOrFail();

        $email->labels()->detach($labelId);
    }

    public function getAll(string $tenantId): Collection
    {
        return EmailLabel::where('tenant_id', $tenantId)
            ->withCount('emails')
            ->orderBy('name')
            ->get();
    }
}
