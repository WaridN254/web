<?php

namespace App\Traits;

/**
 * Add this trait to any Livewire component that needs toast notifications.
 *
 * Usage:
 *   $this->toastSuccess('Product saved!');
 *   $this->toastError('Something went wrong');
 *   $this->toastWarning('Low stock');
 *   $this->toastInfo('New version available');
 *   $this->toastWithAction('Undo delete', 'Undo', 'deleteItem', ['id' => $id]);
 */
trait DispatchesToasts
{
    protected function toastSuccess(string $title, ?string $description = null, ?int $duration = null): void
    {
        $this->dispatchToast('success', $title, $description, $duration);
    }

    protected function toastError(string $title, ?string $description = null, ?int $duration = null): void
    {
        $this->dispatchToast('error', $title, $description, $duration);
    }

    protected function toastWarning(string $title, ?string $description = null, ?int $duration = null): void
    {
        $this->dispatchToast('warning', $title, $description, $duration);
    }

    protected function toastInfo(string $title, ?string $description = null, ?int $duration = null): void
    {
        $this->dispatchToast('info', $title, $description, $duration);
    }

    protected function toastWithAction(
        string $title,
        string $buttonLabel,
        string $livewireEvent,
        array $params = [],
        ?string $description = null,
    ): void {
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => $title,
            'description' => $description,
            'duration' => null,
            'button' => [
                'title' => $buttonLabel,
                'event' => $livewireEvent,
                'params' => $params,
            ],
        ]);
    }

    protected function toastWithJsAction(
        string $title,
        string $buttonLabel,
        string $jsCode,
        ?string $description = null,
        ?int $duration = null,
    ): void {
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => $title,
            'description' => $description,
            'duration' => $duration,
            'button' => [
                'title' => $buttonLabel,
                'js' => $jsCode,
            ],
        ]);
    }

    protected function toastWithUrlAction(
        string $title,
        string $buttonLabel,
        string $url,
        ?string $description = null,
        ?int $duration = null,
    ): void {
        $this->dispatch('toast', [
            'type' => 'success',
            'title' => $title,
            'description' => $description,
            'duration' => $duration,
            'button' => [
                'title' => $buttonLabel,
                'url' => $url,
            ],
        ]);
    }

    private function dispatchToast(string $type, string $title, ?string $description, ?int $duration): void
    {
        $payload = ['type' => $type, 'title' => $title];
        if ($description !== null) $payload['description'] = $description;
        if ($duration !== null) $payload['duration'] = $duration;
        $this->dispatch('toast', $payload);
    }
}
