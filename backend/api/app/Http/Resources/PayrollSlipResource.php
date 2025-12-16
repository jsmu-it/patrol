<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollSlipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_month' => $this->period_month,
            'nip' => $this->nip,
            'name' => $this->name,
            'unit' => $this->unit,
            'position' => $this->position,
            'total_income' => (float) $this->total_income,
            'total_deduction' => (float) $this->total_deduction,
            'net_income' => (float) $this->net_income,
            'sign_location' => $this->sign_location,
            'sign_date' => $this->sign_date?->format('d-m-Y'),
            'income_items' => $this->whenLoaded('incomeItems', function () {
                return $this->incomeItems->map(fn ($item) => [
                    'label' => $item->label,
                    'amount' => (float) $item->amount,
                ]);
            }),
            'deduction_items' => $this->whenLoaded('deductionItems', function () {
                return $this->deductionItems->map(fn ($item) => [
                    'label' => $item->label,
                    'amount' => (float) $item->amount,
                ]);
            }),
            'created_at' => $this->created_at?->format('d-m-Y H:i'),
        ];
    }
}
