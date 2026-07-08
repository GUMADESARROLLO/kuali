<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    public function create(array $data, array $files = []): Ticket
    {
        return DB::transaction(function () use ($data, $files) {
            $ticket = Ticket::create([
                'ticket_number' => $this->generateNumber(),
                'title' => $data['title'],
                'description' => $data['description'],
                'user_id' => $data['user_id'],
                'department_id' => $data['department_id'],
                'category_id' => $data['category_id'],
                'subcategory_id' => $data['subcategory_id'],
                'priority' => $data['priority'] ?? 'media',
                'status' => 'abierto',
            ]);

            // Register history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $data['user_id'],
                'action' => TicketHistory::ACTION_CREATED,
                'description' => 'Ticket creado',
            ]);

            // Upload attachments
            foreach ($files as $file) {
                $this->uploadAttachment($ticket, $file, $data['user_id']);
            }

            return $ticket->load([
                'user', 'department', 'category', 'subcategory',
                'attachments', 'histories',
            ]);
        });
    }

    public function generateNumber(): string
    {
        $last = Ticket::lockForUpdate()->max('id') ?? 0;
        return 'TK-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);
    }

    public function uploadAttachment(Ticket $ticket, UploadedFile $file, int $userId, ?int $commentId = null): TicketAttachment
    {
        $path = $file->storeAs($ticket->ticket_number, $file->getClientOriginalName(), [
            'disk' => config('filesystems.default'),
        ]);

        $attachment = TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'comment_id' => $commentId,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $userId,
        ]);

        return $attachment;
    }
}
