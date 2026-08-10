<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Canteen menu</title>

    <style>
        @page { margin: 1.2cm; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #262626;
            margin: 0;
        }

        .header { margin-bottom: 12px; border-bottom: 2px solid #f59e0b; padding-bottom: 8px; }
        .title { font-size: 18pt; font-weight: bold; margin: 0; }
        .subtitle { font-size: 10pt; color: #737373; margin: 3px 0 0; }

        table.week { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.week th {
            background: #f59e0b;
            color: #ffffff;
            padding: 6px 5px;
            text-align: left;
            font-size: 9pt;
            border: 1px solid #f59e0b;
        }
        table.week th .date { display: block; font-weight: normal; font-size: 7.5pt; }
        table.week td {
            border: 1px solid #e5e5e5;
            padding: 6px 5px;
            vertical-align: top;
        }

        .course { font-size: 6.5pt; text-transform: uppercase; letter-spacing: 0.4px; color: #a3a3a3; margin: 6px 0 2px; }
        .course:first-child { margin-top: 0; }

        .dish { margin-bottom: 4px; }
        .dish-name { font-weight: bold; }
        .price { color: #525252; }
        .old-price { color: #a3a3a3; text-decoration: line-through; }
        .tags { color: #737373; font-size: 6.5pt; font-style: italic; }

        .note { background: #fef3c7; padding: 4px 5px; margin-bottom: 6px; font-size: 7pt; font-style: italic; }
        .empty { color: #a3a3a3; font-style: italic; }

        .footer { margin-top: 10px; font-size: 7pt; color: #a3a3a3; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <p class="title">CanteenMenu</p>
    <p class="subtitle">
        Week of {{ $weekStart->isoFormat('D MMMM') }} — {{ $weekEnd->isoFormat('D MMMM YYYY') }}
    </p>
</div>

<table class="week">
    <thead>
    <tr>
        @foreach ($days as $day)
            <th>
                {{ $day->isoFormat('dddd') }}
                <span class="date">{{ $day->isoFormat('D MMMM') }}</span>
            </th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    <tr>
        @foreach ($days as $day)
            @php $menu = $menus[$day->toDateString()] ?? null; @endphp

            <td>
                @if ($menu && $menu->note)
                    <div class="note">{{ $menu->note }}</div>
                @endif

                @if ($menu && $menu->dishes->isNotEmpty())
                    @php $byCourse = $menu->dishes->groupBy(fn ($dish) => $dish->pivot->course); @endphp

                    @foreach (\App\Models\Menu::COURSES as $course => $label)
                        @if ($byCourse->has($course))
                            <div class="course">{{ $label }}</div>

                            @foreach ($byCourse[$course] as $dish)
                                <div class="dish">
                                    <span class="dish-name">{{ $dish->name }}</span>

                                    @if ($dish->hasPriceOverride())
                                        <span class="old-price">{{ $dish->price_formatted }}</span>
                                        <span class="price">{{ $dish->priceForMenu() }}</span>
                                    @else
                                        <span class="price">{{ $dish->priceForMenu() }}</span>
                                    @endif

                                    @if ($dish->dietaryTags->isNotEmpty())
                                        <div class="tags">{{ $dish->dietaryTags->pluck('name')->implode(', ') }}</div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <span class="empty">No menu planned.</span>
                @endif
            </td>
        @endforeach
    </tr>
    </tbody>
</table>

<div class="footer">
    Generated on {{ now()->isoFormat('D MMMM YYYY, HH:mm') }} — prices in euro, subject to change.
</div>

</body>
</html>
