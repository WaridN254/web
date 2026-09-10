<?php

namespace App\Services\Email;

use App\Models\Email;
use App\Models\EmailLabel;
use Illuminate\Support\Collection;

class EmailSearchService
{
    public function searchQuery($query, string $searchString): \Illuminate\Database\Eloquent\Builder
    {
        $parsedQuery = $this->parseQuery($searchString);

        if (!empty($parsedQuery['text'])) {
            $searchTerm = $parsedQuery['text'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('subject', 'ilike', "%{$searchTerm}%")
                    ->orWhere('body_text', 'ilike', "%{$searchTerm}%")
                    ->orWhere('from_address', 'ilike', "%{$searchTerm}%")
                    ->orWhere('from_name', 'ilike', "%{$searchTerm}%")
                    ->orWhereHas('recipients', function ($rq) use ($searchTerm) {
                        $rq->where('email_address', 'ilike', "%{$searchTerm}%")
                            ->orWhere('name', 'ilike', "%{$searchTerm}%");
                    });
            });
        }

        if (!empty($parsedQuery['from'])) {
            $fromTerm = $parsedQuery['from'];
            $query->where(function ($q) use ($fromTerm) {
                $q->where('from_address', 'ilike', "%{$fromTerm}%")
                    ->orWhere('from_name', 'ilike', "%{$fromTerm}%");
            });
        }

        if (!empty($parsedQuery['subject'])) {
            $subjectTerm = $parsedQuery['subject'];
            $query->where('subject', 'ilike', "%{$subjectTerm}%");
        }

        if (isset($parsedQuery['is_unread']) && $parsedQuery['is_unread']) {
            $query->where('is_read', false);
        }

        if (isset($parsedQuery['is_starred']) && $parsedQuery['is_starred']) {
            $query->where('is_starred', true);
        }

        if (isset($parsedQuery['is_important']) && $parsedQuery['is_important']) {
            $query->where('is_important', true);
        }

        if (!empty($parsedQuery['label'])) {
            $labelName = $parsedQuery['label'];
            $query->whereHas('labels', function ($q) use ($labelName) {
                $q->where('name', 'ilike', $labelName);
            });
        }

        if (!empty($parsedQuery['folder'])) {
            $query->where('folder', $parsedQuery['folder']);
        }

        return $query;
    }

    public function search(string $query, string $tenantId, ?string $accountId = null): Collection
    {
        $queryBuilder = Email::where('tenant_id', $tenantId)
            ->with(['recipients', 'labels', 'account']);

        if ($accountId) {
            $queryBuilder->where('email_account_id', $accountId);
        }

        $this->searchQuery($queryBuilder, $query);

        return $queryBuilder
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    private function parseQuery(string $query): array
    {
        $result = [
            'text' => '',
            'from' => null,
            'subject' => null,
            'label' => null,
            'folder' => null,
            'is_unread' => false,
            'is_starred' => false,
            'is_important' => false,
            'has_attachment' => false,
        ];

        $textParts = [];

        $patterns = [
            'from' => '/from:(\S+)/i',
            'subject' => '/subject:(\S+)/i',
            'label' => '/label:(\S+)/i',
            'folder' => '/folder:(\S+)/i',
            'is_unread' => '/is:unread/i',
            'is_starred' => '/is:starred/i',
            'is_important' => '/is:important/i',
            'has_attachment' => '/has:attachment/i',
        ];

        $remainingQuery = $query;

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $remainingQuery, $matches)) {
                if ($key === 'is_unread' || $key === 'is_starred' || $key === 'is_important' || $key === 'has_attachment') {
                    $result[$key] = true;
                    $remainingQuery = preg_replace($pattern, '', $remainingQuery);
                } else {
                    $result[$key] = $matches[1];
                    $remainingQuery = preg_replace($pattern, '', $remainingQuery);
                }
            }
        }

        $remainingQuery = trim(preg_replace('/\s+/', ' ', $remainingQuery));

        if (!empty($remainingQuery)) {
            $result['text'] = $remainingQuery;
        }

        return $result;
    }
}
