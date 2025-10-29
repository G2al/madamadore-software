<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentPayment;

class StudentPresence extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'is_paid',
    ];

    protected $casts = [
        'date' => 'date',
        'is_paid' => 'boolean', 
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Auto-sincronizzazione Pagamenti:
     * - created  -> crea movimento = costo_lezione
     * - updated  -> se cambia data, sposta il movimento corrispondente
     * - deleted  -> elimina il movimento creato da quella presenza
     */
    protected static function booted(): void
    {
        static::created(function (StudentPresence $presence) {
            $student = $presence->student;
            if ($student && $student->costo_lezione !== null) {
                StudentPayment::create([
                    'student_id' => $student->id,
                    'amount'     => $student->costo_lezione,
                    'date'       => $presence->date,
                ]);
            }
        });

        static::updated(function (StudentPresence $presence) {
            if ($presence->isDirty('is_paid')) {
                $student = $presence->student;

                if ($student) {
                    $allPaid = $student->presences()->where('is_paid', false)->doesntExist();
                    $student->update(['saldato' => $allPaid]);
                }
            }
        });

        static::deleted(function (StudentPresence $presence) {
            // elimina un pagamento compatibile (ultimo inserito per quella data)
            StudentPayment::where('student_id', $presence->student_id)
                ->whereDate('date', $presence->date)
                ->orderByDesc('id')
                ->first()?->delete();
        });
    }
}
