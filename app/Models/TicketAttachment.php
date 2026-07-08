<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'comment_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    protected $appends = ['url', 'icon'];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TicketComment::class, 'comment_id');
    }

    protected function url(): Attribute
    {
        return Attribute::get(function () {
            if (empty($this->file_path) || $this->file_path === '0' || $this->file_path === '') {
                return null;
            }
            try {
                return Storage::disk(config('filesystems.default'))
                    ->temporaryUrl($this->file_path, now()->addHours(2));
            } catch (\Exception) {
                return null;
            }
        });
    }

    protected function icon(): Attribute
    {
        $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        $map = [
            'pdf' => '📄', 'png' => '🖼️', 'jpg' => '🖼️', 'jpeg' => '🖼️',
            'gif' => '🖼️', 'zip' => '🗜️', 'doc' => '📑', 'docx' => '📑',
            'xls' => '📑', 'xlsx' => '📑',
        ];
        return Attribute::get(fn() => $map[$ext] ?? '📎');
    }
}
