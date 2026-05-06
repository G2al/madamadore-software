<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $supplier): void {
            $supplier->phone_number = self::normalizeItalianPhone($supplier->phone_number);
        });

        static::updated(function (self $supplier): void {
            if (! $supplier->wasChanged('name')) {
                return;
            }

            DB::table('fabrics')
                ->where('supplier_id', $supplier->id)
                ->update(['supplier' => $supplier->name]);

            DB::table('dress_fabrics')
                ->where('supplier_id', $supplier->id)
                ->update(['supplier' => $supplier->name]);

            DB::table('shopping_items')
                ->where('supplier_id', $supplier->id)
                ->update(['supplier' => $supplier->name]);
        });
    }

    public function fabrics(): HasMany
    {
        return $this->hasMany(Fabric::class);
    }

    public function dressFabrics(): HasMany
    {
        return $this->hasMany(DressFabric::class);
    }

    public function shoppingItems(): HasMany
    {
        return $this->hasMany(ShoppingItem::class);
    }

    public function whatsappDigits(): string
    {
        return preg_replace('/\D+/', '', (string) $this->phone_number) ?? '';
    }

    public static function normalizeItalianPhone(?string $phoneNumber): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phoneNumber) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '39')) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '3')) {
            return '+39' . $digits;
        }

        return '+' . $digits;
    }
}
