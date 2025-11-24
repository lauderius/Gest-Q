<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QueueApiController extends Controller
{
    // Menu Inicial: Lista todas as filas e quantas pessoas estão à espera
    public function index()
    {
        $queues = Queue::where('active', true)->get()->map(function($q) {
            return [
                'id' => $q->id,
                'name' => $q->name,
                'group_name' => $q->group_name,
                'waiting_count' => $q->tickets()->where('status', 'waiting')->count()
            ];
        });
        return response()->json(['queues' => $queues]);
    }

    // Criar Ticket: Gera número sequencial diário e UUID
    public function storeTicket(Request $request)
    {
        $request->validate(['queue_id' => 'required|exists:queues,id']);
        $queue = Queue::find($request->queue_id);

        // Pega o último número DE HOJE para esta fila
        $lastNumber = Ticket::where('queue_id', $queue->id)
            ->whereDate('created_at', Carbon::today())
            ->max('number') ?? 0;

        $ticket = Ticket::create([
            'queue_id' => $queue->id,
            'ext_id' => (string) Str::uuid(),
            'number' => $lastNumber + 1,
            'status' => 'waiting',
            'person_name' => $request->person,
            'notes' => $request->notes,
        ]);

        return response()->json(['ticket' => $ticket]);
    }

    // Ler Ticket (QR Code): Retorna dados + Posição na fila + Tempo estimado
    public function showTicket($ext_id)
    {
        $ticket = Ticket::where('ext_id', $ext_id)->with('queue')->firstOrFail();

        // Calcula quantas pessoas estão na frente (status waiting e ID menor)
        $position = Ticket::where('queue_id', $ticket->queue_id)
            ->where('status', 'waiting')
            ->where('id', '<', $ticket->id)
            ->count();

        return response()->json([
            'ticket' => $ticket,
            'queue' => $ticket->queue,
            'position' => $position + 1,
            'eta_seconds' => ($position + 1) * $ticket->queue->avg_service_sec,
            'updates' => [
                ['ts' => $ticket->created_at, 'msg' => 'Ticket criado']
            ]
        ]);
    }

    // Atendente: Chamar o próximo
    public function callNext($queueId)
    {
        $nextTicket = Ticket::where('queue_id', $queueId)
            ->where('status', 'waiting')
            ->orderBy('id', 'asc') // FIFO: Primeiro a entrar, primeiro a sair
            ->first();

        if ($nextTicket) {
            $nextTicket->update(['status' => 'serving', 'started_at' => now()]);
            return response()->json(['ticket' => $nextTicket]);
        }

        return response()->json(['message' => 'Fila vazia'], 404);
    }

    // Monitor (TV): Quem está a ser atendido e os próximos 5
    public function monitor($queueId)
    {
        $current = Ticket::where('queue_id', $queueId)->where('status', 'serving')->latest('started_at')->first();
        $next = Ticket::where('queue_id', $queueId)->where('status', 'waiting')->orderBy('id', 'asc')->take(5)->get();

        return response()->json([
            'now' => $current,
            'next' => $next
        ]);
    }

    // Atualizar status (Finalizar/Cancelar)
    public function updateStatus(Request $request, $ext_id)
    {
        $ticket = Ticket::where('ext_id', $ext_id)->firstOrFail();
        $status = $request->status;

        $updateData = ['status' => $status];
        if($status == 'done' || $status == 'cancel') $updateData['finished_at'] = now();
        if($status == 'serving') $updateData['started_at'] = now();

        $ticket->update($updateData);
        return response()->json(['success' => true]);
    }

    // Auxiliar: Obter atual em atendimento
    public function getCurrent($queueId)
    {
        $current = Ticket::where('queue_id', $queueId)->where('status', 'serving')->latest('started_at')->first();
        return response()->json(['ticket' => $current]);
    }

    // Auxiliar: Obter lista de espera
    public function getWaiting($queueId)
    {
         $tickets = Ticket::where('queue_id', $queueId)
            ->where('status', 'waiting')
            ->orderBy('id', 'asc')
            ->take(15)
            ->get();
         return response()->json(['tickets' => $tickets]);
    }
}
