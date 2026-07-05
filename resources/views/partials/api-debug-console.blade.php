@if(app()->isLocal())
@php
    $logs = \App\Services\ApiLogCollector::all();
@endphp

<div id="myadsApiDebugWidget" style="position: fixed; bottom: 16px; right: 16px; z-index: 999999; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;">
    <!-- Floating Trigger Button -->
    <button type="button" id="myadsDebugTrigger" style="background: #E1000C; color: white; border: none; padding: 10px 16px; border-radius: 30px; font-weight: 700; font-size: 13px; cursor: pointer; box-shadow: 0 4px 16px rgba(225, 0, 12, 0.3); display: flex; align-items: center; gap: 8px; transition: transform 0.2s ease, background-color 0.2s ease;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="16 18 22 12 16 6"></polyline>
            <polyline points="8 6 2 12 8 18"></polyline>
        </svg>
        API Hits: <span style="background: white; color: #E1000C; border-radius: 50%; padding: 1px 6px; font-size: 11px;">{{ count($logs) }}</span>
    </button>

    <!-- Slide-Up Drawer -->
    <div id="myadsDebugDrawer" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; height: 400px; background: #ffffff; border-top: 3px solid #E1000C; box-shadow: 0 -4px 32px rgba(0,0,0,0.15); display: flex; flex-direction: column; animation: slideUp 0.3s ease;">
        <!-- Drawer Header -->
        <div style="background: #f8f9fa; padding: 12px 20px; border-bottom: 1px solid #eaeaea; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <strong style="color: #333; font-size: 14px;">MyAds API Debug Console</strong>
                <span style="background: #E1000C; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">Local Environment</span>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="button" id="myadsDebugClear" style="background: transparent; border: none; color: #888; font-size: 12px; cursor: pointer; hover: { color: #E1000C }">Clear</button>
                <button type="button" id="myadsDebugClose" style="background: #eaeaea; border: none; width: 24px; height: 24px; border-radius: 50%; color: #333; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>
        </div>

        <!-- Drawer Content Body -->
        <div style="display: flex; flex: 1; overflow: hidden;">
            <!-- Left List View -->
            <div style="width: 320px; border-right: 1px solid #eaeaea; overflow-y: auto; background: #fafafa;">
                @if(empty($logs))
                    <div style="padding: 20px; text-align: center; color: #888; font-size: 12px;">
                        No API hits captured during this page request.
                    </div>
                @else
                    @foreach($logs as $index => $log)
                        @php
                            $isError = $log['status'] >= 400;
                            $methodColor = match($log['method']) {
                                'POST' => '#28a745',
                                'PUT' => '#fd7e14',
                                'DELETE' => '#dc3545',
                                default => '#007bff'
                            };
                            // Get last parts of URL for cleaner display
                            $parsedUrl = parse_url($log['url'], PHP_URL_PATH);
                            $cleanPath = $parsedUrl ? $parsedUrl : $log['url'];
                        @endphp
                        <div class="debug-log-item" data-index="{{ $index }}" style="padding: 12px 16px; border-bottom: 1px solid #eaeaea; cursor: pointer; transition: background-color 0.2s ease; display: flex; flex-direction: column; gap: 4px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-family: monospace; font-weight: 700; color: {{ $methodColor }}; font-size: 11px;">{{ $log['method'] }}</span>
                                <span style="font-size: 11px; font-weight: 700; color: {{ $isError ? '#dc3545' : '#28a745' }}; background: {{ $isError ? '#fde8eb' : '#eafaf1' }}; padding: 1px 6px; border-radius: 4px;">
                                    {{ $log['status'] }}
                                </span>
                            </div>
                            <div style="font-size: 12px; color: #333; font-family: monospace; word-break: break-all;" title="{{ $log['url'] }}">
                                {{ $cleanPath }}
                            </div>
                            <span style="font-size: 10px; color: #999; text-align: right;">{{ $log['timestamp'] }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Right Detail View -->
            <div id="myadsDebugDetail" style="flex: 1; padding: 20px; overflow-y: auto; background: #ffffff;">
                <div style="text-align: center; color: #888; margin-top: 100px; font-size: 13px;">
                    Select an API request from the list to view full payload & response details.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes slideUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    .debug-log-item:hover {
        background-color: #f1f3f5 !important;
    }
    .debug-log-item.active {
        background-color: #e8f0fe !important;
        border-left: 4px solid #E1000C;
    }
</style>

<script>
    (() => {
        const trigger = document.getElementById('myadsDebugTrigger');
        const drawer = document.getElementById('myadsDebugDrawer');
        const closeBtn = document.getElementById('myadsDebugClose');
        const clearBtn = document.getElementById('myadsDebugClear');
        const detailView = document.getElementById('myadsDebugDetail');
        const logItems = document.querySelectorAll('.debug-log-item');
        const logsData = @json($logs);

        // Hide drawer initially in layout style
        drawer.style.display = 'none';

        trigger.addEventListener('click', () => {
            drawer.style.display = 'flex';
            trigger.style.display = 'none';
        });

        closeBtn.addEventListener('click', () => {
            drawer.style.display = 'none';
            trigger.style.display = 'flex';
        });

        clearBtn.addEventListener('click', () => {
            detailView.innerHTML = `<div style="text-align: center; color: #888; margin-top: 100px; font-size: 13px;">Cleared.</div>`;
            logItems.forEach(item => item.remove());
        });

        logItems.forEach(item => {
            item.addEventListener('click', () => {
                logItems.forEach(el => el.classList.remove('active'));
                item.classList.add('active');

                const idx = item.getAttribute('data-index');
                const log = logsData[idx];

                detailView.innerHTML = `
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div>
                            <h4 style="margin: 0 0 4px; color: #1a1a1a; font-size: 14px;">Endpoint URL</h4>
                            <code style="display: block; background: #f8f9fa; padding: 10px; border-radius: 6px; font-size: 12px; color: #dc3545; word-break: break-all; border: 1px solid #eaeaea;">
                                ${log.url}
                            </code>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <h4 style="margin: 0 0 6px; color: #1a1a1a; font-size: 13px;">Request Parameters / Body</h4>
                                <pre style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 6px; font-size: 11px; margin: 0; overflow-x: auto; max-height: 220px; font-family: monospace;">${JSON.stringify(log.payload, null, 2) || 'Empty payload'}</pre>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 6px; color: #1a1a1a; font-size: 13px;">Response Response</h4>
                                <pre style="background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 6px; font-size: 11px; margin: 0; overflow-x: auto; max-height: 220px; font-family: monospace;">${JSON.stringify(log.response, null, 2) || 'Empty response'}</pre>
                            </div>
                        </div>
                    </div>
                `;
            });
        });
    })();
</script>
@endif
