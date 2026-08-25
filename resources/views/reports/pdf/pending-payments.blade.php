<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .table { w-full: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Listing ERP - Pending Payments Report</div>
    </div>
    <table class="table" style="width: 100%;">
        <thead><tr><th>Client</th><th>Mobile</th><th class="text-right">Outstanding Amount</th></tr></thead>
        <tbody>
            @foreach($data as $c)
                <tr>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->mobile }}</td>
                    <td class="text-right">₹{{ number_format($c->billingCycles->sum('balance'), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
