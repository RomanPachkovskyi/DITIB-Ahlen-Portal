<?php

namespace App\Livewire;

use App\Events\MemberRegistered;
use App\Models\Member;
use App\Models\PostalCode;
use App\Services\MemberAuditLogger;
use App\Services\MemberDuplicateChecker;
use App\Services\ProfilePhotoService;
use App\Support\Iban;
use App\Support\Instagram;
use App\Support\MemberFieldRules;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class MembershipForm extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1 — Persönliche Daten
    public string $anrede = '';

    public string $full_name = '';

    public string $birth_date = '';

    public string $birth_place = '';

    public string $staatsangehoerigkeit = '';

    public int $familienangehoerige = 1;

    public bool $cenaze_fonu = false;

    public string $cenaze_fonu_nr = '';

    public bool $gemeinderegister = false;

    public string $beruf = '';

    public string $heimatstadt = '';

    // Step 2 — Adresse & Kontakt
    public string $street = '';

    public string $postal_code = '';

    public string $city = '';

    public string $state = '';

    public string $email = '';

    public string $phone = '';

    public string $instagram = '';

    // PLZ Autocomplete
    public array $plzSuggestions = [];

    public bool $showPlzDropdown = false;

    // Step 3 — Beitrag & Bankverbindung
    public float $monatsbeitrag = 10.00;

    public string $zahlungsart = 'dauerauftrag';

    public string $kontoinhaber = '';

    public string $iban = '';

    public string $bic = '';

    public string $kreditinstitut = '';

    // Step 3 (додатково) — Zustimmung
    // public string $unterschrift = ''; // TODO: Etap 4 — Unterschrift & Foto
    public bool $sepa_zustimmung = false;

    public bool $dsgvo_zustimmung = false;

    // Step 4 — Optionales Foto
    public ?TemporaryUploadedFile $croppedPhoto = null;

    public ?array $photoResult = null;

    public bool $profile_photo_zustimmung = false;

    public bool $submitted = false;

    public string $member_number = '';

    public array $stepsWithErrors = [];

    public bool $showValidationSummary = false;

    private const DUPLICATE_MEMBER_MESSAGE = 'Zu diesem Geburtsdatum und dieser Telefonnummer liegt bereits ein Mitgliedsantrag vor. Bitte wenden Sie sich an die Verwaltung, falls Sie eine weitere Person anmelden möchten.';

    protected function rulesStep1(): array
    {
        return [
            'anrede' => MemberFieldRules::anrede(),
            'full_name' => MemberFieldRules::fullName(),
            'birth_date' => MemberFieldRules::birthDate(),
            'birth_place' => MemberFieldRules::birthPlace(),
            'staatsangehoerigkeit' => MemberFieldRules::staatsangehoerigkeit(),
            'familienangehoerige' => MemberFieldRules::familienangehoerige(),
            'beruf' => MemberFieldRules::beruf(),
            'heimatstadt' => MemberFieldRules::heimatstadt(),
        ];
    }

    protected function rulesStep2(): array
    {
        return [
            'street' => MemberFieldRules::street(),
            'postal_code' => MemberFieldRules::postalCode(),
            'city' => MemberFieldRules::city(),
            'state' => MemberFieldRules::state(),
            'email' => MemberFieldRules::email(),
            'phone' => MemberFieldRules::phone(),
            'instagram' => MemberFieldRules::instagram(),
        ];
    }

    protected function messages(): array
    {
        return [
            // Pflichtfelder
            'required' => 'Dieses Feld ist erforderlich.',
            'accepted' => 'Ihre Zustimmung ist erforderlich.',

            // Schritt 1
            'anrede.required' => 'Bitte wählen Sie eine Anrede.',
            'anrede.in' => 'Ungültige Anrede.',
            'full_name.required' => 'Bitte geben Sie Ihren Namen ein.',
            'full_name.max' => 'Der Name ist zu lang (max. 255 Zeichen).',
            'full_name.regex' => 'Der Name darf nur Buchstaben und Bindestriche enthalten.',
            'birth_date.required' => 'Bitte geben Sie Ihr Geburtsdatum ein.',
            'birth_date.date' => 'Ungültiges Datumsformat.',
            'birth_date.before' => 'Das Geburtsdatum muss in der Vergangenheit liegen.',
            'birth_place.regex' => 'Nur Buchstaben und Bindestriche erlaubt.',
            'staatsangehoerigkeit.regex' => 'Nur Buchstaben und Bindestriche erlaubt.',
            'familienangehoerige.required' => 'Bitte geben Sie die Anzahl der Familienmitglieder ein.',
            'familienangehoerige.integer' => 'Bitte geben Sie eine ganze Zahl ein.',
            'familienangehoerige.min' => 'Mindestens 1 Familienmitglied erforderlich.',
            'beruf.regex' => 'Nur Buchstaben und Bindestriche erlaubt.',
            'heimatstadt.regex' => 'Nur Buchstaben und Bindestriche erlaubt.',

            // Schritt 2
            'street.required' => 'Bitte geben Sie Ihre Straße und Hausnummer ein.',
            'postal_code.required' => 'Bitte geben Sie Ihre Postleitzahl ein.',
            'postal_code.regex' => 'Die Postleitzahl muss aus 5 Ziffern bestehen.',
            'city.required' => 'Bitte geben Sie Ihren Ort ein.',
            'city.regex' => 'Nur Buchstaben und Bindestriche erlaubt.',
            'state.required' => 'Bitte geben Sie das Bundesland ein.',
            'state.regex' => 'Nur Buchstaben und Bindestriche erlaubt.',
            'email.required' => 'Bitte geben Sie Ihre E-Mail-Adresse ein.',
            'email.email' => 'Ungültige E-Mail-Adresse.',
            'phone.required' => 'Bitte geben Sie Ihre Telefonnummer ein.',
            'instagram.max' => 'Der Instagram-Eintrag ist zu lang.',

            // Schritt 3
            'monatsbeitrag.required' => 'Bitte geben Sie Ihren Monatsbeitrag ein.',
            'monatsbeitrag.numeric' => 'Der Beitrag muss eine Zahl sein.',
            'monatsbeitrag.min' => 'Der Mindestbeitrag beträgt 10,00 €.',
            'zahlungsart.required' => 'Bitte wählen Sie eine Zahlungsweise.',
            'zahlungsart.in' => 'Ungültige Zahlungsweise.',
            'kontoinhaber.required' => 'Bitte geben Sie den Kontoinhaber ein.',
            'kontoinhaber.regex' => 'Nur Buchstaben und Bindestriche erlaubt.',
            'iban.required' => 'Bitte geben Sie Ihre IBAN ein.',
            'iban.regex' => 'Ungültige IBAN.',
            'bic.max' => 'Die BIC darf maximal 11 Zeichen haben.',
            'kreditinstitut.regex' => 'Nur Buchstaben und Bindestriche erlaubt.',
            'sepa_zustimmung.accepted' => 'Bitte stimmen Sie dem SEPA-Lastschriftmandat zu.',
            'dsgvo_zustimmung.accepted' => 'Bitte stimmen Sie der Datenschutzerklärung zu.',

            // Schritt 4
            'croppedPhoto.image' => 'Das zugeschnittene Foto muss eine Bilddatei sein.',
            'croppedPhoto.mimes' => 'Das zugeschnittene Foto muss als JPEG vorliegen.',
            'croppedPhoto.max' => 'Das zugeschnittene Foto darf maximal 1 MB groß sein.',
            'profile_photo_zustimmung.accepted' => 'Bitte stimmen Sie der Speicherung des freiwilligen Profilbildes zu oder entfernen Sie das Foto.',
        ];
    }

    protected function rulesStep3(): array
    {
        $rules = [
            'monatsbeitrag' => MemberFieldRules::monatsbeitrag(),
            'zahlungsart' => MemberFieldRules::zahlungsart(),
            'dsgvo_zustimmung' => 'accepted',
        ];

        if ($this->zahlungsart === 'lastschrift') {
            $rules['sepa_zustimmung'] = 'accepted';
            $rules['kontoinhaber'] = MemberFieldRules::kontoinhaber();
            $rules['iban'] = MemberFieldRules::iban();
            $rules['bic'] = MemberFieldRules::bic();
            $rules['kreditinstitut'] = MemberFieldRules::kreditinstitut();
        }

        return $rules;
    }

    protected function rulesStep4(): array
    {
        $rules = [
            'croppedPhoto' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg',
                'max:1024',
            ],
        ];

        if ($this->croppedPhoto !== null) {
            $rules['profile_photo_zustimmung'] = 'accepted';
        }

        return $rules;
    }

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => $this->rulesStep1(),
            2 => $this->rulesStep2(),
            3 => $this->rulesStep3(),
            4 => $this->rulesStep4(),
            default => [],
        };
    }

    protected function validateStep(int $step): bool
    {
        if ($step === 2) {
            $this->phone = PhoneNumber::format($this->phone);
            $instagram = Instagram::normalize($this->instagram);
            if ($instagram !== null || trim($this->instagram) === '') {
                $this->instagram = $instagram ?? '';
            }
        }

        if ($step === 3 && $this->zahlungsart === 'lastschrift') {
            $this->iban = Iban::format($this->iban);
        }

        $rules = $this->rulesForStep($step);
        $fields = array_keys($rules);
        $data = [];

        foreach ($fields as $field) {
            $data[$field] = $this->{$field};
        }

        $validator = Validator::make($data, $rules, $this->messages());
        $this->resetValidation($fields);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            $this->stepsWithErrors[$step] = true;
            $this->showValidationSummary = true;

            return false;
        }

        unset($this->stepsWithErrors[$step]);

        if ($this->stepsWithErrors === []) {
            $this->showValidationSummary = false;
        }

        return true;
    }

    public function updatedPostalCode(string $value): void
    {
        $value = preg_replace('/[^0-9]/', '', $value);

        if (strlen($value) >= 2) {
            // Load all matches, then group by PLZ and pick the shortest ort
            // (company names like "Sparkasse Münsterland Ost Hauptstelle Ahlen" are always longer than city names)
            $all = PostalCode::where('plz', 'like', $value.'%')
                ->orderBy('plz')
                ->get(['plz', 'ort', 'bundesland']);

            $grouped = [];
            foreach ($all as $row) {
                $plz = $row->plz;
                if (! isset($grouped[$plz]) || mb_strlen($row->ort) < mb_strlen($grouped[$plz]['ort'])) {
                    $grouped[$plz] = [
                        'plz' => $plz,
                        'ort' => $row->ort,
                        'bundesland' => $row->bundesland,
                    ];
                }
            }

            $this->plzSuggestions = array_values(array_slice($grouped, 0, 10));
            $this->showPlzDropdown = count($this->plzSuggestions) > 0;
        } else {
            $this->plzSuggestions = [];
            $this->showPlzDropdown = false;
        }

        // Auto-fill on exact 5-digit match
        if (strlen($value) === 5) {
            $all = PostalCode::where('plz', $value)->get(['ort', 'bundesland']);
            if ($all->isNotEmpty()) {
                // Pick shortest ort = most likely city name
                $best = $all->sortBy(fn ($r) => mb_strlen($r->ort))->first();
                $this->city = $best->ort;
                $this->state = $best->bundesland;
                $this->showPlzDropdown = false;
                $this->plzSuggestions = [];
            }
        }
    }

    public function selectPlz(string $plz, string $ort, string $bundesland): void
    {
        $this->postal_code = $plz;
        $this->city = $ort;
        $this->state = $bundesland;
        $this->plzSuggestions = [];
        $this->showPlzDropdown = false;

        // Clear any validation errors that appeared while typing (partial PLZ)
        $this->resetValidation(['postal_code', 'city', 'state']);
    }

    public function closePlzDropdown(): void
    {
        $this->showPlzDropdown = false;
        $this->plzSuggestions = [];
    }

    public function updatedIban(string $value): void
    {
        $this->iban = Iban::format($value);
        $this->resetValidation('iban');
    }

    public function updatedPhone(string $value): void
    {
        $this->phone = PhoneNumber::format($value);
        $this->resetValidation('phone');
    }

    public function updatedInstagram(string $value): void
    {
        $this->instagram = Instagram::normalize($value) ?? $value;
        $this->resetValidation('instagram');
    }

    public function selectMonatsbeitrag(float $amount): void
    {
        $this->monatsbeitrag = $amount;
        $this->resetValidation('monatsbeitrag');
    }

    public function acceptCroppedPhoto(): void
    {
        $this->validate([
            'croppedPhoto' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg',
                'max:1024',
            ],
        ], $this->messages());

        if ($this->croppedPhoto === null) {
            throw ValidationException::withMessages([
                'croppedPhoto' => 'Bitte übernehmen Sie zuerst ein Foto.',
            ]);
        }

        $dimensions = getimagesize($this->croppedPhoto->getRealPath()) ?: null;

        $this->photoResult = [
            'mime' => $this->croppedPhoto->getMimeType(),
            'size' => $this->croppedPhoto->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
        ];

        unset($this->stepsWithErrors[4]);
        $this->resetValidation('croppedPhoto');
    }

    public function removeCroppedPhoto(): void
    {
        $this->reset(['croppedPhoto', 'photoResult', 'profile_photo_zustimmung']);
        $this->resetValidation(['croppedPhoto', 'profile_photo_zustimmung']);
        unset($this->stepsWithErrors[4]);
    }

    public function updated($propertyName): void
    {
        $rules = $this->rulesForStep($this->step);

        if (array_key_exists($propertyName, $rules)) {
            $this->validateOnly($propertyName, $rules, $this->messages());
        }
    }

    public function nextStep(): void
    {
        if ($this->step === 3) {
            if (! $this->validateRegistrationBeforePhoto()) {
                return;
            }

            $this->step = 4;

            return;
        }

        $this->validateStep($this->step);
        $this->step = min(4, $this->step + 1);
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function submit(): void
    {
        $firstInvalidStep = null;

        foreach ([1, 2, 3, 4] as $step) {
            if (! $this->validateStep($step) && $firstInvalidStep === null) {
                $firstInvalidStep = $step;
            }
        }

        if ($firstInvalidStep !== null) {
            $this->step = $firstInvalidStep;
            $this->showValidationSummary = true;

            return;
        }

        if ($this->hasDuplicateMember()) {
            $this->markDuplicateMemberError();

            return;
        }

        try {
            $member = DB::transaction(function () {
                $member = Member::create([
                    'anrede' => $this->anrede,
                    'full_name' => $this->full_name,
                    'birth_date' => $this->birth_date,
                    'birth_place' => $this->birth_place ?: null,
                    'staatsangehoerigkeit' => $this->staatsangehoerigkeit ?: null,
                    'familienangehoerige' => $this->familienangehoerige,
                    'cenaze_fonu' => $this->cenaze_fonu,
                    'cenaze_fonu_nr' => $this->cenaze_fonu ? ($this->cenaze_fonu_nr ?: null) : null,
                    'gemeinderegister' => $this->gemeinderegister,
                    'beruf' => $this->beruf ?: null,
                    'heimatstadt' => $this->heimatstadt ?: null,
                    'street' => $this->street,
                    'postal_code' => $this->postal_code,
                    'city' => $this->city,
                    'state' => $this->state,
                    'email' => $this->email,
                    'phone' => PhoneNumber::normalize($this->phone),
                    'instagram' => Instagram::normalize($this->instagram),
                    'profile_photo_zustimmung' => $this->croppedPhoto !== null && $this->profile_photo_zustimmung,
                    'profile_photo_zustimmung_at' => $this->croppedPhoto !== null && $this->profile_photo_zustimmung ? now() : null,
                    'monatsbeitrag' => $this->monatsbeitrag,
                    'zahlungsart' => $this->zahlungsart,
                    'kontoinhaber' => $this->zahlungsart === 'lastschrift' ? $this->kontoinhaber : null,
                    'iban' => $this->zahlungsart === 'lastschrift' ? Iban::normalize($this->iban) : null,
                    'bic' => $this->zahlungsart === 'lastschrift' ? ($this->bic ?: null) : null,
                    'kreditinstitut' => $this->zahlungsart === 'lastschrift' ? ($this->kreditinstitut ?: null) : null,
                    'unterschrift' => '', // TODO: canvas підпис
                    'sepa_zustimmung' => $this->zahlungsart === 'lastschrift',
                    'sepa_zustimmung_at' => $this->zahlungsart === 'lastschrift' ? now() : null,
                    'dsgvo_zustimmung' => $this->dsgvo_zustimmung,
                    'zustimmung_at' => now(),
                    'status' => 'pending',
                ]);

                if ($this->croppedPhoto !== null) {
                    app(ProfilePhotoService::class)->store($member, $this->croppedPhoto);
                }

                return $member;
            });
        } catch (ValidationException $exception) {
            $this->step = 4;
            $this->stepsWithErrors[4] = true;
            $this->showValidationSummary = true;

            throw $exception;
        }

        $this->member_number = $member->member_number;
        $this->submitted = true;

        app(MemberAuditLogger::class)->created($member, 'client');

        MemberRegistered::dispatch($member);
    }

    protected function validateRegistrationBeforePhoto(): bool
    {
        $firstInvalidStep = null;

        foreach ([1, 2, 3] as $step) {
            if (! $this->validateStep($step) && $firstInvalidStep === null) {
                $firstInvalidStep = $step;
            }
        }

        if ($firstInvalidStep !== null) {
            $this->step = $firstInvalidStep;
            $this->showValidationSummary = true;

            return false;
        }

        if ($this->hasDuplicateMember()) {
            $this->markDuplicateMemberError();

            return false;
        }

        return true;
    }

    protected function hasDuplicateMember(): bool
    {
        return app(MemberDuplicateChecker::class)
            ->findByBirthDateAndPhone($this->birth_date, $this->phone) !== null;
    }

    protected function markDuplicateMemberError(): void
    {
        $this->step = 2;
        $this->stepsWithErrors[2] = true;
        $this->showValidationSummary = true;
        $this->addError('phone', self::DUPLICATE_MEMBER_MESSAGE);
    }

    public function render()
    {
        return view('livewire.membership-form')
            ->layout('layouts.public');
    }
}
