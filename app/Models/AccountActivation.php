<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AccountActivation extends Model
{
    use HasUuids;

    protected $table = 'account_activations';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['tenant_id', 'email', 'token_hash', 'name', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public static function createToken(string $tenantId, string $email, string $name = null, int $expiresIn = 48): static
    {
        $plainToken = static::generateToken();
        $hashedToken = hash('sha256', $plainToken);

        static::create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'name' => $name,
            'token_hash' => $hashedToken,
            'expires_at' => now()->addHours($expiresIn),
        ]);

        return $plainToken;
    }

    public static function findByToken(string $plainToken): ?static
    {
        $hashedToken = hash('sha256', $plainToken);
        return static::where('token_hash', $hashedToken)->first();
    }

    public function isValid(): bool
    {
        return !$this->used_at && $this->expires_at->isFuture();
    }

    public function markUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
