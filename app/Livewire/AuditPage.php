<?php

namespace App\Livewire;

use App\Models\Audit;
use App\Models\Client;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class AuditPage extends Component
{
    public ?Client $client = null;

    public bool $showNew = false;

    public string $productName  = '';
    public string $productUrl   = '';
    public string $auditorName  = '';
    public string $productType  = 'marketing_site';
    public string $auditMode    = 'ai_assisted';
    public string $auditDate    = '';

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->isClientUser()) {
            $this->client = $user->profile?->client;
        } else {
            $clientId = Session::get('active_client_id');
            $this->client = $clientId ? Client::find($clientId) : null;
        }

        $this->auditDate = now()->toDateString();
    }

    public function audits()
    {
        if (!$this->client) return collect();
        return Audit::where('client_id', $this->client->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function deleteAudit(string $auditId): void
    {
        $audit = \App\Models\Audit::find($auditId);
        if ($audit && $this->client && $audit->client_id === $this->client->id) {
            $audit->delete();
            session()->flash('toast', 'Audit deleted.');
        }
    }

    public function createAudit(): void
    {
        $this->validate([
            'productName' => 'required|string|max:255',
            'productType' => 'required|in:web_app,mobile_app,marketing_site,ecommerce,saas_dashboard',
            'auditDate'   => 'required|date',
        ]);

        if (!$this->client) return;

        $audit = Audit::create([
            'client_id'    => $this->client->id,
            'product_name' => $this->productName,
            'product_url'  => $this->productUrl ?: null,
            'auditor_name' => $this->auditorName ?: null,
            'product_type' => $this->productType,
            'audit_mode'   => $this->auditMode,
            'audit_date'   => $this->auditDate,
            'status'       => 'in_progress',
        ]);

        $this->redirect(route('audits.checklist', $audit->id));
    }

    public function render(): \Illuminate\View\View
    {
        $audits = $this->audits();
        return view('livewire.audit-page', compact('audits'));
    }
}
