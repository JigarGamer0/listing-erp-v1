<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .subtitle { font-size: 12px; color: #666; }
        .table { w-full: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Listing ERP - Collection Report</div>
        <div class="subtitle">From: {{ $dateFrom }} To: {{ $dateTo }}</div>
    </div>
    <table class="table" style="width: 100%;">
        <thead><tr><th>Date</th><th>Client</th><th>Method</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
            @foreach($data as $p)
                <tr>
                    <td>{{ $p->payment_date->format('d/m/Y') }}</td>
                    <td>{{ $p->client->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($p->payment_method) }}</td>
                    <td class="text-right">₹{{ number_format($p->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
