<?php

namespace App\Livewire;

use App\Models\Member;
use Livewire\Component;

class MembershipForm extends Component
{
    public int $step = 1;

    // Step 1 — Persönliche Daten
    public string $full_name = '';
    public string $birth_date = '';
    public string $street = '';
    public string $postal_code = '';
    public string $city = '';
    public string $state = '';
    public string $email = '';
    public string $phone = '';

    // Step 2 — Bankverbindung
    public float $jahresbeitrag = 36.00;
    public string $kontoinhaber = '';
    public string $iban = '';
    public string $bic = '';
    public string $kreditinstitut = '';

    // Step 3 — Unterschrift & Zustimmung
    public string $unterschrift = '';
    public bool $sepa_zustimmung = false;
    public bool $dsgvo_zustimmung = false;

    public bool $submitted = false;

    protected function rulesStep1(): array
    {
        return [
            'full_name'   => 'required|string|max:255',
            'birth_date'  => 'required|date|before:today',
            'street'      => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city'        => 'required|string|max:100',
            'state'       => 'required|string|max:100',
            'email'       => 'required|email|unique:members,email',
            'phone'       => 'required|string|max:30',
        ];
    }

    protected function rulesStep2(): array
    {
        return [
            'jahresbeitrag' => 'required|numeric|min:36',
            'kontoinhaber'  => 'required|string|max:255',
            'iban'          => 'required|string|max:34',
            'bic'           => 'nullable|string|max:11',
            'kreditinstitut' => 'nullable|string|max:255',
        ];
    }

    protected function rulesStep3(): array
    {
        return [
            'unterschrift'    => 'required|string',
            'sepa_zustimmung' => 'accepted',
            'dsgvo_zustimmung' => 'accepted',
        ];
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate($this->rulesStep1());
        } elseif ($this->step === 2) {
            $this->validate($this->rulesStep2());
        }

        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function submit(): void
    {
        $this->validate($this->rulesStep3());

        Member::create([
            'full_name'        => $this->full_name,
            'birth_date'       => $this->birth_date,
            'street'           => $this->street,
            'postal_code'      => $this->postal_code,
            'city'             => $this->city,
            'state'            => $this->state,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'jahresbeitrag'    => $this->jahresbeitrag,
            'kontoinhaber'     => $this->kontoinhaber,
            'iban'             => $this->iban,
            'bic'              => $this->bic ?: null,
            'kreditinstitut'   => $this->kreditinstitut ?: null,
            'unterschrift'     => $this->unterschrift,
            'sepa_zustimmung'  => $this->sepa_zustimmung,
            'dsgvo_zustimmung' => $this->dsgvo_zustimmung,
            'zustimmung_at'    => now(),
            'status'           => 'pending',
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.membership-form')
            ->layout('layouts.public');
    }
}
