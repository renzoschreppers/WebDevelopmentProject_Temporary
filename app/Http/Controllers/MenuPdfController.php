<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class MenuPdfController extends Controller
{
    // Handle the incoming request.
    public function __invoke(Request $request): Response
    {
        $weekStart = $this->parseDate($request->query('week'))->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(4);

        $canSeeDrafts = auth()->check() && auth()->user()->admin;

        $menus = Menu::query()
            ->with(['dishes' => fn ($query) => $query
                ->where('is_available', true)
                ->with('dietaryTags')
                ->orderBy('dish_menu.sort_order')])
            ->whereBetween('date', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()])
            ->when(! $canSeeDrafts, fn ($query) => $query->where('is_published', true))
            ->get()
            ->keyBy(fn (Menu $menu) => $menu->date->toDateString());

        $days = collect(range(0, 4))->map(fn ($offset) => $weekStart->copy()->addDays($offset));

        $pdf = Pdf::loadView('pdf.week-menu', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'days' => $days,
            'menus' => $menus,
        ])->setPaper('a4', 'landscape');

        $filename = 'canteen-menu-'.$weekStart->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    protected function parseDate(?string $value): Carbon
    {
        try {
            return $value ? Carbon::parse($value) : Carbon::today();
        } catch (\Exception) {
            return Carbon::today();
        }
    }
}
