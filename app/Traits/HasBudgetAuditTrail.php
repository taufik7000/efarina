<?php
// app/Traits/HasBudgetAuditTrail.php

namespace App\Traits;

use App\Models\BudgetAuditTrail;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasBudgetAuditTrail
{
    public function auditTrails(): MorphMany
    {
        return $this->morphMany(BudgetAuditTrail::class, 'auditable')->latest();
    }

    public function logAudit(
        string $action, 
        array $oldValues = null, 
        array $newValues = null, 
        float $amountChanged = null,
        string $description = null,
        string $reason = null
    ): void {
        $this->auditTrails()->create([
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'amount_changed' => $amountChanged,
            'description' => $description,
            'reason' => $reason,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'System',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected static function bootHasBudgetAuditTrail()
    {
        static::created(function ($model) {
            $model->logAudit(
                'created',
                null,
                $model->toArray(),
                null,
                class_basename($model) . ' baru telah dibuat'
            );
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();
            
            // Skip jika hanya updated_at yang berubah
            if (count($changes) === 1 && isset($changes['updated_at'])) {
                return;
            }

            $amountChanged = null;
            $description = class_basename($model) . ' telah diperbarui';

            // Deteksi perubahan budget
            if (isset($changes['total_budget'])) {
                $amountChanged = $changes['total_budget'] - $original['total_budget'];
                $description = 'Total budget diubah dari Rp ' . number_format($original['total_budget'], 0, ',', '.') . 
                              ' menjadi Rp ' . number_format($changes['total_budget'], 0, ',', '.');
            }

            if (isset($changes['allocated_amount'])) {
                $amountChanged = $changes['allocated_amount'] - $original['allocated_amount'];
                $description = 'Alokasi budget diubah dari Rp ' . number_format($original['allocated_amount'], 0, ',', '.') . 
                              ' menjadi Rp ' . number_format($changes['allocated_amount'], 0, ',', '.');
            }

            $model->logAudit(
                'updated',
                $original,
                $changes,
                $amountChanged,
                $description
            );
        });

        static::deleted(function ($model) {
            $model->logAudit(
                'deleted',
                $model->toArray(),
                null,
                null,
                class_basename($model) . ' telah dihapus'
            );
        });
    }
}