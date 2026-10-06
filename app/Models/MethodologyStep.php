<?php

namespace App\Models;

use App\Models\Concerns\IsOrderedContent;
use Database\Factories\MethodologyStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'description', 'sort_order', 'is_active'])]
class MethodologyStep extends Model
{
    /** @use HasFactory<MethodologyStepFactory> */
    use HasFactory, IsOrderedContent;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
