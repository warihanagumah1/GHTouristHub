<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center">
<p class="footer-heading">{{ config('app.name', 'GH Tourist Hub') }}</p>
<p class="footer-meta">Questions? Reply to this email or contact support@ghtouristhub.com.</p>
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
