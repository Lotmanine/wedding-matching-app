<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CreditLedgerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;

class CreditLedger extends Model
{
    /** @use HasFactory<CreditLedgerFactory> */
    use HasFactory;

    protected $table = 'credit_ledger';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'delta',
        'balance_after',
        'type',
        'reference_type',
        'reference_id',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'delta'         => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /** Type of the entry in the reader's language; unknown types get a neutral label. */
    public function typeLabel(): string
    {
        $key = 'credits.type.' . $this->type;

        return Lang::has($key) ? (string) __($key) : (string) __('credits.type.other');
    }

    /**
     * Description of an entry written by the platform itself (pack purchase,
     * request unlock, refund after a report, welcome credits), built from its
     * type and reference in the reader's language instead of the stored note.
     *
     * Returns null when the note was typed by a person (an admin's reason for
     * an adjustment, any free text): the caller shows that note as stored.
     * Identifiers are wrapped in a left-to-right span, hence the HtmlString.
     */
    public function systemDescription(): ?HtmlString
    {
        $notes = trim((string) $this->notes);
        $ltr = static fn (string $value): string => '<span dir="ltr">' . e($value) . '</span>';
        $line = static fn (string $key, array $replace = []): HtmlString => new HtmlString((string) __($key, $replace));

        switch ($this->type) {
            case 'purchase':
                if (preg_match('/^Achat (.+) — (\S+)$/u', $notes, $m)) {
                    return $line('credits.entry.purchase_pack', ['pack' => e(__($m[1])), 'uid' => $ltr($m[2])]);
                }

                return $notes === '' || $this->reference_type === Purchase::class ? $line('credits.entry.purchase') : null;

            case 'unlock':
                if (preg_match('/^Unlock (\S+)$/', $notes, $m)) {
                    return $line('credits.entry.unlock_request', ['uid' => $ltr($m[1])]);
                }

                return $notes === '' || $this->reference_type === ServiceRequest::class ? $line('credits.entry.unlock') : null;

            case 'refund':
                if (preg_match('/^Remboursement litige (\S+)$/u', $notes, $m)) {
                    return $line('credits.entry.refund_request', ['uid' => $ltr($m[1])]);
                }

                return $notes === '' || $this->reference_type === LeadDispute::class || $notes === 'Remboursement litige'
                    ? $line('credits.entry.refund')
                    : null;

            case 'bonus':
                if ($notes === 'Bonus de bienvenue') {
                    return $line('credits.entry.welcome_bonus');
                }

                return $notes === '' ? $line('credits.entry.bonus') : null;

            case 'admin_adjustment':
            case 'adjustment':
                return $notes === '' ? $line('credits.entry.adjustment') : null;

            default:
                return null;
        }
    }

    /** Same description as plain text (CSV export): the system line, else the stored note. */
    public function plainDescription(): string
    {
        $system = $this->systemDescription();

        return $system !== null
            ? html_entity_decode(strip_tags($system->toHtml()), ENT_QUOTES | ENT_HTML5, 'UTF-8')
            : (string) $this->notes;
    }
}
