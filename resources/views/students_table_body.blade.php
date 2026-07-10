<tbody class="divide-y divide-slate-100 bg-white">
@forelse($studentsData as $session)
    <!-- Основная строка ученика -->
    <tr class="hover:bg-slate-50/80 font-medium transition" onclick="toggleDetails('details-{{ $session->id }}')">
        <td class="p-3">
            <span class="block font-bold text-slate-800">👤 {{ $session->student_name ?? 'Анонимный гость' }}</span>
            <span class="block text-[10px] text-slate-400">{{ $session->student_email ?? 'Нет аккаунта' }}</span>
        </td>
        <td class="p-3">
            <span class="block text-[11px] text-slate-700">Класс: <strong>{{ $session->grade }}</strong>, Тип: <strong>{{ $session->exam_type }}</strong></span>
            <span class="block text-[10px] text-slate-400">Год сдачи: {{ $session->target_year }} | {{ $session->region }}</span>
        </td>
        @php
            $scales = ['WM', 'VM', 'LR', 'AR', 'VR', 'SP', 'ATT'];
        @endphp
        @foreach($scales as $scale)
            @php $score = $session->cognitive[$scale] ?? null; @endphp
            <td class="p-3 text-center font-bold">
                @if(is_null($score))
                    <span class="text-slate-300">—</span>
                @else
                    <span class="{{ $score >= 70 ? 'text-emerald-600' : ($score >= 40 ? 'text-amber-500' : 'text-rose-600') }}">
                            {{ round($score) }}%
                        </span>
                @endif
            </td>
        @endforeach
    </tr>

    <!-- Раскрывающаяся строка с детальной успеваемостью, отношением и целями -->
    <tr id="details-{{ $session->id }}" class="hidden bg-slate-50/50">
        <td colspan="9" class="p-4 border-t border-b border-slate-200/60">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">

                <!-- Блок 1: Школьные оценки -->
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm space-y-1.5">
                    <span class="block font-bold text-slate-400 uppercase text-[10px] tracking-wider">🏫 Школьная успеваемость:</span>
                    <div class="space-y-1 divide-y divide-slate-100">
                        @forelse($session->grades_list as $g)
                            <div class="flex justify-between py-1 font-medium text-slate-700">
                                <span>{{ $g->title }}</span>
                                <span class="text-slate-500">Четверть: <strong>{{ $g->quarter_grade }}</strong> | Год: <strong>{{ $g->annual_grade }}</strong></span>
                            </div>
                        @empty
                            <p class="text-slate-400 italic">Оценки не заполнены</p>
                        @endforelse
                    </div>
                </div>

                <!-- Блок 2: Отношение к предметам -->
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm space-y-1.5">
                    <span class="block font-bold text-slate-400 uppercase text-[10px] tracking-wider">❤️ Отношение и интерес (шкала 1-5):</span>
                    <div class="space-y-1 divide-y divide-slate-100">
                        @forelse($session->attitudes_list as $a)
                            <div class="flex justify-between py-1 font-medium text-slate-700">
                                <span>📚 Предмет: {{ $a->subject_code }}</span>
                                <span class="font-bold text-blue-600">Ср. балл: {{ $a->avg_score }}</span>
                            </div>
                        @empty
                            <p class="text-slate-400 italic">Анкета интереса не заполнена</p>
                        @endforelse
                    </div>
                </div>

                <!-- Блок 3: Интересы и Трэк -->
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm space-y-2 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <span class="block font-bold text-slate-400 uppercase text-[10px] tracking-wider">🎯 Профессиональный фокус:</span>
                        <p class="text-slate-700 font-semibold bg-blue-50/50 p-2 rounded-lg border border-blue-100">
                            {{ $session->clusters ?? 'Не выбрано' }}
                        </p>
                    </div>
                    <div class="text-[10px] text-slate-400 italic pt-2 border-t border-slate-100">
                        Поступление: {{ $session->target_track ?? 'Не указано' }}
                    </div>
                </div>

            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="p-6 text-center text-slate-400">В системе пока нет завершенных сессий диагностики.</td>
    </tr>
@endforelse
</tbody>
