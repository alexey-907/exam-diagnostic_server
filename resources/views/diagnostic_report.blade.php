<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Результаты профориентационной диагностики</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Times New Roman', sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #0f172a;
        }
        .header p {
            font-size: 10px;
            color: #64748b;
            margin: 4px 0 0 0;
        }
        .section-title {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
            color: #0284c7;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .profile-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .profile-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        .cognitive-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .cognitive-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            margin-bottom: 6px;
            border-radius: 6px;
        }
        .bar-container {
            width: 100%;
            background: #e2e8f0;
            height: 6px;
            border-radius: 3px;
            margin-top: 4px;
            overflow: hidden;
        }
        .bar-fill {
            height: 6px;
            border-radius: 3px;
        }
        .rec-box {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 12px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 15px;
        }
        .badge {
            background: #0284c7;
            color: white;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
        }
        .list-item {
            margin-bottom: 4px;
            padding-left: 10px;
        }
    </style>
</head>
<body>

<!-- Шапка отчета -->
<div class="header">
    <h1>КАРТА НАУЧНОЙ ПРОФОРИЕНТАЦИОННОЙ ДИАГНОСТИКИ</h1>
    <p>Сгенерировано Scoring Engine автоматически • {{ date('d.m.Y H:i') }}</p>
</div>

<!-- Параметры ученика -->
<table class="profile-table">
    <tr>
        <td style="width: 50%;">
            <strong>Ученик:</strong> {{ $session->student_name ?? 'Анонимный профиль' }}<br>
            <strong>Email:</strong> {{ $session->student_email ?? 'Запись без привязки к аккаунту' }}<br>
            <strong>Регион:</strong> {{ $session->region }}
        </td>
        <td style="width: 50%; text-align: right;">
            <strong>Класс обучения:</strong> {{ $session->grade }} класс<br>
            <strong>Целевой экзамен:</strong> {{ $session->exam_type }} (Год сдачи: {{ $session->target_year }})<br>
            <strong>Траектория поступления:</strong> {{ $session->target_track }}
        </td>
    </tr>
</table>

<!-- РЕКОМЕНДАЦИЯ ПРЕДМЕТОВ (Интеллектуальный план из ТЗ) -->
<div class="section-title">Рекомендуемый оптимальный профиль экзаменов</div>
@if($bestSet)
    <div class="rec-box">
        <span class="badge">{{ $bestSet['track'] }} (Соответствие: {{ $bestSet['score'] }}%)</span>
        <p style="font-size: 14px; font-weight: bold; margin: 8px 0 4px 0; letter-spacing: 0.5px;">
            {{ implode('   +   ', $bestSet['set']) }}
        </p>
        <p style="margin: 4px 0 0 0; font-style: italic; font-size: 11px; color: #475569;">
            {{ $bestSet['explanation']['summary'] }}
        </p>
    </div>

    <div style="margin-bottom: 10px;">
        <strong>Пошаговый план самостоятельной подготовки по часам:</strong>
        <div style="margin-top: 6px;">
            @foreach($bestSet['explanation']['firstActions'] as $action)
                <div class="list-item">• {{ $action }}</div>
            @endforeach
        </div>
    </div>
@else
    <p class="list-item" style="color: #64748b;">Ошибка формирования рекомендательного набора предметов.</p>
@endif

<!-- КОГНИТИВНЫЙ ПРОФИЛЬ -->
<div class="section-title">Объективные показатели когнитивного профиля</div>
<div class="cognitive-grid">
    @php
        $cogMap = [
            'WM'  => 'Рабочая память (WM)',
            'VM'  => 'Вербальная память (VM)',
            'LR'  => 'Логическое мышление (LR)',
            'AR'  => 'Абстрактное мышление (AR)',
            'VR'  => 'Вербальное понимание текста (VR)',
            'SP'  => 'Пространственное мышление (SP)',
            'ATT' => 'Саморегуляция и дисциплина (SELF)'
        ];
    @endphp

    @foreach($cogMap as $code => $name)
        @php
            $score = $cognitive[$code] ?? 0;
            $color = $score >= 70 ? '#10b981' : ($score >= 40 ? '#f59e0b' : '#f43f5e');
            $status = $score >= 70 ? 'Сильная сторона' : ($score >= 40 ? 'Средний уровень' : 'Зона риска');
        @endphp
        <div class="cognitive-card">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-weight: bold; font-size: 11px;">{{ $name }}</td>
                    <td style="text-align: right; font-weight: bold; color: {{ $color }};">{{ round($score) }} / 100</td>
                </tr>
            </table>
            <div class="bar-container">
                <div class="bar-fill" style="width: {{ $score }}%; bg-color: {{ $color }}; background-color: {{ $color }};"></div>
            </div>
            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Статус шкалы навыка: <strong>{{ $status }}</strong></div>
        </div>
    @endforeach
</div>

<div style="text-align: center; margin-top: 30px; font-size: 9px; color: #94a3b8; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
    Данный отчет является интеллектуальной собственностью тестирующей платформы Экзамены. {{ date('Y') }} г.
</div>

</body>
</html>
