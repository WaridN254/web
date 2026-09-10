<?php

namespace Tests\Feature;

use App\Models\AccountActivation;
use App\Models\Business;
use App\Models\EmailLog;
use App\Models\Tenant;
use App\Mail\AccountActivationMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use DatabaseTransactions;

    private string $uniqueSuffix;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        RateLimiter::clear('register:127.0.0.1');
        $this->uniqueSuffix = Str::random(8);
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('register:127.0.0.1');
        parent::tearDown();
    }

    private function uniqueName(string $base = 'Biz'): string
    {
        return "{$base} {$this->uniqueSuffix}";
    }

    private function uniqueEmail(string $prefix = 'user'): string
    {
        return "{$prefix}_{$this->uniqueSuffix}@test.com";
    }

    public function test_registration_form_renders(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Register your business');
    }

    public function test_business_registration_creates_tenant_and_sends_email(): void
    {
        $email = $this->uniqueEmail('owner');
        $name = $this->uniqueName('Test Business');

        $response = $this->post('/register', [
            'business_name' => $name,
            'business_type' => 'retail',
            'country' => 'UG',
            'phone' => '+256700000000',
            'email' => $email,
        ]);

        $response->assertRedirect(route('registration.sent'));

        $tenant = Tenant::where('name', $name)->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('onboarding', $tenant->status);
        $this->assertNotNull($tenant->business_id);

        $this->assertDatabaseHas('account_activations', [
            'tenant_id' => $tenant->id,
            'email' => $email,
        ]);

        Mail::assertQueued(AccountActivationMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        $this->assertDatabaseHas('email_logs', [
            'tenant_id' => $tenant->id,
            'type' => 'account_activation',
            'status' => 'queued',
        ]);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $email = $this->uniqueEmail('dup');

        $this->post('/register', [
            'business_name' => $this->uniqueName('Business One'),
            'business_type' => 'retail',
            'country' => 'UG',
            'phone' => '+256700000000',
            'email' => $email,
        ]);

        $response = $this->post('/register', [
            'business_name' => $this->uniqueName('Business Two'),
            'business_type' => 'retail',
            'country' => 'UG',
            'phone' => '+256700000001',
            'email' => $email,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_duplicate_business_name_is_rejected(): void
    {
        $name = $this->uniqueName('Same Name');

        $this->post('/register', [
            'business_name' => $name,
            'business_type' => 'retail',
            'country' => 'UG',
            'phone' => '+256700000000',
            'email' => $this->uniqueEmail('a'),
        ]);

        $response = $this->post('/register', [
            'business_name' => $name,
            'business_type' => 'retail',
            'country' => 'UG',
            'phone' => '+256700000001',
            'email' => $this->uniqueEmail('b'),
        ]);

        $response->assertSessionHasErrors('business_name');
    }

    public function test_resend_creates_new_token_and_invalidates_old(): void
    {
        $email = $this->uniqueEmail('resend');
        $name = $this->uniqueName('Resend Biz');

        $this->post('/register', [
            'business_name' => $name,
            'business_type' => 'retail',
            'country' => 'UG',
            'phone' => '+256700000000',
            'email' => $email,
        ]);

        $tenant = Tenant::where('name', $name)->first();
        $this->assertNotNull($tenant, "Tenant '$name' not found after registration");

        $firstToken = AccountActivation::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($firstToken);

        $response = $this->post('/register/resend', [
            'email' => $email,
        ]);

        $response->assertSessionHas('success');

        $firstToken->refresh();
        $this->assertNotNull($firstToken->used_at, 'Old token should be marked as used');

        $newToken = AccountActivation::where('tenant_id', $tenant->id)
            ->whereNull('used_at')
            ->first();
        $this->assertNotNull($newToken, 'New token should exist');
        $this->assertNotEquals($firstToken->token_hash, $newToken->token_hash, 'New token should be different');

        Mail::assertQueued(AccountActivationMail::class, 2);
    }

    public function test_invalid_token_shows_error(): void
    {
        $response = $this->get(route('activation.show', 'completely-invalid-token'));
        $response->assertStatus(200);
        $response->assertSee('Invalid');
    }

    public function test_used_token_shows_error(): void
    {
        $email = $this->uniqueEmail('used');
        $name = $this->uniqueName('Used Biz');

        $this->post('/register', [
            'business_name' => $name,
            'business_type' => 'retail',
            'country' => 'UG',
            'phone' => '+256700000000',
            'email' => $email,
        ]);

        $tenant = Tenant::where('name', $name)->first();
        $this->assertNotNull($tenant);

        $activation = AccountActivation::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($activation);
        $activation->markUsed();

        $response = $this->get(route('activation.show', 'invalid'));
        $response->assertStatus(200);
        $response->assertSee('Invalid');
    }

    public function test_email_log_is_created_on_sending(): void
    {
        $email = $this->uniqueEmail('log');

        $this->post('/register', [
            'business_name' => $this->uniqueName('Log Biz'),
            'business_type' => 'retail',
            'country' => 'UG',
            'phone' => '+256700000000',
            'email' => $email,
        ]);

        $this->assertDatabaseHas('email_logs', [
            'recipient' => $email,
            'type' => 'account_activation',
        ]);
    }

    public function test_registration_requires_all_fields(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'business_name',
            'business_type',
            'country',
            'phone',
            'email',
        ]);
    }

    public function test_sent_page_renders(): void
    {
        $response = $this->get('/register/sent');
        $response->assertStatus(200);
        $response->assertSee('Check your email');
    }
}
