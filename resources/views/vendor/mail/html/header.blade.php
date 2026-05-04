@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel')
<img src="{{ asset('images/ditib_ahlen_logo.png') }}" class="logo" alt="DITIB Ahlen Logo" style="height: 60px; width: auto; max-width: 100%;">
@else
<img src="{{ asset('images/ditib_ahlen_logo.png') }}" class="logo" alt="{!! strip_tags($slot) !!}" style="height: 60px; width: auto; max-width: 100%;">
@endif
</a>
</td>
</tr>
