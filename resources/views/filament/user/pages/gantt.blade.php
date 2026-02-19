{{--
    Gantt DHTMLX — Project System
    DHTMLX Gantt caricato via CDN (free tier).
    wire:ignore protegge il div dal re-rendering Livewire.
--}}

<x-filament-panels::page>

    {{-- ── Toolbar zoom ──────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-2 mb-3">

        {{-- Pulsanti scala temporale --}}
        <div class="flex items-center gap-1 rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-800 shadow-sm">
            <button onclick="setZoom('hour')"   id="zoom-hour"
                    class="gantt-zoom-btn px-3 py-1 text-xs rounded-md text-gray-600 dark:text-gray-300 hover:bg-primary-500 hover:text-white transition-colors">
                Ora
            </button>
            <button onclick="setZoom('day')"    id="zoom-day"
                    class="gantt-zoom-btn px-3 py-1 text-xs rounded-md bg-primary-500 text-white transition-colors">
                Giorno
            </button>
            <button onclick="setZoom('week')"   id="zoom-week"
                    class="gantt-zoom-btn px-3 py-1 text-xs rounded-md text-gray-600 dark:text-gray-300 hover:bg-primary-500 hover:text-white transition-colors">
                Settimana
            </button>
            <button onclick="setZoom('month')"  id="zoom-month"
                    class="gantt-zoom-btn px-3 py-1 text-xs rounded-md text-gray-600 dark:text-gray-300 hover:bg-primary-500 hover:text-white transition-colors">
                Mese
            </button>
            <button onclick="setZoom('quarter')" id="zoom-quarter"
                    class="gantt-zoom-btn px-3 py-1 text-xs rounded-md text-gray-600 dark:text-gray-300 hover:bg-primary-500 hover:text-white transition-colors">
                Trimestre
            </button>
        </div>

        {{-- Espandi / Comprimi tutto --}}
        <div class="flex items-center gap-1 rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-white dark:bg-gray-800 shadow-sm">
            <button onclick="gantt.eachTask(function(t){ gantt.open(t.id); })"
                    class="px-3 py-1 text-xs rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                ⊞ Espandi tutti
            </button>
            <button onclick="gantt.eachTask(function(t){ gantt.close(t.id); })"
                    class="px-3 py-1 text-xs rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                ⊟ Comprimi tutti
            </button>
        </div>

        {{-- Oggi --}}
        <button onclick="gantt.showDate(new Date())"
                class="px-3 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            📅 Oggi
        </button>

        {{-- Indicatore salvataggio --}}
        <span id="gantt-save-status" class="ml-auto text-xs text-green-500 hidden">✓ Salvato</span>
        <span id="gantt-save-error"  class="ml-auto text-xs text-red-500 hidden">✗ Errore salvataggio</span>
    </div>

    {{-- ── Legenda ────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-4 mb-2 text-xs text-gray-500 dark:text-gray-400">
        <span class="flex items-center gap-1">
            <span class="inline-block w-4 h-3 rounded" style="background:#64748b"></span> Work Package
        </span>
        <span class="flex items-center gap-1">
            <span class="inline-block w-4 h-3 rounded" style="background:#3b82f6"></span> Task
        </span>
        <span class="flex items-center gap-1">
            <span class="inline-block w-4 h-3 rounded" style="background:#ef4444"></span> Percorso critico
        </span>
        @if(auth()->user()?->hasProjectPermission('tasks.edit'))
            <span class="italic">Trascina le barre per modificare le date &bull; Trascina il bordo per cambiare durata</span>
        @endif
    </div>

    {{-- ── CSS DHTMLX ─────────────────────────────────────────────────── --}}
    @once
    <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <style>
        .gantt_task_line.gantt_project {
            background-color: #64748b !important;
            border-color:     #475569 !important;
        }
        .gantt_task_progress { background: rgba(255,255,255,0.35) !important; }
        .gantt_task_line     { border-radius: 4px !important; }
        .gantt_grid_head_cell,
        .gantt_scale_cell    { font-size: 12px; }
        .gantt_task_content  { font-size: 11px; font-weight: 500; }
        .gantt_tooltip       { border-radius: 6px; font-size: 12px; padding: 8px 12px;
                               box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .gantt_weekend_cell  { background: rgba(0,0,0,0.03) !important; }
    </style>
    @endonce

    {{-- ── Contenitore Gantt (wire:ignore per isolare da Livewire) ────── --}}
    <div wire:ignore
         class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm"
         style="background:#fff">
        <div id="gantt_here" style="width:100%; height:calc(100vh - 290px); min-height:420px;"></div>
    </div>

    {{-- ── JavaScript DHTMLX ──────────────────────────────────────────── --}}
    @once
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
    @endonce

    <script>
    (function () {
        // Evita doppia inizializzazione su navigazioni Livewire
        if (window._ganttInitialized) return;

        const CSRF      = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
        const canEdit   = {{ auth()->user()?->hasProjectPermission('tasks.edit') ? 'true' : 'false' }};
        const WP_OFFSET = {{ App\Http\Controllers\GanttController::WP_OFFSET }};

        // ── Plugins (prima di init) ───────────────────────────────────────
        gantt.plugins({ tooltip: true });

        // ── Colonne griglia sinistra ──────────────────────────────────────
        gantt.config.columns = [
            {
                name: 'text', label: 'Attività', tree: true, width: 260, resize: true,
                template: function (task) { return task.text; }
            },
            { name: 'start_date', label: 'Inizio',  align: 'center', width: 90, resize: true },
            { name: 'duration',   label: 'gg',       align: 'center', width: 45 },
            {
                name: 'progress', label: '%', align: 'center', width: 45,
                template: function (task) {
                    return Math.round((task.progress || 0) * 100) + '%';
                }
            },
        ];

        // ── Formato date ──────────────────────────────────────────────────
        gantt.config.date_format = '%Y-%m-%d';

        // ── Aspetto generale ──────────────────────────────────────────────
        gantt.config.fit_tasks           = true;
        gantt.config.show_progress       = true;
        gantt.config.round_dnd_dates     = true;
        gantt.config.drag_project        = false;
        gantt.config.open_tree_initially = true;
        gantt.config.readonly            = !canEdit;

        // ── Readonly per barre WP ─────────────────────────────────────────
        gantt.attachEvent('onBeforeTaskDrag', function (id) {
            return parseInt(id) < WP_OFFSET; // false = blocca drag WP
        });
        gantt.attachEvent('onBeforeTaskChanged', function (id) {
            return parseInt(id) < WP_OFFSET;
        });

        // ── Tooltip ───────────────────────────────────────────────────────
        gantt.templates.tooltip_text = function (start, end, task) {
            const pct    = Math.round((task.progress || 0) * 100);
            const fmt    = gantt.templates.tooltip_date_format;
            const status = task.status ? '<br><b>Stato:</b> ' + task.status : '';
            return '<b>' + task.text + '</b>' +
                   '<br><b>Inizio:</b> ' + fmt(start) +
                   '<br><b>Fine:</b> '   + fmt(end) +
                   '<br><b>Avanzamento:</b> ' + pct + '%' + status;
        };

        // ── Evidenzia sabato/domenica ─────────────────────────────────────
        gantt.templates.timeline_cell_class = function (task, date) {
            const d = date.getDay();
            return (d === 0 || d === 6) ? 'gantt_weekend_cell' : '';
        };

        // ── Imposta scala temporale (chiamata PRIMA di init per i config) ─
        _applyZoomConfig('day');

        // ── Inizializzazione ──────────────────────────────────────────────
        gantt.init('gantt_here');
        window._ganttInitialized = true;

        // ── Caricamento dati JSON ─────────────────────────────────────────
        gantt.load('{{ route("gantt.data") }}', 'json');

        // ── Auto-save (solo se l'utente ha permessi di edit) ─────────────
        if (canEdit) {

            // Salva dopo drag (sposta/ridimensiona barra)
            gantt.attachEvent('onAfterTaskDrag', function (id) {
                if (parseInt(id) >= WP_OFFSET) return;
                _saveTask(id, gantt.getTask(id));
            });

            // Salva dopo drag della progress bar
            gantt.attachEvent('onProgressDragEnd', function (id) {
                if (parseInt(id) >= WP_OFFSET) return;
                _saveTask(id, gantt.getTask(id));
            });

            // Link create
            gantt.attachEvent('onAfterLinkAdd', function (id, link) {
                fetch('{{ route("gantt.link.create") }}', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body:    JSON.stringify({ source: link.source, target: link.target, type: link.type }),
                }).then(r => r.ok ? _showOk() : _showErr()).catch(_showErr);
            });

            // Link delete
            gantt.attachEvent('onAfterLinkDelete', function (id, link) {
                fetch('/gantt/link/' + link.source + '/' + link.target, {
                    method:  'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                }).then(r => r.ok ? _showOk() : _showErr()).catch(_showErr);
            });
        }

        // ── Helper: salva task via PUT ────────────────────────────────────
        function _saveTask(id, task) {
            const pad = n => String(n).padStart(2, '0');
            const fmt = d => d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
            fetch('/gantt/task/' + id, {
                method:  'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body:    JSON.stringify({
                    start_date: fmt(task.start_date),
                    end_date:   fmt(task.end_date),
                    duration:   task.duration,
                    progress:   task.progress,
                }),
            })
            .then(r => r.ok ? _showOk() : _showErr())
            .catch(_showErr);
        }

        // ── Helper: imposta scale senza render (usato prima di init) ─────
        function _applyZoomConfig(level) {
            switch (level) {
                case 'hour':
                    gantt.config.scales = [
                        { unit: 'day',  step: 1, format: '%d %M %Y' },
                        { unit: 'hour', step: 1, format: '%H:%i' },
                    ];
                    gantt.config.min_column_width = 40;
                    break;
                case 'day':
                    gantt.config.scales = [
                        { unit: 'month', step: 1, format: '%F %Y' },
                        { unit: 'day',   step: 1, format: '%d' },
                    ];
                    gantt.config.min_column_width = 38;
                    break;
                case 'week':
                    gantt.config.scales = [
                        { unit: 'month', step: 1, format: '%F %Y' },
                        { unit: 'week',  step: 1, format: function (d) {
                            const s = gantt.date.date_to_str('%d/%m');
                            return s(d) + ' – ' + s(gantt.date.add(d, 6, 'day'));
                        }},
                    ];
                    gantt.config.min_column_width = 90;
                    break;
                case 'month':
                    gantt.config.scales = [
                        { unit: 'year',  step: 1, format: '%Y' },
                        { unit: 'month', step: 1, format: '%M' },
                    ];
                    gantt.config.min_column_width = 70;
                    break;
                case 'quarter':
                    gantt.config.scales = [
                        { unit: 'year',    step: 1, format: '%Y' },
                        { unit: 'quarter', step: 1, format: function (d) {
                            return 'Q' + Math.ceil((d.getMonth() + 1) / 3);
                        }},
                    ];
                    gantt.config.min_column_width = 90;
                    break;
            }
        }

        // ── Indicatori UI salvataggio ─────────────────────────────────────
        function _showOk() {
            const ok  = document.getElementById('gantt-save-status');
            const err = document.getElementById('gantt-save-error');
            err.classList.add('hidden');
            ok.classList.remove('hidden');
            setTimeout(() => ok.classList.add('hidden'), 2500);
        }
        function _showErr() {
            const ok  = document.getElementById('gantt-save-status');
            const err = document.getElementById('gantt-save-error');
            ok.classList.add('hidden');
            err.classList.remove('hidden');
            setTimeout(() => err.classList.add('hidden'), 4000);
        }

    })();

    // ── setZoom globale (chiamata dagli onclick nei pulsanti) ─────────────
    function setZoom(level) {
        // Aggiorna stile pulsanti
        document.querySelectorAll('.gantt-zoom-btn').forEach(function (b) {
            b.classList.remove('bg-primary-500', 'text-white');
            b.classList.add('text-gray-600');
        });
        var active = document.getElementById('zoom-' + level);
        if (active) {
            active.classList.add('bg-primary-500', 'text-white');
            active.classList.remove('text-gray-600');
        }

        // Applica configurazione scale
        switch (level) {
            case 'hour':
                gantt.config.scales = [
                    { unit: 'day',  step: 1, format: '%d %M %Y' },
                    { unit: 'hour', step: 1, format: '%H:%i' },
                ];
                gantt.config.min_column_width = 40;
                break;
            case 'day':
                gantt.config.scales = [
                    { unit: 'month', step: 1, format: '%F %Y' },
                    { unit: 'day',   step: 1, format: '%d' },
                ];
                gantt.config.min_column_width = 38;
                break;
            case 'week':
                gantt.config.scales = [
                    { unit: 'month', step: 1, format: '%F %Y' },
                    { unit: 'week',  step: 1, format: function (d) {
                        var s = gantt.date.date_to_str('%d/%m');
                        return s(d) + ' – ' + s(gantt.date.add(d, 6, 'day'));
                    }},
                ];
                gantt.config.min_column_width = 90;
                break;
            case 'month':
                gantt.config.scales = [
                    { unit: 'year',  step: 1, format: '%Y' },
                    { unit: 'month', step: 1, format: '%M' },
                ];
                gantt.config.min_column_width = 70;
                break;
            case 'quarter':
                gantt.config.scales = [
                    { unit: 'year',    step: 1, format: '%Y' },
                    { unit: 'quarter', step: 1, format: function (d) {
                        return 'Q' + Math.ceil((d.getMonth() + 1) / 3);
                    }},
                ];
                gantt.config.min_column_width = 90;
                break;
        }

        // Re-render solo se gantt è già inizializzato
        if (window._ganttInitialized) {
            gantt.render();
        }
    }
    </script>

</x-filament-panels::page>
