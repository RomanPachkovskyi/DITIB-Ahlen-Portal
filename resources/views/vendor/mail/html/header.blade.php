@props(['url'])
@php
    $brandName = config('mail.brand.name', config('app.name'));
    $brandUrl = config('mail.brand.url', $url);
    $logoUrl = config('mail.brand.logo_url');
@endphp
<tr>
<td class="header">
<a href="{{ $brandUrl }}" style="display: inline-block; text-decoration: none;">
    <img src="{{ $logoUrl }}" class="logo" alt="{{ $brandName }}" width="107" height="60" style="display: block; height: 60px; width: auto; border: 0; outline: none; text-decoration: none;">
    <span class="brand-name">{{ $brandName }}</span>
</a>
</td>
</tr>
