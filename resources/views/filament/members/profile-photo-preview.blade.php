@php
    /** @var \App\Models\Member|null $member */
    $hasPhoto = $member?->profile_photo_path !== null;
    $photoUrl = $hasPhoto
        ? route('members.profile-photo', [
            'member' => $member,
            'v' => $member->profile_photo_uploaded_at?->getTimestamp() ?? time(),
        ])
        : null;
@endphp

<style>
    .ditib-profile-photo-preview {
        width: 30%;
    }

    .ditib-profile-photo-preview__frame {
        aspect-ratio: 1 / 1;
        width: 100%;
    }

    @media (max-width: 640px) {
        .ditib-profile-photo-preview {
            width: 50%;
        }
    }
</style>

<div class="flex justify-start">
    <div class="ditib-profile-photo-preview">
        @if ($hasPhoto)
            <img
                src="{{ $photoUrl }}"
                alt="Profilfoto von {{ $member->full_name }}"
                class="ditib-profile-photo-preview__frame rounded-lg border border-gray-200 object-cover shadow-sm"
                loading="lazy"
            >
        @endif
    </div>
</div>
